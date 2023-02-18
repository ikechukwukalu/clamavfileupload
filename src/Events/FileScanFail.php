<?php

namespace Ikechukwukalu\Clamavfileupload\Events;
use Ikechukwukalu\Clamavfileupload\Foundation\FileUpload;

class FileScanFail extends FileScan
{

    public function __construct(array $scanData)
    {
        parent::__construct($scanData);
        FileUpload::logScanData(json_encode($this->scanData));
    }
}
