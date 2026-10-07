<?php

return [
    // Production acceptance remains off until an explicit owner-approved launch rollout.
    'enabled' => env('SHELF_PURCHASES_ENABLED', false),
    'public_sdk_key' => env('SHELF_REVENUECAT_ANDROID_KEY'),
    'secret_key' => env('SHELF_REVENUECAT_SECRET_KEY'),
    // Existing V2 configuration key; no credential or provider-setting changes.
    'metadata_key_path' => env('SHELF_REVENUECAT_V2_SECRET_KEY_PATH'),
    'metadata_key' => null, // Synthetic tests only.
    'secret_key_path' => env('SHELF_REVENUECAT_SECRET_KEY_PATH'),
    'webhook_authorization' => env('SHELF_REVENUECAT_WEBHOOK_AUTH'),
    'app_id' => env('SHELF_REVENUECAT_APP_ID'),
    'production_enabled' => env('SHELF_REAL_PURCHASES_ENABLED', false),
    'staging_project_confirmed' => env('SHELF_STAGING_REVENUECAT_PROJECT_CONFIRMED', false),
    'test_reader_ids' => [], // Isolated CLI/tests may inject synthetic reader IDs in-process only.
];
