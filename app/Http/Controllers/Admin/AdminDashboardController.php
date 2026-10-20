<?php

namespace App\Http\Controllers\Admin;

use App\Models\Application;
use App\Models\Dashboard\JobCategory;
use Carbon\Carbon;
use App\Models\Job;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AdminDashboardController extends Controller
{
    public function __construct()
    {
        // $this->middleware(['permission:admin-dashboard'])->only('dashboard');
    }

    public function dashboard(Request $request)
    {
        return view('admin.dashboard.index');
    }

    public function getDashboardData(Request $request)
    {
        $total_job_seeker = User::whereUserType('job_seeker')->get()->count('id');
        $total_company = User::whereUserType('employer')->get()->count('id');
        $total_job = Job::get()->count();
        return view('admin.dashboard.content', compact('total_job_seeker', 'total_company', 'total_job'));
    }

    public function getJobChartData()
    {
        $startDate = Carbon::now()->subDays(30)->startOfDay();
        $endDate = Carbon::now()->endOfDay();

        $jobs = $this->getJobCountByType($startDate, $endDate);
        $applications = $this->getApplicationCountByType($startDate, $endDate);

        $data = [
            'categories' => ['Job', 'Application'],
            'series' => [$jobs, $applications],
        ];

        return response()->json($data);
    }

    public function getUsersChartData()
    {
        $startDate = Carbon::now()->subDays(30)->startOfDay();
        $endDate = Carbon::now()->endOfDay();

        $totalJobSeekers = $this->getUserCountByType('job_seeker', $startDate, $endDate);
        $totalEmployers = $this->getUserCountByType('employer', $startDate, $endDate);

        $data = [
            'categories' => ['Job Seekers', 'Employers'],
            'series' => [$totalJobSeekers, $totalEmployers],
        ];

        return response()->json($data);
    }

    /**
     * Get total user count based on user type and date range.
     *
     * @param string $userType
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return int
     */
    private function getUserCountByType($userType, $startDate, $endDate)
    {
        return User::whereBetween('created_at', [$startDate, $endDate])
            ->where('user_type', $userType)
            ->count();
    }

    private function getApplicationCountByType($startDate, $endDate)
    {
        return Application::whereBetween('created_at', [$startDate, $endDate])->count();
    }

    private function getJobCountByType($startDate, $endDate)
    {
        return Job::whereBetween('created_at', [$startDate, $endDate])->count();
    }

    public function getJobCategoryData()
    {
        $jobCategories = JobCategory::with('jobs')->get();

        $data = [
            'categories' => $jobCategories->pluck('name')->toArray(),
            'series' => $jobCategories->map(function ($category) {
                return $category->jobs->count();
            })->toArray(),
        ];

        return response()->json($data);
    }

    public function getRecentEmp()
    {
        $startDate = Carbon::now()->subDays(3)->startOfDay();
        $endDate = Carbon::now()->endOfDay();

        $employers = User::whereUserType('employer')->whereBetween('created_at', [$startDate, $endDate])->latest()->take(5)->get();

        return view('admin.dashboard.recent_emp', compact('employers'));
    }
}
