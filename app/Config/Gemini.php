<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Gemini extends BaseConfig
{
    public string $apiKey = '';
    public string $baseUrl = 'https://generativelanguage.googleapis.com/v1beta';
    public string $uploadBaseUrl = 'https://generativelanguage.googleapis.com/upload/v1beta';
    public string $chatModel = 'gemini-3.5-flash-lite';
    public string $documentModel = 'gemini-3.5-flash';
    public string $embeddingModel = 'gemini-embedding-001';
    public int $embeddingDimensions = 768;
    public bool $chatbotEnabled = true;
    public int $requestTimeout = 120;
    public int $maxDocumentBytes = 52_428_800;

    public function __construct()
    {
        parent::__construct();

        $this->apiKey = trim((string) env('GEMINI_API_KEY', ''));
        $this->baseUrl = rtrim((string) env('GEMINI_BASE_URL', $this->baseUrl), '/');
        $this->uploadBaseUrl = rtrim(
            (string) env('GEMINI_UPLOAD_BASE_URL', $this->uploadBaseUrl),
            '/'
        );
        $this->chatModel = trim((string) env('GEMINI_CHAT_MODEL', $this->chatModel));
        $this->documentModel = trim(
            (string) env('GEMINI_DOCUMENT_MODEL', $this->documentModel)
        );
        $this->embeddingModel = trim(
            (string) env('GEMINI_EMBEDDING_MODEL', $this->embeddingModel)
        );
        $this->embeddingDimensions = max(
            128,
            min((int) env('GEMINI_EMBEDDING_DIMENSIONS', $this->embeddingDimensions), 3072)
        );
        $this->chatbotEnabled = filter_var(
            env('CHATBOT_AI_ENABLED', 'true'),
            FILTER_VALIDATE_BOOLEAN
        );
        $this->requestTimeout = max(
            30,
            min((int) env('GEMINI_REQUEST_TIMEOUT', $this->requestTimeout), 300)
        );
        $this->maxDocumentBytes = max(
            1_048_576,
            min((int) env('GEMINI_MAX_DOCUMENT_BYTES', $this->maxDocumentBytes), 52_428_800)
        );
    }
}
