<?php

namespace Ikechukwukalu\Clamavfileupload\Support;

use Ikechukwukalu\Clamavfileupload\Foundation\FileUpload;
use Ikechukwukalu\Clamavfileupload\Models\FileUpload as FileUploadModel;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class BasicFileUpload extends FileUpload
{

    /**
     * Run files scan and upload.
     *
     * @return  \Ikechukwukalu\Clamavfileupload\Models\FileUpload
     * @return  \Illuminate\Database\Eloquent\Collection
     * @return  bool
     */
    public function fileUpload(): bool|FileUploadModel|EloquentCollection
    {
        if($this->request->file()) {
            $this->storeFiles();

            if (is_array($this->request->file($this->input))) {
                return $this->insertMultipleFiles();
            }

            return $this->insertSingleFile();
        }

        return false;
    }
}
