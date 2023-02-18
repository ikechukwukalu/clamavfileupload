<?php

namespace Ikechukwukalu\Clamavfileupload\Support;

use Illuminate\Http\Request;
use Ikechukwukalu\Clamavfileupload\Models\FileUploads as FileUploadModel;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Ikechukwukalu\Clamavfileupload\Foundation\FileUpload;

class TemporaryFileUpload extends FileUpload
{
    public static function fileUpload(): null
    {
        return null;
    }

    public static function removeFiles(array $files = []):  null|bool
    {
        foreach ($files as $file) {
            $file = str_replace(self::storageDisk()->path('tmp'), '', $file);
            self::storageDisk()->delete('tmp' . $file);
        }

        return true;
    }

    protected static function provideDisk(): Filesystem
    {
        return Storage::build([
            'driver' => self::getDisk(),
            'root' => self::storageDisk()->path('tmp')
        ]);
    }

    protected static function storeFiles(): bool|array
    {
        self::$fileName = self::setFileName();

        if (is_array(self::$request->file(self::$input))) {
            return self::saveMultipleFiles(self::$fileName);
        }

        return self::saveSingleFile(self::$fileName);
    }

    protected static function saveMultipleFiles(null|string $fileName = null): bool|array
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

    protected static function saveSingleFile(null|string $fileName = null): bool|array
    {
        $tmp = $fileName . self::getExtension();

        self::provideDisk()->putFileAs("",
                self::$request->file(self::$input),
                $fileName . self::getExtension());

        return [self::storageDisk()->path("tmp/{$tmp}")];
    }
}
