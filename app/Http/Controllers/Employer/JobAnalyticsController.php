<?php

namespace App\Http\Controllers\Employer;

use App\Models\Job;
use App\Enum\JobAnalytics;
use App\Models\Application;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Dashboard\JobCategory;
use App\Models\PersonalInformation;

class JobAnalyticsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // $jobs = Job::where('user_id', Auth::user()->id)->select('id')->count('id');
        $jobsId = Job::where('user_id', Auth::user()->id)->select('id')->pluck('id');

        $applicationJob =  Application::whereIn('job_id', $jobsId)->count('id');
        $shortListedJob =  Application::whereIn('job_id', $jobsId)->where('status', JobAnalytics::SHORTLISTED)->count('id');
        $interViewJob =  Application::whereIn('job_id', $jobsId)->where('status', JobAnalytics::INTERVIEW)->count('id');

        return view('frontend.pages.job.analytics', compact('jobsId', 'applicationJob', 'interViewJob', 'shortListedJob', 'interViewJob'));
    }

    public function getAnalyticsJobData()
    {
        $jobs = Job::where('user_id', Auth::user()->id)->select('id')->get();
        $application = Application::whereIn('job_id', $jobs)->select('user_id')->get();
        $maleCount = PersonalInformation::whereIn('user_id', $application)->where('gender', 'male')->count('id');
        $feMaleCount = PersonalInformation::whereIn('user_id', $application)->where('gender', 'Female')->count('id');

        $data = [
            'male' =>  $maleCount,
            'female' =>  $feMaleCount,
        ];

        return response()->json($data);
    }
}
