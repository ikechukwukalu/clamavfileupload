<?php

namespace Ikechukwukalu\Clamavfileupload\Listeners;

use Ikechukwukalu\Clamavfileupload\Events\ClamavQueuedFileScan;
use Ikechukwukalu\Clamavfileupload\Facade\QueuedFileUpload;
use Ikechukwukalu\Clamavfileupload\Support\TemporaryFileUpload;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ClamavFileUpload implements ShouldQueue
{
    private Request $request;
    private QueuedFileUpload $fileUpload;

    /**
     * Handle the event.
     *
     * @param Ikechukwukalu\Clamavfileupload\Events\ClamavQueuedFileScan $event
     * @return  void
     */
    public function handle(ClamavQueuedFileScan $event): void
    {
        $this->runFileUploadProcesses($event);
    }

    /**
     * Set file request.
     *
     * @param Ikechukwukalu\Clamavfileupload\Events\ClamavQueuedFileScan $event
     * @return  void
     */
    private function setFileRequest(ClamavQueuedFileScan $event): void
    {
        $this->request = new Request;
        $files = [];

        foreach ($event->tmpFiles as $tmpFile) {
            $extension = explode('.', $tmpFile)[1];
            $files[] = new UploadedFile($tmpFile, ".{$extension}");
        }

        $this->request->files->set($this->fileUpload::$input, $files);
    }

    /**
     * Run file upload processes.
     *
     * @param Ikechukwukalu\Clamavfileupload\Events\ClamavQueuedFileScan $event
     * @return  void
     */
    private function runFileUploadProcesses(ClamavQueuedFileScan $event): void
    {
        $this->fileUpload = new QueuedFileUpload;
        $this->fileUpload::customFileUploadSettings($event->settings);
        $this->setFileRequest($event);
        $this->fileUpload::fileUploadSettings($this->request, $event->ref);
        $this->fileUpload::fileUpload();

        TemporaryFileUpload::removeFiles($event->tmpFiles);
    }
}
