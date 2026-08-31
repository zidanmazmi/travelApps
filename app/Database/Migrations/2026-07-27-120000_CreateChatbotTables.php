<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateChatbotTables extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('chatbot_sessions')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'BIGINT',
                    'constraint'     => 20,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'session_token' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 64,
                ],
                'visitor_name' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 150,
                    'null'       => true,
                ],
                'visitor_phone' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 30,
                    'null'       => true,
                ],
                'source_page' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                ],
                'ip_address' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 64,
                    'null'       => true,
                ],
                'user_agent' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'last_intent' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 80,
                    'null'       => true,
                ],
                'last_message_at' => [
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
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addUniqueKey('session_token');
            $this->forge->addKey('last_message_at');
            $this->forge->createTable('chatbot_sessions', true);
        }

        if (!$this->db->tableExists('chatbot_messages')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'BIGINT',
                    'constraint'     => 20,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'session_id' => [
                    'type'       => 'BIGINT',
                    'constraint' => 20,
                    'unsigned'   => true,
                ],
                'sender' => [
                    'type'       => 'ENUM',
                    'constraint' => ['visitor', 'bot'],
                ],
                'message' => [
                    'type' => 'TEXT',
                ],
                'intent' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 80,
                    'null'       => true,
                ],
                'metadata_json' => [
                    'type' => 'LONGTEXT',
                    'null' => true,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey('session_id');
            $this->forge->addKey('intent');
            $this->forge->addKey('created_at');
            $this->forge->createTable('chatbot_messages', true);
        }

        if (!$this->db->tableExists('chatbot_unanswered')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'BIGINT',
                    'constraint'     => 20,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'session_id' => [
                    'type'       => 'BIGINT',
                    'constraint' => 20,
                    'unsigned'   => true,
                    'null'       => true,
                ],
                'question' => [
                    'type' => 'TEXT',
                ],
                'normalized_question' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'status' => [
                    'type'       => 'ENUM',
                    'constraint' => ['new', 'resolved', 'ignored'],
                    'default'    => 'new',
                ],
                'resolved_by' => [
                    'type'       => 'BIGINT',
                    'constraint' => 20,
                    'unsigned'   => true,
                    'null'       => true,
                ],
                'resolved_at' => [
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
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey('session_id');
            $this->forge->addKey('status');
            $this->forge->addKey('created_at');
            $this->forge->createTable('chatbot_unanswered', true);
        }

        if (!$this->db->tableExists('chatbot_knowledge')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'BIGINT',
                    'constraint'     => 20,
                    'unsigned'       => true,
                    'auto_increment' => true,
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
                'status' => [
                    'type'       => 'ENUM',
                    'constraint' => ['active', 'inactive'],
                    'default'    => 'active',
                ],
                'created_by' => [
                    'type'       => 'BIGINT',
                    'constraint' => 20,
                    'unsigned'   => true,
                    'null'       => true,
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
            $this->forge->addKey('status');
            $this->forge->addKey('deleted_at');
            $this->forge->createTable('chatbot_knowledge', true);
        }

        $settings = [
            'chatbot_enabled'          => '1',
            'chatbot_name'             => 'Asisten Travel',
            'chatbot_welcome_message'  => 'Assalamualaikum. Saya asisten virtual website ini. Saya dapat membantu mencari paket, jadwal, ketersediaan seat, harga, alamat kantor, dan informasi resmi lainnya.',
            'chatbot_fallback_message' => 'Mohon maaf, informasi tersebut belum tersedia. Saya hanya dapat membantu seputar paket, jadwal, layanan, dan informasi resmi website ini.',
        ];

        foreach ($settings as $key => $value) {
            $exists = $this->db->table('site_settings')->where('setting_key', $key)->get()->getRowArray();
            if (!$exists) {
                $this->db->table('site_settings')->insert([
                    'setting_key'   => $key,
                    'setting_value' => $value,
                    'created_at'    => date('Y-m-d H:i:s'),
                    'updated_at'    => date('Y-m-d H:i:s'),
                ]);
            }
        }
    }

    public function down()
    {
        $this->forge->dropTable('chatbot_knowledge', true);
        $this->forge->dropTable('chatbot_unanswered', true);
        $this->forge->dropTable('chatbot_messages', true);
        $this->forge->dropTable('chatbot_sessions', true);
    }
}
