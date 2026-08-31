<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSmartContextToChatbotSessions extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('chatbot_sessions')) {
            return;
        }

        $fields = [];

        if (!$this->db->fieldExists('conversation_context_json', 'chatbot_sessions')) {
            $fields['conversation_context_json'] = [
                'type'  => 'LONGTEXT',
                'null'  => true,
                'after' => 'last_intent',
            ];
        }

        if (!$this->db->fieldExists('context_updated_at', 'chatbot_sessions')) {
            $fields['context_updated_at'] = [
                'type'  => 'DATETIME',
                'null'  => true,
                'after' => 'last_message_at',
            ];
        }

        if ($fields !== []) {
            $this->forge->addColumn('chatbot_sessions', $fields);
        }
    }

    public function down()
    {
        if (!$this->db->tableExists('chatbot_sessions')) {
            return;
        }

        $fields = [];

        if ($this->db->fieldExists('conversation_context_json', 'chatbot_sessions')) {
            $fields[] = 'conversation_context_json';
        }

        if ($this->db->fieldExists('context_updated_at', 'chatbot_sessions')) {
            $fields[] = 'context_updated_at';
        }

        if ($fields !== []) {
            $this->forge->dropColumn('chatbot_sessions', $fields);
        }
    }
}
