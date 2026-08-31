<?php

namespace App\Models;

use CodeIgniter\Model;

class LeadActivityModel extends Model
{
    protected $table      = 'travel_lead_activities';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = false;

    protected $allowedFields = [
        'lead_id',
        'admin_id',
        'activity_type',
        'description',
        'old_status',
        'new_status',
        'created_at',
    ];

    public function record(
        int $leadId,
        string $activityType,
        ?string $description = null,
        ?string $oldStatus = null,
        ?string $newStatus = null,
        ?int $adminId = null
    ): bool {
        return (bool) $this->insert([
            'lead_id'       => $leadId,
            'admin_id'      => $adminId,
            'activity_type' => $activityType,
            'description'   => $description,
            'old_status'    => $oldStatus,
            'new_status'    => $newStatus,
            'created_at'    => date('Y-m-d H:i:s'),
        ]);
    }
}
