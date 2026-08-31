<?php

namespace App\Services;

use App\Models\ChatbotKnowledgeModel;
use App\Models\ChatbotSourceAnswerModel;
use App\Models\ChatbotSourceModel;
use App\Models\PackageModel;
use RuntimeException;
use Throwable;

class ChatbotDocumentTrainingService
{
    private $db;
    private ChatbotSourceModel $sourceModel;
    private ChatbotSourceAnswerModel $answerModel;
    private ChatbotKnowledgeModel $knowledgeModel;
    private GeminiService $gemini;

    public function __construct(?GeminiService $gemini = null)
    {
        $this->db = \Config\Database::connect();
        $this->sourceModel = new ChatbotSourceModel();
        $this->answerModel = new ChatbotSourceAnswerModel();
        $this->knowledgeModel = new ChatbotKnowledgeModel();
        $this->gemini = $gemini ?? new GeminiService();
    }

    public function processSource(int $sourceId): array
    {
        $source = $this->sourceModel->find($sourceId);
        if (!$source) {
            throw new RuntimeException('Sumber dokumen tidak ditemukan.');
        }

        $this->sourceModel->update($sourceId, [
            'status'        => 'processing',
            'error_message' => null,
        ]);

        try {
            $absolutePath = $this->absoluteSourcePath((string) $source['stored_path']);
            $extraction = $this->gemini->extractDocument(
                $absolutePath,
                (string) $source['mime_type'],
                (string) $source['document_type'],
                $source['admin_notes'] ?? null
            );

            $title = trim((string) ($extraction['document_title'] ?? ''));
            if ($title === '') {
                $title = (string) $source['title'];
            }

            $validFrom = $this->normalizeDate($extraction['valid_from'] ?? null);
            $validUntil = $this->normalizeDate($extraction['valid_until'] ?? null);
            $confidence = max(0, min((float) ($extraction['confidence'] ?? 0), 1));

            $this->db->transStart();

            $this->deactivatePublishedKnowledge($sourceId);
            $this->db->table('chatbot_source_answers')
                ->where('source_id', $sourceId)
                ->delete();

            $pairs = $this->normalizeQuestionPairs($extraction);
            foreach ($pairs as $pair) {
                $this->answerModel->insert([
                    'source_id'   => $sourceId,
                    'question'    => mb_substr($pair['question'], 0, 255),
                    'keywords'    => $pair['keywords'],
                    'answer'      => $pair['answer'],
                    'category'    => $pair['category'],
                    'program'     => $pair['program'],
                    'confidence'  => $pair['confidence'],
                    'is_selected' => $pair['confidence'] >= 0.55 ? 1 : 0,
                    'status'      => 'draft',
                ]);
            }

            $this->sourceModel->update($sourceId, [
                'title'             => mb_substr($title, 0, 255),
                'document_type'     => in_array(
                    $extraction['document_type'] ?? '',
                    array_keys(ChatbotSourceModel::TYPE_OPTIONS),
                    true
                ) ? $extraction['document_type'] : $source['document_type'],
                'summary'           => trim((string) ($extraction['summary'] ?? '')),
                'extracted_text'    => trim((string) ($extraction['raw_text'] ?? '')),
                'structured_json'   => json_encode(
                    $extraction,
                    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT
                ),
                'confidence'        => $confidence,
                'valid_from'        => $validFrom ?: ($source['valid_from'] ?? null),
                'valid_until'       => $validUntil ?: ($source['valid_until'] ?? null),
                'status'            => 'review',
                'error_message'     => null,
                'last_processed_at' => date('Y-m-d H:i:s'),
                'published_at'      => null,
                'published_by'      => null,
            ]);

            $this->db->transComplete();

            if (!$this->db->transStatus()) {
                throw new RuntimeException('Hasil ekstraksi gagal disimpan ke database.');
            }

            return [
                'source'        => $this->sourceModel->find($sourceId),
                'answer_count'  => count($pairs),
                'confidence'    => $confidence,
            ];
        } catch (Throwable $e) {
            $this->sourceModel->update($sourceId, [
                'status'            => 'failed',
                'error_message'     => mb_substr($e->getMessage(), 0, 2000),
                'last_processed_at' => date('Y-m-d H:i:s'),
            ]);

            throw $e;
        }
    }

    public function publishSource(
        int $sourceId,
        ?int $adminId,
        bool $replacePrevious = true
    ): array {
        $source = $this->sourceModel->find($sourceId);
        if (!$source) {
            throw new RuntimeException('Sumber dokumen tidak ditemukan.');
        }

        if (!in_array($source['status'] ?? '', ['review', 'published'], true)) {
            throw new RuntimeException('Dokumen harus selesai diekstrak dan ditinjau sebelum dipublikasikan.');
        }

        $answers = $this->answerModel
            ->where('source_id', $sourceId)
            ->where('is_selected', 1)
            ->findAll();

        if ($answers === []) {
            throw new RuntimeException('Pilih minimal satu jawaban yang akan dipublikasikan.');
        }

        $now = date('Y-m-d H:i:s');
        $published = 0;
        $embeddingFailures = 0;

        $this->db->transStart();

        if ($replacePrevious) {
            $this->archivePreviousSources($source);
        }

        foreach ($answers as $item) {
            $question = trim((string) ($item['question'] ?? ''));
            $answer = trim((string) ($item['answer'] ?? ''));
            if ($question === '' || $answer === '') {
                continue;
            }

            $embeddingJson = null;
            $embeddingModel = null;

            try {
                $embedding = $this->gemini->createEmbedding(
                    $question . "\n" .
                    (string) ($item['keywords'] ?? '') . "\n" .
                    $answer
                );
                if ($embedding !== null) {
                    $embeddingJson = json_encode($embedding['vector']);
                    $embeddingModel = $embedding['model'];
                }
            } catch (Throwable $e) {
                $embeddingFailures++;
                log_message('warning', 'Embedding chatbot gagal: ' . $e->getMessage());
            }

            $knowledgeData = [
                'question'        => mb_substr($question, 0, 255),
                'keywords'        => $this->normalizeKeywords((string) ($item['keywords'] ?? '')),
                'answer'          => $answer,
                'status'          => 'active',
                'created_by'      => $adminId,
                'source_id'       => $sourceId,
                'source_title'    => mb_substr((string) $source['title'], 0, 255),
                'source_priority' => (int) ($source['priority'] ?? 50),
                'auto_generated'  => 1,
                'embedding_json'  => $embeddingJson,
                'embedding_model' => $embeddingModel,
                'valid_from'      => $source['valid_from'] ?: null,
                'valid_until'     => $source['valid_until'] ?: null,
                'approved_at'     => $now,
            ];

            $knowledgeId = (int) ($item['knowledge_id'] ?? 0);
            $existing = $knowledgeId > 0
                ? $this->knowledgeModel->withDeleted()->find($knowledgeId)
                : null;

            if ($existing) {
                if (!empty($existing['deleted_at'])) {
                    $this->knowledgeModel->withDeleted()->update($knowledgeId, ['deleted_at' => null]);
                }
                $this->knowledgeModel->update($knowledgeId, $knowledgeData);
            } else {
                $knowledgeId = (int) $this->knowledgeModel->insert($knowledgeData, true);
            }

            $this->answerModel->update((int) $item['id'], [
                'knowledge_id' => $knowledgeId,
                'status'       => 'published',
            ]);
            $published++;
        }

        $this->answerModel
            ->where('source_id', $sourceId)
            ->where('is_selected', 0)
            ->set(['status' => 'inactive'])
            ->update();

        $this->sourceModel->update($sourceId, [
            'status'        => 'published',
            'published_by'  => $adminId,
            'published_at'  => $now,
            'error_message' => null,
        ]);

        $this->db->transComplete();

        if (!$this->db->transStatus()) {
            throw new RuntimeException('Publikasi jawaban gagal disimpan.');
        }

        return [
            'published'          => $published,
            'embedding_failures' => $embeddingFailures,
        ];
    }

    public function unpublishSource(int $sourceId): void
    {
        $source = $this->sourceModel->find($sourceId);
        if (!$source) {
            throw new RuntimeException('Sumber dokumen tidak ditemukan.');
        }

        $this->db->transStart();
        $this->deactivatePublishedKnowledge($sourceId);
        $this->answerModel
            ->where('source_id', $sourceId)
            ->set(['status' => 'draft'])
            ->update();
        $this->sourceModel->update($sourceId, [
            'status'       => 'review',
            'published_at' => null,
            'published_by' => null,
        ]);
        $this->db->transComplete();
    }

    public function refreshManualKnowledgeEmbedding(int $knowledgeId): bool
    {
        $knowledge = $this->knowledgeModel->find($knowledgeId);
        if (!$knowledge) {
            return false;
        }

        $embedding = $this->gemini->createEmbedding(
            (string) $knowledge['question'] . "\n" .
            (string) ($knowledge['keywords'] ?? '') . "\n" .
            (string) $knowledge['answer']
        );

        if ($embedding === null) {
            return false;
        }

        return $this->knowledgeModel->update($knowledgeId, [
            'embedding_json'  => json_encode($embedding['vector']),
            'embedding_model' => $embedding['model'],
            'approved_at'     => $knowledge['approved_at'] ?? date('Y-m-d H:i:s'),
        ]);
    }

    private function normalizeQuestionPairs(array $extraction): array
    {
        $pairs = [];
        $seen = [];

        foreach (($extraction['qa_pairs'] ?? []) as $pair) {
            if (!is_array($pair)) {
                continue;
            }

            $question = trim((string) ($pair['question'] ?? ''));
            $answer = trim((string) ($pair['answer'] ?? ''));
            if (mb_strlen($question) < 3 || mb_strlen($answer) < 5) {
                continue;
            }

            $fingerprint = mb_strtolower($question);
            if (isset($seen[$fingerprint])) {
                continue;
            }
            $seen[$fingerprint] = true;

            $variations = is_array($pair['variations'] ?? null)
                ? $pair['variations']
                : [];

            $program = mb_strtolower(trim((string) ($pair['program'] ?? '')));
            if (!in_array($program, array_keys(PackageModel::getProgramOptions()), true)) {
                $program = null;
            }

            $pairs[] = [
                'question'   => $question,
                'keywords'   => $this->normalizeKeywords(implode("\n", $variations)),
                'answer'     => $answer,
                'category'   => mb_substr(trim((string) ($pair['category'] ?? 'other')), 0, 50),
                'program'    => $program,
                'confidence' => max(0, min((float) ($pair['confidence'] ?? 0), 1)),
            ];

            if (count($pairs) >= 60) {
                break;
            }
        }

        if ($pairs === [] && trim((string) ($extraction['summary'] ?? '')) !== '') {
            $pairs[] = [
                'question'   => 'Apa informasi utama dari dokumen ' .
                    trim((string) ($extraction['document_title'] ?? 'Informasi Travel')) . '?',
                'keywords'   => 'informasi dokumen, ringkasan dokumen, isi dokumen',
                'answer'     => trim((string) $extraction['summary']),
                'category'   => 'other',
                'program'    => null,
                'confidence' => max(0, min((float) ($extraction['confidence'] ?? 0), 1)),
            ];
        }

        return $pairs;
    }

    private function archivePreviousSources(array $source): void
    {
        $builder = $this->db->table('chatbot_sources')
            ->select('id')
            ->where('id !=', (int) $source['id'])
            ->where('document_type', $source['document_type'])
            ->where('status', 'published')
            ->where('deleted_at', null);

        if (!empty($source['package_id'])) {
            $builder->where('package_id', (int) $source['package_id']);
        } else {
            $builder->where('package_id', null);
        }

        $previous = $builder->get()->getResultArray();

        foreach ($previous as $item) {
            $previousId = (int) $item['id'];
            $this->deactivatePublishedKnowledge($previousId);
            $this->db->table('chatbot_source_answers')
                ->where('source_id', $previousId)
                ->set(['status' => 'inactive'])
                ->update();
            $this->db->table('chatbot_sources')
                ->where('id', $previousId)
                ->update([
                    'status'     => 'archived',
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
        }
    }

    private function deactivatePublishedKnowledge(int $sourceId): void
    {
        if (!$this->db->tableExists('chatbot_knowledge')
            || !$this->db->fieldExists('source_id', 'chatbot_knowledge')) {
            return;
        }

        $this->db->table('chatbot_knowledge')
            ->where('source_id', $sourceId)
            ->update([
                'status'     => 'inactive',
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
    }

    private function normalizeKeywords(string $keywords): ?string
    {
        $parts = preg_split('/[\r\n,;|]+/u', $keywords) ?: [];
        $parts = array_map(static fn(string $part): string => trim($part), $parts);
        $parts = array_values(array_unique(array_filter(
            $parts,
            static fn(string $part): bool => $part !== ''
        )));

        return $parts === []
            ? null
            : implode(', ', array_slice($parts, 0, 50));
    }

    private function normalizeDate($value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        $timestamp = strtotime($value);
        if ($timestamp === false) {
            return null;
        }

        return date('Y-m-d', $timestamp);
    }

    private function absoluteSourcePath(string $storedPath): string
    {
        $storedPath = ltrim(str_replace(['\\', '..'], ['/', ''], $storedPath), '/');
        $path = WRITEPATH . $storedPath;
        $base = realpath(WRITEPATH . 'uploads/chatbot_sources');
        $realPath = realpath($path);
        $basePrefix = $base === false
            ? null
            : rtrim($base, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        if ($basePrefix === null
            || $realPath === false
            || !str_starts_with($realPath, $basePrefix)) {
            throw new RuntimeException('Lokasi file sumber tidak valid.');
        }

        return $realPath;
    }
}
