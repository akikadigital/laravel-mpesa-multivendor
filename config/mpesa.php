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
];
