<?php

use Illuminate\Support\Facades\Route;
use Narakode\FineAuth\Http\Controllers\AuthController;

Route::post('/login', [AuthController::class, 'login'])->name('auth.login');
Route::get('/me', [AuthController::class, 'me'])
    ->middleware('auth:sanctum')
    ->name('auth.me');