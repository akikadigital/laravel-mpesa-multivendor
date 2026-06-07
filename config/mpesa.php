<?php

return [
    // Define mpesa environment
    'env' => env('MPESA_ENV', 'sandbox'),
    'debug' => env('MPESA_DEBUG_MODE', true),
    'sandbox' => [
        'url' => 'https://sandbox.safaricom.co.ke',
    ],
    'production' => [
        'url' => 'https://api.safaricom.co.ke',
    ],
    /* Optional parameters for Daraja API:
    * Ideally, these should be set from the database to allow multivendor support,
    * however, we can use this as a fallback in case the database values are not set.
    */
    'shortcode' => env('MPESA_SHORTCODE', ''),
    'consumer_key' => env('MPESA_CONSUMER_KEY', ''),
    'consumer_secret' => env('MPESA_CONSUMER_SECRET', ''),
    'api_username' => env('MPESA_API_USERNAME', ''),
    'api_password' => env('MPESA_API_PASSWORD', ''),
    'passkey' => env('MPESA_PASSKEY', ''),
];
