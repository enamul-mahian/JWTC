<?php

namespace App\Services;

use App\Enum\RoleType;
use App\Enum\StatusType;
use App\Enum\UserType;
use App\Models\PersonalInformation;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class RegistrationOtpService
{
    public function issue(array $data): string
    {
        $email = Str::lower(trim((string) $data['email']));
        $now = now();
        $code = (string) random_int(100000, 999999);
        $id = (string) Str::uuid();

        if (!in_array($data['user_type'], [UserType::EMPLOYER, UserType::JOB_SEEKER], true)) {
            throw ValidationException::withMessages(['user_type' => 'Invalid account type.']);
        }
        if (User::where('email', $email)->exists()) {
            throw ValidationException::withMessages(['email' => 'This email is already registered.']);
        }

        $data['email'] = $email;
        // Only a password hash can enter persistent storage, never a plaintext password.
        $data['password_hash'] = Hash::make($data['password']);
        unset($data['password'], $data['password_confirmation'], $data['h-captcha-response']);

        DB::transaction(function () use ($email, $now, $code, $id, $data) {
            // Serialize requests for the same email via the unique email index.
            $existing = DB::table('pending_registrations')
                ->where('email', $email)->lockForUpdate()->first();

            if ($existing && now()->lessThan(\Illuminate\Support\Carbon::parse($existing->sent_at)->addSeconds(60))) {
                throw ValidationException::withMessages([
                    'otp' => 'A verification code was recently sent. Please wait one minute.',
                ]);
            }

            if ($existing) {
                DB::table('pending_registrations')->where('id', $existing->id)->delete();
            }

            DB::table('pending_registrations')->insert([
                'id' => $id, 'email' => $email,
                'encrypted_payload' => Crypt::encryptString(json_encode($data, JSON_THROW_ON_ERROR)),
                'code_hash' => Hash::make($code),
                'attempts' => 0,
                'expires_at' => $now->copy()->addMinutes(10),
                'sent_at' => $now, 'created_at' => $now, 'updated_at' => $now,
            ]);
        }, 3);

        try {
            Mail::raw(
                "Your JWTC registration verification code is: {$code}\n"
                . "This code expires in 10 minutes. Do not share it with anyone.",
                function ($message) use ($email) {
                    $message->to($email)->subject('JWTC registration verification');
                }
            );
        } catch (\Throwable $exception) {
            DB::table('pending_registrations')->where('id', $id)->delete();
            throw ValidationException::withMessages([
                'otp' => 'We could not deliver the verification code. Please try again.',
            ]);
        }

        return $id;
    }

    public function confirm(string $id, string $code): User
    {
        $created = DB::transaction(function () use ($id, $code) {
            $record = DB::table('pending_registrations')->where('id', $id)
                ->lockForUpdate()->first();

            if (!$record || now()->greaterThan($record->expires_at)) {
                throw ValidationException::withMessages([
                    'otp' => 'The code has expired. Register again to get a fresh code.',
                ]);
            }
            if ((int) $record->attempts >= 5) {
                throw ValidationException::withMessages([
                    'otp' => 'Too many attempts. Register again.',
                ]);
            }
            if (!Hash::check($code, $record->code_hash)) {
                DB::table('pending_registrations')->where('id', $id)
                    ->update(['attempts' => (int) $record->attempts + 1]);
                return null;
            }

            $data = json_decode(Crypt::decryptString($record->encrypted_payload), true, 512, JSON_THROW_ON_ERROR);
            if (User::where('email', $record->email)->exists() ||
                User::where('phone', $data['phone'])->exists()) {
                throw ValidationException::withMessages([
                    'email' => 'This email or phone number has already been registered.',
                ]);
            }

            $roleName = $data['user_type'] === UserType::EMPLOYER
                ? RoleType::EMPLOYER : RoleType::JOB_SEEKER;
            $role = Role::where('name', $roleName)->where('guard_name', 'web')->first();
            if (!$role) {
                throw ValidationException::withMessages([
                    'otp' => 'Account registration is temporarily unavailable.',
                ]);
            }

            $user = User::create([
                'name' => $data['name'],
                'email' => $record->email,
                'phone' => $data['phone'],
                'user_type' => $data['user_type'],
                'job_category_id' => $data['job_category_id'] ?? null,
                'status' => StatusType::ACTIVE,
                'password' => $data['password_hash'],
            ]);
            $user->forceFill(['email_verified_at' => now()])->save();
            $user->assignRole($role);

            if ($data['user_type'] === UserType::JOB_SEEKER) {
                PersonalInformation::create([
                    'user_id' => $user->id,
                    'first_name' => $data['name'],
                    'last_name' => $data['name'],
                ]);
            }

            DB::table('pending_registrations')->where('id', $id)->delete();
            return $user;
        }, 3);

        if (!$created) {
            throw ValidationException::withMessages(['otp' => 'Invalid verification code.']);
        }

        event(new Registered($created));
        return $created;
    }
}
