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
    public function findJobs()
    {
        $locations = Country::whereStatus(1)->get();
        return view('jobseeker.application.find-job', compact('locations'));
    }

    public function getAllJobs()
    {
        $jobs = Job::with('country')->paginate(5);
        return view('jobseeker.application.jobs_table', compact('jobs'));
    }

    public function getApplicationForm($id)
    {
        $job = Job::findOrFail($id);

        return view('jobseeker.application.application_form', compact('job'));
    }

    public function jobDetails($id)
    {
        $job = Job::with(['company', 'job_category', 'degree_level', 'degree_name', 'country'])->where('id', $id)->first();
        
        return view('jobseeker.application.job_details', compact('job'));
    }

    public function search(Request $request)
    {
        $query = Job::where('is_publish', 1);

        if ($request->filled('title')) {
            $title = $request->input('title');
            $query->where(function ($query) use ($title) {
                $query->where('title', 'like', '%' . $title . '%')
                    ->orWhere('job_responsibilities', 'like', '%' . $title . '%')
                    ->orWhere('job_context', 'like', '%' . $title . '%');
            });
        }

        if ($request->filled('location')) {
            $query->where('country_id', $request->input('location'));
        }

        $jobs = $query->where('is_publish', 1)->where('application_deadline', '>', now())->paginate(5);

        return view('jobseeker.application.jobs_table', compact('jobs'));
    }


    public function savePost(Request $request)
    {
        $validated = $request->validate([
            'job_id' => 'required|integer|exists:jobs,id',
            'employer_id' => 'required|integer|exists:companies,id',
            'expected_salary' => 'required|numeric|min:0',
        ]);

        $userId = auth()->id();

        try {
            // Locking the applicant serializes concurrent submissions from this account.
            // The code is consumed in the same transaction as the application insert.
            $application = \Illuminate\Support\Facades\DB::transaction(function () use ($validated, $userId) {
                \App\Models\User::whereKey($userId)->lockForUpdate()->firstOrFail();

                if (CheckApplyEligibility() == 0) {
                    throw new \DomainException('Please apply for a unique code.');
                }

                if (Application::where('user_id', $userId)
                    ->where('job_id', $validated['job_id'])->exists()) {
                    throw new \DomainException('You have already applied to this job.');
                }

                $uniqueCode = UniqueCode::where('user_id', $userId)
                    ->where('is_used', 0)
                    ->whereNotNull('code')
                    ->lockForUpdate()
                    ->first();

                if (!$uniqueCode) {
                    throw new \DomainException('Please apply for a unique code.');
                }

                $application = Application::create([
                    'user_id' => $userId,
                    'job_id' => $validated['job_id'],
                    'employer_id' => $validated['employer_id'],
                    'expected_salary' => $validated['expected_salary'],
                    'date_applied' => now(),
                    'status' => Status::APPLIED,
                ]);

                $uniqueCode->is_used = 1;
                $uniqueCode->save();

                return $application;
            }, 3);
        } catch (\DomainException $e) {
            return redirect()->route('find-jobs')->withError($e->getMessage());
        } catch (\Throwable $e) {
            report($e);
            return redirect()->route('find-jobs')->withError('Application could not be submitted. Please try again.');
        }

        // Delivery failures must not undo a successfully committed application.
        try {
            $company = Company::find($application->employer_id);
            if ($company && $company->user_id) {
                sendNotification($company->user_id, 'New job application', '/', 'click', auth()->user()->name . ' applied for a job');
            }
        } catch (\Throwable $e) {
            report($e);
        }

        return redirect()->route('find-jobs')->withSuccess('Successfully applied');
    }

        public function appliedJob()
    {
        return view('jobseeker.application.applied_jobs');
    }

    public function getAppliedJob()
    {
        $jobs = Job::with(['country', 'company', 'applications'])
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
