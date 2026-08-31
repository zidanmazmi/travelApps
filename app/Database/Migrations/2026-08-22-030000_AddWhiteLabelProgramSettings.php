<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddWhiteLabelProgramSettings extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('site_settings')) {
            return;
        }

        $exists = $this->db->table('site_settings')
            ->where('setting_key', 'package_program_options')
            ->get()
            ->getRowArray();

        if ($exists) {
            return;
        }

        $this->db->table('site_settings')->insert([
            'setting_key'   => 'package_program_options',
            'setting_value' => "reguler:Reguler\nplus:Plus\nramadhan:Ramadhan\nhaji-khusus:Haji Khusus",
            'created_at'    => date('Y-m-d H:i:s'),
            'updated_at'    => date('Y-m-d H:i:s'),
        ]);
    }

    public function down()
    {
        if (!$this->db->tableExists('site_settings')) {
            return;
        }

        $this->db->table('site_settings')
            ->where('setting_key', 'package_program_options')
            ->delete();
    }
}
