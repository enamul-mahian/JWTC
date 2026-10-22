<?php

return [
    // Enable only after running the additive OTP migration and verifying SMTP
    // on a staging environment. Existing production users are unaffected.
    'password_change_otp' => [
        'enabled' => env('PASSWORD_CHANGE_OTP_ENABLED', false),
        'ttl_minutes' => 10,
        'resend_seconds' => 60,
        'max_attempts' => 5,
    ],
];
