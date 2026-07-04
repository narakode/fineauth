<?php

use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Support\Facades\Route;
use Narakode\FineAuth\Http\Controllers\AuthController;

Route::middleware(EncryptCookies::class)
    ->group(function () {
        Route::post('/login', [AuthController::class, 'login'])->name('auth.login');
        Route::get('/me', [AuthController::class, 'me'])
            ->middleware('auth:sanctum')
            ->name('auth.me');
        Route::post('/refresh-token', [AuthController::class, 'refreshToken'])->name('auth.refresh-token');
    });