<?php

namespace Ikechukwukalu\Clamavfileupload;

use Ikechukwukalu\Clamavfileupload\Events\ClamavFileScan;
use Ikechukwukalu\Clamavfileupload\Events\ClamavQueuedFileScan;
use Ikechukwukalu\Clamavfileupload\Events\FileScanPass;
use Ikechukwukalu\Clamavfileupload\Events\FileScanFail;
use Ikechukwukalu\Clamavfileupload\Listeners\ClamavFileUpload;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{

    protected $listen = [
        ClamavFileScan::class => [],
        ClamavQueuedFileScan::class => [
            ClamavFileUpload::class
        ],
        FileScanPass::class => [],
        FileScanFail::class => []
    ];

    public function boot()
    {
        parent::boot();
    }
}
