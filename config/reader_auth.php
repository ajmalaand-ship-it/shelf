<?php

return [
    // Enable after migration and SMTP validation. Registration accepts any email;
    // staging is required before any reader other than the owner uses the app.
    'apple_enabled' => env('APPLE_SIGN_IN_ENABLED', false),
    'apple_client_id' => env('APPLE_SIGN_IN_CLIENT_ID'),
    'apple_team_id' => env('APPLE_SIGN_IN_TEAM_ID'),
    'apple_key_id' => env('APPLE_SIGN_IN_KEY_ID'),
    'apple_private_key_path' => env('APPLE_SIGN_IN_PRIVATE_KEY_PATH'),
    'apple_relay_ready' => env('APPLE_SIGN_IN_RELAY_READY', false),
    'enabled' => env('READER_ACCOUNTS_ENABLED', false),
    'google_web_client_id' => env('GOOGLE_WEB_CLIENT_ID'),
    'google_android_client_id' => env('GOOGLE_ANDROID_CLIENT_ID'),
];
