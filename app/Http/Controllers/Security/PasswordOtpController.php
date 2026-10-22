<?php

namespace App\Http\Controllers\Security;

use App\Http\Controllers\Controller;
use App\Services\PasswordOtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PasswordOtpController extends Controller
{
    public function confirm(Request $request, PasswordOtpService $passwordOtp)
    {
        abort_unless(config('security.password_change_otp.enabled'), 404);

        $validated = $request->validate([
            'otp' => ['required', 'digits:6'],
        ]);

        $passwordOtp->confirm($request->user(), $validated['otp']);

        // A password change invalidates other browser sessions as well.
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Password changed. Sign in again.']);
        }

        return redirect()->route('login')->with(
            'message', 'Password changed successfully. Please sign in again.'
        );
    }
}
