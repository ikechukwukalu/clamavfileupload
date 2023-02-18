<?php

namespace Ikechukwukalu\Clamavfileupload\Facade;

use Ikechukwukalu\Clamavfileupload\Events\ClamavQueuedFileScan;
use Ikechukwukalu\Clamavfileupload\Models\FileUploads as FileUploadModel;
use Ikechukwukalu\Clamavfileupload\Support\ClamavFileUpload as ClamavFileUploadSupport;
use Ikechukwukalu\Clamavfileupload\Support\TemporaryFileUpload;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\Request;

class QueuedFileUpload extends FileUploadLogic
{
    public static function uploadFiles(Request $request,
                array $settings = []): bool|FileUploadModel|EloquentCollection
    {
        self::customFileUploadSettings($settings);
        self::fileUploadSettings($request);
        return self::clamavFileUpload($settings);
    }

    protected static function clamavFileUpload(array $settings = []): bool
    {
        $tmpFiles = TemporaryFileUpload::storeFiles();
        ClamavQueuedFileScan::dispatch($tmpFiles, $settings);

        return true;
    }

}
