<?php

namespace Ikechukwukalu\Clamavfileupload;

use Ikechukwukalu\Clamavfileupload\Events\ClamavEvent;
use Ikechukwukalu\Clamavfileupload\Events\ClamavFileScan;
use Ikechukwukalu\Clamavfileupload\Events\ClamavIsNotRunning;
use Ikechukwukalu\Clamavfileupload\Events\ClamavQueuedFileScan;
use Ikechukwukalu\Clamavfileupload\Events\FileScanPass;
use Ikechukwukalu\Clamavfileupload\Events\FileScanFail;
use Ikechukwukalu\Clamavfileupload\Listeners\ClamavFileUpload;
use Ikechukwukalu\Clamavfileupload\Events\SavedFilesIntoDB;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{

    protected $listen = [
        ClamavEvent::class => [],
        ClamavFileScan::class => [],
        ClamavIsNotRunning::class => [],
        ClamavQueuedFileScan::class => [
            ClamavFileUpload::class
        ],
        FileScanPass::class => [],
        FileScanFail::class => [],
        SavedFilesIntoDB::class => []
    ];

    public function boot()
    {
        parent::boot();
    }
}
