<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ResetLegacyBrandingDefaults extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('site_settings')) {
            return;
        }

        // Only replace exact known legacy defaults. Custom client values are never overwritten.
        $legacyDefaults = [
            'site_name' => ['hash' => 'a43e6ab3d91bb91c7d62ce15f339450b8d116defba72421297a73811ed1ecf08', 'replacement' => 'Travel Umroh & Haji'],
            'bank_account_name' => ['hash' => 'a43e6ab3d91bb91c7d62ce15f339450b8d116defba72421297a73811ed1ecf08', 'replacement' => ''],
            'site_email' => ['hash' => '0c22dfef8bee8a5c5c9efbff4aa3c14c727b644e4ef7265fd82ea98cf014d8c7', 'replacement' => ''],
            'contact_email' => ['hash' => '0c22dfef8bee8a5c5c9efbff4aa3c14c727b644e4ef7265fd82ea98cf014d8c7', 'replacement' => ''],
            'site_phone' => ['hash' => '6c37e37da34b1484a05441f8087c9700673f1c54ad89aa7ea7594664d21dc3b0', 'replacement' => ''],
            'contact_phone' => ['hash' => '6c37e37da34b1484a05441f8087c9700673f1c54ad89aa7ea7594664d21dc3b0', 'replacement' => ''],
            'site_whatsapp' => ['hash' => '02957aa7f5821986e12d58d158c0f1d70a9abfe5df0cdc27696479984399f25c', 'replacement' => ''],
            'social_whatsapp' => ['hash' => '02957aa7f5821986e12d58d158c0f1d70a9abfe5df0cdc27696479984399f25c', 'replacement' => ''],
            'site_address' => ['hash' => 'd49b2f4ede4ee244f75cc67c38531aecbbe526dae57f5a51a2f5d750243ac266', 'replacement' => ''],
            'contact_address' => ['hash' => 'd49b2f4ede4ee244f75cc67c38531aecbbe526dae57f5a51a2f5d750243ac266', 'replacement' => ''],
            'instagram_url' => ['hash' => 'e1f752349c74a85125bf6935ccdc724831f78144566f33ae70ae7c3887e9dba3', 'replacement' => ''],
            'social_instagram' => ['hash' => 'e1f752349c74a85125bf6935ccdc724831f78144566f33ae70ae7c3887e9dba3', 'replacement' => ''],
            'chatbot_name' => ['hash' => '3078ff008b0ed5b021d4dd8572fbfd8d2ebcfb1246232400e8d8a3a527c7d75b', 'replacement' => 'Asisten Travel'],
            'chatbot_welcome_message' => ['hash' => '0d20a01b0d9159658479a9e7512e3e2bd334577fb6b831acdd056c63be4ed4c6', 'replacement' => 'Assalamualaikum. Saya asisten virtual website ini. Saya dapat membantu mencari paket, jadwal, ketersediaan seat, harga, alamat kantor, dan informasi resmi lainnya.'],
            'chatbot_fallback_message' => ['hash' => '11c0ec7864fc281b5345e407a28d42e5c2d4010933e0c03ff15b2108144f5fb2', 'replacement' => 'Mohon maaf, informasi tersebut belum tersedia. Saya hanya dapat membantu seputar paket, jadwal, layanan, dan informasi resmi website ini.'],
            'bank_account_number' => ['hash' => 'c775e7b757ede630cd0aa1113bd102661ab38829ca52a6422ab782862f268646', 'replacement' => ''],
        ];

        $table = $this->db->table('site_settings');

        foreach ($legacyDefaults as $key => $rule) {
            $row = $table->where('setting_key', $key)->get()->getRowArray();
            if (!$row) {
                continue;
            }

            $current = trim((string) ($row['setting_value'] ?? ''));
            if (!hash_equals($rule['hash'], hash('sha256', $current))) {
                continue;
            }

            $table->where('setting_key', $key)->update([
                'setting_value' => $rule['replacement'],
                'updated_at'    => date('Y-m-d H:i:s'),
            ]);
        }
    }

    public function down()
    {
        // Intentionally irreversible: legacy vendor branding must not be restored.
    }
}
