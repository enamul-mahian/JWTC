<?php

namespace App\Http\Controllers\Employer;

use App\Enum\JobStatus;
use App\Enum\RoleType;
use App\Enum\StatusType;
use App\Enum\SubscriptionType;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreJobRequest;
use App\Http\Requests\UpdateJobRequest;
use App\Mail\JobNotificationMail;
use App\Models\Application;
use App\Models\Country;
use App\Models\Dashboard\Company;
use App\Models\Dashboard\DegreeLavel;
use App\Models\Dashboard\JobCategory;
use App\Models\Job;
use App\Models\JobType;
use App\Models\Skill;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Laravelcm\Subscriptions\Models\Subscription;

class JobController extends Controller
{
    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        if (!auth()->user()->can('post-job')) {
            return redirect()->route('home');
        }

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
        $oldValue = Cache::get('key');
        $title = __('Create job');
        $company = Company::where('user_id', Auth::user()->id)->first();
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
        if (!auth()->user()->can('post-job')) {
            return redirect()->route('home');
        }

        $jobs = $request->all();

        $jobs['slug'] = Str::slug(strtolower(str_replace(' ', '-', $request->title))) . '-' . Carbon::parse(time())->format('ymdHis');
        $jobs['employment_status'] = json_encode($request->employment_status);
        $jobs['job_level'] = json_encode($request->job_level);
        $jobs['work_place'] = json_encode($request->work_place);
        $jobs['other_benefits'] = json_encode($request->other_benefits);
        $jobs['skills'] = json_encode($request->skills);
        $jobs['gender'] = json_encode($request->gender);
        $jobs['job_status'] = JobStatus::PENDING;
        $jobs['user_id'] = Auth::user()->id;

        $job = Job::create($jobs);

        $users = User::where('job_category_id', $job->job_category_id)->where('user_type', RoleType::JOB_SEEKER)->where('auto_receive_email', 1)->get();

        if ($job->is_publish != 0) {
            foreach ($users as $user) {

                Mail::to($user->email)->send(new JobNotificationMail($job, $user));
            }
        }

        Cache::forget('key');

        return redirect()->route('employer.dashboard')->with([
            'success' => 'job post success',
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show($slug)
    {
        if (!auth()->user()->can('post-job')) {
            return redirect()->route('home');
        }

        $jobDetails = Job::with('company', 'degree_level', 'degree_name', 'country')->withTrashed()->where('slug', $slug)->first();
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
        if (!auth()->user()->can('post-job')) {
            return redirect()->route('home');
        }

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
        if (!auth()->user()->can('post-job')) {
            return redirect()->route('home');
        }
        $jobs = $request->all();
        $jobs['slug'] = Str::slug(strtolower(str_replace(' ', '-', $request->title))) . '-' . Carbon::parse(time())->format('ymdHis');
        $jobs['employment_status'] = json_encode($request->employment_status);
        $jobs['job_level'] = json_encode($request->job_level);
        $jobs['work_place'] = json_encode($request->work_place);
        $jobs['other_benefits'] = json_encode($request->other_benefits);
        $jobs['skills'] = json_encode($request->skills);
        $jobs['gender'] = json_encode($request->gender);
        $jobs['job_status'] = JobStatus::PENDING;
        $jobs['user_id'] = Auth::user()->id;

        $job->update($jobs);

        return redirect()->route('employer.dashboard')->withSuccess('job update success');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Job $job)
    {
        $job->delete();

        return redirect()->back()->withWarning('Job archive success');
    }

    public function archiveJob()
    {
        $title = __('Archive Jobs');

        $jobs = Job::with('company', 'degree_level', 'degree_name')->onlyTrashed()->where('user_id', Auth::user()->id)->latest()->paginate(3);

        return view('frontend.pages.job.list', compact('jobs', 'title'));
    }

    public function restoreJob($id)
    {
        $job = Job::onlyTrashed()->findOrFail($id);
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
        if ($request->mode == StatusType::ACTIVE) {
            DB::table('jobs')->where('id', $request->id)->update(['is_publish' => StatusType::ACTIVE]);
        } else {
            DB::table('jobs')->where('id', $request->id)->update(['is_publish' => StatusType::INACTIVE]);
        }

        return response()->json(['is_publish' => $request->mode]);
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
        $app = Application::find($request->application_id);
        $app->status = $request->status;
        $app->save();

        return redirect()->route('job.show-apply', [$app->job_id])->withSuccess('Application status Updated Successfully.');
    }
}
