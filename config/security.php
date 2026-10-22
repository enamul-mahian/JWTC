<?php

return [
    // Enable only after running the additive OTP migration and verifying SMTP
    // on a staging environment. Existing production users are unaffected.
    // Enable only after migrations, SMTP and both browser/API flows are verified.
    'registration_otp' => [
        'enabled' => env('REGISTRATION_EMAIL_OTP_ENABLED', false),
    ],
    'password_change_otp' => [
        'enabled' => env('PASSWORD_CHANGE_OTP_ENABLED', false),
        'ttl_minutes' => 10,
        'resend_seconds' => 60,
        'max_attempts' => 5,
    ],
];
