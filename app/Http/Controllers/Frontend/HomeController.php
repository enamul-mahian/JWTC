<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Dashboard\JobCategory;
use App\Models\Education;
use App\Models\FrontPage;
use App\Models\Job;
use App\Models\JobseekerSkill;
use App\Models\Language;
use App\Models\Location;
use App\Models\PersonalInformation;
use App\Models\Training;
use App\Models\WorkExperience;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function __invoke()
    {
        // $locations = Country::whereIn('id',\Illuminate\Support\Facades\DB::table('job_location_options')->where('enabled',true)->select('country_id'))->orderBy('name')->get(['id','name']);
        // $job_categories = JobCategory::whereStatus(1)->get();
        // $categoriesWithCount = JobCategory::withCount('jobs')->get();
        // $latestJobs = Job::with(['country', 'company'])->latest()->take(10)->get();
        // $locationWithCount = Country::withCount('jobs')->get();

        // return view('frontend.index', compact('locations', 'job_categories', 'categoriesWithCount', 'latestJobs', 'locationWithCount'));

        // $jobCategories = JobCategory::latest()->get();
        // $title = __('Employer Account Create');

        // return view('frontend.pages.register', compact('title', 'jobCategories'));


        // Keep historic published job URLs reachable, but don't represent closed
        // applications as vacancies accepting applications.
        $today = now()->toDateString();
        $published = Job::query()->where('is_publish', 1);
        $latestJobs = (clone $published)->with(['company', 'country', 'job_category'])
            ->orderByRaw('CASE WHEN application_deadline >= ? THEN 0 WHEN application_deadline IS NULL THEN 1 ELSE 2 END', [$today])
            ->orderByDesc('created_at')->orderByDesc('id')->limit(6)->get();
        $publishedJobCount = (clone $published)->count();
        $openJobCount = (clone $published)->whereDate('application_deadline', '>=', $today)->count();
        $closedJobCount = (clone $published)->whereDate('application_deadline', '<', $today)->count();
        $hiringCompanyCount = (clone $published)->distinct()->count('company_id');
        $locations = Country::whereIn('id',\Illuminate\Support\Facades\DB::table('job_location_options')->where('enabled',true)->select('country_id'))->orderBy('name')->get(['id','name']);
        return view('frontend.index', compact('latestJobs', 'publishedJobCount', 'openJobCount', 'closedJobCount', 'hiringCompanyCount', 'locations'));
    }

    public function publicJob(int $job)
    {
        $job = Job::with(['company', 'country', 'job_category'])->where('is_publish', 1)->findOrFail($job);
        return view('frontend.pages.public_job', compact('job'));
    }

    public function searchJobs(Request $request)
    {
        $data=app(\App\Services\JobSearch::class)->data($request);
        if($data['jobs']->currentPage()>$data['jobs']->lastPage())return redirect()->route('search-jobs',array_merge($request->except('page'),['page'=>1]));
        return view('frontend.pages.jobs',$data);
    }

    public function getPageData($url)
    {
        $page = FrontPage::where('url', $url)->first();
        return view('frontend.pages.page', compact('page'));
    }

    public function getJobCountsByCountry()
    {
        $jobCounts = [
            'countryData' => Country::withCount('jobs')
                ->get()
                ->map(function ($country) {
                    $jobSeekerCount = PersonalInformation::where('nationality', $country->name)->count();
                    return [
                        'name' => $country->name,
                        'jobs_count' => $country->jobs_count,
                        'job_seeker_count' => $jobSeekerCount,
                    ];
                }),
        ];

        return response()->json($jobCounts);
    }
}
