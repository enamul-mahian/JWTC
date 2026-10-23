<?php

namespace App\Http\Controllers\JobSeeker;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class JobSeekerDashboardController extends Controller
{
    public function dashboard(){
        return redirect()->route('job-seeker-profile');
    }
    
    public function profileData(){
        return view('jobseeker.dashboard.profile_data');
    }

    public function uploadPhoto(Request $request){
        try {
            if ($request->hasFile('photo')) {
                $user = auth()->user();
                $image = $request->file('photo');
                $photo = fileUploader($image, getFilePath('profilephoto'), getFileSize('profilephoto'), $user->photo);
                $user = auth()->user();
                $user->photo = $photo;
                $user->save();
            }
            
            return response()->json(['success' => 'form submitted'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
        
    }
}