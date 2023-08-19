<?php

namespace Ikechukwukalu\Clamavfileupload\Facades\Services;

use Illuminate\Support\Facades\Facade;

class NoClamavFileUpload extends Facade
{

    protected static function getFacadeAccessor()
    {
        return 'NoClamavFileUpload';
    }
}
