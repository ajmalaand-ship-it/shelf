<?php

return [
    // Enable after migration and SMTP validation. Registration accepts any email;
    // staging is required before any reader other than the owner uses the app.
    'enabled' => env('READER_ACCOUNTS_ENABLED', false),
    'google_web_client_id' => env('GOOGLE_WEB_CLIENT_ID'),
    'google_android_client_id' => env('GOOGLE_ANDROID_CLIENT_ID'),
];
