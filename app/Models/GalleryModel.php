<?php

namespace App\Models;

use CodeIgniter\Model;

class GalleryModel extends Model
{
    protected $table      = 'galleries';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $useSoftDeletes = true;
    protected $useTimestamps  = true;

    protected $allowedFields = [
        'title',
        'image',
        'media_type',
        'video_file',
        'thumbnail',
        'mime_type',
        'file_size',
        'aspect_ratio',
        'duration_seconds',
        'description',
        'sort_order',
        'status',
    ];
}
