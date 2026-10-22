<?php

namespace App\Http\Controllers\Employer;

use App\Enum\UserType;
use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Dashboard\Company;
use App\Models\EmployerCvView;
use App\Models\Job;
use App\Models\PersonalInformation;
use App\Models\User;
use App\Services\ApplicationStatusManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ManageJobController extends Controller
{
    /**
     * The /employer routes have legacy auth-only middleware. Enforce the
     * tenant boundary here as well; a URL ID must never authorize a CV read.
     */
    private function company(Request $request): Company
    {
        $user = $request->user();
        abort_unless($user && $user->user_type === UserType::EMPLOYER
            && $user->can('post-job'), 403);

        return Company::query()->where('user_id', $user->id)->firstOrFail();
    }

    private function ownedJob(Request $request, int $jobId): Job
    {
        $company = $this->company($request);

        return Job::query()
            ->whereKey($jobId)
            ->where('user_id', $request->user()->id)
            ->where('company_id', $company->id)
            ->firstOrFail();
    }

    public function managePage(Request $request)
    {
        $company = $this->company($request);
        $title = __('Manage Job');
        $owned = Job::query()->where('user_id', $request->user()->id)
            ->where('company_id', $company->id);
        $jobs = (clone $owned)->get();
        $counts = (clone $owned)->withCount('applications')->latest()->paginate(20);

        return view('frontend.pages.manegeJob.list', compact('title', 'jobs', 'counts'));
    }

    public function viewApplication(Request $request, int $id)
    {
        $job = $this->ownedJob($request, $id);
        $title = __('Applicable User');
        $jobId = $job->id;

        $applicationUser = Application::where('job_id', $job->id)
            ->whereNotNull('user_id')->distinct()->pluck('user_id');
        $personalInfo = PersonalInformation::whereIn('user_id', $applicationUser)
            ->latest()->paginate(20);

        $shortListApplication = Application::where('job_id', $job->id)
            ->where('status', 2)->pluck('user_id');
        $shortListPersonalInfo = PersonalInformation::whereIn('user_id', $shortListApplication)
            ->paginate(20);

        $interview = Application::where('job_id', $job->id)
            ->where('status', 3)->pluck('user_id');
        $interviewPersonalInfo = PersonalInformation::whereIn('user_id', $interview)
            ->paginate(20);

        return view('frontend.pages.manegeJob.applicationUser',
            compact('title', 'personalInfo', 'jobId', 'shortListPersonalInfo', 'interviewPersonalInfo'));
    }

    public function getResume(Request $request, int $userId, int $applicationId)
    {
        $company = $this->company($request);

        // The applicant must have applied to a job owned by THIS employer.
        // Also bind the requested applicant ID to that exact application.
        Application::query()
            ->whereKey($applicationId)
            ->where('user_id', $userId)
            ->whereHas('job', function ($query) use ($request, $company) {
                $query->where('user_id', $request->user()->id)
                    ->where('company_id', $company->id);
            })->firstOrFail();

        $user = User::with([
            'personal_info', 'work_experiences', 'trainings', 'skills',
            'languages', 'educations.degreeLevel', 'educations.degreeName',
        ])->findOrFail($userId);

        $view = EmployerCvView::firstOrNew([
            'employer_id' => $company->id,
            'user_id' => $userId,
            'application_id' => $applicationId,
        ]);
        $view->count = max(0, (int) $view->count) + 1;
        $view->save();

        return view('frontend.pages.manegeJob.viewResume', compact('user'));
    }

    public function shortListCv(Request $request)
    {
        $company = $this->company($request);
        $data = $request->validate([
            'applicationId' => ['required', 'integer'],
            // Legacy shortcut actions are shortlist/interview only. Other
            // configurable stages must use explicitly authorized workflows.
            'status' => ['required', 'integer', Rule::in([2, 3])],
        ]);

        $application = Application::query()
            ->whereKey($data['applicationId'])
            ->whereHas('job', function ($query) use ($request, $company) {
                $query->where('user_id', $request->user()->id)
                    ->where('company_id', $company->id);
            })->firstOrFail();

        $priorStatus = (int) $application->status;
        $updated = app(ApplicationStatusManager::class)->transition(
            $application, (int) $data['status'], $request->user(), 'employer'
        );

        if ($priorStatus !== (int) $updated->status && $updated->user_id) {
            $message = (int) $updated->status === 2
                ? 'Your application has been shortlisted.'
                : 'You have been invited to interview for ' . ($updated->job?->title ?? 'your application') . '.';

            try {
                sendNotification(
                    $updated->user_id,
                    'Job application update',
                    route('jobseeker.application.timeline', ['application' => $updated->id]),
                    'click',
                    $message
                );
            } catch (\Throwable $exception) {
                // Notification failure must not undo a committed status
                // change; report privately for a delivery retry.
                report($exception);
            }
        }

        return response()->json([
            'message' => 'Application status recorded.',
            'status' => (int) $updated->status,
        ]);
    }
}
