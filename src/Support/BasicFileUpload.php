<?php

namespace Ikechukwukalu\Clamavfileupload\Support;

use Ikechukwukalu\Clamavfileupload\Models\FileUpload as FileUploadModel;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Ikechukwukalu\Clamavfileupload\Foundation\FileUpload;

class BasicFileUpload extends FileUpload
{

    /**
     * Run files scan and upload.
     *
     * @return  \Ikechukwukalu\Clamavfileupload\Models\FileUpload
     * @return  \Illuminate\Database\Eloquent\Collection
     * @return  bool
     */
    public static function fileUpload(): bool|FileUploadModel|EloquentCollection
    {
        if(self::$request->file()) {
            self::storeFiles();

            if (is_array(self::$request->file(self::$input))) {
                return self::insertMultipleFiles();
            }

            return self::insertSingleFile();
        }

        return false;
    }
}
