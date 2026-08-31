<?php

namespace App\Models;

use CodeIgniter\Model;

class ChatbotSourceModel extends Model
{
    protected $table          = 'chatbot_sources';
    protected $primaryKey     = 'id';
    protected $returnType     = 'array';
    protected $useSoftDeletes = true;
    protected $useTimestamps  = true;

    protected $allowedFields = [
        'package_id',
        'title',
        'document_type',
        'original_name',
        'stored_path',
        'mime_type',
        'file_size',
        'status',
        'summary',
        'extracted_text',
        'structured_json',
        'confidence',
        'valid_from',
        'valid_until',
        'priority',
        'admin_notes',
        'error_message',
        'created_by',
        'published_by',
        'last_processed_at',
        'published_at',
    ];

    public const TYPE_OPTIONS = [
        'itinerary' => 'Itinerary',
        'flyer'     => 'Flyer / Brosur',
        'booklet'   => 'Booklet',
        'other'     => 'Dokumen Lainnya',
    ];
}
