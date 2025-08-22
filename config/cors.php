<?php

return [

    'paths' => ['api/*', 'sanctum/csrf-cookie', '/uploads'],

    'allowed_methods' => ['*'],

   'allowed_origins' => [
    'https://vendurhub.com',
    'https://www.vendurhub.com',
    'https://api.vendurhub.com',
    'https://www.api.vendurhub.com',
],

    // Allow localhost with any port
    'allowed_origins_patterns' => [
        '/^http:\/\/localhost(:[0-9]+)?$/',
        '/^http:\/\/127\.0\.0\.1(:[0-9]+)?$/',
    ],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];