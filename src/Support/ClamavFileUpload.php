<?php

namespace Ikechukwukalu\Clamavfileupload\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Ikechukwukalu\Clamavfileupload\Models\FileUploads as FileUploadModel;
use Illuminate\Support\Facades\Storage;
use Ikechukwukalu\Clamavfileupload\Foundation\ClamAV;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\Log;
use Ikechukwukalu\Clamavfileupload\Events\FileScanPass;
use Ikechukwukalu\Clamavfileupload\Events\FileScanFail;

class ClamavFileUpload
{
    use ClamAV;

    public static Request $request;
    public static null|string $name;
    public static string $fileName;
    public static string $input;
    public static string $ref;
    public static null|string $folder;
    public static string $uploadPath;
    public static bool $queue;
    public static array $scanData;

    public static function fileUpload(): null|FileUploadModel|EloquentCollection
    {
        if(self::$request->file()) {
            self::storeFiles();

            if (!self::areFilesSafe()) {
                return self::removeFiles();
            }

            if (is_array(self::$request->file(self::$input))) {
                return self::insertMultipleFiles();
            }

            return self::insertSingleFile();
        }

        return null;
    }

    public static function getDisk(): string
    {
        return config('clamavfileupload.disk');
    }

    public static function scanFile($filePath, $file): array
    {
        $data = [];

        if (self::ping()) {
            $data['status'] = self::scan($filePath);
        }

        $data['message'] = str_replace($filePath,
                            $file->getClientOriginalName(),
                            self::getMessage());

        if (self::getMessage() === self::OK) {
            $data['errorFile'] = null;
            $data['error'] = null;

            FileScanPass::dispatch($data);

            return $data;
        }

        $data['errorFile'] = $file;
        $data['error'] = self::getMessage();

        FileScanFail::dispatch($data);

        return $data;
    }

    public static function removeTemporaryFiles($files): null
    {
        foreach ($files as $file) {
            $file = str_replace(self::sDisk()->path('tmp'), '', $file);
            self::sDisk()->delete('tmp' . $file);
        }

        return null;
    }

    protected static function setRef(): string
    {
        return (string) Str::uuid();
    }

    protected static function storeFilesTemporarily(): array
    {
        $disk = Storage::build([
            'driver' => self::getDisk(),
            'root' => self::sDisk()->path('tmp'),
        ]);

        $fileName = self::setFileName();

        if (is_array(self::$request->file(self::$input))) {
            $tmpFiles = [];
            $i = 1;

            foreach (self::$request->file(self::$input) as $file) {
                $tmp = $fileName . "_{$i}" . self::getExtension($file);
                $disk->putFileAs("", $file, $tmp);
                $tmpFiles[] = self::sDisk()->path("tmp/{$tmp}");

                $i ++;
            }

            return $tmpFiles;
        }

        $tmp = $fileName . self::getExtension();
        $disk->putFileAs("", self::$request->file(self::$input),
                $fileName . self::getExtension());

        return [self::sDisk()->path("tmp/{$tmp}")];
    }

    private static function insertMultipleFiles(): null|EloquentCollection
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

    private static function insertSingleFile(): null|FileUploadModel
    {
        return FileUploadModel::create(self::getFileModelData());
    }

    private static function getFileModelData($file = null, $i = null): array
    {
        [$fileName, $relativeFilePath] = self::fileNameAndPath($file, $i);
        return [
            'ref' => self::$ref,
            'name' => self::getName($file, $i),
            'file_name' => $fileName,
            'url' => asset(self::sDisk()->url($relativeFilePath)),
            'size' => self::sDisk()->size($relativeFilePath),
            'extension' => self::getExtension($file),
            'disk' => self::getDisk(),
            'mime_type' => self::sDisk()->mimeType($relativeFilePath),
            'path' => self::sDisk()->path($relativeFilePath),
        ];
    }

    private static function getName($file = null, $i = null): string
    {
        if ($file && $i) {
            return self::$name ? self::$name . "_{$i}"
                : $file->getClientOriginalName();
        }

        return self::$name ?? self::$request->file(self::$input)
                ->getClientOriginalName();
    }

    private static function setFileName(): string
    {
        return time() . Str::random(40);
    }

    private static function storeFiles(): void
    {
        $disk = Storage::build([
            'driver' => self::getDisk(),
            'root' => self::sDisk()->path(self::$uploadPath),
        ]);

        self::$fileName = self::setFileName();

        if (is_array(self::$request->file(self::$input))) {
            $i = 1;
            foreach (self::$request->file(self::$input) as $file) {
                $disk->putFileAs("", $file, self::$fileName . "_{$i}" .
                        self::getExtension($file));
                $i ++;
            }

            return;
        }

        $disk->putFileAs("",
                self::$request->file(self::$input),
                self::$fileName . self::getExtension());
    }

    private static function getExtension($file = null): string
    {
        if ($file) {
            return '.' . $file->getClientOriginalExtension();
        }

        return '.' . self::$request->file(self::$input)
                ->getClientOriginalExtension();
    }

    private static function getRelativeFilePath($fileName): string
    {
        return self::$uploadPath . "/" . $fileName;
    }

    private static function areFilesSafe(): bool
    {
        if (!is_array(self::$request->file(self::$input))) {
            [$fileName, $relativeFilePath] = self::fileNameAndPath();

            self::$scanData = self::scanFile(self::sDisk()->path($relativeFilePath),
                                    self::$request->file(self::$input));

            if (!self::$scanData['status']) {
                Log::alert(self::$scanData['error']);
                return self::$scanData['status'];
            }
        }

        $i = 1;
        foreach (self::$request->file(self::$input) as $file) {
            [$fileName, $relativeFilePath] = self::fileNameAndPath($file, $i);

            self::$scanData = self::scanFile(self::sDisk()->path($relativeFilePath),
                                $file);

            if (!self::$scanData['status']) {
                Log::alert(self::$scanData['error']);
                return self::$scanData['status'];
            }

            $i ++;
        }

        return true;
    }

    private static function removeFiles(): null
    {
        if (!is_array(self::$request->file(self::$input))) {
            [$fileName, $relativeFilePath] = self::fileNameAndPath();

            self::sDisk()->delete($relativeFilePath);

            return null;
        }

        $i = 1;
        foreach (self::$request->file(self::$input) as $file) {
            [$fileName, $relativeFilePath] = self::fileNameAndPath($file, $i);

            self::sDisk()->delete($relativeFilePath);

            $i ++;
        }

        return null;
    }

    private static function sDisk(): FilesystemAdapter
    {
        return Storage::disk(self::getDisk());
    }

    private static function fileNameAndPath($file = null, $i = null): array
    {
        if ($file && $i) {
            $fileName = self::$fileName . "_{$i}" . self::getExtension($file);
            return [$fileName, self::getRelativeFilePath($fileName)];
        }

        $fileName = self::$fileName . self::getExtension();
        return [$fileName, self::getRelativeFilePath($fileName)];
    }
}
