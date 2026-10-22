<?php

namespace App\Http\Controllers;

use App\Enum\RoleType;
use App\Enum\UserType;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserAuthLoginController extends Controller
{
    public function userLogin(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'h-captcha-response' => [
                \Illuminate\Validation\Rule::requiredIf((bool) config('services.hcaptcha.enabled')),
                'nullable', 'string', 'max:8192',
                new \App\Rules\ValidHcaptcha(),
            ],
        ]);
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return redirect()->back()->with([
                'message' => 'credential doesn\'t match'
            ]);
        }

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            // Authentication passed
            $user = Auth::user();

            // Redirect or perform additional actions
            if ($user->user_type === RoleType::JOB_SEEKER || $user->user_type === UserType::JOB_SEEKER) {
                return redirect()->route('view-jobseeker-profile');
            } elseif ($user->user_type === RoleType::EMPLOYER || $user->user_type === UserType::EMPLOYER) {
                return redirect()->route('employer.dashboard');
            } elseif ($user->user_type === RoleType::ADMIN || $user->user_type === UserType::ADMIN) {
                return redirect()->route('admin.dashboard');
            }
        }
        // Default redirect
        return redirect('/');
    }
    public function login()
    {
        $title = 'Login';

        if (Auth::user()) {
            $user = User::findOrFail(Auth::user()->id);
            if ($user->user_type == UserType::JOB_SEEKER) {
                return redirect()->route('view-jobseeker-profile');
            } elseif ($user->user_type == UserType::EMPLOYER) {
                return redirect()->route('employer.dashboard');
            } else {
                return redirect()->route('admin.dashboard');
            }
        }

        return view('frontend.pages.login', compact('title'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
