<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Enum\UserType;
use App\Enum\StatusType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class JobSeekerController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:active-jobSeeker'])->only('getActiveJobSeeker');
        $this->middleware(['permission:inactive-jobSeeker'])->only('getInActiveJobSeeker');
    }

    public function getActiveJobSeeker()
    {
        $items = User::where('user_type', UserType::JOB_SEEKER)->where('status', StatusType::ACTIVE)->latest()->get();

        return view('admin.jobseeker.index', compact('items'));
    }

    public function getInActiveJobSeeker()
    {
        $items = User::where('user_type', UserType::JOB_SEEKER)->where('status', StatusType::INACTIVE)->get();

        return view('admin.jobseeker.index', compact('items'));
    }

    public function viewJobSeeker($id)
    {
        $user = User::whereId($id)->with(['personal_info', 'work_experiences', 'trainings', 'skills', 'languages', 'educations'])->where('user_type', UserType::JOB_SEEKER)->where('status', StatusType::ACTIVE)->first();

        return view('admin.jobseeker.show', compact('user'));
    }


    public function jobSeekerStatusUpdate(Request $request)
    {
        if ($request->mode == 0) {
            DB::table('users')->where('id', $request->id)->update(['status' => $request->mode]);
        } else {
            DB::table('users')->where('id', $request->id)->update(['status' => $request->mode]);
        }

        return response()->json(['status', $request->mode]);
    }

    public function cvView($id)
    {
        $user = User::with(
            ['personal_info', 'work_experiences', 'trainings', 'skills', 'languages', 'educations.degreeLevel', 'educations.degreeName']
        )->where('id',$id)->first();

        return view('jobseeker.cv-view', compact('user'));
    }
}
