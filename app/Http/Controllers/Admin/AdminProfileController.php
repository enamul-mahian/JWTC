<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AdminProfileController extends Controller
{
    public function profileSetting(){
        $user = auth()->user();
        return view('admin.setting.profile', compact('user'));
    }
    
    public function changePassword(){
        $user = auth()->user();
        return view('admin.setting.change_password', compact('user'));
    }

    public function updateProfile(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required'
        ]);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        $user = auth()->user();
        $request->validate([
            'email' => 'required|email|unique:users,email,' . $user->id,
        ]);

        DB::beginTransaction();
        try {

            if ($request->hasFile('photo')) {
                $old = $user->photo;
                $photo = fileUploader($request->photo, getFilePath('userphoto'), getFileSize('userphoto'), $old);
                $user->photo = $photo;
            }
            $user->name = $request->name;
            $user->email = $request->email;
            $user->save();
            DB::commit();
            return back()->withSuccess('User updated successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withError('An error occurred: ' . $e->getMessage());
        }
    }

    public function updateAdminPassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = $request->user();
        if (!Hash::check($validated['current_password'], (string) $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        if (config('security.password_change_otp.enabled')) {
            app(\App\Services\PasswordOtpService::class)->issue(
                $user, Hash::make($validated['new_password'])
            );
            return back()->with('otp_sent', 'We emailed you a six-digit verification code.');
        }

        DB::transaction(function () use ($user, $validated) {
            $user->forceFill([
                'password' => Hash::make($validated['new_password']),
                'remember_token' => \Illuminate\Support\Str::random(60),
            ])->save();
            $user->tokens()->delete();
            // A previously issued challenge must not revert a later password.
            if (\Illuminate\Support\Facades\Schema::hasTable('email_otp_challenges')) {
                DB::table('email_otp_challenges')->where('user_id', $user->id)
                    ->where('purpose', 'password_change')->delete();
            }
        });

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login')->with(
            'message', 'Password changed successfully. Please sign in again.'
        );
    }
}
