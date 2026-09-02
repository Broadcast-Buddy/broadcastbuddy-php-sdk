<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Broadcast Buddy API Key (Session ID)
    |--------------------------------------------------------------------------
    |
    | In Broadcast Buddy, your API Key corresponds to your active WhatsApp
    | Session ID found in your dashboard at https://broadcastbuddy.app.
    |
    */
    'api_key' => env('BROADCAST_BUDDY_API_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | Request Timeout
    |--------------------------------------------------------------------------
    |
    | Maximum time in seconds to wait for responses from the Broadcast Buddy API.
    |
    */
    'timeout' => env('BROADCAST_BUDDY_TIMEOUT', 30),
];
