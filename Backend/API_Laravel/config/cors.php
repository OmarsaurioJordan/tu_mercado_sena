<?php

return [

    'paths' => ['api/*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'http://localhost:3000',  // React
        'http://localhost:5173',  // Vite
        'http://localhost',       // apps locales
        '*'                       // permitir acceso desde móvil, ngrok, dominio, etc.
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],  // necesario para permitir Authorization: Bearer token

    'exposed_headers' => ['Authorization'],

    'max_age' => 0,

    'supports_credentials' => false, // IMPORTANTE en JWT
];
