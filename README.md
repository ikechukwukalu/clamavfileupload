# CLAMAV FILE UPLOAD

[![Latest Version on Packagist](https://img.shields.io/packagist/v/ikechukwukalu/clamavfileupload?style=flat-square)](https://packagist.org/packages/ikechukwukalu/clamavfileupload)
[![Quality Score](https://img.shields.io/scrutinizer/quality/g/ikechukwukalu/clamavfileupload/main?style=flat-square)](https://scrutinizer-ci.com/g/ikechukwukalu/clamavfileupload/)
[![Total Downloads](https://img.shields.io/packagist/dt/ikechukwukalu/clamavfileupload?style=flat-square)](https://packagist.org/packages/ikechukwukalu/clamavfileupload)

File upload with ClamAV anti-virus scan

## REQUIREMENTS

- PHP 8.1+
- Laravel 10+

## STEPS TO INSTALL

``` shell
composer require ikechukwukalu/clamavfileupload
```

### PUBLISH MIGRATIONS

- `php artisan vendor:publish --tag=cfu-migrations`

### PUBLISH CONFIG

- `php artisan vendor:publish --tag=cfu-config`

## PUBLISH LANG

- `php artisan vendor:publish --tag=cfu-lang`

## LICENSE

The CFU package is an open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
