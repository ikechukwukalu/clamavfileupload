<?php

namespace Ikechukwukalu\Clamavfileupload\Listeners;

use Ikechukwukalu\Clamavfileupload\Facades\Services\FileUpload;
use Ikechukwukalu\Clamavfileupload\Events\QueuedDeleteMultiple;
use Ikechukwukalu\Clamavfileupload\Events\QueuedForceDeleteMultiple;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class FileDeleteMultiple implements ShouldQueue
{

    public function handle(QueuedDeleteMultiple|QueuedForceDeleteMultiple $event): void
    {
        if ($event instanceof QueuedForceDeleteMultiple) {
            FileUpload::forceDeleteMultiple($event->ids, $event->ref);
            return;
        }

        FileUpload::deleteMultiple($event->ids, $event->ref);
    }

}
