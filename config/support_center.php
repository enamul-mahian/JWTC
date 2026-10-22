<?php

return [
    'enabled' => env('SUPPORT_CENTER_ENABLED', false),
    'max_open_tickets_per_user' => 20,
    'max_ticket_length' => 10000,
    'max_reply_length' => 5000,
    'max_refund_reason_length' => 5000,
    'currencies' => ['USD', 'GBP', 'EUR', 'BDT'],
    'attachments_enabled' => false,
    // Not a payment gateway and never issues money movements automatically.
    'automatic_refunds_enabled' => false,
];
