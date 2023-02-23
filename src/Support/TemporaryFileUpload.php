<?php

namespace Ikechukwukalu\Clamavfileupload\Support;

use Illuminate\Http\Request;
use Ikechukwukalu\Clamavfileupload\Models\FileUpload as FileUploadModel;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Ikechukwukalu\Clamavfileupload\Foundation\FileUpload;

class TemporaryFileUpload extends FileUpload
{
    /**
     * Run files scan and upload.
     *
     * @return  bool
     */
    public static function fileUpload(): bool
    {
        return false;
    }

    /**
     * Remove single or multiple files.
     *
     * @param array $files
     * @return  ?bool
     */
    public static function removeFiles(array $files = []):  ?bool
    {
        foreach ($files as $file) {
            $file = str_replace(self::storageDisk()->path('tmp'), '', $file);
            self::storageDisk()->delete('tmp' . $file);
        }

        return true;
    }

    /**
     * Provide \Illuminate\Support\Facades\Storage::build.
     *
     * @return  \Illuminate\Contracts\Filesystem\Filesystem
     */
    protected static function provideDisk(): Filesystem
    {
        return Storage::build([
            'driver' => self::getDisk(),
            'root' => self::storageDisk()->path('tmp')
        ]);
    }

    /**
     * Save single or multiple files.
     *
     * @return  bool
     * @return  array
     */
    protected static function storeFiles(): bool|array
    {
        self::$fileName = self::setFileName();

        if (is_array(self::$request->file(self::$input))) {
            return self::saveMultipleFiles(self::$fileName);
        }

        return self::saveSingleFile(self::$fileName);
    }

    /**
     * Save multiple files.
     *
     * @param ?string $fileName
     * @return  bool
     * @return  array
     */
    protected static function saveMultipleFiles(?string $fileName = null): bool|array
    {
        $disk = self::provideDisk();
        $tmpFiles = [];
        $i = 1;

        foreach (self::$request->file(self::$input) as $file) {
            $tmp = $fileName . "_{$i}" . self::getExtension($file);
            $disk->putFileAs("", $file, $tmp);
            $tmpFiles[] = self::storageDisk()->path("tmp/{$tmp}");

            $i ++;
        }

        return $tmpFiles;
    }

    /**
     * Save single file.
     *
     * @param ?string $fileName
     * @return  bool
     * @return  array
     */
    protected static function saveSingleFile(?string $fileName = null): bool|array
    {
        $tmp = $fileName . self::getExtension();

        self::provideDisk()->putFileAs("",
                self::$request->file(self::$input),
                $fileName . self::getExtension());

        return [self::storageDisk()->path("tmp/{$tmp}")];
    }
}
