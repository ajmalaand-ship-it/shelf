<?php

return [
    // Owner decision: first 100 books are managed manually in Play Console.
    'deferred' => true,
    'enabled' => env('SHELF_PLAY_SYNC_ENABLED', false),
    'credentials_path' => env('SHELF_PLAY_SERVICE_ACCOUNT_PATH'),
    'package' => 'services.shelf.app',
];
