<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Stateful Domains
    |--------------------------------------------------------------------------
    |
    | Aquí defines los dominios que usarán autenticación stateful (con cookies).
    | Para una SPA (Single Page Application) en el mismo dominio, usa cookies.
    | Para aplicaciones móviles o frontend separado, usa tokens (lo que hacemos).
    |
    | Por ahora, déjalo vacío porque usaremos solo tokens.
    */
    'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', sprintf(
        '%s%s',
        'localhost,localhost:3000,127.0.0.1,127.0.0.1:8000,::1',
        env('APP_URL') ? ','.parse_url(env('APP_URL'), PHP_URL_HOST) : ''
    ))),

    /*
    |--------------------------------------------------------------------------
    | Sanctum Guards
    |--------------------------------------------------------------------------
    |
    | Define qué guards de autenticación debe usar Sanctum.
    | Por defecto usa 'web', pero como usamos tokens API, esto no es crítico.
    */
    'guard' => ['web'],

    /*
    |--------------------------------------------------------------------------
    | Expiration Minutes
    |--------------------------------------------------------------------------
    |
    | Define cuánto tiempo (en minutos) son válidos los tokens antes de expirar.
    | null = los tokens nunca expiran (debes revocarlos manualmente)
    | 
    | Para tu proyecto, recomiendo null porque:
    | - Los usuarios del SENA no quieren estar logueándose constantemente
    | - Puedes implementar revocación manual si un usuario reporta robo
    | - Si quieres expiración, usa algo como 43200 (30 días)
    */
    'expiration' => null,

    /*
    |--------------------------------------------------------------------------
    | Sanctum Middleware
    |--------------------------------------------------------------------------
    |
    | When authenticating your first-party SPA with Sanctum you may need to
    | customize some of the middleware Sanctum uses while processing the
    | request. You may change the middleware listed below as required.
    |
    */

    'middleware' => [
        'authenticate_session' => Laravel\Sanctum\Http\Middleware\AuthenticateSession::class,
        'encrypt_cookies' => Illuminate\Cookie\Middleware\EncryptCookies::class,
        'validate_csrf_token' => Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
    ],

];
