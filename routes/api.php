<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\ImportadoresController;
use App\Http\Controllers\ImportadorDocumentosController;
use App\Http\Controllers\UserController;

Route::middleware(['auth:sanctum'])->group(function () {

    Route::get('/user', function (Request $request) {
        return $request->user()->load('roles');
    });

    // Importadores (consulta para todos)
    Route::get('/importadores', [ImportadoresController::class, 'index']);
    Route::get('/importadores/{id}', [ImportadoresController::class, 'show']);

    // Documentos (consulta/descarga para todos)
    Route::get('/importadores/{id}/documentos', [ImportadorDocumentosController::class, 'index']);
    Route::get('/documentos/{doc}/download', [ImportadorDocumentosController::class, 'download']);

    // Acciones restringidas a admin
    Route::middleware('admin')->group(function () {

        // Importadores (solo admin)
        Route::post('/importadores', [ImportadoresController::class, 'store']);
        Route::put('/importadores/{id}', [ImportadoresController::class, 'update']);
        Route::delete('/importadores/{id}', [ImportadoresController::class, 'destroy']);

        // Documentos (solo admin)
        Route::post('/importadores/{id}/documentos', [ImportadorDocumentosController::class, 'store']);
        Route::delete('/documentos/{doc}', [ImportadorDocumentosController::class, 'destroy']);

        // Usuarios (solo admin)
        Route::get('/usuarios', [UserController::class, 'index']);
        Route::post('/usuarios', [UserController::class, 'store']);
        Route::put('/usuarios/{user}', [UserController::class, 'update']);
        Route::delete('/usuarios/{user}', [UserController::class, 'destroy']);
    });
});
