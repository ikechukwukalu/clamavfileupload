<?php

namespace Ikechukwukalu\Clamavfileupload\Foundation;

use Ikechukwukalu\Clamavfileupload\Models\FileUpload as FileUploadModel;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class FileUpload
{
    public static Request $request;
    public static ?string $name;
    public static string $fileName;
    public static string $input;
    public static string $ref;
    public static ?string $folder;
    public static string $uploadPath;
    public static array $scanData;

    /**
     * Log scan data.
     *
     * @param  string  $message
     * @return  bool
     */
    public static function logScanData(string $message): void
    {
        if (config('clamavfileupload.log_scan_data')) {
            Log::alert($message);
        }
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
            'root' => self::storageDisk()->path(self::$uploadPath),
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
            return self::saveMultipleFiles();
        }

        return self::saveSingleFile();
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

        $i = 1;
        foreach (self::$request->file(self::$input) as $file) {
            $disk->putFileAs("", $file, self::$fileName . "_{$i}" .
                    self::getExtension($file));
            $i ++;
        }

        return true;
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
        self::provideDisk()->putFileAs("",
                self::$request->file(self::$input),
                self::$fileName . self::getExtension());

        return true;
    }

    /**
     * Remove single or multiple files.
     *
     * @param array $files
     * @return  ?bool
     */
    protected static function removeFiles(array $files = []): ?bool
    {
        if (is_array(self::$request->file(self::$input))) {
            return self::deleteMultipleFiles();
        }

        return self::deleteSingleFile();
    }

    /**
     * Delete multiple files.
     *
     * @return  bool
     */
    protected static function deleteMultipleFiles(): bool
    {
        $i = 1;
        foreach (self::$request->file(self::$input) as $file) {
            [$fileName, $relativeFilePath] = self::fileNameAndPath($file, $i);

            $file = str_replace(self::storageDisk()
                        ->path(self::$uploadPath), '', $file);
            self::storageDisk()->delete(self::$uploadPath . $file);

            $i ++;
        }

        return true;
    }

    /**
     * Delete single file.
     *
     * @return  bool
     */
    protected static function deleteSingleFile(): bool
    {
        [$fileName, $relativeFilePath] = self::fileNameAndPath();

        $file = str_replace(self::storageDisk()
                ->path(self::$uploadPath), '', self::$request->file(self::$input));
        self::storageDisk()->delete(self::$uploadPath . $file);

        return true;
    }

    /**
     * Set UUID ref.
     *
     * @return  string
     */
    protected static function setRef(): string
    {
        return (string) Str::uuid();
    }

    /**
     * Get UUID ref.
     *
     * @param ?any $file
     * @param ?any $i
     * @return  string
     */
    protected static function getName($file = null, $i = null): string
    {
        if ($file && $i) {
            return self::$name ? self::$name . "_{$i}"
                : $file->getClientOriginalName();
        }

        return self::$name ?? self::$request->file(self::$input)
                ->getClientOriginalName();
    }

    /**
     * Set file name.
     *
     * @param ?any $file
     * @return  string
     */
    protected static function setFileName(): string
    {
        return time() . Str::random(40);
    }

    /**
     * Get extension.
     *
     * @param ?any $file
     * @return  string
     */
    protected static function getExtension($file = null): string
    {
        if ($file) {
            return '.' . $file->getClientOriginalExtension();
        }

        return '.' . self::$request->file(self::$input)
                ->getClientOriginalExtension();
    }

    /**
     * Get relative path.
     *
     * @param any $fileName
     * @return  string
     */
    protected static function getRelativeFilePath($fileName): string
    {
        return self::$uploadPath . "/" . $fileName;
    }

    /**
     * Get disk.
     *
     * @return  string
     */
    protected static function getDisk(): string
    {
        return config('clamavfileupload.disk');
    }

    /**
     * Get \Illuminate\Support\Facades\Storage::disk.
     *
     * @return  \Illuminate\Contracts\Filesystem\Filesystem
     */
    protected static function storageDisk(): Filesystem
    {
        return Storage::disk(self::getDisk());
    }

    /**
     * Get file name and path.
     *
     * @param ?any $file
     * @param ?any $i
     * @return  array
     */
    protected static function fileNameAndPath($file = null, $i = null): array
    {
        if ($file && $i) {
            $fileName = self::$fileName . "_{$i}" . self::getExtension($file);
            return [$fileName, self::getRelativeFilePath($fileName)];
        }

        $fileName = self::$fileName . self::getExtension();
        return [$fileName, self::getRelativeFilePath($fileName)];
    }

    /**
     * Get file model data.
     *
     * @param ?any $file
     * @param ?any $i
     * @return  array
     */
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

    /**
     * Insert multiple files.
     *
     * @return  \Illuminate\Database\Eloquent\Collection
     */
    protected static function insertMultipleFiles(): ?EloquentCollection
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

    /**
     * Insert single file.
     *
     * @return  \Ikechukwukalu\Clamavfileupload\Models\FileUpload
     */
    protected static function insertSingleFile(): ?FileUploadModel
    {
        return FileUploadModel::create(self::getFileModelData());
    }
}
