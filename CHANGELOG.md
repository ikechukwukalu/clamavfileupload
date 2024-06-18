# v3.0.1

- Fixed test issues

## v3.0.0

- Updated package to support Laravel 11

## v2.0.6

- Fixed upload path bug
- Added file encryption for s3

## v2.0.4

- Added file encryption

## v2.0.3

- Implemented failed upload message for BasicFileUpload and TemporaryFileUpload

## v2.0.2

- Added new soft delete functions which are `deleteAll(string $ref)`, `deleteMultiple(array $ids, null|string $ref = null)` and `deleteOne(int|string $id, null|string $ref = null)`
- Added new delete functions that will permanently delete files from the database and disk which are `forceDeleteAll(string $ref)`, `forceDeleteMultiple(array $ids, null|string $ref = null)` and `forceDeleteOne(string $ref, int|string $id`
- Delete functions can also be queued by calling them from the `\Ikechukwukalu\Clamavfileupload\Facades\Services\QueuedFileUpload` facade
- Added `getFiles(null|string $ref = null, null|string|array $id = null, bool $trashed = false)` function

## v2.0.0

- Refactored code by removing static functions and switching to Facades.
- Added `disk` option for custom settings
- Added error check `isSuccessful()` and error message display `getErrorMessage()` for failed uploads
- Added check to recognise custom `s3` disk configurations within the `s3_disks` config array
- Switched from `$ref` and `$scanData` to `getRef()` and `getScanData()`

## v1.0.7

- Fixed file URL bug for s3 disk
- Fixed file scan bug for s3 Disk
- Added file scan for s3 disk over local `tmp` folder before uploading to s3 bucket
- Added Hashing options for file path
- Fixed vulnerability issue(s) found by snyk test

## v1.0.6

- Make file visibility adjustable on file upload

## v1.0.5

- Improve file url saved into DB

## v1.0.4

- Added Accesor for `url` field

## v1.0.3

- Fixed minor bug in `packages/ikechukwukalu/clamavfileupload/src/Foundation/FileUpload.php` on `L353`

## v1.0.2

- Added hashed option to settings

## v1.0.1

- Added NoClamavFileUpload
- Added folder and hashed field to db
- Added Hashing options for file_name and url
- Fixed FileScanPass Event dispatch bug
- Added SavedFilesIntoDB and ClamavIsNotRunning events
