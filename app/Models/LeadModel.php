<?php

namespace App\Models;

use CodeIgniter\Model;

class LeadModel extends Model
{
    protected $table         = 'travel_leads';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useSoftDeletes = true;
    protected $useTimestamps  = true;

    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    protected $allowedFields = [
        'lead_no',
        'package_id',
        'departure_id',
        'full_name',
        'phone',
        'email',
        'city',
        'total_participants',
        'notes',
        'source',
        'status',
        'lead_temperature',
        'assigned_admin_id',
        'next_follow_up_at',
        'last_contacted_at',
        'whatsapp_clicked_at',
        'converted_registration_id',
        'converted_at',
        'lost_reason',
        'deleted_by',
        'delete_reason',
    ];

    public function generateLeadNo(): string
    {
        do {
            $leadNo = 'LEAD-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(2)));
        } while ($this->withDeleted()->where('lead_no', $leadNo)->first());

        return $leadNo;
    }

    public function statusLabels(): array
    {
        return [
            'new'              => 'Baru',
            'contacted'        => 'Sudah Dihubungi',
            'follow_up'        => 'Follow-up',
            'qualified'        => 'Prospek Serius',
            'waiting_decision' => 'Menunggu Keputusan',
            'converted'        => 'Berhasil Daftar',
            'not_interested'   => 'Tidak Berminat',
            'unreachable'      => 'Tidak Bisa Dihubungi',
            'cancelled'        => 'Dibatalkan',
        ];
    }

    public function temperatureLabels(): array
    {
        return [
            'cold' => 'Cold',
            'warm' => 'Warm',
            'hot'  => 'Hot',
        ];
    }
}
