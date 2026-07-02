<?php

namespace Narakode\FineAuth;

use Illuminate\Support\ServiceProvider;

class FineAuthServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishesMigrations([
                __DIR__ . '/../database/migrations' => database_path('migrations')
            ]);
        }
    }
}