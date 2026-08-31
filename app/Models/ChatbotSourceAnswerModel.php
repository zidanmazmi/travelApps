<?php

namespace App\Models;

use CodeIgniter\Model;

class ChatbotSourceAnswerModel extends Model
{
    protected $table         = 'chatbot_source_answers';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'source_id',
        'knowledge_id',
        'question',
        'keywords',
        'answer',
        'category',
        'program',
        'confidence',
        'is_selected',
        'status',
    ];
}
