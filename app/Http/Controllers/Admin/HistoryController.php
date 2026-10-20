<?php

namespace App\Http\Controllers\Admin;

use App\Models\Job;
use App\Models\User;
use App\Models\Application;
use Illuminate\Http\Request;
use App\Models\Dashboard\Company;
use App\Http\Controllers\Controller;

class HistoryController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:employer-job-history'])->only('employerJobHistory');
        $this->middleware(['permission:jobSeeker-history'])->only('jobSeekerHistory');
        $this->middleware(['permission:employer-cv-downloads'])->only('cvDownloads');
    }


    public function employerJobHistory()
    {
        $jobs = Company::with('company_sizes')->withCount(['jobs'])->get();

        return view('admin.history.employer_job_history', compact('jobs'));
    }

    public function companyJobs($id)
    {
        $jobs = Job::with(['applications', 'job_category', 'country'])->where('company_id', $id)->get();

        return view('admin.history.company_jobs', compact('jobs', 'id'));
    }

    public function jobAppliedUsers($id)
    {
        $users = Application::with(['user'])->where('job_id', $id)->get();

        return view('admin.history.job_applied_users', compact('users', 'id'));
    }


    public function jobSeekerHistory()
    {
        $users = User::withCount('applications')->where('user_type', 'job_seeker')->get();

        return view('admin.history.jobseeker_application_history', compact('users'));
    }

    public function appliedJobs($id)
    {
        $applications = Application::with(['job', 'job.country', 'job.job_category'])->where('user_id', $id)->get();

        return view('admin.history.applied_jobs', compact('applications'));
    }

    public function cvDownloads()
    {
        $downloads = User::with('cv_downloads')->where('user_type', 'employer')->get();

        return view('admin.history.cv_download', compact('downloads'));
    }
}
