<?php

namespace Ikechukwukalu\Clamavfileupload\Foundation;

use Ikechukwukalu\Clamavfileupload\Events\SavedFilesIntoDB;
use Ikechukwukalu\Clamavfileupload\Models\FileUpload as FileUploadModel;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class FileUpload
{
    public Request $request;
    public null|string $ref;

    protected array $scanData;
    protected bool $hashed;
    protected bool $visible;
    protected bool $success;
    protected null|string $name = null;
    protected null|string $errorMessage = null;
    protected null|string $folder = null;
    protected string $disk;
    protected string $fileName;
    protected string $input;
    protected string $uploadPath;

    /**
     * Log scan data.
     *
     * @param  string  $message
     * @return  void
     */
    public function logScanData(string $message): void
    {
        if (config('clamavfileupload.log_scan_data')) {
            Log::alert($message);
        }
    }

    /**
     * Customise file upload settings.
     *
     * @param  array  $settings
     * @return  void
     */
    public function customFileUploadSettings(array $settings = []): void
    {
        $whiteList = ['name', 'input', 'folder',
            'uploadPath', 'hashed', 'visible', 'disk'];

        foreach ($settings as $key => $setting) {
            if (in_array($key, $whiteList)) {
                $this->{$key} = $setting;
            }
        }

        foreach ($this->defaultFileUploadSettings() as $key => $setting) {
            if (!array_key_exists($key, $settings)) {
                $this->{$key} = $setting;
            }
        }

        if ($this->folder) {
            $this->uploadPath .= ("/" . $this->folder);
        }
    }

    /**
     * Set fixed file upload settings.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  null|string  $ref
     * @return  void
     */
    public function fileUploadSettings(Request $request, string $ref = null): void
    {
        $this->request = $request;
        $this->ref = $ref ?? $this->setRef();
    }

    /**
     * Get ref.
     *
     * @return  string
     */
    public function getRef(): string
    {
        return $this->ref;
    }

    /**
     * Get scan data.
     *
     * @return  string
     */
    public function getScanData(): array
    {
        return $this->scanData;
    }

    /**
     * Get input.
     *
     * @return  string
     */
    public function getInput(): string
    {
        return $this->input;
    }

    /**
     * Check if file upload was successful.
     *
     * @return  bool
     */
    public function isSuccessful(): bool
    {
        return $this->success;
    }

    /**
     * Get error message.
     *
     * @return  null|string
     */
    public function getErrorMessage(): null|string
    {
        return $this->errorMessage;
    }

    /**
     * Provide \Illuminate\Support\Facades\Storage::build.
     *
     * @return  \Illuminate\Contracts\Filesystem\Filesystem
     */
    protected function provideDisk(): Filesystem
    {
        $this->storageDisk()->makeDirectory($this->uploadPath);
        return $this->storageDisk();
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
            return $this->saveMultipleFiles();
        }

        return $this->saveSingleFile();
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

        $i = 1;
        foreach ($this->request->file($this->input) as $file) {
            $fileName = $this->fileName . "_{$i}" . $this->getExtension($file);

            if ($this->visible) {
                $disk->putFileAs($this->uploadPath, $file, $fileName, 'public');
            } else {
                $disk->putFileAs($this->uploadPath, $file, $fileName);
            }

            $i ++;
        }

        return true;
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
        $fileName = $this->fileName . $this->getExtension();

        if ($this->visible) {
            $this->provideDisk()->putFileAs($this->uploadPath,
                    $this->request->file($this->input), $fileName, 'public');
        } else {
            $this->provideDisk()->putFileAs($this->uploadPath,
                    $this->request->file($this->input), $fileName);
        }

        return true;
    }

    /**
     * Remove single or multiple files.
     *
     * @param array $files
     * @return  bool
     */
    protected function removeFiles(array $files = []): bool
    {
        if (is_array($this->request->file($this->input))) {
            return $this->deleteMultipleFiles();
        }

        return $this->deleteSingleFile();
    }

    /**
     * Delete multiple files.
     *
     * @return  bool
     */
    protected function deleteMultipleFiles(): bool
    {
        $i = 1;
        foreach ($this->request->file($this->input) as $file) {
            [$fileName, $relativeFilePath] = $this->fileNameAndPath($file, $i);

            $this->storageDisk()->delete($relativeFilePath);

            $i ++;
        }

        return true;
    }

    /**
     * Delete single file.
     *
     * @return  bool
     */
    protected function deleteSingleFile(): bool
    {
        [$fileName, $relativeFilePath] = $this->fileNameAndPath();

        $this->storageDisk()->delete($relativeFilePath);

        return true;
    }

    /**
     * Set UUID ref.
     *
     * @return  string
     */
    protected function setRef(): string
    {
        return (string) Str::uuid();
    }

    /**
     * Get UUID ref.
     *
     * @param $file
     * @param $i
     * @return  string
     */
    protected function getName($file = null, $i = null): string
    {
        if ($file && $i) {
            return $this->name ? $this->name . "_{$i}"
                : $file->getClientOriginalName();
        }

        return $this->name ?? $this->request->file($this->input)
                ->getClientOriginalName();
    }

    /**
     * Set file name.
     *
     * @return  string
     */
    protected function setFileName(): string
    {
        return time() . Str::random(40);
    }

    /**
     * Save file name in database.
     *
     * @param $file
     * @return  string
     */
    protected function saveFileNameInDB($fileName): string
    {
        if ($this->hashed) {
            return Crypt::encryptString($fileName);
        }

        return $fileName;
    }

    /**
     * Save file url in database.
     *
     * @param $file
     * @return  string
     */
    protected function saveURLInDB($relativeFilePath): string
    {
        $url = $this->storageDisk()->url($relativeFilePath);

        if ($this->hashed) {
            return Crypt::encryptString($url);
        }

        return $url;
    }

    /**
     * Save file path in database.
     *
     * @param $file
     * @return  string
     */
    protected function savePathInDB($relativeFilePath): string
    {
        $path = $this->storageDisk()->path($relativeFilePath);

        if ($this->hashed) {
            return Crypt::encryptString($path);
        }

        return $path;
    }

    /**
     * Get extension.
     *
     * @param $file
     * @return  string
     */
    protected function getExtension($file = null): string
    {
        if ($file) {
            return '.' . $file->getClientOriginalExtension();
        }

        return '.' . $this->request->file($this->input)
                ->getClientOriginalExtension();
    }

    /**
     * Get relative path.
     *
     * @param $fileName
     * @return  string
     */
    protected function getRelativeFilePath($fileName): string
    {
        return $this->uploadPath . "/" . $fileName;
    }

    /**
     * Get disk.
     *
     * @return  string
     */
    protected function getDisk(): string
    {
        return $this->disk;
    }

    /**
     * Get \Illuminate\Support\Facades\Storage::disk.
     *
     * @return  \Illuminate\Contracts\Filesystem\Filesystem
     */
    protected function storageDisk(): Filesystem
    {
        return Storage::disk($this->getDisk());
    }

    /**
     * Get file name and path.
     *
     * @param $file
     * @param $i
     * @return  array
     */
    protected function fileNameAndPath($file = null, $i = null): array
    {
        if ($file && $i) {
            $fileName = $this->fileName . "_{$i}" . $this->getExtension($file);
            return [$fileName, $this->getRelativeFilePath($fileName)];
        }

        $fileName = $this->fileName . $this->getExtension();
        return [$fileName, $this->getRelativeFilePath($fileName)];
    }

    /**
     * Get file model data.
     *
     * @param $file
     * @param $i
     * @return  array
     */
    protected function getFileModelData($file = null, $i = null): array
    {
        [$fileName, $relativeFilePath] = $this->fileNameAndPath($file, $i);
        return [
            'ref' => $this->ref,
            'name' => $this->getName($file, $i),
            'file_name' => $this->saveFileNameInDB($fileName),
            'url' => $this->saveURLInDB($relativeFilePath),
            'size' => $this->storageDisk()->size($relativeFilePath),
            'extension' => $this->getExtension($file),
            'disk' => $this->getDisk(),
            'mime_type' => $this->storageDisk()->mimeType($relativeFilePath),
            'path' => $this->savePathInDB($relativeFilePath),
            'folder' => $this->folder,
            'hashed' => $this->hashed
        ];
    }

    /**
     * Insert multiple files.
     *
     * @return  \Illuminate\Database\Eloquent\Collection
     */
    protected function insertMultipleFiles(): bool|EloquentCollection
    {
        $data = [];
        $i = 1;

        foreach ($this->request->file($this->input) as $file) {
            [$fileName, $relativeFilePath] = $this->fileNameAndPath($file, $i);

            if ($this->storageDisk()->missing($relativeFilePath)
                || ($this->storageDisk()->size($relativeFilePath) < 1)
            ) {
                $this->removeFiles();
                return $this->failedUpload(
                        trans('clamavfileupload::clamav.corrupt_file',
                        ['name' => $fileName]
                    ));
            }

            $data[] = $this->getFileModelData($file, $i);
            $i ++;
        }

        if (FileUploadModel::insert($data)) {
            $files = FileUploadModel::where('ref', $this->ref)->get();
            SavedFilesIntoDB::dispatch($files, $this->ref);
            $this->wasUploaded();

            return $files;
        }

        return $this->failedUpload(
                trans('clamavfileupload::clamav.database_error',
                ['message' => 'multiple records']
            ));
    }

    /**
     * Insert single file.
     *
     * @return  \Ikechukwukalu\Clamavfileupload\Models\FileUpload
     */
    protected function insertSingleFile(): bool|FileUploadModel
    {
        [$fileName, $relativeFilePath] = $this->fileNameAndPath();

        if ($this->storageDisk()->missing($relativeFilePath)
            || ($this->storageDisk()->size($relativeFilePath) < 1)
        ) {
            $this->removeFiles();
            return $this->failedUpload(
                    trans('clamavfileupload::clamav.corrupt_file',
                    ['name' => $fileName]
                ));
        }

        if ($file = FileUploadModel::create($this->getFileModelData())) {
            SavedFilesIntoDB::dispatch($file, $this->ref);
            $this->wasUploaded();

            return $file;
        }


        return $this->failedUpload(
            trans('clamavfileupload::clamav.database_error',
            ['message' => 'single record']
        ));
    }

    /**
     * Default file upload settings.
     *
     * @return  array
     */
    protected function defaultFileUploadSettings(): array
    {
        return [
            'name' => null,
            'input' => config('clamavfileupload.input', 'file'),
            'folder' => null,
            'uploadPath' => config('clamavfileupload.path', 'public'),
            'hashed' => config('clamavfileupload.hashed', false),
            'visible' => config('clamavfileupload.visible', true),
            'disk' => config('clamavfileupload.disk', 'local')
        ];
    }

    /**
     * Set that files failed to upload.
     *
     * @param string $message
     * @return bool
     */
    protected function failedUpload(string $message): bool
    {
        $this->errorMessage = $message;
        $this->success = false;

        return false;
    }

    /**
     * Set that files was uploaded.
     *
     * @return bool
     */
    protected function wasUploaded(): bool
    {
        $this->success = true;

        return true;
    }
}
