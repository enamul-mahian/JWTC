<?php

return [
    // Disabled on production until staging migration, RBAC review and sign-off.
    'enabled' => env('APPLICATION_CHAT_ENABLED', false),
    'max_message_length' => 4000,
    'poll_seconds' => 15,
    // File sharing is intentionally disabled until malware scanning,
    // secure storage, retention and content review are implemented.
    'attachments_enabled' => false,
];
