<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use RuntimeException;

class GenericClientSeeder extends Seeder
{
    public function run()
    {
        $this->seedSiteSettings();
        $this->seedSuperAdmin();
    }

    private function seedSiteSettings(): void
    {
        if (!$this->db->tableExists('site_settings')) {
            throw new RuntimeException('Tabel site_settings belum tersedia. Siapkan schema/migration terlebih dahulu.');
        }

        $settings = [
            'site_name'                   => 'Travel Umroh & Haji',
            'site_tagline'                => 'Perjalanan ibadah yang aman, nyaman, dan terarah',
            'site_logo'                   => '',
            'site_logo_white'             => '',
            'site_favicon'                => '',
            'email_header_logo'           => '',
            'brand_primary_color'         => '#0D6EFD',
            'brand_secondary_color'       => '#6C757D',
            'contact_email'               => '',
            'contact_phone'               => '',
            'contact_address'             => '',
            'social_facebook'             => '',
            'social_instagram'            => '',
            'social_whatsapp'             => '',
            'youtube_url'                 => '',
            'footer_text'                 => '',
            'package_program_options'     => "reguler:Reguler\nplus:Plus\nramadhan:Ramadhan\nhaji-khusus:Haji Khusus",
            'business_hours'              => 'Senin - Sabtu, 09.00 - 17.00',
            'bank_name'                   => '',
            'bank_account_number'         => '',
            'bank_account_name'           => '',
            'payment_enabled'             => '0',
            'chatbot_enabled'             => '1',
            'chatbot_name'                => 'Asisten Travel',
            'chatbot_welcome_message'     => 'Assalamualaikum. Saya asisten virtual website ini. Saya dapat membantu mencari paket, jadwal, ketersediaan seat, harga, alamat kantor, dan informasi resmi lainnya.',
            'chatbot_fallback_message'    => 'Mohon maaf, informasi tersebut belum tersedia. Saya hanya dapat membantu seputar paket, jadwal, layanan, dan informasi resmi website ini.',
            'chatbot_ai_enabled'          => '1',
            'chatbot_ai_min_confidence'   => '0.45',

            // Legacy aliases retained temporarily for backward compatibility.
            'site_email'                  => '',
            'site_phone'                  => '',
            'site_address'                => '',
            'site_whatsapp'               => '',
            'facebook_url'                => '',
            'instagram_url'               => '',
        ];

        $table = $this->db->table('site_settings');
        $now = date('Y-m-d H:i:s');

        foreach ($settings as $key => $value) {
            $exists = $table->where('setting_key', $key)->get()->getRowArray();
            if ($exists) {
                continue;
            }

            $table->insert([
                'setting_key'   => $key,
                'setting_value' => $value,
                'created_at'    => $now,
                'updated_at'    => $now,
            ]);
        }
    }

    private function seedSuperAdmin(): void
    {
        if (!$this->db->tableExists('users')) {
            throw new RuntimeException('Tabel users belum tersedia. Siapkan schema/migration terlebih dahulu.');
        }

        $exists = $this->db->table('users')
            ->where('role', 'super_admin')
            ->where('deleted_at', null)
            ->get()
            ->getRowArray();

        if ($exists) {
            return;
        }

        $email = trim((string) env('SEED_ADMIN_EMAIL', ''));
        $password = (string) env('SEED_ADMIN_PASSWORD', '');
        $name = trim((string) env('SEED_ADMIN_NAME', 'Super Admin'));

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('Isi SEED_ADMIN_EMAIL dengan alamat email valid pada .env sebelum menjalankan GenericClientSeeder.');
        }

        if (
            $password === ''
            || $password === 'your_secure_admin_password_here'
            || strlen($password) < 12
        ) {
            throw new RuntimeException('Isi SEED_ADMIN_PASSWORD dengan password bootstrap minimal 12 karakter sebelum menjalankan GenericClientSeeder.');
        }

        $now = date('Y-m-d H:i:s');

        $this->db->table('users')->insert([
            'name'              => $name !== '' ? $name : 'Super Admin',
            'email'             => $email,
            'password'          => password_hash($password, PASSWORD_DEFAULT),
            'role'              => 'super_admin',
            'status'            => 'active',
            'is_email_verified' => 1,
            'email_verified_at' => $now,
            'created_at'        => $now,
            'updated_at'        => $now,
        ]);
    }
}
