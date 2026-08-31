<?php

namespace App\Models;

use CodeIgniter\Model;

class ChatbotUnansweredModel extends Model
{
    protected $table         = 'chatbot_unanswered';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'session_id',
        'question',
        'normalized_question',
        'status',
        'resolved_by',
        'resolved_at',
    ];
}
