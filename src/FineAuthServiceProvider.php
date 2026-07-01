<?php

namespace Narakode\FineAuth;

use Illuminate\Support\ServiceProvider;

class FineAuthServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/routes/api.php');

        if ($this->app->runningInConsole()) {
            $this->publishesMigrations([__DIR__ . './migrations']);
        }
    }
}