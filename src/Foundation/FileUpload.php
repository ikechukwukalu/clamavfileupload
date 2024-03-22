<?php

namespace Ikechukwukalu\Clamavfileupload\Foundation;

use Ikechukwukalu\Clamavfileupload\Events\FileDeleteFail;
use Ikechukwukalu\Clamavfileupload\Events\FileDeletePass;
use Ikechukwukalu\Clamavfileupload\Events\FileForceDeleteFail;
use Ikechukwukalu\Clamavfileupload\Events\FileForceDeletePass;
use Ikechukwukalu\Clamavfileupload\Events\SavedFilesIntoDB;
use Ikechukwukalu\Clamavfileupload\Models\FileUpload as FileUploadModel;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
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
    protected string $uploadPath = '/';

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
        $whiteList = ['name', 'input', 'folder', 'hashed', 'visible', 'disk'];
        $this->uploadPath = '/';

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
            $this->uploadPath .= $this->folder;
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
     * Get files.
     *
     * @param null|string $ref = null
     * @param null|string|array $id = null
     * @param bool $trashed = false
     * @return  FileUploadModel|EloquentCollection
     */
    public function getFiles(null|string $ref = null, null|string|array $id = null, bool $trashed = false): FileUploadModel|EloquentCollection
    {
        if (
            !($trashed || isset($ref) || isset($id))
        ) {
            return FileUploadModel::all();
        }


        $fileUpload = FileUploadModel::query();
        if ($trashed) {
            $fileUpload = FileUploadModel::withTrashed(true);
        }

        if (is_string($ref)) {
            $fileUpload->where('ref', $ref);
        }

        if (is_array($id)) {
            $fileUpload->whereIn('id', $id);
        } elseif (isset($id)) {
            $fileUpload->where('id', $id);
            return $fileUpload->first();
        }


        return $fileUpload->get();
    }

    /**
     * Soft delete all files from database by ref.
     *
     * @param string $ref
     * @return bool
     */
    public function deleteAll(string $ref): bool
    {
        if (!$response = $this->_deleteAll($ref)) {
            return false;
        }

        [$fileUploads, $files] = $response;

        return $this->_softDeleteFiles($fileUploads, $files);
    }

    /**
     * Soft delete multiple files from database by ref and Ids.
     *
     * @param array $ids
     * @param null|string $ref
     * @return bool
     */
    public function deleteMultiple(array $ids, null|string $ref = null): bool
    {
        if (!$response = $this->_deleteMultiple($ids, $ref)) {
            return false;
        }

        [$fileUploads, $files] = $response;

        return $this->_softDeleteFiles($fileUploads, $files);
    }

    /**
     * Soft delete single file from database by ref and id.
     *
     * @param string $ref
     * @param int|string $id
     * @return bool
     */
    public function deleteOne(int|string $id, null|string $ref = null): bool
    {
        if (!$response = $this->_deleteOne($id, $ref)) {
            return false;
        }

        [$fileUpload, $files] = $response;

        return $this->_softDeleteFiles($fileUpload, $files);
    }

    /**
     * Permanently delete all files from directory and database by ref.
     *
     * @param string $ref
     * @return bool
     */
    public function forceDeleteAll(string $ref): bool
    {
        if (!$response = $this->_deleteAll($ref)) {
            return false;
        }

        [$fileUploads, $files] = $response;
        return $this->_forceDeleteFiles($fileUploads, $files);
    }

    /**
     * Permanently delete multiple files from directory
     * and database by ref and Ids.
     *
     * @param string $ref
     * @param array $ids
     * @return bool
     */
    public function forceDeleteMultiple(array $ids, null|string $ref = null): bool
    {
        if (!$response = $this->_deleteMultiple($ids, $ref)) {
            return false;
        }

        [$fileUploads, $files] = $response;

        return $this->_forceDeleteFiles($fileUploads, $files);
    }

    /**
     * Permanently delete single file from directory
     * and database by ref and id.
     *
     * @param string $ref
     * @param int|string $id
     * @return bool
     */
    public function forceDeleteOne(int|string $id, null|string $ref = null): bool
    {
        if (!$response = $this->_deleteOne($id, $ref)) {
            return false;
        }

        [$fileUpload, $files] = $response;

        return $this->_forceDeleteFiles($fileUpload, $files);
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
        return $this->tryCatch(function() use($fileName) {
            $disk = $this->storageDisk();

            $i = 1;
            foreach ($this->request->file($this->input) as $file) {
                $fileName = $this->fileName . "_{$i}" . $this->getExtension($file);
                $disk->putFileAs($this->folder, $file, $fileName);

                if ($this->visible) {
                    $disk->putFileAs($this->folder, $file, $fileName);
                    $disk->setVisibility($this->folder . "/" . $fileName, 'public');
                }

                $i ++;
            }

            return true;

        }, function() {
            return false;
        });
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
        return $this->tryCatch(function() use($fileName) {
            $fileName = $this->fileName . $this->getExtension();
            $file = $this->request->file($this->input);
            $disk = $this->storageDisk();

            $disk->putFileAs($this->uploadPath, $file, $fileName);

            if ($this->visible) {
                $disk->setVisibility($this->folder . "/" . $fileName, 'public');
            }

            return true;

        }, function() {
            return false;
        });
    }

    /**
     * Remove single or multiple files.
     *
     * @param array $files
     * @return  bool
     */
    protected function removeFiles(array $files = [], null|string $disk = null): bool
    {
        if ($files !== []) {
            return $this->deleteMultipleFiles($files, $disk);
        }

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
    protected function deleteMultipleFiles(array $files = [], null|string $disk = null): bool
    {
        if ($files !== []) {
            return $this->tryCatch(function() use($files, $disk) {
                foreach ($files as $file) {
                    Storage::disk($disk)->delete($file);
                }

                return true;

            }, function() {
                return false;
            });
        }

        return $this->tryCatch(function() {
            $i = 1;
            foreach ($this->request->file($this->input) as $file) {
                [$fileName, $relativeFilePath] = $this->fileNameAndPath($file, $i);

                $this->storageDisk()->delete($relativeFilePath);

                $i ++;
            }

            return true;

        }, function() {
            return false;
        });
    }

    /**
     * Delete single file.
     *
     * @return  bool
     */
    protected function deleteSingleFile(): bool
    {
        return $this->tryCatch(function() {
            [$fileName, $relativeFilePath] = $this->fileNameAndPath();

            $this->storageDisk()->delete($relativeFilePath);

            return true;

        }, function() {
            return false;
        });
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
     * @param string $relativeFilePath
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
     * Save file path in database.
     *
     * @param string $relativeFilePath
     * @return  string
     */
    protected function saveRelativePathInDB($relativeFilePath): string
    {
        if ($this->hashed) {
            return Crypt::encryptString($relativeFilePath);
        }

        return $relativeFilePath;
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
        return $this->folder . "/" . $fileName;
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
            'relative_path' => $this->saveRelativePathInDB($relativeFilePath),
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
            ) {
                return $this->failedUpload(
                        trans('clamavfileupload::clamav.file_not_found',
                        ['name' => $fileName]
                    ));
            }

            if (($this->storageDisk()->size($relativeFilePath) < 1)
            ) {
                $this->removeFiles();
                return $this->failedUpload(
                        trans('clamavfileupload::clamav.corrupt_file',
                        ['name' => $fileName]
                    ));
            }

            $data[] = $this->getFileModelData($file, $i);
            $this->encryptFile($relativeFilePath, $file);
            $i ++;
        }

        return $this->tryCatch(function() use($data) {
            FileUploadModel::insert($data);
            $files = FileUploadModel::where('ref', $this->ref)->get();
            SavedFilesIntoDB::dispatch($files, $this->ref);
            $this->wasUploaded();

            return $files;

        }, function() {
            return $this->failedUpload(
                    trans('clamavfileupload::clamav.database_error',
                    ['message' => 'multiple records']
            ));
        });

    }

    /**
     * Insert single file.
     *
     * @return  \Ikechukwukalu\Clamavfileupload\Models\FileUpload
     */
    protected function insertSingleFile(): bool|FileUploadModel
    {
        [$fileName, $relativeFilePath] = $this->fileNameAndPath();

        if ($this->storageDisk()->missing($relativeFilePath))
        {
            return $this->failedUpload(
                    trans('clamavfileupload::clamav.file_not_found',
                    ['name' => $fileName]
                ));
        }

        if (($this->storageDisk()->size($relativeFilePath) < 1))
        {
            $this->removeFiles();
            return $this->failedUpload(
                    trans('clamavfileupload::clamav.corrupt_file',
                    ['name' => $fileName]
                ));
        }

        return $this->tryCatch(function() use ($fileName, $relativeFilePath) {
            $file = FileUploadModel::create($this->getFileModelData());
            SavedFilesIntoDB::dispatch($file, $this->ref);
            $this->wasUploaded();
            $this->encryptFile($relativeFilePath, $this->request->file($this->input));

            return $file;

        }, function() {
            return $this->failedUpload(
                trans('clamavfileupload::clamav.database_error',
                ['message' => 'single record']
            ));
        });
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
     * Set that files failed to delete.
     *
     * @param string $message
     * @return bool
     */
    protected function failedDelete(string $message): bool
    {
        return $this->failedUpload($message);
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

    /**
     * Try catch block.
     *
     * @param callable $tryFunc
     * @param callable $catchFunc
     * @return mixed
     */
    protected function tryCatch(callable $tryFunc, callable $catchFunc): mixed
    {
        try {
            return $tryFunc();
        } catch (\Throwable $th) {
            $this->failedUpload($th->getMessage());
            Log::error($th->getMessage());
            return $catchFunc();
        }
    }

    /**
     *
     * @param \Ikechukwukalu\Clamavfileupload\Models\FileUpload|\Illuminate\Database\Eloquent\Collection $fileUploads
     * @param array $files
     * @return bool
     */
    public function _softDeleteFiles(EloquentCollection|FileUploadModel $fileUploads, array $files): bool
    {
        return $this->tryCatch(function() use($fileUploads, $files) {
            if ($fileUploads instanceof EloquentCollection) {
                $fileUploads->each->delete();
                return true;
            }

            $fileUploads->delete();
            FileDeletePass::dispatch($files);

            return true;

        }, function() use($files) {
            FileDeleteFail::dispatch($files);
            return false;
        });
    }

    /**
     *
     * @param \Ikechukwukalu\Clamavfileupload\Models\FileUpload|\Illuminate\Database\Eloquent\Collection $fileUploads
     * @param array $files
     * @return bool
     */
    public function _forceDeleteFiles(EloquentCollection|FileUploadModel $fileUploads, array $files): bool
    {
        return $this->tryCatch(function() use($fileUploads, $files) {
            if ($fileUploads instanceof EloquentCollection) {
                $disk = $fileUploads[0]->disk;
                $fileUploads->each->forceDelete();
                $this->removeFiles($files, $disk);

                return true;
            }

            $disk = $fileUploads->disk;
            $fileUploads->forceDelete();
            $this->removeFiles($files, $disk);
            FileForceDeletePass::dispatch($files);

            return true;

        }, function() use($files) {
            FileForceDeleteFail::dispatch($files);
            return false;
        });
    }

    /**
     *
     * @param \Ikechukwukalu\Clamavfileupload\Models\FileUpload|\Illuminate\Database\Eloquent\Collection $fileUploads
     * @param array $files
     * @return array
     */
    public function extractPath(EloquentCollection|FileUploadModel $fileUploads): array
    {
        if ($fileUploads instanceof FileUploadModel) {
            return [$fileUploads->relative_path];
        }

        return $fileUploads->pluck('relative_path')->toArray();
    }

    /**
     * Soft delete all files from database by ref.
     *
     * @param string $ref
     * @return bool
     */
    private function _deleteAll(string $ref): array|null
    {
        $fileUploads = FileUploadModel::where('ref', $ref)->get();

        if ($fileUploads->count() < 1) {
            $this->failedDelete(
                trans('clamavfileupload::clamav.files_not_found_in_db'));
            return null;
        }

        $files = $this->extractPath($fileUploads);

        return [$fileUploads, $files];
    }

    /**
     * Soft delete multiple files from database by ref and Ids.
     *
     * @param array $ids
     * @param null|string $ref
     * @return bool
     */
    private function _deleteMultiple(array $ids, null|string $ref = null): array|null
    {
        $query = FileUploadModel::query();
        if ($ref) {
            $query->where('ref', $ref);
        }

        $fileUploads = $query->whereIn('id', $ids)->get();
        if ($fileUploads->count() < 1) {
            $this->failedDelete(
                trans('clamavfileupload::clamav.files_not_found_in_db'));
            return null;
        }

        $files = $this->extractPath($fileUploads);

        return [$fileUploads, $files];
    }

    /**
     * Soft delete single file from database by ref and id.
     *
     * @param string $ref
     * @param int|string $id
     * @return bool
     */
    private function _deleteOne(int|string $id, null|string $ref = null): array|null
    {
        $query = FileUploadModel::query();
        if ($ref) {
            $query->where('ref', $ref);
        }

        if (!$fileUpload = $query->where('id', $id)->first()) {
            $this->failedDelete(
                trans('clamavfileupload::clamav.files_not_found_in_db'));
            return null;
        }

        $files = $this->extractPath($fileUpload);

        return [$fileUpload, $files];
    }

    /**
     * EncryptFile.
     *
     * @param string $relativeFilePath
     * @param \Illuminate\Http\UploadedFile $file
     * @return void
     */
    private function encryptFile(string $relativeFilePath, UploadedFile $file): void
    {
        $options = $this->visible ? 'public' : [];

        if ($this->hashed) {
            // $this->storageDisk()->delete($relativeFilePath);
            $this->storageDisk()->put("{$relativeFilePath}", Crypt::encrypt($file->getContent()), $options);
        }
    }

}
