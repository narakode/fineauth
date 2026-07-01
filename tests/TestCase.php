<?php

namespace Narakode\FineAuth\Tests;

use Illuminate\Foundation\Testing\Attributes\Seeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Narakode\FineAuth\FineAuth;
use Narakode\FineAuth\FineAuthServiceProvider;
use Orchestra\Testbench\Attributes\WithMigration;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Workbench\Database\Seeders\DatabaseSeeder;

#[Seeder(DatabaseSeeder::class)]
#[WithMigration]
abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function getPackageProviders($app)
    {
        return [
            FineAuthServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app)
    {
        $app['config']->set('auth.providers.users.model', \Workbench\App\Models\User::class);
    }

    protected function defineDatabaseMigrations()
    {
        $this->loadMigrationsFrom([
            __DIR__ . '/../vendor/laravel/sanctum/database/migrations',
            __DIR__ . '/../database/migrations'
        ]);
    }

    protected function defineRoutes($router)
    {
        FineAuth::routes();
    }
}
