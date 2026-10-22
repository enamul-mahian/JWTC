<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => '/auth/google/callback',
    ],

    'linkedin' => [
        'client_id' => '787kr1qms5y60d',
        'client_secret' => 'Le1vUEYqG4Q5YHvI',
        'redirect' => 'http://localhost:8000/auth/linkedin/callback',
    ],


    'hcaptcha' => [
        'enabled' => env('HCAPTCHA_ENABLED', false),
        'sitekey' => env('HCAPTCHA_SITEKEY'),
        'secret' => env('HCAPTCHA_SECRET'),
    ],

];
