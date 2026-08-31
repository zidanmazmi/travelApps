<?php

namespace App\Models;

use CodeIgniter\Model;

class PilgrimModel extends Model
{
    protected $table         = 'pilgrims';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';

    protected $useTimestamps = true;

    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $allowedFields = [
        'registration_id',
        'is_leader',
        'full_name',
        'nik',
        'birth_place',
        'birth_date',
        'gender',
        'phone',
        'email',
        'address',
        'passport_number',
    ];

    public function getByRegistration($registrationId)
    {
        return $this->where('registration_id', $registrationId)
            ->orderBy('is_leader', 'DESC')
            ->findAll();
    }
}