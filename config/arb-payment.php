<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Al Rajhi Bank Payment Gateway Configuration
    |--------------------------------------------------------------------------
    */

    'environment' => env('ARB_ENVIRONMENT', 'test'), // 'test' or 'production'

    /*
    |--------------------------------------------------------------------------
    | Merchant Information
    |--------------------------------------------------------------------------
    */
    'merchant_id' => env('ARB_MERCHANT_ID', ''),

    /*
    |--------------------------------------------------------------------------
    | Test Environment Configuration
    |--------------------------------------------------------------------------
    */
    'test' => [
        'payment_url' => env('ARB_TEST_PAYMENT_URL', 'https://securepayments.neoleap.com.sa/pg/payment/hosted.htm'),
        'api_url' => env('ARB_TEST_API_URL', 'https://securepayments.neoleap.com.sa/pg/payment/tranportal.htm'),
        'tranportal_id' => env('ARB_TEST_TRANPORTAL_ID', ''),
        'tranportal_password' => env('ARB_TEST_TRANPORTAL_PASSWORD', ''),
        'resource_key' => env('ARB_TEST_RESOURCE_KEY', ''),
        'terminal_id' => env('ARB_TEST_TERMINAL_ID', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | Production Environment Configuration
    |--------------------------------------------------------------------------
    */
    'production' => [
        'payment_url' => env('ARB_PAYMENT_URL', ''),
        'api_url' => env('ARB_API_URL', ''),
        'tranportal_id' => env('ARB_TRANPORTAL_ID', ''),
        'tranportal_password' => env('ARB_TRANPORTAL_PASSWORD', ''),
        'resource_key' => env('ARB_RESOURCE_KEY', ''),
        'terminal_id' => env('ARB_TERMINAL_ID', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | Currency Settings
    |--------------------------------------------------------------------------
    */
    'currency' => env('ARB_CURRENCY', 'SAR'),
    'currency_code' => env('ARB_CURRENCY_CODE', '682'), // SAR = 682 (ISO 4217 numeric code)

    /*
    |--------------------------------------------------------------------------
    | Callback URLs
    |--------------------------------------------------------------------------
    */
    'response_url' => env('ARB_RESPONSE_URL', env('APP_URL') . '/arb/callback'),
    'error_url' => env('ARB_ERROR_URL', env('APP_URL') . '/arb/error'),

    /*
    |--------------------------------------------------------------------------
    | Language
    |--------------------------------------------------------------------------
    */
    'language' => env('ARB_LANGUAGE', 'USA'), // 'USA' for English, 'AR' for Arabic

    /*
    |--------------------------------------------------------------------------
    | Transaction Settings
    |--------------------------------------------------------------------------
    */
    'transaction' => [
        'action' => env('ARB_TRANSACTION_ACTION', '1'), // 1: Purchase, 2: Authorization
        'timeout' => env('ARB_TRANSACTION_TIMEOUT', 30), // seconds
        'log_enabled' => env('ARB_LOG_ENABLED', true),
    ],
];
