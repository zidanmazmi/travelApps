<?php

namespace App\Models;

use CodeIgniter\Model;

class AdminActivityLogModel extends Model
{
    protected $table      = 'admin_activity_logs';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $useTimestamps = false;

    protected $allowedFields = [
        'admin_id',
        'admin_name',
        'admin_email',
        'module',
        'action',
        'description',
        'ip_address',
        'user_agent',
        'created_at',
    ];
}