<?php

namespace Ikechukwukalu\Clamavfileupload\Listeners;

use Ikechukwukalu\Clamavfileupload\Facades\Services\FileUpload;
use Ikechukwukalu\Clamavfileupload\Events\QueuedDeleteOne;
use Ikechukwukalu\Clamavfileupload\Events\QueuedForceDeleteOne;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class FileDeleteOne implements ShouldQueue
{

    public function handle(QueuedDeleteOne|QueuedForceDeleteOne $event): void
    {
        if ($event instanceof QueuedForceDeleteOne) {
            FileUpload::forceDeleteOne($event->ids, $event->ref);
            return;
        }

        FileUpload::deleteOne($event->ids, $event->ref);
    }

}
