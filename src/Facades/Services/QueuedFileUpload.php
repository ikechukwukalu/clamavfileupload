<?php

namespace Ikechukwukalu\Clamavfileupload\Facades\Services;

use Illuminate\Support\Facades\Facade;

class QueuedFileUpload extends Facade
{

    protected static function getFacadeAccessor()
    {
        return 'QueuedFileUpload';
    }
}
