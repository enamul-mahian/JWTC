<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class PasswordOtpService
{
    private const PURPOSE = 'password_change';

    /**
     * The new password is stored only as a password hash, never as plaintext.
     * SMTP must work before enabling this flow.
     */
    public function issue(User $user, string $newPasswordHash): void
    {
        $now = now();
        $code = (string) random_int(100000, 999999);
        $codeHash = Hash::make($code);

        DB::transaction(function () use ($user, $newPasswordHash, $now, $codeHash) {
            User::whereKey($user->getKey())->lockForUpdate()->firstOrFail();

            $existing = DB::table('email_otp_challenges')
                ->where('user_id', $user->getKey())
                ->where('purpose', self::PURPOSE)
                ->lockForUpdate()->first();

            if ($existing && $now->diffInSeconds($existing->sent_at) < (int) config('security.password_change_otp.resend_seconds', 60)
                && $now->greaterThanOrEqualTo($existing->sent_at)) {
                throw ValidationException::withMessages([
                    'otp' => 'An email code was recently sent. Try again after one minute.',
                ]);
            }

            DB::table('email_otp_challenges')->updateOrInsert([
                'user_id' => $user->getKey(), 'purpose' => self::PURPOSE,
            ], [
                'code_hash' => $codeHash,
                'payload_hash' => $newPasswordHash,
                'attempts' => 0,
                'expires_at' => $now->copy()->addMinutes((int) config('security.password_change_otp.ttl_minutes', 10)),
                'sent_at' => $now,
                'updated_at' => $now,
                'created_at' => $existing->created_at ?? $now,
            ]);
        }, 3);

        try {
            Mail::raw(
                "Your JWTC password change verification code is: {$code}\n"
                . "It expires in 10 minutes. If you did not request this change, ignore this email.",
                function ($message) use ($user) {
                    $message->to($user->email)->subject('JWTC password verification code');
                }
            );
        } catch (\Throwable $error) {
            DB::table('email_otp_challenges')
                ->where('user_id', $user->getKey())
                ->where('purpose', self::PURPOSE)
                ->where('code_hash', $codeHash)
                ->delete();
            throw ValidationException::withMessages([
                'otp' => 'We could not deliver your email verification code. Please try again.',
            ]);
        }
    }

    public function confirm(User $user, string $code): void
    {
        DB::transaction(function () use ($user, $code) {
            $lockedUser = User::whereKey($user->getKey())->lockForUpdate()->firstOrFail();
            $challenge = DB::table('email_otp_challenges')
                ->where('user_id', $lockedUser->getKey())
                ->where('purpose', self::PURPOSE)
                ->lockForUpdate()->first();

            if (!$challenge || now()->greaterThan($challenge->expires_at)) {
                throw ValidationException::withMessages(['otp' => 'Your code has expired. Request a new code.']);
            }
            if ((int) $challenge->attempts >= (int) config('security.password_change_otp.max_attempts', 5)) {
                throw ValidationException::withMessages(['otp' => 'Too many attempts. Request a new code.']);
            }
            if (!Hash::check($code, $challenge->code_hash)) {
                DB::table('email_otp_challenges')
                    ->where('id', $challenge->id)
                    ->update(['attempts' => (int) $challenge->attempts + 1]);
                return false;
            }

            $lockedUser->forceFill(['password' => $challenge->payload_hash])->save();
            DB::table('email_otp_challenges')->where('id', $challenge->id)->delete();
            $lockedUser->tokens()->delete();

            return true;
        }, 3) ?: throw ValidationException::withMessages([
            'otp' => 'Invalid verification code.',
        ]);
    }
}
