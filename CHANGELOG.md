# v2.0.0

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
