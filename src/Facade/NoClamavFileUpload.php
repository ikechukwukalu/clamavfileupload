<?php

namespace Ikechukwukalu\Clamavfileupload\Facade;

use Ikechukwukalu\Clamavfileupload\Models\FileUpload as FileUploadModel;
use Ikechukwukalu\Clamavfileupload\Support\BasicFileUpload;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\Request;

class NoClamavFileUpload extends FileUploadLogic
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
        return self::runFileUpload();
    }

    /**
     * Run files scan and upload.
     *
     * @return  \Ikechukwukalu\Clamavfileupload\Models\FileUpload
     * @return  \Illuminate\Database\Eloquent\Collection
     * @return  bool
     */
    protected static function runFileUpload(): bool|FileUploadModel|EloquentCollection
    {
        if ($data = BasicFileUpload::fileUpload()) {
            return $data;
        }

        return false;
    }

}
