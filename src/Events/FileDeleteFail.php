<?php

namespace Ikechukwukalu\Clamavfileupload\Events;

use Ikechukwukalu\Clamavfileupload\Facades\Foundation\FoundationFileUpload;

class FileDeleteFail extends FileDelete
{

    /**
     * Create a new event instance.
     *
     * @param  array  $data
     */
    public function __construct(array $data)
    {
        parent::__construct($data);
        FoundationFileUpload::logScanData(json_encode($this->data));
    }
}
