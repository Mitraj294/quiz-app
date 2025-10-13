<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. Adjust these settings as needed for your dev setup.
    |
    */

    // Apply CORS headers to all routes (including web routes like /register)
    'paths' => ['*'],

    'allowed_methods' => ['*'],

    // Allow the Vite dev origin(s). Do NOT use ['*'] when credentials are true.
    'allowed_origins' => [
        'http://127.0.0.1:8000',
        'http://localhost:8000',
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    // Expose Inertia headers so client can validate the response and version
    'exposed_headers' => ['X-Inertia', 'X-Inertia-Version'],

    'max_age' => 0,

    // We need credentials (cookies) for auth flows (Sanctum/Inertia)
    'supports_credentials' => true,

];
