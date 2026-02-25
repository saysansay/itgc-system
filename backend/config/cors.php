<?php

return [

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    // Only allow same-origin by default; override with FRONTEND_URL if needed.
    'allowed_origins' => [env('FRONTEND_URL', env('APP_URL', 'http://localhost'))],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
