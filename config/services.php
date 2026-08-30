<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'revenuecat' => [
        'secret_api_key' => env('REVENUECAT_SECRET_API_KEY'),
        'entitlement' => env('REVENUECAT_ENTITLEMENT', 'unlock_all'),
        'timeout_seconds' => env('REVENUECAT_TIMEOUT_SECONDS', 4),
        'positive_ttl_seconds' => env('REVENUECAT_POSITIVE_TTL_SECONDS', 86400),
        'negative_ttl_seconds' => env('REVENUECAT_NEGATIVE_TTL_SECONDS', 300),
    ],

];
