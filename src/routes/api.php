<?php

use Illuminate\Support\Facades\Route;
use Narakode\FineAuth\Http\Controllers\AuthController;

Route::post('/login', [AuthController::class, 'login'])->name('auth.login');