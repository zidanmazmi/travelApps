<?php

namespace App\Models;

use CodeIgniter\Model;

class ChatbotMessageModel extends Model
{
    protected $table      = 'chatbot_messages';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $useTimestamps = false;

    protected $allowedFields = [
        'session_id',
        'sender',
        'message',
        'intent',
        'metadata_json',
        'created_at',
    ];
}
