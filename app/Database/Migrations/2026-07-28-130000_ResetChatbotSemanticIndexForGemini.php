<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ResetChatbotSemanticIndexForGemini extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('chatbot_knowledge')
            || !$this->db->fieldExists('embedding_json', 'chatbot_knowledge')
            || !$this->db->fieldExists('embedding_model', 'chatbot_knowledge')) {
            return;
        }

        $this->db->table('chatbot_knowledge')
            ->where('embedding_model IS NOT NULL', null, false)
            ->notLike('embedding_model', 'gemini-', 'after')
            ->set([
                'embedding_json'  => null,
                'embedding_model' => null,
            ])
            ->update();
    }

    public function down()
    {
        // Embedding lama tidak dapat dipulihkan. Jalankan sinkronisasi untuk membuat ulang.
    }
}
