<?php

namespace App\Http\Controllers\Employer;

use App\Models\Job;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Dashboard\JobCategory;
use App\Models\UploadedCv;
use Illuminate\Support\Facades\Auth;

class EmployerCvBankController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $jobCategories = JobCategory::whereStatus(1)->get();

        $applicationCounts = [];

        foreach ($jobCategories as $category) {
            $jobs = Job::where('user_id', Auth::user()->id)
                ->where('job_category_id', $category->id)
                ->pluck('id');

            $applicationCount = Application::whereIn('job_id', $jobs)->count();

            // Store category ID and name along with the application count
            $applicationCounts[$category->name] = [
                'id' => $category->id,
                'count' => $applicationCount,
            ];
        }
       
        return view('frontend.pages.cvBank.index', ['applicationCounts' => $applicationCounts]);
    }


    public function cvDownload($id)
    {
        return $id;
    }

    public function payMessage($id, $count)
    {
        return view('frontend.pages.cvBank.downloadCv', compact('id', 'count'));
    }
}