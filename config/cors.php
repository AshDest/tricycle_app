<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for cross-origin resource sharing (API access from website).
    |
    */

    'paths' => ['api/*'],

    'allowed_methods' => ['GET'],

    'allowed_origins' => ['*'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['Content-Type', 'Accept', 'X-Requested-With'],

    'exposed_headers' => [],

    'max_age' => 3600,

    'supports_credentials' => false,

];

