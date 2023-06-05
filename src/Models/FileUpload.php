<?php

namespace Ikechukwukalu\Clamavfileupload\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Storage;

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
        'url',
        'folder',
        'hashed',
    ];

    protected function url(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => $value,
            set: fn (string $value) => Storage::url($value),
        );
    }

}
