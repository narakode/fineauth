<?php

namespace Narakode\FineAuth;

use Illuminate\Support\Facades\Route;

class FineAuth
{

    public static function routes(array $attributes = [])
    {
        Route::group($attributes, function () {
            require __DIR__ . '/../routes/api.php';
        });
    }

}