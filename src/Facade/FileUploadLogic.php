<?php

namespace Ikechukwukalu\Clamavfileupload\Facade;

use Ikechukwukalu\Clamavfileupload\Support\ClamavFileUpload;
use Ikechukwukalu\Clamavfileupload\Models\FileUploads as FileUploadModel;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\Request;

abstract class FileUploadLogic extends ClamavFileUpload
{
    abstract public static function uploadFiles(Request $request,
                array $settings = []): bool|FileUploadModel|EloquentCollection;
    abstract protected static function clamavFileUpload(): bool|FileUploadModel|EloquentCollection;

    public static function customFileUploadSettings(array $settings = []): void
    {
        $whiteList = ['name', 'input', 'folder', 'uploadPath'];

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

    public static function fileUploadSettings(Request $request, string $ref = null): void
    {
        self::$request = $request;
        self::$ref = $ref ?? self::setRef();
    }

    private static function defaultFileUploadSettings(): array
    {
        return [
            'name' => null,
            'input' => config('clamavfileupload.input', 'file'),
            'folder' => null,
            'uploadPath' => config('clamavfileupload.path', 'public')
        ];
    }

}
