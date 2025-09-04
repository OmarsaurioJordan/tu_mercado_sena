<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\IntegridadController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\SubCategoriaController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::resource('/integridades', IntegridadController::class);
Route::apiResource('/categorias', CategoriaController::class);
Route::apiResource('/subcategorias', SubCategoriaController::class);
