<?php

namespace Ikechukwukalu\Clamavfileupload\Services;

use Ikechukwukalu\Clamavfileupload\Contracts\FileUploadInterface;
use Ikechukwukalu\Clamavfileupload\Facades\Support\TemporaryFileUpload;
use Ikechukwukalu\Clamavfileupload\Events\ClamavFileScan;
use Ikechukwukalu\Clamavfileupload\Models\FileUpload as FileUploadModel;
use Ikechukwukalu\Clamavfileupload\Support\ClamavFileUpload;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUpload extends ClamavFileUpload implements FileUploadInterface
{
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
     * @param  array $settings
     * @return  \Ikechukwukalu\Clamavfileupload\Models\FileUpload
     * @return  \Illuminate\Database\Eloquent\Collection
     * @return  bool
     */
    protected function runFileUpload(array $settings = []): bool|FileUploadModel|EloquentCollection
    {
        ClamavFileScan::dispatch();

        if (in_array($this->getDisk(), config('clamavfileupload.s3_disks'))) {
            return $this->runFileUploadForS3($settings);
        }

        if ($data = $this->fileUpload()) {
            return $data;
        }

        return false;
    }

    /**
     * Set file request.
     *
     * @param array $tmpFiles
     * @return \Illuminate\Http\Request
     */
    private function setFileRequest(array $tmpFiles): Request
    {
        $request = new Request;

        if (count($tmpFiles) > 1) {
            $request->files->set($this->input, $this->setMultipleFiles($tmpFiles));
        } else {
            $request->files->set($this->input, $this->setSingleFile($tmpFiles[0]));
        }

        return $request;
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
     * Run file upload for s3.
     *
     * @param  array $settings
     * @return  \Ikechukwukalu\Clamavfileupload\Models\FileUpload
     * @return  \Illuminate\Database\Eloquent\Collection
     * @return  bool
     */
    protected function runFileUploadForS3(array $settings = []): bool|FileUploadModel|EloquentCollection
    {
        $fileUpload = new TemporaryFileUpload;
        $fileUpload::customFileUploadSettings($settings);
        $fileUpload::fileUploadSettings($this->request);
        $tmpFiles = $fileUpload::fileUpload();

        $request = $this->setFileRequest($tmpFiles);
        $this->customFileUploadSettings($settings);
        $this->fileUploadSettings($request);
        $data = $this->fileUpload();

        TemporaryFileUpload::removeFiles($tmpFiles);

        return $data;
    }
}
