<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddWhiteLabelSiteSettings extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('site_settings')) {
            return;
        }

        $settings = [
            'site_logo'             => '',
            'site_logo_white'       => '',
            'site_favicon'          => '',
            'email_header_logo'     => '',
            'brand_primary_color'   => '#0D6EFD',
            'brand_secondary_color' => '#6C757D',
            'contact_email'         => $this->getSettingValue('site_email'),
            'contact_phone'         => $this->getSettingValue('site_phone'),
            'contact_address'       => $this->getSettingValue('site_address'),
            'social_facebook'       => $this->getSettingValue('facebook_url'),
            'social_instagram'      => $this->getSettingValue('instagram_url'),
            'social_whatsapp'       => $this->getSettingValue('site_whatsapp'),
            'footer_text'           => '',
        ];

        foreach ($settings as $key => $value) {
            $exists = $this->db->table('site_settings')
                ->where('setting_key', $key)
                ->get()
                ->getRowArray();

            if ($exists) {
                continue;
            }

            $this->db->table('site_settings')->insert([
                'setting_key'   => $key,
                'setting_value' => $value,
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s'),
            ]);
        }
    }

    public function down()
    {
        if (!$this->db->tableExists('site_settings')) {
            return;
        }

        $introducedKeys = [
            'site_logo',
            'site_logo_white',
            'site_favicon',
            'email_header_logo',
            'brand_primary_color',
            'brand_secondary_color',
            'contact_email',
            'contact_phone',
            'contact_address',
            'social_facebook',
            'social_instagram',
            'social_whatsapp',
            'footer_text',
        ];

        $this->db->table('site_settings')
            ->whereIn('setting_key', $introducedKeys)
            ->delete();
    }

    private function getSettingValue(string $key): string
    {
        $setting = $this->db->table('site_settings')
            ->where('setting_key', $key)
            ->get()
            ->getRowArray();

        return (string) ($setting['setting_value'] ?? '');
    }
}
