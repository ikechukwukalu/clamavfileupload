<?php

namespace Ikechukwukalu\Clamavfileupload\Facade;

use Ikechukwukalu\Clamavfileupload\Events\ClamavFileScan;
use Ikechukwukalu\Clamavfileupload\Models\FileUpload as FileUploadModel;
use Ikechukwukalu\Clamavfileupload\Support\TemporaryFileUpload;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUpload extends FileUploadLogic
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
    public static function uploadFiles(Request $request,
                array $settings = []): bool|FileUploadModel|EloquentCollection
    {
        self::customFileUploadSettings($settings);
        self::fileUploadSettings($request);
        return self::runFileUpload($settings);
    }

    /**
     * Run files scan and upload.
     *
     * @param  array $settings
     * @return  \Ikechukwukalu\Clamavfileupload\Models\FileUpload
     * @return  \Illuminate\Database\Eloquent\Collection
     * @return  bool
     * @return  bool
     */
    protected static function runFileUpload(array $settings = []): bool|FileUploadModel|EloquentCollection
    {
        ClamavFileScan::dispatch();
        if (self::getDisk() !== 'public'
            && self::getDisk() !== 'local'
        ) {
            $tmpFiles = TemporaryFileUpload::storeFiles();
            $request = self::setFileRequest($tmpFiles);

            self::customFileUploadSettings($settings);
            self::fileUploadSettings($request);
            $data = self::fileUpload();

            TemporaryFileUpload::removeFiles($tmpFiles);

            return $data;
        }

        if ($data = self::fileUpload()) {
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
    private static function setFileRequest(array $tmpFiles): Request
    {
        $request = new Request;
        $files = [];

        foreach ($tmpFiles as $tmpFile) {
            $extension = explode('.', $tmpFile)[1];
            $files[] = new UploadedFile($tmpFile, ".{$extension}");
        }

        $request->files->set(self::$input, $files);

        return $request;
    }
}
