<?php

namespace Ikechukwukalu\Clamavfileupload\Listeners;

use Ikechukwukalu\Clamavfileupload\Events\ClamavQueuedFileScan;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Illuminate\Support\Facades\Log;

class ClamavFileUpload implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(ClamavQueuedFileScan $event): void
    {
        $event->fileUpload::customFileUploadSettings($event->settings);

        $request = new Request;
        $files = [];

        foreach ($event->tmpFiles as $tmpFile) {
            $extension = explode('.', $tmpFile)[1];
            $files[] = new UploadedFile($tmpFile, ".{$extension}");
        }

        $request->files->set($event->fileUpload::$input, $files);

        $event->fileUpload::fileUploadSettings($request);
        $event->fileUpload::fileUpload();
        $event->fileUpload::removeTemporaryFiles($event->tmpFiles);

        if (config('clamavfileupload.log_queue_data')) {
            Log::info(json_encode($event->fileUpload::$scanData));
        }
    }
}
