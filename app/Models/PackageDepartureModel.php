<?php

namespace App\Models;

use CodeIgniter\Model;

class PackageDepartureModel extends Model
{
    protected $table         = 'package_departures';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';

    protected $useTimestamps = true;

    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $allowedFields = [
        'package_id',
        'departure_date',
        'return_date',
        'quota',
        'booked',
        'status',
    ];

    public function getByPackage($packageId)
    {
        return $this->where('package_id', $packageId)
            ->orderBy('departure_date', 'ASC')
            ->findAll();
    }

    public function getAvailableByPackage($packageId)
    {
        return $this->where('package_id', $packageId)
            ->where('status', 'available')
            ->orderBy('departure_date', 'ASC')
            ->findAll();
    }
}