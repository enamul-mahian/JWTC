<?php

namespace App\Http\Controllers\JobSeeker;

use App\Models\Job;
use App\Models\Location;
use App\Constants\Status;
use App\Models\Application;
use Illuminate\Http\Request;
use App\Models\Dashboard\Company;
use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\UniqueCode;
use Illuminate\Support\Facades\Auth;

class JobApplicationController extends Controller
{
    public function findJobs(Request $request)
    {
        abort_unless($request->user()?->user_type===\App\Enum\UserType::JOB_SEEKER,403);
        $data=app(\App\Services\JobSearch::class)->data($request);
        if($data['jobs']->currentPage()>$data['jobs']->lastPage())return redirect()->route('find-jobs',array_merge($request->except('page'),['page'=>1]));
        return view('jobseeker.application.find-job',$data);
    }
    public function getAllJobs(Request $request)
    {
        return view('jobseeker.application.jobs_table',app(\App\Services\JobSearch::class)->data($request));
    }

    public function getApplicationForm($id)
    {
        $job = Job::where('is_publish', 1)->findOrFail($id);

        return view('jobseeker.application.application_form', compact('job'));
    }

    public function jobDetails($id)
    {
        $job = Job::with(['company', 'job_category', 'degree_level', 'degree_name', 'country'])->where('is_publish', 1)->findOrFail($id);
        
        return view('jobseeker.application.job_details', compact('job'));
    }

    public function search(Request $request)
    {
        return view('jobseeker.application.jobs_table',app(\App\Services\JobSearch::class)->data($request));
    }

    public function savePost(Request $request)
    {
        $data = $request->validate([
            'job_id' => ['required', 'integer', 'exists:jobs,id'],
            'employer_id' => ['sometimes', 'integer', 'exists:companies,id'],
            'expected_salary' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'application_code' => ['required', 'string', 'max:100'],
        ]);

        $job = Job::findOrFail($data['job_id']);
        if (isset($data['employer_id']) &&
            (int) $data['employer_id'] !== (int) $job->company_id) {
            return back()->withError('Employer does not match the selected job.');
        }

        try {
            $application = app(\App\Services\ApplicationSubmissionService::class)->submit(
                $request->user(), $job, (float) $data['expected_salary'], $data['application_code']
            );
        } catch (\DomainException $e) {
            return back()->withError($e->getMessage());
        } catch (\Throwable $e) {
            report($e);
            return back()->withError('Application could not be submitted. Please try again.');
        }

        try {
            $company = Company::find($application->employer_id);
            if ($company && $company->user_id) {
                sendNotification($company->user_id, 'New job application', '/', 'click',
                    $request->user()->name . ' applied for a job');
            }
        } catch (\Throwable $e) {
            report($e);
        }

        return redirect()->route('find-jobs')->withSuccess('Successfully applied');
    }

    public function appliedJob()
    {
        $jobs = Job::with([
            'country', 'company',
            'applications' => fn ($query) => $query->where('user_id', Auth::id()),
        ])->whereHas('applications', fn ($query) => $query->where('user_id', Auth::id()))
            ->latest('id')->paginate(5);
        return view('jobseeker.application.applied_jobs', compact('jobs'));
    }

    public function getAppliedJob()
    {
        $jobs = Job::with([
                'country', 'company',
                'applications' => fn ($query) => $query->where('user_id', Auth::id()),
            ])
            ->whereHas('applications', function ($query) {
                $query->where('user_id', Auth::id());
            })
            ->paginate(5);
            
        return view('jobseeker.application.applied_jobs_table', compact('jobs'));
    }

    public function shortListedJob()
    {
        return view('jobseeker.application.shortlist_jobs');
    }

    public function getShortlistedJob()
    {
        $jobs = Job::with(['country', 'company', 'applications'])
            ->whereHas('applications', function ($query) {
                $query->where('user_id', Auth::id())
                    ->where('status', 2);
            })
            ->paginate(5);

        return view('jobseeker.application.shortlist_jobs_table', compact('jobs'));
    }
}
