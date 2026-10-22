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

        $audience = $request->route('audience') ?? UserType::JOB_SEEKER;
        $allowed = $audience === 'admin' ? [UserType::ADMIN, UserType::SUPER_ADMIN] : [$audience];
        if (!in_array(strtolower((string)$user->user_type), $allowed, true)) return back()->withErrors(['email' => 'Use the sign-in panel for your account type.'])->onlyInput('email');
        if (strtolower((string)$user->user_type) !== UserType::EMPLOYER && (int)$user->status !== 1) return back()->withErrors(['email' => 'This account is inactive.'])->onlyInput('email');
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            // Authentication passed
            $user = Auth::user();

            if (strtolower((string)$user->user_type) === UserType::EMPLOYER && (!$user->employer_approved_at || (int)$user->status !== 1)) return redirect()->route('company.approval');
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
    public function login(Request $request)
    {
        $audience = $request->route('audience') ?? UserType::JOB_SEEKER;
        $title = $audience === UserType::EMPLOYER ? 'Company Sign In' : ($audience === 'admin' ? 'Administrator Sign In' : 'Job Seeker Sign In');

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

        return view('frontend.auth.login', compact('title', 'audience'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
