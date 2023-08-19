<?php

namespace Ikechukwukalu\Clamavfileupload\Contracts;

use Ikechukwukalu\Clamavfileupload\Models\FileUpload as FileUploadModel;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\Request;

interface FileUploadInterface
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
                array $settings = []): bool|FileUploadModel|EloquentCollection;
}
