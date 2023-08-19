<?php

namespace Ikechukwukalu\Clamavfileupload\Support;

use Ikechukwukalu\Clamavfileupload\Models\FileUpload as FileUploadModel;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\Storage;
use Ikechukwukalu\Clamavfileupload\Events\FileScanPass;
use Ikechukwukalu\Clamavfileupload\Events\FileScanFail;
use Ikechukwukalu\Clamavfileupload\Trait\ClamAV;
use Ikechukwukalu\Clamavfileupload\Foundation\FileUpload;

abstract class ClamavFileUpload extends FileUpload
{
    use ClamAV;

    /**
     * Run files scan and upload.
     *
     * @param  array $settings
     * @return  \Ikechukwukalu\Clamavfileupload\Models\FileUpload
     * @return  \Illuminate\Database\Eloquent\Collection
     * @return  bool
     */
    abstract protected function runFileUpload(array $settings = []): bool|FileUploadModel|EloquentCollection;

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

            if (!$this->areFilesSafe()) {
                return $this->removeFiles();
            }

            if (is_array($this->request->file($this->input))) {
                return $this->insertMultipleFiles();
            }

            return $this->insertSingleFile();
        }

        return false;
    }

    /**
     * Scan file.
     *
     * @param $filePath
     * @param $file
     * @return  array
     */
    public function scanFile($filePath, $file): array
    {
        $data = [
            'ref' => $this->ref,
            'status' => false
        ];

        if ($this->ping()) {
            $data['status'] = $this->scan($filePath);
        }

        $data['message'] = str_replace($filePath,
                            $file->getClientOriginalName(),
                            $this->getMessage());

        if ($this->getMessage() == 'OK') {
            $data['errorFile'] = null;
            $data['error'] = null;

            return $data;
        }

        $data['errorFile'] = $file;
        $data['error'] = $this->getMessage();
        $this->failedUpload($data['error']);

        return $data;
    }

    /**
     * Are files safe.
     *
     * @return  bool
     */
    private function areFilesSafe(): bool
    {
        if (is_array($this->request->file($this->input))) {
            return $this->areMultipleFilesSafe();
        }

        return $this->isSingleFileSafe();
    }

    /**
     * Is single file safe.
     *
     * @return  bool
     */
    private function isSingleFileSafe(): bool
    {
        [$fileName, $relativeFilePath] = $this->fileNameAndPath();
        $storageDisk = $this->storageDisk();

        if (in_array($this->getDisk(), config('clamavfileupload.s3_disks'))) {
            [$storageDisk, $relativeFilePath] =
                $this->getTempDiskAndPath();
        }

        $this->scanData = $this->scanFile($storageDisk->path($relativeFilePath),
                                $this->request->file($this->input));

        if ($this->scanData['status']) {
            FileScanPass::dispatch($this->scanData);
            return true;
        }

        $this->logScanData($this->scanData['error']);
        FileScanFail::dispatch($this->scanData);

        return false;
    }

    /**
     * Are multiple files safe.
     *
     * @return  bool
     */
    private function areMultipleFilesSafe(): bool
    {
        $i = 1;
        $storageDisk = $this->storageDisk();

        foreach ($this->request->file($this->input) as $file) {
            [$fileName, $relativeFilePath] = $this->fileNameAndPath($file, $i);

            if (in_array($this->getDisk(), config('clamavfileupload.s3_disks'))
            ) {
                [$storageDisk, $relativeFilePath] =
                    $this->getTempDiskAndPath($file);
            }

            $this->scanData = $this->scanFile($storageDisk->path($relativeFilePath),
                                $file);

            if (!$this->scanData['status']) {
                $this->logScanData($this->scanData['error']);
                FileScanFail::dispatch($this->scanData);

                return false;
            }

            $i ++;
        }

        FileScanPass::dispatch($this->scanData);
        return true;
    }

    private function getTempDiskAndPath(mixed $file = null): array
    {
        if (!$file) {
            $file = $this->request->file($this->input);
        }

        $storageDisk = Storage::disk('local');
        $ary = explode('/', $file->getRealPath());
        $relativeFilePath = 'tmp/' . array_pop($ary);

        return [$storageDisk, $relativeFilePath];
    }
}
