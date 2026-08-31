<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';

    protected $useSoftDeletes   = true;
    protected $useTimestamps    = true;

    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';
    protected $deletedField     = 'deleted_at';

    protected $allowedFields    = [
        'name',
        'nik',
        'email',
        'phone',
        'password',
        'role',
        'status',
        'last_login',
        'is_email_verified',
        'email_verified_at',
        'email_verification_token',
        'reset_token',
        'reset_token_expired_at',
        'deleted_by',
        'delete_reason',
    ];

    protected $validationRules = [
        'name'     => 'required|min_length[3]',
        'email'    => 'required|valid_email|is_unique[users.email]',
        'nik'      => 'permit_empty|is_unique[users.nik]',
        'password' => 'required|min_length[6]',
        'role'     => 'required|in_list[super_admin,admin,jamaah]',
    ];

    protected $validationMessages = [
        'email' => [
            'is_unique' => 'Email sudah terdaftar.',
        ],
        'nik' => [
            'is_unique' => 'NIK sudah terdaftar.',
        ],
    ];
}