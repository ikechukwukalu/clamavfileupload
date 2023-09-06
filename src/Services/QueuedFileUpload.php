<?php

namespace Ikechukwukalu\Clamavfileupload\Services;

use Ikechukwukalu\Clamavfileupload\Contracts\FileUploadInterface;
use Ikechukwukalu\Clamavfileupload\Facades\Support\TemporaryFileUpload;
use Ikechukwukalu\Clamavfileupload\Events\ClamavQueuedFileScan;
use Ikechukwukalu\Clamavfileupload\Models\FileUpload as FileUploadModel;
use Ikechukwukalu\Clamavfileupload\Support\ClamavFileUpload;
use Ikechukwukalu\Clamavfileupload\Trait\QueuedDelete;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\Request;

class QueuedFileUpload extends ClamavFileUpload implements FileUploadInterface
{
    use QueuedDelete;

    /**
     * Upload single or multiple files.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  array  $settings
     * @return  \Ikechukwukalu\Clamavfileupload\Models\FileUpload
     * @return  \Illuminate\Database\Eloquent\Collection
     * @return  bool
     */
    public function uploadFiles(Request $request,
                array $settings = []): bool|FileUploadModel|EloquentCollection
    {
        $this->customFileUploadSettings($settings);
        $this->fileUploadSettings($request);
        return $this->runFileUpload($settings);
    }

    /**
     * Run files scan and upload.
     *
     * @param  array  $settings
     * @return  bool
     */
    protected function runFileUpload(array $settings = []): bool
    {
        $fileUpload = new TemporaryFileUpload;
        $fileUpload::customFileUploadSettings($settings);
        $fileUpload::fileUploadSettings($this->request);

        $tmpFiles = $fileUpload::fileUpload();
        $this->ref = $this->setRef();

        ClamavQueuedFileScan::dispatch($tmpFiles, $settings, $this->ref);
        return true;
    }


}
