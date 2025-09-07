<?php

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

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::resource('/integridades', IntegridadController::class);
Route::apiResource('/categorias', CategoriaController::class);
Route::apiResource('/subcategorias', SubCategoriaController::class);
Route::apiResource('/usuarios', UsuarioController::class);
Route::apiResource('/productos', ProductoController::class);
Route::apiResource('/favoritos', FavoritoController::class);
Route::apiResource('/chats', ChatController::class);
Route::apiResource('/mensajes', MensajeController::class);