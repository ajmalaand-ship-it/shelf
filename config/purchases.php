<?php

return [
    // Step 4 accepts only verified Google Play sandbox transactions.
    'enabled' => env('SHELF_PURCHASES_ENABLED', false),
    'public_sdk_key' => env('SHELF_REVENUECAT_ANDROID_KEY'),
    'secret_key' => env('SHELF_REVENUECAT_SECRET_KEY'),
    'webhook_authorization' => env('SHELF_REVENUECAT_WEBHOOK_AUTH'),
    'app_id' => env('SHELF_REVENUECAT_APP_ID'),
    'environment' => 'SANDBOX',
    'test_reader_ids' => [], // Isolated CLI/tests may inject synthetic reader IDs in-process only.
];
