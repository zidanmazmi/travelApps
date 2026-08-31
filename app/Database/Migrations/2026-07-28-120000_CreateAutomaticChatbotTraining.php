<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAutomaticChatbotTraining extends Migration
{
    public function up()
    {
        $this->createSourcesTable();
        $this->createSourceAnswersTable();
        $this->extendKnowledgeTable();
        $this->seedSettings();
    }

    public function down()
    {
        $this->forge->dropTable('chatbot_source_answers', true);
        $this->forge->dropTable('chatbot_sources', true);

        if (!$this->db->tableExists('chatbot_knowledge')) {
            return;
        }

        $fields = [];
        foreach ([
            'source_id',
            'source_title',
            'source_priority',
            'auto_generated',
            'embedding_json',
            'embedding_model',
            'valid_from',
            'valid_until',
            'approved_at',
        ] as $field) {
            if ($this->db->fieldExists($field, 'chatbot_knowledge')) {
                $fields[] = $field;
            }
        }

        if ($fields !== []) {
            $this->forge->dropColumn('chatbot_knowledge', $fields);
        }
    }

    private function createSourcesTable(): void
    {
        if ($this->db->tableExists('chatbot_sources')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'package_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
            ],
            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'document_type' => [
                'type'       => 'ENUM',
                'constraint' => ['itinerary', 'flyer', 'booklet', 'other'],
                'default'    => 'other',
            ],
            'original_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'stored_path' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
            ],
            'mime_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'file_size' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'default'    => 0,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['uploaded', 'processing', 'review', 'published', 'archived', 'failed'],
                'default'    => 'uploaded',
            ],
            'summary' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'extracted_text' => [
                'type' => 'LONGTEXT',
                'null' => true,
            ],
            'structured_json' => [
                'type' => 'LONGTEXT',
                'null' => true,
            ],
            'confidence' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,4',
                'null'       => true,
            ],
            'valid_from' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'valid_until' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'priority' => [
                'type'       => 'INT',
                'constraint' => 5,
                'default'    => 50,
            ],
            'admin_notes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'error_message' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_by' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
            ],
            'published_by' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
            ],
            'last_processed_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'published_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('package_id');
        $this->forge->addKey('document_type');
        $this->forge->addKey('status');
        $this->forge->addKey('valid_until');
        $this->forge->addKey('deleted_at');
        $this->forge->createTable('chatbot_sources', true);
    }

    private function createSourceAnswersTable(): void
    {
        if ($this->db->tableExists('chatbot_source_answers')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'source_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
            ],
            'knowledge_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
            ],
            'question' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'keywords' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'answer' => [
                'type' => 'TEXT',
            ],
            'category' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'program' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'confidence' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,4',
                'null'       => true,
            ],
            'is_selected' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['draft', 'published', 'inactive'],
                'default'    => 'draft',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('source_id');
        $this->forge->addKey('knowledge_id');
        $this->forge->addKey('status');
        $this->forge->createTable('chatbot_source_answers', true);
    }

    private function extendKnowledgeTable(): void
    {
        if (!$this->db->tableExists('chatbot_knowledge')) {
            return;
        }

        $fields = [];
        if (!$this->db->fieldExists('source_id', 'chatbot_knowledge')) {
            $fields['source_id'] = [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
            ];
        }
        if (!$this->db->fieldExists('source_title', 'chatbot_knowledge')) {
            $fields['source_title'] = [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ];
        }
        if (!$this->db->fieldExists('source_priority', 'chatbot_knowledge')) {
            $fields['source_priority'] = [
                'type'       => 'INT',
                'constraint' => 5,
                'default'    => 50,
            ];
        }
        if (!$this->db->fieldExists('auto_generated', 'chatbot_knowledge')) {
            $fields['auto_generated'] = [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ];
        }
        if (!$this->db->fieldExists('embedding_json', 'chatbot_knowledge')) {
            $fields['embedding_json'] = [
                'type' => 'LONGTEXT',
                'null' => true,
            ];
        }
        if (!$this->db->fieldExists('embedding_model', 'chatbot_knowledge')) {
            $fields['embedding_model'] = [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ];
        }
        if (!$this->db->fieldExists('valid_from', 'chatbot_knowledge')) {
            $fields['valid_from'] = [
                'type' => 'DATE',
                'null' => true,
            ];
        }
        if (!$this->db->fieldExists('valid_until', 'chatbot_knowledge')) {
            $fields['valid_until'] = [
                'type' => 'DATE',
                'null' => true,
            ];
        }
        if (!$this->db->fieldExists('approved_at', 'chatbot_knowledge')) {
            $fields['approved_at'] = [
                'type' => 'DATETIME',
                'null' => true,
            ];
        }

        if ($fields !== []) {
            $this->forge->addColumn('chatbot_knowledge', $fields);
        }
    }

    private function seedSettings(): void
    {
        if (!$this->db->tableExists('site_settings')) {
            return;
        }

        $settings = [
            'chatbot_ai_enabled' => '1',
            'chatbot_ai_min_confidence' => '0.45',
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
}
