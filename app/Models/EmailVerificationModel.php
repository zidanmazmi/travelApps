<?php

namespace App\Models;

use CodeIgniter\Model;

class EmailVerificationModel extends Model
{
    protected $table         = 'email_verifications';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';

    protected $useTimestamps = true;

    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $allowedFields = [
        'email',
        'verification_code',
        'purpose',
        'is_verified',
        'expired_at',
        'verified_at',
    ];

    public function createCode($email, $purpose = 'registration')
    {
        $code = (string) random_int(100000, 999999);

        $this->insert([
            'email'             => $email,
            'verification_code' => $code,
            'purpose'           => $purpose,
            'is_verified'       => 0,
            'expired_at'        => date('Y-m-d H:i:s', strtotime('+15 minutes')),
        ]);

        return $code;
    }

    public function getValidCode($email, $code, $purpose = 'registration')
    {
        return $this->where('email', $email)
            ->where('verification_code', $code)
            ->where('purpose', $purpose)
            ->where('is_verified', 0)
            ->where('expired_at >=', date('Y-m-d H:i:s'))
            ->orderBy('id', 'DESC')
            ->first();
    }

    public function markAsVerified($id)
    {
        return $this->update($id, [
            'is_verified' => 1,
            'verified_at' => date('Y-m-d H:i:s'),
        ]);
    }
}