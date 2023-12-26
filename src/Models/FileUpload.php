<?php

namespace Ikechukwukalu\Clamavfileupload\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Crypt;

class FileUpload extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = "file_uploads";

    protected $fillable = [
        'ref',
        'name',
        'file_name',
        'size',
        'extension',
        'disk',
        'mime_type',
        'path',
        'relative_path',
        'url',
        'folder',
        'hashed',
    ];

    protected $casts = [
        'hashed' => 'boolean'
    ];


        protected function getFileNameAttribute($value)
        {
            if ($this->hashed) {
                return Crypt::decryptString($value);
            }

            return $value;
        }

        protected function getPathAttribute($value)
        {
            if ($this->hashed) {
                return Crypt::decryptString($value);
            }

            return $value;
        }


        protected function getRelativePathAttribute($value)
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

}
