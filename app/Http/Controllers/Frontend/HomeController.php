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
        // $locations = Country::whereStatus(1)->get();
        // $job_categories = JobCategory::whereStatus(1)->get();
        // $categoriesWithCount = JobCategory::withCount('jobs')->get();
        // $latestJobs = Job::with(['country', 'company'])->latest()->take(10)->get();
        // $locationWithCount = Country::withCount('jobs')->get();

        // return view('frontend.index', compact('locations', 'job_categories', 'categoriesWithCount', 'latestJobs', 'locationWithCount'));

        // $jobCategories = JobCategory::latest()->get();
        // $title = __('Employer Account Create');

        // return view('frontend.pages.register', compact('title', 'jobCategories'));


        return view('frontend.index');
    }

    public function searchJobs(Request $request)
    {

        $title = $request->input('title');
        $country_id = $request->input('country_id');
        $category_id = $request->input('category_id');

        $query = Job::query();

        if ($request->filled('title')) {
            $title = $request->input('title');

            $query->where(function ($query) use ($title) {
                $query->where('title', 'like', '%' . $title . '%')
                    ->orWhere('job_responsibilities', 'like', '%' . $title . '%')
                    ->orWhere('job_context', 'like', '%' . $title . '%');
            });
        }

        if ($country_id) {
            $query->where('country_id', $country_id);
        }

        if ($category_id) {
            $query->where('job_category_id', $category_id);
        }

        $jobs = $query->where('is_publish', 1)->where('application_deadline', '>', now())->with(['country', 'company'])->paginate(10);

        $locations = Country::whereStatus(1)->get();
        $job_categories = JobCategory::whereStatus(1)->get();

        return view('frontend.pages.jobs', compact('jobs', 'locations', 'job_categories', 'title', 'country_id', 'category_id'));
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
