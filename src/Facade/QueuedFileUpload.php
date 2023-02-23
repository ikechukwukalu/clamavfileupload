<?php

namespace Ikechukwukalu\Clamavfileupload\Facade;

use Ikechukwukalu\Clamavfileupload\Events\ClamavQueuedFileScan;
use Ikechukwukalu\Clamavfileupload\Models\FileUpload as FileUploadModel;
use Ikechukwukalu\Clamavfileupload\Support\ClamavFileUpload as ClamavFileUploadSupport;
use Ikechukwukalu\Clamavfileupload\Support\TemporaryFileUpload;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\Request;

class QueuedFileUpload extends FileUploadLogic
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
        return self::clamavFileUpload($settings);
    }

    /**
     * Run files scan and upload.
     *
     * @param  array  $settings
     * @return  bool
     */
    protected static function clamavFileUpload(array $settings = []): bool
    {
        $tmpFiles = TemporaryFileUpload::storeFiles();
        self::$ref = self::setRef();

        ClamavQueuedFileScan::dispatch($tmpFiles, $settings, self::$ref);
        return true;
    }

}
