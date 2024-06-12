<?php

use App\Http\Controllers\CRM\AuthController;
use App\Http\Controllers\CRM\MainController;
use App\Http\Controllers\CRM\UserController;
use App\Http\Controllers\QRController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [AuthController::class, 'login'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');

Route::get('qr/show', [QRController::class, 'showActualSession'])->name('qr.show');

Route::middleware('auth')->group(function () {
    Route::get('/', [MainController::class, 'index'])->name('main');
    Route::get('logs', [MainController::class, 'logs'])->name('logs');

    Route::get('users', [UserController::class, 'index'])->name('users');
    Route::get('users/add', [UserController::class, 'create'])->name('users.add');
    Route::post('users/add', [UserController::class, 'create'])->name('users.create');
    Route::get('users/{id}', [UserController::class, 'show'])->name('users.show');
    Route::post('users/{id}/ban', [UserController::class, 'toggleBanUser'])->name('users.ban');
    Route::post('users/{id}/unban', [UserController::class, 'toggleBanUser'])->name('users.unban');

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
