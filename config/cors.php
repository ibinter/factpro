<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Applied to routes matching 'paths'. For the public API v1, origins are
    | restricted to the production domain and localhost in development.
    | The web (Inertia) routes do not need CORS since they are same-origin.
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],

    'allowed_origins' => [
        env('APP_URL', 'http://localhost'),
        'https://factpro.ibigsoft.com',
    ],

    'allowed_origins_patterns' => [
        '#^https?://localhost(:\d+)?$#',
        '#^https?://127\.0\.0\.1(:\d+)?$#',
    ],

    'allowed_headers' => ['Content-Type', 'X-Requested-With', 'Authorization', 'Accept', 'X-CSRF-TOKEN'],

    'exposed_headers' => ['X-RateLimit-Limit', 'X-RateLimit-Remaining', 'X-RateLimit-Reset'],

    'max_age' => 3600,

    'supports_credentials' => true,
];
