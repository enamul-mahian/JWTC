<?php

namespace App\Http\Controllers\Employer;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Dashboard\Company;
use App\Models\EmployerCvView;
use App\Models\Job;
use App\Models\PersonalInformation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ManageJobController extends Controller
{
    public function managePage()
    {
        $title = __('Manage Job');
        $jobs = Job::where('user_id', Auth::user()->id)->get();
        $counts = Job::withCount(['applications'])->where('user_id', Auth::user()->id)->latest()->paginate(20);

        return view('frontend.pages.manegeJob.list', compact('title', 'jobs', 'counts'));
    }

    public function viewApplication($id)
    {
        $title = __('Applicable User');
        $jobId = $id;

        // Get unique user IDs who applied for the job
        $applicationUser = Application::where('job_id', $id)->pluck('user_id')->unique();

        // Retrieve personal information for all applicants
        $personalInfo = PersonalInformation::whereIn('user_id', $applicationUser)->latest()->paginate(20);

        // Retrieve personal information for shortlisted applicants
        $shortListApplication = Application::where('job_id', $id)->where('status', 2)->pluck('user_id');
        $shortListPersonalInfo = PersonalInformation::whereIn('user_id', $shortListApplication)->paginate(20);

        // Retrieve personal information for applicants who got an interview
        $interview = Application::where('job_id', $id)->where('status', 3)->pluck('user_id');
        $interviewPersonalInfo = PersonalInformation::whereIn('user_id', $interview)->paginate(20);

        return view('frontend.pages.manegeJob.applicationUser', compact('title', 'personalInfo', 'jobId', 'shortListPersonalInfo', 'interviewPersonalInfo'));
    }

    public function getResume($userId, $applicationId)
    {
        $employer = Company::where('user_id', auth()->user()->id)->first();

        // Increment count if EmployerCvView exists, otherwise set count to 1
        $employerCvView = EmployerCvView::where('user_id', $userId)->first();
        $count = $employerCvView ? $employerCvView->count + 1 : 1;

        // Update or create EmployerCvView record
        EmployerCvView::updateOrCreate(
            ['user_id' => $userId, 'application_id' => $applicationId],
            ['count' => $count, 'employer_id' => $employer->id]
        );

        // Load user with related models
        $user = User::with([
            'personal_info',
            'work_experiences',
            'trainings',
            'skills',
            'languages',
            'educations.degreeLevel',
            'educations.degreeName',
        ])->find($userId);

        return view('frontend.pages.manegeJob.viewResume', compact('user'));
    }

    public function shortListCv(Request $request)
    {
        try {
            $app = Application::where('id', $request->applicationId)->firstOrFail();
            $job = Job::find($app->job_id);
            $app->update(['status' => $request->status]);

            $status = ($request->status == 2) ? 'You are shortlisted' : 'You are invited to interview ' . $job->title ?? '';
            sendNotification($app->user_id, $title = 'Test', $url = '/', $action = 'click', $outro = $status);
            return response()->json(['message' => 'CV shortlisted successfully', 'status' => $request->mode]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
