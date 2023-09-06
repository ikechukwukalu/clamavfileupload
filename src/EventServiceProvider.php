<?php

namespace Ikechukwukalu\Clamavfileupload;

use Ikechukwukalu\Clamavfileupload\Events\ClamavEvent;
use Ikechukwukalu\Clamavfileupload\Events\ClamavFileScan;
use Ikechukwukalu\Clamavfileupload\Events\ClamavIsNotRunning;
use Ikechukwukalu\Clamavfileupload\Events\ClamavQueuedFileScan;
use Ikechukwukalu\Clamavfileupload\Events\FileDeleteFail;
use Ikechukwukalu\Clamavfileupload\Events\FileDeletePass;
use Ikechukwukalu\Clamavfileupload\Events\FileForceDeleteFail;
use Ikechukwukalu\Clamavfileupload\Events\FileForceDeletePass;
use Ikechukwukalu\Clamavfileupload\Events\FileScanPass;
use Ikechukwukalu\Clamavfileupload\Events\FileScanFail;
use Ikechukwukalu\Clamavfileupload\Events\QueuedDeleteAll;
use Ikechukwukalu\Clamavfileupload\Events\QueuedDeleteMultiple;
use Ikechukwukalu\Clamavfileupload\Events\QueuedDeleteOne;
use Ikechukwukalu\Clamavfileupload\Events\QueuedForceDeleteAll;
use Ikechukwukalu\Clamavfileupload\Events\QueuedForceDeleteMultiple;
use Ikechukwukalu\Clamavfileupload\Events\QueuedForceDeleteOne;
use Ikechukwukalu\Clamavfileupload\Events\SavedFilesIntoDB;
use Ikechukwukalu\Clamavfileupload\Listeners\ClamavFileUpload;
use Ikechukwukalu\Clamavfileupload\Listeners\FileDeleteAll;
use Ikechukwukalu\Clamavfileupload\Listeners\FileDeleteMultiple;
use Ikechukwukalu\Clamavfileupload\Listeners\FileDeleteOne;
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
        QueuedDeleteAll::class => [
            FileDeleteAll::class
        ],
        QueuedDeleteMultiple::class => [
            FileDeleteMultiple::class
        ],
        QueuedDeleteOne::class => [
            FileDeleteOne::class,
        ],
        QueuedForceDeleteAll::class => [
            FileDeleteAll::class
        ],
        QueuedForceDeleteMultiple::class => [
            FileDeleteMultiple::class
        ],
        QueuedForceDeleteOne::class => [
            FileDeleteOne::class,
        ],
        FileDeletePass::class => [],
        FileDeleteFail::class => [],
        FileForceDeletePass::class => [],
        FileForceDeleteFail::class => [],
        FileScanPass::class => [],
        FileScanFail::class => [],
        SavedFilesIntoDB::class => []
    ];

    public function boot()
    {
        parent::boot();
    }
}
