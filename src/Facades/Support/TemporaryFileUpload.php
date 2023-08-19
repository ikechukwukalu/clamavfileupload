<?php

namespace Ikechukwukalu\Clamavfileupload\Facades\Support;

use Illuminate\Support\Facades\Facade;

class TemporaryFileUpload extends Facade
{

    protected static function getFacadeAccessor()
    {
        return 'TemporaryFileUpload';
    }
}
