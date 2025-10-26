<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::get('/health', fn () => response()->json([
    'status' => 'ok',
    'framework' => 'Laravel',
], 200));

Route::prefix('auth')->group(function () {
    // Rutas públicas
    Route::post('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/login',    [AuthController::class, 'login'])->name('login');

    // 🔒 Rutas protegidas por JWT
    Route::middleware('jwt.verify')->group(function () {
        Route::get('/me',      [AuthController::class, 'me'])->name('me');
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::post('/refresh',[AuthController::class, 'refresh'])->name('refresh');
    });
});