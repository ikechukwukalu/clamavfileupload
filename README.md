# CLAMAV FILE UPLOAD

[![Latest Version on Packagist](https://img.shields.io/packagist/v/ikechukwukalu/clamavfileupload?style=flat-square)](https://packagist.org/packages/ikechukwukalu/clamavfileupload)
[![Quality Score](https://img.shields.io/scrutinizer/quality/g/ikechukwukalu/clamavfileupload/main?style=flat-square)](https://scrutinizer-ci.com/g/ikechukwukalu/clamavfileupload/)
[![Total Downloads](https://img.shields.io/packagist/dt/ikechukwukalu/clamavfileupload?style=flat-square)](https://packagist.org/packages/ikechukwukalu/clamavfileupload)
[![Licence](https://img.shields.io/packagist/l/ikechukwukalu/clamavfileupload?style=flat-square)](https://packagist.org/packages/ikechukwukalu/clamavfileupload)

A simple File upload Laravel package with ClamAV anti-virus scan. This library
was built riding on an existing clamav php library [kissit/php-clamav-scan](https://github.com/kissit/php-clamav-scan).

## REQUIREMENTS

- PHP 8.1+
- Laravel 10+
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
```

- `php artisan vendor:publish --tag=cfu-migrations`
- `php artisan migrate`

### FILE UPLOAD

```php
use Ikechukwukalu\Clamavfileupload\Facade\FileUpload;


FileUpload::uploadFiles($request); //returns bool|FileUploadModel|EloquentCollection

/**
 * Default settings
 *
 * 'name' => null // This is different from file name
 * 'input' => config('clamavfileupload.input', 'file')
 * 'folder' => null
 * 'uploadPath' => config('clamavfileupload.path', 'public')
 *
 *
 */

/**
 * You can also overwrite the default settings with custom settings
 */
$settings = [
    'folder' => 'pdfs'
];
FileUpload::uploadFiles($request, $settings); //returns bool|FileUploadModel|EloquentCollection

/**
 * Access last scan results
 */
FileUpload::$scanData
```

### QUEUED FILE UPLOAD

This process stores the file in a `tmp` directory and sets up a queue for
the clamav scan and uploads the `tmp` files to their designated directory. Files will be removed from the `tmp` directory at the end of the process.

- Set `REDIS_CLIENT=predis` and `QUEUE_CONNECTION=redis` within your `.env` file.
- `php artisan queue:work`

```php
use Ikechukwukalu\Clamavfileupload\Facade\QueuedFileUpload;


QueuedFileUpload::uploadFiles($request); //returns bool|FileUploadModel|EloquentCollection

/**
 * Default settings
 *
 * 'name' => null // This is different from file name
 * 'input' => config('clamavfileupload.input', 'file')
 * 'folder' => null
 * 'uploadPath' => config('clamavfileupload.path', 'public')
 *
 *
 */

/**
 * You can also overwrite the default settings with custom settings
 */
$settings = [
    'folder' => 'pdfs'
];

QueuedFileUpload::uploadFiles($request, $settings); //returns bool|FileUploadModel|EloquentCollection

/**
 * Make sure to save the $ref UUID so as to be
 * able to retrieve uploaded files from the database.
 */
QueuedFileUpload::$ref
```

## EVENTS

```php
/**
 * Dispatches when \Ikechukwukalu\Clamavfileupload\Facade\FileUpload::uploadFiles()
 * is called.
 */
\Ikechukwukalu\Clamavfileupload\Events\ClamavFileScan::class

/**
 * Dispatches when \Ikechukwukalu\Clamavfileupload\Facade\QueuedFileUpload::uploadFiles()
 * is called.
 */
\Ikechukwukalu\Clamavfileupload\Events\ClamavQueuedFileScan::class

/**
 * Dispatches when any all files scanned are safe.
 */
\Ikechukwukalu\Clamavfileupload\Events\FileScanPass::class

/**
 * Dispatches when a file scanned has a problem.
 */
\Ikechukwukalu\Clamavfileupload\Events\FileScanFail::class
```

### NOTE

- When a single file scanned fails, the process is ended and every uploaded file is removed.
- Every batch of uploaded files has a `$ref` UUID assigned to them.
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
];
```

## PUBLISH LANG

- `php artisan vendor:publish --tag=cfu-lang`

## LICENSE

The CFU package is an open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
