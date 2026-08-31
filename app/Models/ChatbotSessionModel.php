<?php

namespace App\Models;

use CodeIgniter\Model;

class ChatbotSessionModel extends Model
{
    protected $table         = 'chatbot_sessions';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'session_token',
        'visitor_name',
        'visitor_phone',
        'source_page',
        'ip_address',
        'user_agent',
        'last_intent',
        'conversation_context_json',
        'last_message_at',
        'context_updated_at',
    ];
}
