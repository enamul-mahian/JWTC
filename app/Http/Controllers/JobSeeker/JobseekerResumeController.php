<?php

namespace App\Http\Controllers\JobSeeker;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class JobseekerResumeController extends Controller
{
    public function makeResume(Request $request){
        return view('jobseeker.dashboard.resume');
    }
    
    public function getResume(Request $request){
        $user = User::with(
            ['personal_info','work_experiences', 'trainings', 'skills', 'languages', 'educations.degreeLevel', 'educations.degreeName']
        )->where('id', auth()->user()->id)->first();
        return view('resume.resume', compact('user'));
    }
}