<?php

namespace Ikechukwukalu\Clamavfileupload\Support;

use Illuminate\Http\Request;
use Ikechukwukalu\Clamavfileupload\Models\FileUploads as FileUploadModel;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Ikechukwukalu\Clamavfileupload\Events\FileScanPass;
use Ikechukwukalu\Clamavfileupload\Events\FileScanFail;
use Ikechukwukalu\Clamavfileupload\Trait\ClamAV;
use Ikechukwukalu\Clamavfileupload\Foundation\FileUpload;

class ClamavFileUpload extends FileUpload
{
    use ClamAV;

    public static function fileUpload(): bool|null|FileUploadModel|EloquentCollection
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

    private static function areFilesSafe(): bool
    {
        if (!is_array(self::$request->file(self::$input))) {
            [$fileName, $relativeFilePath] = self::fileNameAndPath();

            self::$scanData = self::scanFile(self::storageDisk()->path($relativeFilePath),
                                    self::$request->file(self::$input));

            if (!self::$scanData['status']) {
                self::logScanData(self::$scanData['error']);
                return self::$scanData['status'];
            }
        }

        $i = 1;
        foreach (self::$request->file(self::$input) as $file) {
            [$fileName, $relativeFilePath] = self::fileNameAndPath($file, $i);

            self::$scanData = self::scanFile(self::storageDisk()->path($relativeFilePath),
                                $file);

            if (!self::$scanData['status']) {
                self::logScanData(self::$scanData['error']);
                return self::$scanData['status'];
            }

            $i ++;
        }

        return true;
    }
}
