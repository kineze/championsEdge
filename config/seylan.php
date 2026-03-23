<?php

return [
    'merchant_id' => env('SEYLAN_MERCHANT_ID'),
    'password' => env('SEYLAN_API_PASSWORD'),
    'api_version' => env('SEYLAN_API_VERSION', '100'),
    'sandbox' => env('SEYLAN_SANDBOX', true),
    'base_test' => env('SEYLAN_BASE_TEST', 'https://test-seylan.mtf.gateway.mastercard.com'),
    'base_live' => env('SEYLAN_BASE_LIVE', 'https://seylan.mtf.gateway.mastercard.com'),
];
