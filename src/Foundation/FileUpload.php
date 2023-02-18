<?php

namespace Ikechukwukalu\Clamavfileupload\Foundation;

use Ikechukwukalu\Clamavfileupload\Models\FileUploads as FileUploadModel;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class FileUpload
{
    public static Request $request;
    public static null|string $name;
    public static string $fileName;
    public static string $input;
    public static string $ref;
    public static null|string $folder;
    public static string $uploadPath;
    public static bool $queue;
    public static array $scanData;

    public static function logScanData(string $message): void
    {
        if (config('clamavfileupload.log_scan_data')) {
            Log::alert($message);
        }
    }

    protected static function provideDisk(): FilesystemAdapter
    {
        return Storage::build([
            'driver' => self::getDisk(),
            'root' => self::storageDisk()->path(self::$uploadPath),
        ]);
    }

    protected static function storeFiles(): bool|array
    {
        self::$fileName = self::setFileName();

        if (is_array(self::$request->file(self::$input))) {
            return self::saveMultipleFiles();
        }

        return self::saveSingleFile();
    }

    protected static function saveMultipleFiles(null|string $fileName = null): bool|array
    {
        $disk = self::provideDisk();

        $i = 1;
        foreach (self::$request->file(self::$input) as $file) {
            $disk->putFileAs("", $file, self::$fileName . "_{$i}" .
                    self::getExtension($file));
            $i ++;
        }

        return true;
    }

    protected static function saveSingleFile(null|string $fileName = null): bool|array
    {
        self::provideDisk()->putFileAs("",
                self::$request->file(self::$input),
                self::$fileName . self::getExtension());

        return true;
    }

    protected static function removeFiles(array $files = []): null|bool
    {
        if (is_array(self::$request->file(self::$input))) {
            return self::deleteMultipleFiles();
        }

        return self::deleteSingleFile();
    }

    protected static function deleteMultipleFiles(): null
    {
        $i = 1;
        foreach (self::$request->file(self::$input) as $file) {
            [$fileName, $relativeFilePath] = self::fileNameAndPath($file, $i);

            $file = str_replace(self::storageDisk()
                        ->path(self::$uploadPath), '', $file);
            self::storageDisk()->delete(self::$uploadPath . $file);

            $i ++;
        }

        return null;
    }

    protected static function deleteSingleFile(): null
    {
        [$fileName, $relativeFilePath] = self::fileNameAndPath();

        $file = str_replace(self::storageDisk()
                ->path(self::$uploadPath), '', $file);
        self::storageDisk()->delete(self::$uploadPath . $file);

        return null;
    }

    protected static function setRef(): string
    {
        return (string) Str::uuid();
    }

    protected static function getName($file = null, $i = null): string
    {
        if ($file && $i) {
            return self::$name ? self::$name . "_{$i}"
                : $file->getClientOriginalName();
        }

        return self::$name ?? self::$request->file(self::$input)
                ->getClientOriginalName();
    }

    protected static function setFileName(): string
    {
        return time() . Str::random(40);
    }

    protected static function getExtension($file = null): string
    {
        if ($file) {
            return '.' . $file->getClientOriginalExtension();
        }

        return '.' . self::$request->file(self::$input)
                ->getClientOriginalExtension();
    }

    protected static function getRelativeFilePath($fileName): string
    {
        return self::$uploadPath . "/" . $fileName;
    }

    protected static function getDisk(): string
    {
        return config('clamavfileupload.disk');
    }

    protected static function storageDisk(): FilesystemAdapter
    {
        return Storage::disk(self::getDisk());
    }

    protected static function fileNameAndPath($file = null, $i = null): array
    {
        if ($file && $i) {
            $fileName = self::$fileName . "_{$i}" . self::getExtension($file);
            return [$fileName, self::getRelativeFilePath($fileName)];
        }

        $fileName = self::$fileName . self::getExtension();
        return [$fileName, self::getRelativeFilePath($fileName)];
    }

    protected static function getFileModelData($file = null, $i = null): array
    {
        [$fileName, $relativeFilePath] = self::fileNameAndPath($file, $i);
        return [
            'ref' => self::$ref,
            'name' => self::getName($file, $i),
            'file_name' => $fileName,
            'url' => asset(self::storageDisk()->url($relativeFilePath)),
            'size' => self::storageDisk()->size($relativeFilePath),
            'extension' => self::getExtension($file),
            'disk' => self::getDisk(),
            'mime_type' => self::storageDisk()->mimeType($relativeFilePath),
            'path' => self::storageDisk()->path($relativeFilePath),
        ];
    }

    protected static function insertMultipleFiles(): null|EloquentCollection
    {
        $data = [];
        $i = 1;

        foreach (self::$request->file(self::$input) as $file) {
            $data[] = self::getFileModelData($file, $i);
            $i ++;
        }

        if (FileUploadModel::insert($data)) {
            return FileUploadModel::where('ref', self::$ref)->get();
        }

        return null;
    }

    protected static function insertSingleFile(): null|FileUploadModel
    {
        return FileUploadModel::create(self::getFileModelData());
    }
}
