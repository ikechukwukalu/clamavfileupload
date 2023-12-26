<?php

namespace Ikechukwukalu\Clamavfileupload\Tests;

use Ikechukwukalu\Clamavfileupload\ClamavFileUploadServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\Concerns\InteractsWithExceptionHandling;

abstract class TestCase extends BaseTestCase
{
    use InteractsWithExceptionHandling;

    public function setUp(): void
    {
      parent::setUp();
    }

    protected function defineDatabaseMigrations()
    {
        $this->loadLaravelMigrations();
        $this->loadMigrationsFrom(__DIR__.'/../src/migrations');
    }

    protected function getPackageProviders($app): array
    {
        return [ClamavFileUploadServiceProvider::class];
    }
}
