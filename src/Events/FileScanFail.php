<?php

namespace Ikechukwukalu\Clamavfileupload\Events;

use Ikechukwukalu\Clamavfileupload\Facades\Foundation\FoundationFileUpload;

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
        FoundationFileUpload::logScanData(json_encode($this->scanData));
    }
}
