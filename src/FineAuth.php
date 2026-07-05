<?php

namespace Narakode\FineAuth;

use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Route;

class FineAuth
{

    public static function routes(array $attributes = [])
    {
        Route::group($attributes, function () {
            require __DIR__ . '/../routes/api.php';
        });
    }

    public static function createAuthResult(User $user)
    {
        return [
            'access_token' => $user->createToken('api')->plainTextToken,
            'user' => $user
        ];
    }

}