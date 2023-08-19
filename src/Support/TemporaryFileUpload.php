<?php

namespace Ikechukwukalu\Clamavfileupload\Support;

use Ikechukwukalu\Clamavfileupload\Foundation\FileUpload;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;

class TemporaryFileUpload extends FileUpload
{
    /**
     * Run files scan and upload.
     *
     * @return  bool
     */
    public function fileUpload(): bool|array
    {
        if($this->request->file()) {
            return $this->storeFiles();
        }

        return false;
    }

    /**
     * Remove single or multiple files.
     *
     * @param array $files
     * @return  bool
     */
    public function removeFiles(array $files = []):  bool
    {
        foreach ($files as $file) {
            $file = str_replace($this->storageDisk()->path('tmp'), '', $file);
            $this->storageDisk()->delete('tmp' . $file);
        }

        return true;
    }

    /**
     * Provide \Illuminate\Support\Facades\Storage::build.
     *
     * @return  \Illuminate\Contracts\Filesystem\Filesystem
     */
    protected function provideDisk(): Filesystem
    {
        return Storage::build([
            'driver' => 'local',
            'root' => $this->storageDisk()->path('tmp')
        ]);
    }

    /**
     * Get \Illuminate\Support\Facades\Storage::disk.
     *
     * @return  \Illuminate\Contracts\Filesystem\Filesystem
     */
    protected function storageDisk(): Filesystem
    {
        return Storage::disk('local');
    }

    /**
     * Save single or multiple files.
     *
     * @return  bool
     * @return  array
     */
    protected function storeFiles(): bool|array
    {
        $this->fileName = $this->setFileName();

        if (is_array($this->request->file($this->input))) {
            return $this->saveMultipleFiles($this->fileName);
        }

        return $this->saveSingleFile($this->fileName);
    }

    /**
     * Save multiple files.
     *
     * @param null|string $fileName
     * @return  bool
     * @return  array
     */
    protected function saveMultipleFiles(null|string $fileName = null): bool|array
    {
        $disk = $this->provideDisk();
        $tmpFiles = [];
        $i = 1;

        foreach ($this->request->file($this->input) as $file) {
            $tmp = $fileName . "_{$i}" . $this->getExtension($file);
            $disk->putFileAs("", $file, $tmp);
            $tmpFiles[] = $this->storageDisk()->path("tmp/{$tmp}");

            $i ++;
        }

        return $tmpFiles;
    }

    /**
     * Save single file.
     *
     * @param null|string $fileName
     * @return  bool
     * @return  array
     */
    protected function saveSingleFile(null|string $fileName = null): bool|array
    {
        $tmp = $fileName . $this->getExtension();

        $this->provideDisk()->putFileAs("",
                $this->request->file($this->input),
                $fileName . $this->getExtension());

        return [$this->storageDisk()->path("tmp/{$tmp}")];
    }
}
