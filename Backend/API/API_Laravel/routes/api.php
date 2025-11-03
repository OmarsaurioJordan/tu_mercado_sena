<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\IntegridadController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\SubCategoriaController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\FavoritoController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\MensajeController;
use App\Services\AuthService;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

/**
 * RUTAS PÚBLICAS (Sin autenticación)
 * Cualquiera puede entrar a ellas
 */
Route::prefix('auth')->group(function()  {
    // POST /api/auth/register
    // Registra un nuevo usuario
    Route::post('/register', [AuthController::class, 'register']);

    // POST /api/auth/login
    // Inicia sesión y retornar un token
    Route::post('/login', [AuthController::class, 'login']);

});

/**
 * RUTAS PROTEGIDAS (Requieren autenticación)
 * 
 * El middleware personalizado "jwtVerify" verifica el token.
 * 
 */
Route::middleware('jwtVerify')->group(function (){

    // === AUTENTICACIÓN ===
    Route::prefix('auth')->group(function () {
        // Cerrar sesión
        Route::post('/logout', [AuthController::class, 'logout']);

        // Refrescar token
        Route::post('/refresh', [AuthController::class, 'refresh']);

        // Obtener usuario autenticado
        Route::get('/me', [AuthController::class, 'me']);
    });
});

/**
 * RUTAS DE PRUEBA
 * 
 * GET /api/ping
 */
Route::get('/ping', function () {
    return response()->json([
        'message' => 'pong',
        'timestamp' => now()->toIso8601String()
    ]);
});

Route::get('test-injection', function (AuthService $authService) {
    return response()->json([
        'message' => 'Inyección de dependencias funciona correctamente',
        'service_class' => get_class($authService),
    ]);
});