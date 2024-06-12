<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\QRController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->controller(AuthController::class)->group(function () {
    Route::post('login', 'login');
    Route::post('token', 'launchApp');
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('qr/generate', [QRController::class, 'generate']);
});

Route::post('qr/check/{id}', [QRController::class, 'check']);
