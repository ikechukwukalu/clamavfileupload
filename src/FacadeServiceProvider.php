<?php

namespace Ikechukwukalu\Clamavfileupload;

use Ikechukwukalu\Clamavfileupload\Foundation\FileUpload as FoundationFileUpload;
use Ikechukwukalu\Clamavfileupload\Services\FileUpload;
use Ikechukwukalu\Clamavfileupload\Services\QueuedFileUpload;
use Ikechukwukalu\Clamavfileupload\Services\NoClamavFileUpload;
use Ikechukwukalu\Clamavfileupload\Support\BasicFileUpload;
use Ikechukwukalu\Clamavfileupload\Support\TemporaryFileUpload;
use Illuminate\Support\ServiceProvider;

class FacadeServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind('FoundationFileUpload', FoundationFileUpload::class);

        $this->app->bind('BasicFileUpload', BasicFileUpload::class);
        $this->app->bind('TemporaryFileUpload', TemporaryFileUpload::class);

        $this->app->bind('FileUpload', FileUpload::class);
        $this->app->bind('QueuedFileUpload', QueuedFileUpload::class);
        $this->app->bind('NoClamavFileUpload', NoClamavFileUpload::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
