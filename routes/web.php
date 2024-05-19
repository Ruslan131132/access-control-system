<?php

use App\Http\Controllers\CRM\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [AuthController::class, 'login'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');

Route::middleware('auth')->group(function () {
    Route::get('/', fn () => view('main'))->name('main');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
