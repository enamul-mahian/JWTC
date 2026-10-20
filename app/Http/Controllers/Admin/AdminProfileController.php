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

    function updateAdminPassword(Request $request) {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = Auth::user();

        // Check if the current password matches the one in the database
        if (!Hash::check($request->input('current_password'), $user->password)) {
            return redirect()->back()->with('error', 'Current password is incorrect');
        }

        // Update the password
        $user->update([
            'password' => Hash::make($request->input('new_password')),
        ]);

        return redirect()->route('setting')->with('success','Data updated successfully');
    }
}