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
 * El middleware 'auth:sanctum' verifica automaticamente el token.
 * Si el token es inválido o no existe, retorna 401 Unauthorized
 */
Route::middleware('auth:sanctum')->group(function () {
    // POST /api/auth/logout
    // Cierra la sesión actual y revoca el token
    Route::post('/logout', [AuthController::class, 'logout']);

    // GET /api/user
    // Obtiene la información del usuario autenticado
    Route::get('/user', [AuthController::class, 'user']);

    // Aqui continua la lista de rutas protegidas

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