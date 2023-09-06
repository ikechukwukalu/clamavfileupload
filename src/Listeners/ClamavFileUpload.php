<?php

namespace Ikechukwukalu\Clamavfileupload\Listeners;

use Ikechukwukalu\Clamavfileupload\Facades\Services\QueuedFileUpload;
use Ikechukwukalu\Clamavfileupload\Facades\Support\TemporaryFileUpload;
use Ikechukwukalu\Clamavfileupload\Events\ClamavQueuedFileScan;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ClamavFileUpload implements ShouldQueue
{
    private QueuedFileUpload $fileUpload;
    private Request $request;

    /**
     * Handle the event.
     *
     * @param \Ikechukwukalu\Clamavfileupload\Events\ClamavQueuedFileScan $event
     * @return  void
     */
    public function handle(ClamavQueuedFileScan $event): void
    {
        $this->runFileUploadProcesses($event);
    }

    /**
     * Set file request.
     *
     * @param \Ikechukwukalu\Clamavfileupload\Events\ClamavQueuedFileScan $event
     * @return  void
     */
    private function setFileRequest(ClamavQueuedFileScan $event): void
    {
        $this->request = new Request;

        if (count($event->tmpFiles) > 1) {
            $this->request->files->set($this->fileUpload::getInput(), $this->setMultipleFiles($event->tmpFiles));
        } else {
            $this->request->files->set($this->fileUpload::getInput(), $this->setSingleFile($event->tmpFiles[0]));
        }
    }

    /**
     * Set multiple files.
     *
     * @param array $tmpFiles
     * @return array
     */
    private function setMultipleFiles(array $tmpFiles): array
    {
        $files = [];

        foreach ($tmpFiles as $tmpFile) {
            $extension = explode('.', $tmpFile)[1];
            $files[] = new UploadedFile($tmpFile, ".{$extension}");
        }

        return $files;
    }

    /**
     * Set single files.
     *
     * @param string $tmpFile
     * @return UploadedFile
     */
    private function setSingleFile(string $tmpFile): UploadedFile
    {
        $extension = explode('.', $tmpFile)[1];
        return new UploadedFile($tmpFile, ".{$extension}");
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
