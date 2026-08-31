<?php

namespace App\Services;

use App\Models\SiteSettingModel;
use App\Models\PackageModel;
use Throwable;

class ChatbotAIService
{
    private $db;
    private GeminiService $gemini;
    private array $settings;

    public function __construct(?GeminiService $gemini = null)
    {
        $this->db = \Config\Database::connect();
        $this->gemini = $gemini ?? new GeminiService();
        $this->settings = (new SiteSettingModel())->getAllSettings();
    }

    public function enhance(
        string $message,
        int $sessionId,
        array $conversationContext,
        array $baseline
    ): ?array {
        if (!$this->gemini->isConfigured()
            || ($this->settings['chatbot_ai_enabled'] ?? '1') !== '1') {
            return null;
        }

        $baselineIntent = (string) ($baseline['intent'] ?? '');
        if (in_array($baselineIntent, [
            'empty',
            'greeting',
            'thanks',
            'context_reset',
            'contact',
            'office_address',
            'business_hours',
        ], true)) {
            return null;
        }

        try {
            $knowledge = $this->retrieveKnowledge($message, $conversationContext);
            $packages = $this->packageSnapshot($message, $conversationContext);
            $history = $this->conversationHistory($sessionId);

            $grounding = [
                'pesan_pengunjung' => $message,
                'waktu_sekarang'   => date('Y-m-d H:i:s') . ' Asia/Jakarta (WIB, GMT+7)',
                'konteks_tersimpan' => $this->cleanContext($conversationContext),
                'riwayat_ringkas'   => $history,
                'hasil_mesin_deterministik' => [
                    'intent'   => $baselineIntent,
                    'jawaban'  => (string) ($baseline['reply'] ?? ''),
                    'paket_ui' => $baseline['packages'] ?? [],
                ],
                'data_paket_resmi' => $packages,
                'knowledge_resmi'  => array_map(static function (array $item): array {
                    return [
                        'knowledge_id'   => (int) ($item['id'] ?? 0),
                        'source_id'      => (int) ($item['source_id'] ?? 0),
                        'source_title'   => (string) ($item['source_title'] ?? 'Knowledge Manual'),
                        'priority'       => (int) ($item['source_priority'] ?? 50),
                        'question'       => (string) ($item['question'] ?? ''),
                        'variations'     => (string) ($item['keywords'] ?? ''),
                        'official_answer'=> (string) ($item['answer'] ?? ''),
                        'valid_until'    => $item['valid_until'] ?? null,
                    ];
                }, $knowledge),
                'aturan_output' => [
                    'Jika data pasti tersedia, jawab langsung tanpa meminta konfirmasi admin.',
                    'Jika satu fakta tidak tersedia, jangan menggantinya dengan data program atau paket lain.',
                    'Pertahankan kartu paket dari hasil mesin bila masih relevan.',
                    'used_source_ids hanya berisi source_id yang benar-benar digunakan; gunakan array kosong untuk knowledge manual.',
                ],
            ];

            $ai = $this->gemini->generateChatbotAnswer($grounding);
            $reply = trim((string) ($ai['reply'] ?? ''));
            $confidence = max(0, min((float) ($ai['confidence'] ?? 0), 1));
            $minimum = max(
                0.2,
                min((float) ($this->settings['chatbot_ai_min_confidence'] ?? 0.45), 0.95)
            );

            if ($reply === '' || $confidence < $minimum) {
                return null;
            }

            $result = $baseline;
            $result['reply'] = mb_substr($reply, 0, 5000);
            $result['intent'] = trim((string) ($ai['intent'] ?? '')) ?: $baselineIntent;
            $result['unanswered'] = (bool) ($ai['unanswered'] ?? false);
            $result['_ai_powered'] = true;
            $result['_ai_confidence'] = $confidence;
            $result['_ai_source_ids'] = array_values(array_unique(array_map(
                'intval',
                is_array($ai['used_source_ids'] ?? null) ? $ai['used_source_ids'] : []
            )));
            $result['_ai_context'] = is_array($ai['context'] ?? null) ? $ai['context'] : [];

            $aiContext = $result['_ai_context'];
            if (empty($ai['preserve_package_cards'])) {
                $result['packages'] = [];
            } elseif (!empty($aiContext['program']) && !empty($result['packages'])) {
                $program = $this->normalizeProgram($aiContext['program']);
                if ($program !== null) {
                    $result['packages'] = array_values(array_filter(
                        $result['packages'],
                        fn(array $package): bool =>
                            $this->normalizeProgram($package['program'] ?? null) === $program
                    ));
                }
            }

            if (!empty($aiContext['topic'])) {
                $result['_context_topic'] = mb_substr((string) $aiContext['topic'], 0, 50);
            }
            if (!empty($aiContext['focus'])) {
                $result['_context_focus'] = mb_substr((string) $aiContext['focus'], 0, 50);
            }

            if (!empty($ai['needs_handoff'])) {
                $result['quick_replies'] = [
                    ['label' => 'Hubungi Admin', 'message' => 'Hubungi admin'],
                    ['label' => 'Tanya Hal Lain', 'message' => 'Saya ingin menanyakan hal lain'],
                ];
            }

            return $result;
        } catch (Throwable $e) {
            log_message('warning', 'Smart free-text fallback: ' . $e->getMessage());

            return null;
        }
    }

    private function retrieveKnowledge(string $message, array $context): array
    {
        if (!$this->db->tableExists('chatbot_knowledge')) {
            return [];
        }

        $rows = $this->db->table('chatbot_knowledge')
            ->where('status', 'active')
            ->where('deleted_at', null)
            ->orderBy('id', 'DESC')
            ->limit(500)
            ->get()
            ->getResultArray();

        $today = date('Y-m-d');
        $queryEmbedding = null;
        try {
            $embedding = $this->gemini->createEmbedding($message, 'RETRIEVAL_QUERY');
            $queryEmbedding = $embedding['vector'] ?? null;
        } catch (Throwable $e) {
            log_message('warning', 'Query embedding gagal: ' . $e->getMessage());
        }

        $queryTokens = $this->tokens($message);
        $desiredProgram = $this->extractProgram($message)
            ?? $this->normalizeProgram($context['program'] ?? null);

        $ranked = [];
        foreach ($rows as $row) {
            if (!empty($row['valid_from']) && $row['valid_from'] > $today) {
                continue;
            }
            if (!empty($row['valid_until']) && $row['valid_until'] < $today) {
                continue;
            }

            $scopeText = trim(
                (string) ($row['question'] ?? '') . ' ' .
                (string) ($row['keywords'] ?? '')
            );
            $text = trim($scopeText . ' ' . (string) ($row['answer'] ?? ''));
            $rowTokens = $this->tokens($text);
            $intersection = count(array_intersect($queryTokens, $rowTokens));
            $lexical = $intersection / max(1, count($queryTokens));
            $semantic = 0.0;

            if (is_array($queryEmbedding) && !empty($row['embedding_json'])) {
                $rowEmbedding = json_decode((string) $row['embedding_json'], true);
                if (is_array($rowEmbedding) && count($rowEmbedding) === count($queryEmbedding)) {
                    $semantic = $this->cosineSimilarity($queryEmbedding, $rowEmbedding);
                }
            }

            $rowPrograms = $this->extractPrograms($scopeText);
            $rowProgram = count($rowPrograms) === 1 ? $rowPrograms[0] : null;
            $programScore = 0.0;
            if ($desiredProgram !== null) {
                if ($rowPrograms !== [] && !in_array($desiredProgram, $rowPrograms, true)) {
                    continue;
                }
                if (in_array($desiredProgram, $rowPrograms, true)) {
                    $programScore = 0.35;
                }
            }

            $priority = max(1, min((int) ($row['source_priority'] ?? 50), 100)) / 1000;
            $score = ($semantic * 0.62) + ($lexical * 0.33) + $programScore + $priority;

            if ($intersection === 0 && $semantic < 0.28 && $rowProgram === null) {
                continue;
            }

            $row['_relevance'] = $score;
            $ranked[] = $row;
        }

        usort($ranked, static function (array $a, array $b): int {
            return ($b['_relevance'] ?? 0) <=> ($a['_relevance'] ?? 0);
        });

        return array_slice($ranked, 0, 12);
    }

    private function packageSnapshot(string $message, array $context): array
    {
        if (!$this->db->tableExists('packages')) {
            return [];
        }

        $program = $this->extractProgram($message)
            ?? $this->normalizeProgram($context['program'] ?? null);
        $builder = $this->db->table('packages')
            ->where('status', 'active')
            ->where('deleted_at', null)
            ->orderBy('price', 'ASC');

        if ($program !== null && $this->db->fieldExists('program', 'packages')) {
            $builder->where('program', $program);
        }

        $packages = $builder->limit($program !== null ? 20 : 12)->get()->getResultArray();
        if ($packages === []) {
            return [];
        }

        $packageIds = array_map('intval', array_column($packages, 'id'));
        $departures = [];
        if ($this->db->tableExists('package_departures')) {
            $departureRows = $this->db->table('package_departures')
                ->select('id, package_id, departure_date, return_date, quota, booked, status, (quota - booked) AS remaining_seat', false)
                ->whereIn('package_id', $packageIds)
                ->where('status', 'available')
                ->where('departure_date >=', date('Y-m-d'))
                ->orderBy('departure_date', 'ASC')
                ->get()
                ->getResultArray();

            foreach ($departureRows as $departure) {
                $packageId = (int) $departure['package_id'];
                if (count($departures[$packageId] ?? []) < 3) {
                    $departures[$packageId][] = $departure;
                }
            }
        }

        return array_map(static function (array $package) use ($departures): array {
            $packageId = (int) $package['id'];

            return [
                'id'               => $packageId,
                'program'          => $package['program'] ?? null,
                'name'             => $package['name'] ?? null,
                'badge'            => $package['badge'] ?? null,
                'description'      => mb_substr(strip_tags((string) ($package['description'] ?? '')), 0, 1200),
                'airline'          => $package['airline'] ?? null,
                'hotel_makkah'     => $package['hotel_makkah'] ?? null,
                'hotel_madinah'    => $package['hotel_madinah'] ?? null,
                'facilities'       => $package['facilities_text'] ?? null,
                'price'            => isset($package['price']) ? (float) $package['price'] : null,
                'duration_days'    => isset($package['duration_days']) ? (int) $package['duration_days'] : null,
                'duration_nights'  => isset($package['duration_nights']) ? (int) $package['duration_nights'] : null,
                'departures'       => $departures[$packageId] ?? [],
            ];
        }, $packages);
    }

    private function conversationHistory(int $sessionId): array
    {
        if (!$this->db->tableExists('chatbot_messages')) {
            return [];
        }

        $rows = $this->db->table('chatbot_messages')
            ->select('sender, message, intent, created_at')
            ->where('session_id', $sessionId)
            ->orderBy('id', 'DESC')
            ->limit(12)
            ->get()
            ->getResultArray();

        $rows = array_reverse($rows);

        return array_map(static fn(array $row): array => [
            'role'    => ($row['sender'] ?? '') === 'visitor' ? 'pengunjung' : 'chatbot',
            'message' => mb_substr((string) ($row['message'] ?? ''), 0, 1500),
            'intent'  => $row['intent'] ?? null,
        ], $rows);
    }

    private function cleanContext(array $context): array
    {
        $allowed = [
            'topic',
            'focus',
            'program',
            'package_id',
            'package_name',
            'month_label',
            'year',
            'airline',
            'room_type',
            'participants',
            'last_question',
            'last_answer',
        ];

        return array_intersect_key($context, array_flip($allowed));
    }

    private function tokens(string $text): array
    {
        $text = mb_strtolower(strip_tags($text));
        $text = str_replace(['umrah', 'turkiye', 'ramadan'], ['umroh', 'turki', 'ramadhan'], $text);
        $text = preg_replace('/\b(ga|gak|nggak|enggak)\b/u', 'tidak', $text);
        $text = preg_replace('/\b(dapet|dpt)\b/u', 'dapat', (string) $text);
        $text = preg_replace('/\b(pake|pakek)\b/u', 'pakai', (string) $text);
        $text = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', (string) $text);
        $parts = preg_split('/\s+/u', trim((string) $text)) ?: [];
        $stopWords = [
            'yang', 'dan', 'atau', 'untuk', 'dari', 'dengan', 'saya', 'mau',
            'ingin', 'apa', 'berapa', 'bisa', 'ini', 'itu', 'nya', 'aja',
        ];

        $siteName = mb_strtolower(site_setting('site_name', ''));
        $siteName = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $siteName);
        $siteNameTokens = preg_split('/\s+/u', trim((string) $siteName)) ?: [];
        $stopWords = array_values(array_unique(array_merge(
            $stopWords,
            array_filter($siteNameTokens, static fn(string $token): bool => mb_strlen($token) >= 3)
        )));

        return array_values(array_unique(array_filter(
            $parts,
            static fn(string $token): bool =>
                mb_strlen($token) >= 3 && !in_array($token, $stopWords, true)
        )));
    }

    private function cosineSimilarity(array $a, array $b): float
    {
        $dot = 0.0;
        $normA = 0.0;
        $normB = 0.0;

        foreach ($a as $index => $value) {
            $left = (float) $value;
            $right = (float) ($b[$index] ?? 0);
            $dot += $left * $right;
            $normA += $left * $left;
            $normB += $right * $right;
        }

        if ($normA <= 0 || $normB <= 0) {
            return 0.0;
        }

        return $dot / (sqrt($normA) * sqrt($normB));
    }

    private function extractProgram(string $text): ?string
    {
        $matches = $this->extractPrograms($text);

        return count($matches) === 1 ? $matches[0] : null;
    }

    private function extractPrograms(string $text): array
    {
        $text = mb_strtolower($text);
        $matches = [];
        foreach (array_keys(PackageModel::getProgramOptions()) as $program) {
            if (preg_match('/\b' . preg_quote($program, '/') . '\b/u', $text)) {
                $matches[] = $program;
            }
        }

        return $matches;
    }

    private function normalizeProgram($value): ?string
    {
        $program = mb_strtolower(trim((string) $value));

        return in_array($program, array_keys(PackageModel::getProgramOptions()), true)
            ? $program
            : null;
    }
}
