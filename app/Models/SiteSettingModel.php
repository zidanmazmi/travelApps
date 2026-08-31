<?php

namespace App\Models;

use CodeIgniter\Model;

class SiteSettingModel extends Model
{
    protected $table      = 'site_settings';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $useTimestamps = true;

    protected $allowedFields = [
        'setting_key',
        'setting_value',
    ];

    public function getAllSettings(): array
    {
        $settings = $this->findAll();

        $result = [];

        foreach ($settings as $setting) {
            $result[$setting['setting_key']] = $setting['setting_value'];
        }

        return $result;
    }

    public function updateSetting(string $key, ?string $value): void
    {
        $exists = $this->where('setting_key', $key)->first();

        if ($exists) {
            $this->where('setting_key', $key)
                ->set([
                    'setting_value' => $value,
                    'updated_at'    => date('Y-m-d H:i:s'),
                ])
                ->update();
        } else {
            $this->insert([
                'setting_key'   => $key,
                'setting_value' => $value,
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s'),
            ]);
        }
    }
}