<?php

namespace App\Models;

use CodeIgniter\Model;

class ChatbotKnowledgeModel extends Model
{
    protected $table          = 'chatbot_knowledge';
    protected $primaryKey     = 'id';
    protected $returnType     = 'array';
    protected $useSoftDeletes = true;
    protected $useTimestamps  = true;

    protected $allowedFields = [
        'question',
        'keywords',
        'answer',
        'status',
        'created_by',
        'source_id',
        'source_title',
        'source_priority',
        'auto_generated',
        'embedding_json',
        'embedding_model',
        'valid_from',
        'valid_until',
        'approved_at',
    ];
}
