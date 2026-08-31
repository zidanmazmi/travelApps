<?php

namespace App\Models;

use CodeIgniter\Model;

class RegistrationModel extends Model
{
    protected $table            = 'registrations';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';

    protected $useSoftDeletes   = true;
    protected $useTimestamps    = true;

    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';
    protected $deletedField     = 'deleted_at';

    protected $allowedFields    = [
        'user_id',
        'registration_no',
        'package_id',
        'departure_id',
        'total_participants',
        'total_amount',
        'registration_status',
        'payment_status',
        'note',
    ];

    public function generateRegistrationNo()
    {
        do {
            $registrationNo = 'HQM-' . date('Y') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));

            $exists = $this->where('registration_no', $registrationNo)->first();
        } while ($exists);

        return $registrationNo;
    }

    public function getByRegistrationNo($registrationNo)
{
    return $this->select("
            registrations.*,
            packages.name AS package_name,
            packages.slug AS package_slug,
            package_departures.departure_date,
            package_departures.return_date,
            package_departures.quota,
            package_departures.booked,
            package_departures.status AS departure_status,
            users.name AS user_name,
            users.email AS user_email,
            users.phone AS user_phone,
            users.nik AS user_nik
        ", false)
        ->join('packages', 'packages.id = registrations.package_id', 'left')
        ->join('package_departures', 'package_departures.id = registrations.departure_id', 'left')
        ->join('users', 'users.id = registrations.user_id', 'left')
        ->where('registrations.registration_no', $registrationNo)
        ->first();
}

public function getByUser($userId)
{
    return $this->select("
            registrations.*,
            packages.name AS package_name,
            packages.slug AS package_slug,
            package_departures.departure_date,
            package_departures.return_date,
            users.name AS user_name,
            users.email AS user_email,
            users.phone AS user_phone,
            users.nik AS user_nik
        ", false)
        ->join('packages', 'packages.id = registrations.package_id', 'left')
        ->join('package_departures', 'package_departures.id = registrations.departure_id', 'left')
        ->join('users', 'users.id = registrations.user_id', 'left')
        ->where('registrations.user_id', $userId)
        ->orderBy('registrations.id', 'DESC')
        ->findAll();
}
}