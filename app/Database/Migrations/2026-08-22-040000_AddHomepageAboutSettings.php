<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddHomepageAboutSettings extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('site_settings')) {
            return;
        }

        $settings = [
            'about_kicker'              => 'Lorem Ipsum',
            'about_title'               => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            'about_description'         => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
            'about_image'               => '',
            'about_note_label'          => 'Lorem Ipsum',
            'about_note_title'          => 'Lorem ipsum dolor sit amet',
            'about_point_1_title'       => 'Lorem Ipsum',
            'about_point_1_description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            'about_point_2_title'       => 'Dolor Sit Amet',
            'about_point_2_description' => 'Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
            'about_point_3_title'       => 'Consectetur',
            'about_point_3_description' => 'Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris.',
            'about_cta_text'            => 'Lorem Ipsum',
            'about_cta_note'            => 'Lorem ipsum dolor sit amet',
        ];

        $table = $this->db->table('site_settings');
        $now = date('Y-m-d H:i:s');

        foreach ($settings as $key => $value) {
            $exists = $table
                ->where('setting_key', $key)
                ->get()
                ->getRowArray();

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

    public function down()
    {
        if (!$this->db->tableExists('site_settings')) {
            return;
        }

        $this->db->table('site_settings')
            ->whereIn('setting_key', [
                'about_kicker',
                'about_title',
                'about_description',
                'about_image',
                'about_note_label',
                'about_note_title',
                'about_point_1_title',
                'about_point_1_description',
                'about_point_2_title',
                'about_point_2_description',
                'about_point_3_title',
                'about_point_3_description',
                'about_cta_text',
                'about_cta_note',
            ])
            ->delete();
    }
}
