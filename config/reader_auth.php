<?php

return [
    // Enable only after migration and SMTP validation. Public registration waits
    // for the owner's staging boundary; owner-email testing works before then.
    'enabled' => env('READER_ACCOUNTS_ENABLED', false),
    'public_registration' => env('READER_PUBLIC_REGISTRATION', false),
    'google_web_client_id' => env('GOOGLE_WEB_CLIENT_ID'),
    'google_android_client_id' => env('GOOGLE_ANDROID_CLIENT_ID'),
];
