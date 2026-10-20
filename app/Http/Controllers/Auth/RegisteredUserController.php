<?php

namespace App\Http\Controllers\Auth;

use App\Enum\StatusType;
use App\Enum\UserType;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the Job Seeker registration page.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle Job Seeker registration.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                'unique:users,email',
            ],

            'phone' => [
                'required',
                'string',
                'max:30',
                'unique:users,phone',
            ],

            'referral_code' => [
                'required',
                'string',
                'max:100',
            ],

            'password' => [
                'required',
                'confirmed',
                'min:4',
                Rules\Password::defaults(),
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Check Job Seeker Referral Code
        |--------------------------------------------------------------------------
        */

        $general = gs();

        if (
            !$general ||
            empty($general->job_seeker_referral_code)
        ) {
            return back()
                ->withErrors([
                    'referral_code' => 'Job Seeker registration is currently unavailable.',
                ])
                ->withInput($request->except([
                    'password',
                    'password_confirmation',
                ]));
        }

        $enteredCode = trim((string) $request->referral_code);
        $correctCode = trim((string) $general->job_seeker_referral_code);

        if (!hash_equals($correctCode, $enteredCode)) {
            return back()
                ->withErrors([
                    'referral_code' => 'Invalid referral code.',
                ])
                ->withInput($request->except([
                    'password',
                    'password_confirmation',
                ]));
        }

        /*
        |--------------------------------------------------------------------------
        | Create Job Seeker
        |--------------------------------------------------------------------------
        */

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,

            'user_type' => UserType::JOB_SEEKER,

            'status' => StatusType::ACTIVE,

            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect()->route('home');
    }
}