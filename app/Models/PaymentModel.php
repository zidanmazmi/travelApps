<?php

namespace App\Models;

use CodeIgniter\Model;

class PaymentModel extends Model
{
    protected $table         = 'payments';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';

    protected $useTimestamps = true;
    protected $useSoftDeletes = false;

    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $allowedFields = [
        'registration_id',
        'order_id',
        'transaction_id',
        'payment_type',
        'gross_amount',
        'transaction_status',
        'fraud_status',
        'va_number',
        'payment_code',
        'snap_token',
        'snap_redirect_url',
        'raw_response',
        'paid_at',
        'paid_email_sent_at',
        'expired_at',
    ];

    public function getByRegistration($registrationId)
    {
        return $this->where('registration_id', $registrationId)
            ->orderBy('id', 'DESC')
            ->first();
    }

    public function getByOrderId($orderId)
    {
        return $this->where('order_id', $orderId)->first();
    }
}
