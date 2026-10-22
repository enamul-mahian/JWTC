<?php

namespace App\Http\Controllers\Employer;

use App\Enum\JobStatus;
use App\Enum\UserType;
use App\Enum\StatusType;
use App\Enum\SubscriptionType;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreJobRequest;
use App\Http\Requests\UpdateJobRequest;
use App\Models\Application;
use App\Models\Country;
use App\Models\Dashboard\Company;
use App\Models\Dashboard\DegreeLavel;
use App\Models\Dashboard\JobCategory;
use App\Models\Job;
use App\Models\JobType;
use App\Models\Skill;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravelcm\Subscriptions\Models\Subscription;

class JobController extends Controller
{
    private function employerCompany(): Company
    {
        $user = Auth::user();
        abort_unless($user && $user->user_type === UserType::EMPLOYER
            && $user->can('post-job'), 403);

        return Company::where('user_id', $user->id)->firstOrFail();
    }

    private function requireOwnedJob(Job $job): void
    {
        $company = $this->employerCompany();
        abort_unless((int) $job->user_id === (int) Auth::id()
            && (int) $job->company_id === (int) $company->id, 404);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $company = $this->employerCompany();

        // $subscription = Subscription::with('plan')->where('subscriber_id', Auth::user()->id)->first();

        // $lastDate = '';

        // if (!$subscription) {
        //     return redirect()->route('subscriptions')->withError('Please buy a subscription first.');
        // } else if (Carbon::now()->gt($lastDate)) {
        //     return redirect()->route('subscriptions')->withError('Your subscription has expired. Please renew.');
        // }

        // if ($subscription->plan->plan_type == SubscriptionType::MONTHLY) {
        //     $lastDate = $subscription?->updated_at->addMonth();
        // } else if ($subscription->plan->plan_type == SubscriptionType::HALFYEARLY) {
        //     $lastDate = $subscription?->updated_at->addMonths(6);
        // } else if ($subscription->plan->plan_type == SubscriptionType::YEARLY) {
        //     $lastDate = $subscription?->updated_at->addYear();
        // }

        $job = new Job();
        // Laravel's per-session old input replaces the unsafe global cache key.
        $oldValue = old('title') !== null ? old() : null;
        $title = __('Create job');
        $jobCategory = JobCategory::where('status', StatusType::ACTIVE)->select('id', 'name')->get();
        $degreeLevel = DegreeLavel::where('status', StatusType::ACTIVE)->select('id', 'name')->get();
        $locations = Country::where('status', StatusType::ACTIVE)->select('id', 'name')->get();
        $jobTypes = JobType::where('status', StatusType::ACTIVE)->select('id', 'name')->get();

        return view('frontend.pages.job.create', compact('job', 'title', 'company', 'jobCategory', 'degreeLevel', 'locations', 'oldValue', 'jobTypes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreJobRequest $request)
    {
        $company = $this->employerCompany();

        // Only validated fields can be accepted; ownership and moderation
        // flags are always set on the server, never from request payloads.
        $jobs = $request->validated();

        $jobs['slug'] = Str::slug($request->title) . '-' . Str::lower(Str::random(10));
        $jobs['employment_status'] = json_encode($request->employment_status);
        $jobs['job_level'] = json_encode($request->job_level);
        $jobs['work_place'] = json_encode($request->work_place);
        $jobs['other_benefits'] = json_encode($request->other_benefits);
        $jobs['skills'] = json_encode($request->skills);
        $jobs['gender'] = json_encode($request->gender);
        $jobs['job_status'] = JobStatus::PENDING;
        $jobs['user_id'] = Auth::id();
        $jobs['company_id'] = $company->id;
        $jobs['is_publish'] = 0; // Admin must explicitly approve a new job.

        Job::create($jobs);

        return redirect()->route('employer.dashboard')->with([
            'success' => 'job post success',
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show($slug)
    {
        $jobDetails = Job::with('company', 'degree_level', 'degree_name', 'country')
            ->withTrashed()->where('slug', $slug)->firstOrFail();

        // Live, approved jobs may be read by authenticated job seekers;
        // drafts and archived jobs may only be previewed by their employer.
        if ($jobDetails->trashed() || (int) $jobDetails->is_publish !== 1) {
            $this->requireOwnedJob($jobDetails);
        }
        $other_benefits = json_decode($jobDetails?->other_benefits);
        $workPlaces = json_decode($jobDetails?->work_place);
        $employment_status = JobType::whereIn('id', json_decode($jobDetails->employment_status ?? '[]'))->select('name')->get();
        $genders = json_decode($jobDetails->gender);
        $skills = Skill::whereIn('id', json_decode($jobDetails->skills))->select('skill_name')->get();

        return view('frontend.pages.job.details', compact('jobDetails', 'other_benefits', 'workPlaces', 'employment_status', 'genders', 'skills'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function jobSkills($id)
    {
        $skill = Skill::where('job_category_id', $id)->select('id', 'skill_name')->get();
        return response()->json($skill);
    }

    public function edit(Job $job)
    {
        $this->requireOwnedJob($job);

        $title = __('Update job');
        $company = Company::where('id', $job->company_id)->first();
        $jobCategory = JobCategory::where('status', StatusType::ACTIVE)->get();
        $degreeLevel = DegreeLavel::where('status', StatusType::ACTIVE)->select('id', 'name')->get();
        $skills = json_decode($job->skills);
        $locations = Country::where('status', StatusType::ACTIVE)->select('id', 'name')->get();
        $jobTypes = JobType::where('status', StatusType::ACTIVE)->select('id', 'name')->get();

        return view('frontend.pages.job.edit', compact(
            'title',
            'company',
            'jobCategory',
            'degreeLevel',
            'job',
            'locations',
            'jobTypes'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateJobRequest $request, Job $job)
    {
        $this->requireOwnedJob($job);
        $jobs = $request->validated();
        // Preserve the original slug for historic links and SEO.
        $jobs['employment_status'] = json_encode($request->employment_status);
        $jobs['job_level'] = json_encode($request->job_level);
        $jobs['work_place'] = json_encode($request->work_place);
        $jobs['other_benefits'] = json_encode($request->other_benefits);
        $jobs['skills'] = json_encode($request->skills);
        $jobs['gender'] = json_encode($request->gender);
        $jobs['job_status'] = JobStatus::PENDING;
        $jobs['is_publish'] = 0; // Edited content goes back to moderation.
        unset($jobs['company_id'], $jobs['user_id'], $jobs['slug']);

        $job->update($jobs);

        return redirect()->route('employer.dashboard')->withSuccess('job update success');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Job $job)
    {
        $this->requireOwnedJob($job);
        $job->delete();

        return redirect()->back()->withWarning('Job archive success');
    }

    public function archiveJob()
    {
        $company = $this->employerCompany();
        $title = __('Archive Jobs');

        $jobs = Job::with('company', 'degree_level', 'degree_name')->onlyTrashed()->where('user_id', Auth::id())->where('company_id', $company->id)->latest()->paginate(3);

        return view('frontend.pages.job.list', compact('jobs', 'title'));
    }

    public function restoreJob($id)
    {
        $company = $this->employerCompany();
        $job = Job::onlyTrashed()->where('user_id', Auth::id())
            ->where('company_id', $company->id)->findOrFail($id);
        $job->restore();

        return redirect()->route('employer.dashboard')->withSuccess('job restore success');
    }

    #Admin functions...........................
    public function getActiveJob()
    {
        $items = Job::where('is_publish', StatusType::ACTIVE)->latest()->get();

        return view('admin.job.index', compact('items'));
    }

    public function jobStatusUpdate(Request $request)
    {
        abort_unless($request->user()?->can('active-job'), 403);
        $data = $request->validate([
            'id' => ['required', 'integer', 'exists:jobs,id'],
            'mode' => ['required', 'integer', 'in:0,1'],
        ]);
        DB::table('jobs')->where('id', $data['id'])
            ->update(['is_publish' => (int) $data['mode']]);

        return response()->json(['is_publish' => (int) $data['mode']]);
    }


    public function getInActiveJob()
    {
        $items = Job::where('is_publish', StatusType::INACTIVE)->get();

        return view('admin.job.index', compact('items'));
    }

    public function viewJob($id)
    {
        $jobDetails = Job::with('country', 'company')->whereId($id)->first();
        $other_benefits = json_decode($jobDetails->other_benefits);
        $workPlaces = json_decode($jobDetails->work_place);
        $genders = json_decode($jobDetails->gender);
        $skills = Skill::whereIn('id', json_decode($jobDetails->skills))->select('skill_name')->get();

        return view('admin.job.details', compact('jobDetails', 'other_benefits', 'workPlaces', 'genders', 'skills'));
    }

    public function viewJobApplicants($id)
    {
        $jobDetails = Job::with('country', 'company')->whereId($id)->first();
        $applications = Application::where('job_id', $id)->get();

        return view('admin.job.applicants', compact('jobDetails', 'applications'));
    }

    public function jobApplicationStatusUpdate(Request $request)
    {
        abort_unless($request->user()?->can('active-job'), 403);
        $data = $request->validate([
            'application_id' => ['required', 'integer', 'exists:applications,id'],
            'status' => ['required', 'integer', 'min:1'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $app = app(\App\Services\ApplicationStatusManager::class)->transition(
            Application::findOrFail($data['application_id']),
            (int) $data['status'], $request->user(), 'admin', $data['note'] ?? null
        );

        return redirect()->route('job.show-apply', [$app->job_id])
            ->withSuccess('Application status updated and recorded in history.');
    }
}
