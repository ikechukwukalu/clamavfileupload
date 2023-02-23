<?php

namespace Tests;

use Ikechukwukalu\Clamavfileupload\ClamavFileUploadServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function defineDatabaseMigrations()
    {
        $this->loadLaravelMigrations();
        $this->loadMigrationsFrom(__DIR__.'/../migrations');
    }

    protected function getPackageProviders($app): array
    {
        return [ClamavFileUploadServiceProvider::class];
    }
}
