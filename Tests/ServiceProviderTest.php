<?php

namespace Ikechukwukalu\Clamavfileupload\Tests;

use Illuminate\Support\ServiceProvider;
use Ikechukwukalu\Clamavfileupload\ClamavFileUploadServiceProvider;

class ServiceProviderTest extends TestCase
{
    public function test_merges_config(): void
    {
        static::assertSame(
            $this->app->make('files')
                ->getRequire(ClamavFileUploadServiceProvider::CONFIG),
            $this->app->make('config')->get('clamavfileupload')
        );
    }

    public function test_loads_translations(): void
    {
        static::assertArrayHasKey('clamavfileupload',
            $this->app->make('translator')->getLoader()->namespaces());
    }
}
