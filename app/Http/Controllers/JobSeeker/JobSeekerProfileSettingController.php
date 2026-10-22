<?php

namespace App\Http\Controllers\JobSeeker;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class JobSeekerProfileSettingController extends Controller
{
    public function changePassword(){
        return view('jobseeker.dashboard.change_password');
    }

    public function updatePassword(Request $request){

        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = Auth::user();

        // Check if the current password matches the one in the database
        if (!Hash::check($request->input('current_password'), $user->password)) {
            return redirect()->back()->with('error', 'Current password is incorrect');
        }

        if (config('security.password_change_otp.enabled')) {
            app(\App\Services\PasswordOtpService::class)->issue(
                $user, Hash::make($request->input('new_password'))
            );
            return response()->json([
                'otp_required' => true,
                'success' => 'We emailed you a six-digit code. Enter it to complete your password change.',
            ], 202);
        }

        // Legacy behavior remains available until OTP is staged and enabled.
        $user->update([
            'password' => Hash::make($request->input('new_password')),
        ]);

        return response()->json(['success' => 'Password changed successfully']);
    }
}