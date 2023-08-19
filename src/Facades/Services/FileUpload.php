<?php

namespace Ikechukwukalu\Clamavfileupload\Facades\Services;

use Illuminate\Support\Facades\Facade;

class FileUpload extends Facade
{

    protected static function getFacadeAccessor()
    {
        return 'FileUpload';
    }
}
