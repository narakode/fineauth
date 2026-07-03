<?php

namespace Narakode\FineAuth;

use Illuminate\Support\ServiceProvider;

class FineAuthServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishesMigrations([
                __DIR__ . '/../database/migrations/create_refresh_tokens_table.php' => database_path('migrations/' . date('Y_m_d_His') . '_create_refresh_tokens_table.php')
            ]);
        }
    }
}