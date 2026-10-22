<?php

return [
    'enabled' => env('EMAIL_STUDIO_ENABLED', false),
    'from_address' => env('EMAIL_STUDIO_FROM_ADDRESS', env('MAIL_FROM_ADDRESS')),
    'from_name' => env('EMAIL_STUDIO_FROM_NAME', env('MAIL_FROM_NAME', 'Join Gallagher')),
    // Templates / send permissions and credentials are separate.
    // SMTP credentials belong ONLY in MAIL_* environment configuration.
    'max_pdf_kb' => 8192,
    'max_image_kb' => 2048,
    'max_body_chars' => 40000,
    'max_templates' => 200,
    'retention_days' => 90,
];
