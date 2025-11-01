<?php

use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ImportadoresController;

Route::middleware(['auth:sanctum'])->group(function () {

    // Devolver el usuario con sus roles
    Route::get('/user', function (Request $request) {
        return $request->user()->load('roles');
    });

    // Importadores
    Route::get('/importadores', [ImportadoresController::class, 'index']);
    Route::get('/importadores/{id}', [ImportadoresController::class, 'show']);
    Route::post('/importadores', [ImportadoresController::class, 'store']);

    // Usuarios (solo admin)
    Route::middleware('admin')->group(function () {
        Route::get('/usuarios', [UserController::class, 'index']);
        Route::post('/usuarios', [UserController::class, 'store']);
        Route::put('/usuarios/{user}', [UserController::class, 'update']);
        Route::delete('/usuarios/{user}', [UserController::class, 'destroy']);
    });
});
