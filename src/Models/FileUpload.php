<?php

namespace Ikechukwukalu\Clamavfileupload\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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

}
