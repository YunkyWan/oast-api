<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ImportadoresController;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/importadores', [ImportadoresController::class, 'index']);
    Route::get('/importadores/{id}', [ImportadoresController::class, 'show']);
    Route::post('/importadores', [ImportadoresController::class, 'store']);
});
