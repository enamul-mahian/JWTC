<?php

namespace App\Http\Controllers\JobSeeker;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class JobSeekerProfileShowController extends Controller
{
    public function show()
    {
        $user = User::with(
            ['personal_info', 'work_experiences', 'trainings', 'skills', 'languages', 'educations.degreeLevel', 'educations.degreeName']
        )->where('id', auth()->user()->id)->first();
        $users = auth()->user();
// return $users;
        return view('jobseeker.dashboard.profile_show', compact('user'));
    }
}
