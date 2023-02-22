<?php

namespace Tests;

use Ikechukwukalu\Clamavfileupload\ClamavFileUploadServiceProvider;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    public function setUp(): void
    {
      parent::setUp();
    }

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
