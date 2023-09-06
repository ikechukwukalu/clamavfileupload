<?php

namespace Ikechukwukalu\Clamavfileupload\Listeners;

use Ikechukwukalu\Clamavfileupload\Facades\Services\FileUpload;
use Ikechukwukalu\Clamavfileupload\Events\QueuedDeleteAll;
use Ikechukwukalu\Clamavfileupload\Events\QueuedForceDeleteAll;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class FileDeleteAll implements ShouldQueue
{

    public function handle(QueuedDeleteAll|QueuedForceDeleteAll $event): void
    {
        if ($event instanceof QueuedForceDeleteAll) {
            FileUpload::forceDeleteAll($event->ref);
            return;
        }

        FileUpload::deleteAll($event->ref);
    }

}
