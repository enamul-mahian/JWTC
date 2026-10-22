<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;

class ValidHcaptcha implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!config('services.hcaptcha.enabled', false)) {
            return;
        }

        $secret = config('services.hcaptcha.secret');
        $siteKey = config('services.hcaptcha.sitekey');

        // Enabled protection must never be silently bypassed when misconfigured.
        if (!is_string($secret) || $secret === '' ||
            !is_string($siteKey) || $siteKey === '' ||
            !is_string($value) || $value === '') {
            $fail('Human verification is required. Please refresh and try again.');
            return;
        }

        try {
            $response = Http::asForm()
                ->acceptJson()
                ->timeout(8)
                ->post('https://api.hcaptcha.com/siteverify', [
                    'secret' => $secret,
                    'sitekey' => $siteKey,
                    'response' => $value,
                    // Do not use user-supplied forwarding headers as an IP source.
                    'remoteip' => request()->ip(),
                ]);

            if ($response->successful() && $response->json('success') === true) {
                return;
            }
        } catch (\Throwable $exception) {
            // Never log tokens or the account secret. Fail closed.
        }

        $fail('Human verification failed or expired. Please retry the challenge.');
    }
}
