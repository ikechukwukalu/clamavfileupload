# v1.0.7

- Fixed file URL bug for s3 disk
- Fixed file scan bug for s3 Disk
- Added file scan for s3 disk over local `tmp` folder before uploading to s3 bucket
- Added Hashing options for file path

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
