<?php

namespace Ikechukwukalu\Clamavfileupload\Facade;

use Ikechukwukalu\Clamavfileupload\Support\ClamavFileUpload;
use Ikechukwukalu\Clamavfileupload\Models\FileUpload as FileUploadModel;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\Request;

abstract class FileUploadLogic extends ClamavFileUpload
{
    /**
     * Upload single or multiple files.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  array  $settings
     * @return  \Ikechukwukalu\Clamavfileupload\Models\FileUpload
     * @return  \Illuminate\Database\Eloquent\Collection
     * @return  bool
     */
    abstract public static function uploadFiles(Request $request,
                array $settings = []): bool|FileUploadModel|EloquentCollection;

    /**
     * Run files scan and upload.
     *
     * @param  array  $settings
     * @return  \Ikechukwukalu\Clamavfileupload\Models\FileUpload
     * @return  \Illuminate\Database\Eloquent\Collection
     * @return  bool
     */
    abstract protected static function runFileUpload(): bool|FileUploadModel|EloquentCollection;

    /**
     * Customise file upload settings.
     *
     * @param  array  $settings
     * @return  void
     */
    public static function customFileUploadSettings(array $settings = []): void
    {
        $whiteList = ['name', 'input', 'folder', 'uploadPath'];

        foreach ($settings as $key => $setting) {
            if (in_array($key, $whiteList)) {
                self::${$key} = $setting;
            }
        }

        foreach (self::defaultFileUploadSettings() as $key => $setting) {
            if (!array_key_exists($key, $settings)) {
                self::${$key} = $setting;
            }
        }

        if (self::$folder) {
            self::$uploadPath .= ("/" . self::$folder);
        }
    }

    /**
     * Set fixed file upload settings.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  ?string  $ref
     * @return  void
     */
    public static function fileUploadSettings(Request $request, string $ref = null): void
    {
        self::$request = $request;
        self::$ref = $ref ?? self::setRef();
    }

    /**
     * Default file upload settings.
     *
     * @return  array
     */
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
