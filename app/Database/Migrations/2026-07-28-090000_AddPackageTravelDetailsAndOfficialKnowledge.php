<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPackageTravelDetailsAndOfficialKnowledge extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('packages')) {
            return;
        }

        $fields = [];

        if (!$this->db->fieldExists('airline', 'packages')) {
            $fields['airline'] = [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ];
        }

        if (!$this->db->fieldExists('hotel_makkah', 'packages')) {
            $fields['hotel_makkah'] = [
                'type' => 'TEXT',
                'null' => true,
            ];
        }

        if (!$this->db->fieldExists('hotel_madinah', 'packages')) {
            $fields['hotel_madinah'] = [
                'type' => 'TEXT',
                'null' => true,
            ];
        }

        if (!$this->db->fieldExists('facilities_text', 'packages')) {
            $fields['facilities_text'] = [
                'type' => 'TEXT',
                'null' => true,
            ];
        }

        if ($fields !== []) {
            $this->forge->addColumn('packages', $fields);
        }
    }

    public function down()
    {
        if (!$this->db->tableExists('packages')) {
            return;
        }

        $fields = [];
        foreach (['airline', 'hotel_makkah', 'hotel_madinah', 'facilities_text'] as $field) {
            if ($this->db->fieldExists($field, 'packages')) {
                $fields[] = $field;
            }
        }

        if ($fields !== []) {
            $this->forge->dropColumn('packages', $fields);
        }
    }
}
