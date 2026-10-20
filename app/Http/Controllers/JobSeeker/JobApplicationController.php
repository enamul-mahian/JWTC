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
        $canApply = CheckApplyEligibility();

        if ($canApply == 0) {
            return redirect()->route('find-jobs')->withError('Please apply for unique code.');
        }

        $request->validate([
            'job_id' => 'required',
            'employer_id' => 'required',
            'expected_salary' => 'required',
        ]);

        try {
            $userId = auth()->user()->id;
            $jobId = $request->job_id;

            // Check if the user has already applied to this job
            $existingApplication = Application::where('user_id', $userId)->where('job_id', $jobId)->exists();
            if ($existingApplication) {
                return redirect()->route('find-jobs')->withError('You have already applied to this job.');
            }

            $inputs = $request->all();
            $inputs['user_id'] = $userId;
            $inputs['date_applied'] = now();
            $inputs['status'] = Status::APPLIED;
            $app = Application::create($inputs);
            $company = Company::find($app->employer_id);
            
            sendNotification($company->user_id, $title='test', $url='/', $action='click', $outro=auth()->user()->name.' '.'applied jobs');
            
            $uniqueCode = UniqueCode::where('user_id', auth()->user()->id)->where('is_used', 0)->where('code', '!=', null)->first();
            $uniqueCode->is_used = 1;
            $uniqueCode->save();

            return redirect()->route('find-jobs')->withSuccess('Successfully applied');
        } catch (\Exception $e) {
            return redirect()->route('find-jobs')->withError($e->getMessage());
        }
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
