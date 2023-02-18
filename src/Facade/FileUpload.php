<?php

namespace Ikechukwukalu\Clamavfileupload\Facade;

use Ikechukwukalu\Clamavfileupload\Support\ClamavFileUpload;
use Ikechukwukalu\Clamavfileupload\Events\ClamavFileScan;
use Ikechukwukalu\Clamavfileupload\Events\ClamavQueuedFileScan;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Str;
use Ikechukwukalu\Clamavfileupload\Models\FileUploads as FileUploadModel;

class FileUpload extends ClamavFileUpload
{
    public static function uploadFiles(Request $request,
                array $settings = []): bool|FileUploadModel|EloquentCollection
    {
        self::customFileUploadSettings($settings);
        self::fileUploadSettings($request);

        if (self::$queue)
        {
            return self::clamavQueuedFileUpload($settings);
        }

        return self::clamavFileUpload();
    }

    public static function customFileUploadSettings(array $settings = []): void
    {
        $whiteList = ['name', 'input', 'folder', 'queue', 'uploadPath'];

        foreach ($settings as $key => $setting) {
            if (in_array($key, $whiteList)) {
                self::${$key} = $setting;
            }
        }

        foreach (self::defaultFileUploadSettings() as $key => $setting) {
            if (array_key_exists($key, $settings)) {
                continue;
            }

            self::${$key} = $setting;
        }

        if (self::$folder) {
            self::$uploadPath .= ("/" . self::$folder);
        }
    }

    public static function fileUploadSettings(Request $request): void
    {
        self::$request = $request;
        self::$ref = self::setRef();
    }

    private static function defaultFileUploadSettings(): array
    {
        return [
            'name' => null,
            'input' => config('clamavfileupload.input', 'file'),
            'folder' => null,
            'queue' => config('clamavfileupload.queue', true),
            'uploadPath' => config('clamavfileupload.path', 'public')
        ];
    }

    private static function clamavFileUpload(): false|FileUploadModel|EloquentCollection
    {
        ClamavFileScan::dispatch();
        if ($data = self::fileUpload()) {
            return $data;
        }

        return false;
    }

    private static function clamavQueuedFileUpload(array $settings = []): bool
    {
        $tmpFiles = self::storeFilesTemporarily();
        ClamavQueuedFileScan::dispatch(FileUpload::class, $tmpFiles, $settings);

        return true;
    }

}
