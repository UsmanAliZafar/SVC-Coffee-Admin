<?php

return [

    /*
    |--------------------------------------------------------------------------
    | API Keys
    |--------------------------------------------------------------------------
    |
    | Valid API keys for authenticating requests. Store these securely!
    | You can generate keys using: php artisan api:generate-key
    |
    */

    'valid_keys' => [
        env('API_KEY_FRONT_APP', 'front_app_key_here'),
        // Add more API keys as needed
    ],

    /*
    |--------------------------------------------------------------------------
    | IP Whitelist
    |--------------------------------------------------------------------------
    |
    | Enable IP restriction for additional security
    |
    */

    'ip_whitelist_enabled' => env('API_IP_WHITELIST_ENABLED', false),

    'allowed_ips' => [
        '127.0.0.1',
        '::1',
        // Add warehouse server IPs
        // env('WAREHOUSE_SERVER_IP'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Control API request limits per API key
    |
    */

    'rate_limit' => [
        'enabled' => env('API_RATE_LIMIT_ENABLED', true),
        'max_requests' => env('API_RATE_LIMIT_MAX', 1000), // Max requests
        'per_minutes' => env('API_RATE_LIMIT_MINUTES', 60), // Time window in minutes
    ],

    /*
    |--------------------------------------------------------------------------
    | API Logging
    |--------------------------------------------------------------------------
    |
    | Configure API request logging
    |
    */

    'logging' => [
        'enabled' => env('API_LOGGING_ENABLED', true),
        'log_channel' => env('API_LOG_CHANNEL', 'daily'),
    ],

    /*
    |--------------------------------------------------------------------------
    | CORS Settings
    |--------------------------------------------------------------------------
    |
    | Configure CORS for API requests
    |
    */

    'cors' => [
        'allowed_origins' => [
            'http://localhost:3000',
            'https://your-warehouse-system.com',
            // Add your allowed origins
        ],
    ],

];
