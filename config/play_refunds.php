<?php

return [
    // A dedicated production cron is installed only after the verified rollout.
    'enabled' => env('SHELF_PLAY_REFUNDS_ENABLED', true),
];
