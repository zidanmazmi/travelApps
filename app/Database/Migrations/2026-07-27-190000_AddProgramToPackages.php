<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddProgramToPackages extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('packages') || $this->db->fieldExists('program', 'packages')) {
            return;
        }

        $this->forge->addColumn('packages', [
            'program' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => true,
                'after'      => 'badge',
            ],
        ]);
    }

    public function down()
    {
        if ($this->db->tableExists('packages') && $this->db->fieldExists('program', 'packages')) {
            $this->forge->dropColumn('packages', 'program');
        }
    }
}
