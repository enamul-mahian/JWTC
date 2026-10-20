<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\Country;
use App\Models\Location;
use Illuminate\Http\Request;
use App\Models\Dashboard\JobCategory;

class AjaxSearchController extends Controller
{
    public function index(Request $request)
    {
        $location = Country::where('name', $request->input('country'))->first();

        if ($location->id)
        {
            $data = $location;
        } else {
            $data = null;
        }

        return response()->json($data);
    }

    public function show($name)
   {
        $location = Country::where('name', $name)->first();

        $query = Job::query();
        $jobs = $query->where('is_publish', 1)
            ->where('application_deadline', '>', now())
            ->where('country_id', $location->id)
            ->with(['country', 'company'])->paginate(10);

        return view('frontend.pages.jobs', [
            'jobs' => $jobs ?? [],
            'locations' => Country::whereStatus(1)->get(),
            'job_categories' => JobCategory::whereStatus(1)->get(),
            'title' => '',
            'country_id' => '',
            'category_id' => '',
        ]);
    }
}
