<?php

namespace Ikechukwukalu\Clamavfileupload\Events;
use Ikechukwukalu\Clamavfileupload\Foundation\FileUpload;

class FileScanFail extends FileScan
{

    /**
     * Create a new event instance.
     *
     * @param  array  $scanData
     */
    public function __construct(array $scanData)
    {
        parent::__construct($scanData);
        FileUpload::logScanData(json_encode($this->scanData));
    }
}
