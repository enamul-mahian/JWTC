<?php

return [
    // Opt in on isolated staging first. Legacy public layouts and uploads
    // remain intact until branding/content and mobile QA are approved.
    'enabled' => env('JWTC_REDESIGN_ENABLED', false),
];
