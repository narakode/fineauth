<?php

namespace Narakode\FineAuth;

use Illuminate\Support\Facades\Route;

class FineAuth
{

    public static function routes()
    {
        Route::group([], function () {
            require __DIR__ . '/../routes/api.php';
        });
    }

}