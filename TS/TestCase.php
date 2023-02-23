<?php

namespace Ikechukwukalu\Clamavfileupload\Tests;

use Ikechukwukalu\Clamavfileupload\ClamavFileUploadServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
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
