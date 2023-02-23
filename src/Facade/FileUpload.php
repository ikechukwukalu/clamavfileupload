<?php

namespace Ikechukwukalu\Clamavfileupload\Facade;

use Ikechukwukalu\Clamavfileupload\Events\ClamavFileScan;
use Ikechukwukalu\Clamavfileupload\Models\FileUpload as FileUploadModel;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

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
        return self::clamavFileUpload();
    }

    /**
     * Run files scan and upload.
     *
     * @return  \Ikechukwukalu\Clamavfileupload\Models\FileUpload
     * @return  \Illuminate\Database\Eloquent\Collection
     * @return  bool
     */
    protected static function clamavFileUpload(): bool|FileUploadModel|EloquentCollection
    {
        ClamavFileScan::dispatch();
        if ($data = self::fileUpload()) {
            return $data;
        }

        return false;
    }
}
