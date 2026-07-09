<?php

namespace Narakode\FineAuth;

use Illuminate\Support\ServiceProvider;
use Narakode\FineAuth\Auth\AuthCredentials;
use Narakode\FineAuth\Auth\Authenticator;
use Narakode\FineAuth\Auth\AuthMeta;
use Narakode\FineAuth\Auth\Default\AuthMeta as DefaultAuthMeta;
use Narakode\FineAuth\Auth\Default\Authenticator as DefaultAuthenticator;
use Narakode\FineAuth\Auth\Default\AuthCredentials as DefaultAuthCredentials;

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

    public function register()
    {
        $this->app->singleton(AuthMeta::class, DefaultAuthMeta::class);
        $this->app->singleton(Authenticator::class, DefaultAuthenticator::class);
        $this->app->singleton(AuthCredentials::class, DefaultAuthCredentials::class);

        $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'fineauth');
    }
}