<?php

namespace Ikechukwukalu\Clamavfileupload\Facade;

use Ikechukwukalu\Clamavfileupload\Events\ClamavFileScan;
use Ikechukwukalu\Clamavfileupload\Models\FileUploads as FileUploadModel;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class FileUpload extends FileUploadLogic
{
    public static function uploadFiles(Request $request,
                array $settings = []): bool|FileUploadModel|EloquentCollection
    {
        self::customFileUploadSettings($settings);
        self::fileUploadSettings($request);
        return self::clamavFileUpload();
    }

    protected static function clamavFileUpload(): bool|FileUploadModel|EloquentCollection
    {
        ClamavFileScan::dispatch();
        if ($data = self::fileUpload()) {
            return $data;
        }

        return false;
    }
}
