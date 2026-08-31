<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddLocalMediaToGalleries extends Migration
{
    public function up()
    {
        $fields = [
            'media_type' => [
                'type'       => 'ENUM',
                'constraint' => ['image', 'video'],
                'default'    => 'image',
                'null'       => false,
                'after'      => 'title',
            ],
            'video_file' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'image',
            ],
            'thumbnail' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'video_file',
            ],
            'mime_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'after'      => 'thumbnail',
            ],
            'file_size' => [
                'type'     => 'BIGINT',
                'unsigned' => true,
                'null'     => true,
                'after'    => 'mime_type',
            ],
            'aspect_ratio' => [
                'type'       => 'ENUM',
                'constraint' => ['portrait', 'landscape', 'square'],
                'default'    => 'landscape',
                'null'       => false,
                'after'      => 'file_size',
            ],
            'duration_seconds' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
                'after'    => 'aspect_ratio',
            ],
        ];

        foreach ($fields as $name => $definition) {
            if (!$this->db->fieldExists($name, 'galleries')) {
                $this->forge->addColumn('galleries', [$name => $definition]);
            }
        }
    }

    public function down()
    {
        foreach (['duration_seconds', 'aspect_ratio', 'file_size', 'mime_type', 'thumbnail', 'video_file', 'media_type'] as $field) {
            if ($this->db->fieldExists($field, 'galleries')) {
                $this->forge->dropColumn('galleries', $field);
            }
        }
    }
}
