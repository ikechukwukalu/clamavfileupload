<?php

namespace Ikechukwukalu\Clamavfileupload\Services;

use Ikechukwukalu\Clamavfileupload\Contracts\FileUploadInterface;
use Ikechukwukalu\Clamavfileupload\Facades\Support\BasicFileUpload;
use Ikechukwukalu\Clamavfileupload\Models\FileUpload as FileUploadModel;
use Ikechukwukalu\Clamavfileupload\Support\ClamavFileUpload;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\Request;

class NoClamavFileUpload extends ClamavFileUpload implements FileUploadInterface
{

    private BasicFileUpload $fileUpload;

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
        $this->fileUpload = new BasicFileUpload;
        $this->fileUpload::customFileUploadSettings($settings);
        $this->fileUpload::fileUploadSettings($request);

        return $this->runFileUpload($settings);
    }

    /**
     * Run files scan and upload.
     *
     * @param  array $settings
     * @return  \Ikechukwukalu\Clamavfileupload\Models\FileUpload
     * @return  \Illuminate\Database\Eloquent\Collection
     * @return  bool
     */
    protected function runFileUpload($settings = []): bool|FileUploadModel|EloquentCollection
    {
        if ($data = $this->fileUpload::fileUpload()) {
            $this->ref = $this->fileUpload::getRef();
            $this->success = $this->fileUpload::isSuccessful();

            return $data;
        }

        $this->failedUpload($this->fileUpload::getErrorMessage());
        return false;
    }

}
