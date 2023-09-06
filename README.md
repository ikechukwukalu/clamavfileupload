# CLAMAV FILE UPLOAD

[![Latest Version on Packagist](https://img.shields.io/packagist/v/ikechukwukalu/clamavfileupload?style=flat-square)](https://packagist.org/packages/ikechukwukalu/clamavfileupload)
[![Quality Score](https://img.shields.io/scrutinizer/quality/g/ikechukwukalu/clamavfileupload/main?style=flat-square)](https://scrutinizer-ci.com/g/ikechukwukalu/clamavfileupload/)
[![Code Quality](https://img.shields.io/codefactor/grade/github/ikechukwukalu/clamavfileupload?style=flat-square)](https://www.codefactor.io/repository/github/ikechukwukalu/clamavfileupload)
[![Known Vulnerabilities](https://snyk.io/test/github/ikechukwukalu/clamavfileupload/badge.svg?style=flat-square)](https://security.snyk.io/package/composer/ikechukwukalu%2Fclamavfileupload)
[![Github Workflow Status](https://img.shields.io/github/actions/workflow/status/ikechukwukalu/clamavfileupload/clamavfileupload.yml?branch=main&style=flat-square)](https://github.com/ikechukwukalu/clamavfileupload/actions/workflows/clamavfileupload.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/ikechukwukalu/clamavfileupload?style=flat-square)](https://packagist.org/packages/ikechukwukalu/clamavfileupload)
[![Licence](https://img.shields.io/packagist/l/ikechukwukalu/clamavfileupload?style=flat-square)](https://github.com/ikechukwukalu/clamavfileupload/blob/main/LICENSE.md)

A simple File upload Laravel package with ClamAV anti-virus scan. This library was built riding on an existing clamav php library [kissit/php-clamav-scan](https://github.com/kissit/php-clamav-scan).

## REQUIREMENTS

- PHP 8.0+
- Laravel 9+
- Clamav

## STEPS TO INSTALL

``` shell
composer require ikechukwukalu/clamavfileupload
```

- `php artisan vendor:publish --tag=cfu-config`

```shell
CLAMD_SOCK="/var/run/clamav/clamd.sock"
CLAMD_SOCK_LEN=20000
CLAMD_IP=null
CLAMD_PORT=3310
FILE_UPLOAD_INPUT=file
FILE_UPLOAD_PATH=public
FILE_UPLOAD_DISK=local
FILE_UPLOAD_LOG_SCAN_DATA=false
HASHED=false
VISIBLE=true
```

- `php artisan vendor:publish --tag=cfu-migrations`
- `php artisan migrate`

### CLAMAV SCAN FILE UPLOAD

```php
use Ikechukwukalu\Clamavfileupload\Facades\Services\FileUpload;


FileUpload::uploadFiles($request); //returns bool|FileUploadModel|EloquentCollection

/**
 * Default settings
 *
 * 'name' => null // This is different from file name
 * 'input' => config('clamavfileupload.input', 'file')
 * 'folder' => null
 * 'uploadPath' => config('clamavfileupload.path', 'public')
 * 'hashed' => config('clamavfileupload.hashed', false)
 * 'visible' => config('clamavfileupload.visible', true)
 * 'disk' => config('clamavfileupload.disk', 'local')
 *
 *
 */

/**
 * You can also overwrite the default settings with custom settings
 */
$settings = [
    'folder' => 'pdfs'
];

$fileUpload = new FileUpload;
$fileUpload::uploadFiles($request, $settings); //returns bool|FileUploadModel|EloquentCollection

/**
 * Access last scan results
 */
$fileUpload::getScanData()

/**
 * Check if upload was successful
 */
if (!$fileUpload::isSuccessful()) {
    echo $fileUpload::getErrorMessage();
}

/**
 * Make sure to save the $ref UUID so as to be
 * able to retrieve the uploaded file(s) from the database.
 */
$fileUpload::getRef()

/**
 * Soft delete files
 */

/**
 * @param string $ref
 * @return bool
 */
FileUpload::deleteAll($ref);

/**
 * @param string $ref
 * @param array $ids
 * @return bool
 */
FileUpload::deleteMultiple($ref, $ids);

/**
 * @param string $ref
 * @param int $id
 * @return bool
 */
FileUpload::deleteOne($ref, $id);


/**
 * Permanently delete files from the database and disk
 */

/**
 * @param string $ref
 * @return bool
 */
FileUpload::forceDeleteAll($ref);

/**
 * @param string $ref
 * @param array $ids
 * @return bool
 */
FileUpload::forceDeleteMultiple($ref, $ids);

/**
 * @param string $ref
 * @param int $id
 * @return bool
 */
FileUpload::forceDeleteOne($ref, $id);
```

### QUEUED CLAMAV SCAN FILE UPLOAD

This process stores the file in a `tmp` directory and sets up a queue for the clamav scan and uploads the `tmp` files to their designated directory. At the end of the process temp files would have been removed from the `tmp` directory.

- To use `Redis` set `REDIS_CLIENT=predis` and `QUEUE_CONNECTION=redis` within your `.env` file.
- `php artisan queue:work`

```php
use Ikechukwukalu\Clamavfileupload\Facades\Services\QueuedFileUpload;


QueuedFileUpload::uploadFiles($request); //returns bool|FileUploadModel|EloquentCollection

/**
 * Default settings
 *
 * 'name' => null // This is different from file name
 * 'input' => config('clamavfileupload.input', 'file')
 * 'folder' => null
 * 'uploadPath' => config('clamavfileupload.path', 'public')
 * 'hashed' => config('clamavfileupload.hashed', false)
 * 'visible' => config('clamavfileupload.visible', true)
 * 'disk' => config('clamavfileupload.disk', 'local')
 *
 *
 */

/**
 * You can also overwrite the default settings with custom settings
 */
$settings = [
    'folder' => 'pdfs'
];

$fileUpload = new QueuedFileUpload;
$fileUpload::uploadFiles($request, $settings); //returns bool|FileUploadModel|EloquentCollection

/**
 * Make sure to save the $ref UUID so as to be
 * able to retrieve the uploaded file(s) from the database.
 */
$fileUpload::getRef()

/**
 * Soft delete files
 */

/**
 * @param string $ref
 * @return bool
 */
QueuedFileUpload::deleteAll($ref);

/**
 * @param string $ref
 * @param array $ids
 * @return bool
 */
QueuedFileUpload::deleteMultiple($ref, $ids);

/**
 * @param string $ref
 * @param int $id
 * @return bool
 */
QueuedFileUpload::deleteOne($ref, $id);


/**
 * Permanently delete files from the database and disk
 */

/**
 * @param string $ref
 * @return bool
 */
QueuedFileUpload::forceDeleteAll($ref);

/**
 * @param string $ref
 * @param array $ids
 * @return bool
 */
QueuedFileUpload::forceDeleteMultiple($ref, $ids);

/**
 * @param string $ref
 * @param int $id
 * @return bool
 */
QueuedFileUpload::forceDeleteOne($ref, $id);
```

### NO CLAMAV SCAN FILE UPLOAD

```php
use Ikechukwukalu\Clamavfileupload\Facades\Services\NoClamavFileUpload;


NoClamavFileUpload::uploadFiles($request); //returns bool|FileUploadModel|EloquentCollection

/**
 * Default settings
 *
 * 'name' => null // This is different from file name
 * 'input' => config('clamavfileupload.input', 'file')
 * 'folder' => null
 * 'uploadPath' => config('clamavfileupload.path', 'public')
 * 'hashed' => config('clamavfileupload.hashed', false)
 * 'visible' => config('clamavfileupload.visible', true)
 * 'disk' => config('clamavfileupload.disk', 'local')
 *
 *
 */

/**
 * You can also overwrite the default settings with custom settings
 */
$settings = [
    'folder' => 'pdfs'
];

$fileUpload = new NoClamavFileUpload;
$fileUpload::uploadFiles($request, $settings); //returns bool|FileUploadModel|EloquentCollection

/**
 * Check if upload was successful
 */
if (!$fileUpload::isSuccessful()) {
    echo $fileUpload::getErrorMessage();
}

/**
 * Make sure to save the $ref UUID so as to be
 * able to retrieve the uploaded file(s) from the database.
 */
$fileUpload::getRef()

/**
 * Soft delete files
 */

/**
 * @param string $ref
 * @return bool
 */
NoClamavFileUpload::deleteAll($ref);

/**
 * @param string $ref
 * @param array $ids
 * @return bool
 */
NoClamavFileUpload::deleteMultiple($ref, $ids);

/**
 * @param string $ref
 * @param int $id
 * @return bool
 */
NoClamavFileUpload::deleteOne($ref, $id);


/**
 * Permanently delete files from the database and disk
 */

/**
 * @param string $ref
 * @return bool
 */
NoClamavFileUpload::forceDeleteAll($ref);

/**
 * @param string $ref
 * @param array $ids
 * @return bool
 */
NoClamavFileUpload::forceDeleteMultiple($ref, $ids);

/**
 * @param string $ref
 * @param int $id
 * @return bool
 */
NoClamavFileUpload::forceDeleteOne($ref, $id);
```

### HASH

If the `HASHED` param within your `.env` is set to `true` the `file_name`, `path` and `url` fields will be encrypted before they are saved into the DB.

It might be helpful to extend the Model file `Ikechukwukalu\Clamavfileupload\Models\FileUpload` and add the following code:

``` php
use Illuminate\Support\Facades\Crypt;


    protected function getFileNameAttribute($value)
    {
        if ($this->hashed) {
            return Crypt::decryptString($value);
        }

        return $value;
    }

    protected function getUrlAttribute($value)
    {
        if ($this->hashed) {
            return Crypt::decryptString($value);
        }

        return $value;
    }
```

## EVENTS

```php
/**
 * Dispatches when FileUpload::uploadFiles()
 * is called.
 *
 */
\Ikechukwukalu\Clamavfileupload\Events\ClamavFileScan::class

/**
 * Dispatches when QueuedFileUpload::uploadFiles()
 * is called.
 *
 * @param  array  $tmpFiles
 * @param  array  $settings
 * @param  string  $ref
 */
\Ikechukwukalu\Clamavfileupload\Events\ClamavQueuedFileScan::class

/**
 * Dispatches when all files scanned are safe.
 *
 * @param  array  $scanData
 */
\Ikechukwukalu\Clamavfileupload\Events\FileScanPass::class

/**
 * Dispatches when a file scanned has a problem.
 *
 * @param  array  $scanData
 */
\Ikechukwukalu\Clamavfileupload\Events\FileScanFail::class

/**
 * Dispatches when files have been stored and saved into the Database.
 *
 * @param  FileUploadModel|EloquentCollection $files
 * @param  string  $ref
 */
\Ikechukwukalu\Clamavfileupload\Events\SavedFilesIntoDB::class

/**
 * Dispatches when clamav is not running.
 *
 */
\Ikechukwukalu\Clamavfileupload\Events\ClamavIsNotRunning::class

/**
 * Dispatches when file soft delete fails.
 *
 * @param  array  $data
 */
\Ikechukwukalu\Clamavfileupload\Events\FileDeleteFail::class

/**
 * Dispatches when file soft delete passes.
 *
 * @param  array  $data
 */
\Ikechukwukalu\Clamavfileupload\Events\FileDeletePass::class

/**
 * Dispatches when permanent file delete from database and disk fails.
 *
 * @param  array  $data
 */
\Ikechukwukalu\Clamavfileupload\Events\FileForceDeleteFail::class

/**
 * Dispatches when permanent file delete from database and disk passes.
 *
 * @param  array  $data
 */
\Ikechukwukalu\Clamavfileupload\Events\FileForceDeletePass::class

/**
 * Dispatches when a QueuedFileUpload::deleteAll($ref) is called.
 *
 * @param  string  $ref
 */
\Ikechukwukalu\Clamavfileupload\Events\QueuedDeleteAll::class

/**
 * Dispatches when a QueuedFileUpload::deleteMultiple($ref, $ids) is called.
 *
 * @param  string  $ref
 * @param  array  $ids
 */
\Ikechukwukalu\Clamavfileupload\Events\QueuedDeleteMultiple::class

/**
 * Dispatches when a QueuedFileUpload::deleteOne($ref, $id) is called.
 *
 * @param  string  $ref
 * @param  int|string  $id
 */
\Ikechukwukalu\Clamavfileupload\Events\QueuedDeleteOne::class

/**
 * Dispatches when a QueuedFileUpload::forceDeleteAll($ref) is called.
 *
 * @param  string  $ref
 */
\Ikechukwukalu\Clamavfileupload\Events\QueuedForceDeleteAll::class

/**
 * Dispatches when a QueuedFileUpload::forceDeleteMultiple($ref, $ids) is called.
 *
 * @param  string  $ref
 * @param  array  $ids
 */
\Ikechukwukalu\Clamavfileupload\Events\QueuedForceDeleteMultiple::class

/**
 * Dispatches when a QueuedFileUpload::forceDeleteOne($ref, $id) is called.
 *
 * @param  string  $ref
 * @param  int|string  $id
 */
\Ikechukwukalu\Clamavfileupload\Events\QueuedForceDeleteOne::class
```

### NOTE

- When a single file scanned fails, the process is ended and every uploaded file is removed.
- Every batch of uploaded files has a `$ref` UUID assigned to them.
- When using `s3` disk, files are first stored in a `tmp` directory using the `local` disk where they will be scanned before being uploaded to the `s3` bucket
- Always add custom `s3` disks to the `s3_disk` array within the configurations file.
- Model file `Ikechukwukalu\Clamavfileupload\Models\FileUpload`

```php
protected $fillable = [
    'ref',
    'name',
    'file_name',
    'size',
    'extension',
    'disk',
    'mime_type',
    'path',
    'url',
    'folder',
    'hashed',
];
```

## PUBLISH LANG

- `php artisan vendor:publish --tag=cfu-lang`

## LICENSE

The CFU package is an open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
