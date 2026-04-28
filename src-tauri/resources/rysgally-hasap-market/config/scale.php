<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Scale Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for electronic scale integration via Ethernet
    |
    */

    'ip' => env('SCALE_IP', '192.168.1.100'),
    'port' => env('SCALE_PORT', 8080),
    'timeout' => env('SCALE_TIMEOUT', 10),

    /*
    |--------------------------------------------------------------------------
    | Auto Export Settings
    |--------------------------------------------------------------------------
    |
    | Automatically export weighable products to scale on creation/update
    |
    */

    'auto_export_on_create' => env('SCALE_AUTO_EXPORT_ON_CREATE', true),
    'auto_export_on_update' => env('SCALE_AUTO_EXPORT_ON_UPDATE', false),

    /*
    |--------------------------------------------------------------------------
    | Scale API Endpoints
    |--------------------------------------------------------------------------
    |
    | API endpoints for scale operations
    |
    */

    'endpoints' => [
        'health' => '/api/health',
        'products' => '/api/products',
        'storage' => '/api/storage',
    ],
];
