<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | CORS permite que tu API sea accesible desde diferentes dominios.
    | Esto es necesario para aplicaciones móviles y frontends separados.
    |
    */

    // Rutas a las que se aplica CORS
    // paths => ['api/*'] significa que todas las rutas que empiecen con /api tendrán CORS
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    // Métodos HTTP permitidos
    // * = todos los métodos (GET, POST, PUT, PATCH, DELETE, OPTIONS)
    'allowed_methods' => ['*'],

    // Orígenes permitidos
    // * = cualquier origen puede acceder (útil en desarrollo)
    // En producción, especifica los dominios exactos:
    // ['https://tumercadosena.com', 'https://admin.tumercadosena.com']
    'allowed_origins' => ['*'],

    // Patrones de orígenes permitidos (regex)
    'allowed_origins_patterns' => [],

    // Headers permitidos en las peticiones
    // * = todos los headers
    'allowed_headers' => ['*'],

    // Headers expuestos al cliente
    // Por ejemplo, si retornas headers personalizados, agrégalos aquí
    'exposed_headers' => [],

    // Edad máxima del cache de preflight (OPTIONS request)
    // 0 = no cachear, lo cual está bien para desarrollo
    // En producción, usa algo como 86400 (24 horas)
    'max_age' => 0,

    // ¿Soportar credenciales? (cookies, authorization headers, TLS certificates)
    // true = sí (necesario para Sanctum con cookies)
    'supports_credentials' => true,

];