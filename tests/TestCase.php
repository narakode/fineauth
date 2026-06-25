<?php

namespace Narakode\FineAuth\Tests;

use Narakode\FineAuth\FineAuthServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app)
    {
        return [
            FineAuthServiceProvider::class
        ];
    }
}
