<?php

namespace App\Http\Controllers\Auth;

use App\Enum\UserType;
use App\Http\Controllers\Controller;
use App\Services\RegistrationOtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RegistrationOtpController extends Controller
{
    public function show(Request $request)
    {
        abort_unless(config('security.registration_otp.enabled'), 404);
        abort_unless($request->session()->has('pending_registration_id'), 404);

        return view('auth.registration_otp');
    }

    public function confirm(Request $request, RegistrationOtpService $otp)
    {
        abort_unless(config('security.registration_otp.enabled'), 404);

        $data = $request->validate([
            'otp' => ['required', 'digits:6'],
        ]);

        $id = $request->session()->get('pending_registration_id');
        abort_unless(is_string($id) && $id !== '', 404);

        $user = $otp->confirm($id, $data['otp']);
        $request->session()->forget('pending_registration_id');
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route(
            $user->user_type === UserType::EMPLOYER ? 'employer.dashboard' : 'view-jobseeker-profile'
        )->with('success', 'Your email has been verified. Welcome!');
    }
}
