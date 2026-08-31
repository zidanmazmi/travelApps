<?php

namespace App\Models;

use CodeIgniter\Model;

class NotificationModel extends Model
{
    protected $table      = 'notifications';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $useTimestamps = true;

    protected $allowedFields = [
        'title',
        'message',
        'type',
        'reference_id',
        'is_read',
    ];
}