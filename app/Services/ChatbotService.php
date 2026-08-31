<?php

namespace App\Services;

use App\Models\ChatbotKnowledgeModel;
use App\Models\ChatbotMessageModel;
use App\Models\ChatbotSessionModel;
use App\Models\ChatbotUnansweredModel;
use App\Models\PackageModel;
use App\Models\SiteSettingModel;

class ChatbotService
{
    private $db;
    private ChatbotSessionModel $sessionModel;
    private ChatbotMessageModel $messageModel;
    private ChatbotUnansweredModel $unansweredModel;
    private ChatbotKnowledgeModel $knowledgeModel;
    private ChatbotAIService $aiService;
    private array $settings = [];

    public function __construct()
    {
        $this->db              = \Config\Database::connect();
        $this->sessionModel    = new ChatbotSessionModel();
        $this->messageModel    = new ChatbotMessageModel();
        $this->unansweredModel = new ChatbotUnansweredModel();
        $this->knowledgeModel  = new ChatbotKnowledgeModel();
        $this->aiService       = new ChatbotAIService();
        $this->settings        = (new SiteSettingModel())->getAllSettings();
    }

    public function processMessage(
        string $message,
        ?string $sessionToken,
        ?string $sourcePage,
        ?string $ipAddress,
        ?string $userAgent
    ): array {
        $message = trim(strip_tags($message));
        $message = mb_substr($message, 0, 500);

        $chatSession = $this->getOrCreateSession(
            $sessionToken,
            $sourcePage,
            $ipAddress,
            $userAgent
        );

        $normalized = $this->normalize($message);
        $context = $this->decodeConversationContext(
            $chatSession['conversation_context_json'] ?? null,
            $chatSession['context_updated_at'] ?? null
        );
        $analysis = $this->analyzeMessage($normalized, $context);

        $this->saveMessage(
            (int) $chatSession['id'],
            'visitor',
            $message,
            null,
            null
        );

        $result = $this->buildResponse($message, $normalized, $context, $analysis);
        $aiResult = $this->aiService->enhance(
            $message,
            (int) $chatSession['id'],
            $context,
            $result
        );
        if ($aiResult !== null) {
            $result = $aiResult;
            $aiContext = is_array($result['_ai_context'] ?? null)
                ? $result['_ai_context']
                : [];

            foreach (['topic', 'focus', 'program', 'room_type', 'participants'] as $entity) {
                if (($aiContext[$entity] ?? null) !== null) {
                    $analysis[$entity] = $aiContext[$entity];
                }
            }

            if (!empty($analysis['program'])) {
                $analysis['program_changed'] =
                    $analysis['program'] !== ($context['program'] ?? null);
            }
        } else {
            $result['reply'] = $this->applyNaturalContextTone(
                (string) $result['reply'],
                $context,
                $analysis,
                (string) ($result['intent'] ?? '')
            );
        }
        $nextContext = $this->evolveConversationContext(
            $context,
            $analysis,
            $result,
            $normalized
        );

        $this->saveMessage(
            (int) $chatSession['id'],
            'bot',
            $result['reply'],
            $result['intent'],
            [
                'packages'      => $result['packages'] ?? [],
                'quick_replies' => $result['quick_replies'] ?? [],
                'smart_context' => $this->publicContextSummary($nextContext),
                'used_context'  => (bool) ($analysis['is_follow_up'] ?? false),
                'ai_powered'    => (bool) ($result['_ai_powered'] ?? false),
                'ai_confidence' => $result['_ai_confidence'] ?? null,
                'ai_source_ids' => $result['_ai_source_ids'] ?? [],
            ]
        );

        $sessionUpdate = [
            'source_page'     => $sourcePage ?: ($chatSession['source_page'] ?? null),
            'last_intent'     => $result['intent'],
            'last_message_at' => date('Y-m-d H:i:s'),
        ];

        if ($this->db->fieldExists('conversation_context_json', 'chatbot_sessions')) {
            $sessionUpdate['conversation_context_json'] = json_encode($nextContext, JSON_UNESCAPED_UNICODE);
        }
        if ($this->db->fieldExists('context_updated_at', 'chatbot_sessions')) {
            $sessionUpdate['context_updated_at'] = date('Y-m-d H:i:s');
        }

        $this->sessionModel->update($chatSession['id'], $sessionUpdate);

        if (!empty($result['unanswered'])) {
            $this->storeUnanswered((int) $chatSession['id'], $message, $normalized);
        }

        $result['session_token'] = $chatSession['session_token'];
        $result['contextual'] = (bool) ($analysis['is_follow_up'] ?? false);
        unset(
            $result['_knowledge_id'],
            $result['_knowledge_question'],
            $result['_context_topic'],
            $result['_context_focus'],
            $result['_ai_powered'],
            $result['_ai_confidence'],
            $result['_ai_source_ids'],
            $result['_ai_context']
        );

        return $result;
    }

    public function attachLeadIdentity(int $sessionId, string $name, string $phone): void
    {
        $this->sessionModel->update($sessionId, [
            'visitor_name'  => $name,
            'visitor_phone' => $phone,
        ]);
    }

    public function findSessionByToken(string $token): ?array
    {
        return $this->sessionModel
            ->where('session_token', $token)
            ->first();
    }

    public function saveSystemMessage(int $sessionId, string $message, string $intent, array $metadata = []): void
    {
        $this->saveMessage($sessionId, 'bot', $message, $intent, $metadata);
        $this->sessionModel->update($sessionId, [
            'last_intent'     => $intent,
            'last_message_at' => date('Y-m-d H:i:s'),
        ]);
    }

    private function buildResponse(
        string $original,
        string $normalized,
        array $context,
        array $analysis
    ): array
    {
        $defaultQuickReplies = $this->defaultQuickReplies();

        if ($normalized === '') {
            return [
                'intent'        => 'empty',
                'reply'         => 'Silakan tuliskan pertanyaan Anda mengenai paket, jadwal, seat, harga, atau informasi ' . $this->siteName() . '.',
                'packages'      => [],
                'quick_replies' => $defaultQuickReplies,
                'unanswered'    => false,
            ];
        }

        if (!empty($analysis['reset_context'])) {
            return [
                'intent'         => 'context_reset',
                'reply'          => 'Baik, konteks percakapan sebelumnya sudah saya hapus. Silakan mulai dengan pertanyaan baru.',
                'packages'       => [],
                'quick_replies'  => $defaultQuickReplies,
                'unanswered'     => false,
                '_context_topic' => null,
                '_context_focus' => null,
            ];
        }

        if ($this->isGreeting($normalized)) {
            return [
                'intent'        => 'greeting',
                'reply'         => $this->settings['chatbot_welcome_message']
                    ?? 'Assalamualaikum. Saya Asisten ' . $this->siteName() . '. Ada yang dapat saya bantu?',
                'packages'      => [],
                'quick_replies' => $defaultQuickReplies,
                'unanswered'    => false,
            ];
        }

        if ($this->containsAny($normalized, ['terima kasih', 'makasih', 'thanks', 'cukup'])) {
            return [
                'intent'        => 'thanks',
                'reply'         => 'Sama-sama. Semoga informasi yang saya berikan membantu. Silakan tanyakan kembali bila masih ada yang ingin diketahui.',
                'packages'      => [],
                'quick_replies' => $defaultQuickReplies,
                'unanswered'    => false,
            ];
        }

        if ($this->isRoomTypeExplanationQuestion($normalized)) {
            return [
                'intent'         => 'room_type_explanation',
                'reply'          => 'Double berarti 2 jamaah dalam 1 kamar, Triple 3 jamaah, Quad 4 jamaah, dan Quint 5 jamaah. Tipe kamar menunjukkan jumlah jamaah yang menempati kamar; susunan tempat tidur mengikuti kebijakan hotel.',
                'packages'       => [],
                'quick_replies'  => [
                    ['label' => 'Lihat Harga', 'message' => 'Berapa harga paketnya?'],
                    ['label' => 'Tanya Hotel', 'message' => 'Hotel yang digunakan apa?'],
                    ['label' => 'Hubungi Admin', 'message' => 'Hubungi admin'],
                ],
                'unanswered'     => false,
                '_context_topic' => 'package',
                '_context_focus' => 'room_type_explanation',
            ];
        }

        // Permintaan untuk berbicara dengan admin adalah perpindahan topik yang
        // tegas. Jangan biarkan konteks paket/harga sebelumnya mengambil alih.
        if ($this->containsAny($normalized, [
            'hubungi admin',
            'kontak admin',
            'bicara dengan admin',
            'chat admin',
            'sambungkan admin',
            'sambungkan ke admin',
        ])) {
            return $this->contactResponse($defaultQuickReplies);
        }

        // Jawaban manual admin diprioritaskan setelah salam agar informasi resmi
        // yang sudah disiapkan dapat menjawab pertanyaan pengunjung secara proaktif.
        $desiredProgram = $analysis['program']
            ?? (!empty($analysis['is_follow_up']) ? ($context['program'] ?? null) : null);
        $knowledge = $this->findKnowledgeMatch(
            $normalized,
            false,
            $analysis['focus'] ?? null,
            $desiredProgram
        );
        $effectiveNormalized = $normalized;

        if ($knowledge === null && !empty($analysis['is_follow_up'])) {
            $effectiveNormalized = $this->buildContextualQuery($normalized, $context, $analysis);
            $contextProgram = !empty($analysis['clear_program'])
                ? null
                : ($analysis['program'] ?? $context['program'] ?? null);
            $knowledge = $this->findKnowledgeMatch(
                $effectiveNormalized,
                true,
                $analysis['focus'] ?? $context['focus'] ?? null,
                $contextProgram
            );
        }

        if ($knowledge !== null) {
            $knowledgeTopic = $this->detectExplicitTopic(
                $this->normalize((string) ($knowledge['question'] ?? ''))
            ) ?? ($analysis['topic'] ?? 'knowledge');

            return [
                'intent'              => 'custom_knowledge',
                'reply'               => $knowledge['answer'],
                'packages'            => [],
                'quick_replies'       => $this->contextualQuickReplies($knowledgeTopic, $context, $analysis),
                'unanswered'          => false,
                '_knowledge_id'       => $knowledge['id'],
                '_knowledge_question' => $knowledge['question'],
                '_context_topic'      => $knowledgeTopic,
                '_context_focus'      => $analysis['focus'] ?? $this->detectFocus($this->normalize((string) $knowledge['question'])),
            ];
        }

        $officialResponse = $this->officialContextResponse(
            $normalized,
            $effectiveNormalized,
            $context,
            $analysis
        );
        if ($officialResponse !== null) {
            return $officialResponse;
        }

        if ($this->containsAny($normalized, ['alamat', 'lokasi', 'kantor dimana', 'kantor di mana', 'maps', 'map'])) {
            $address = trim((string) ($this->settings['site_address'] ?? ''));
            $reply = $address !== ''
                ? 'Kantor ' . $this->siteName() . ' berada di ' . $address . '.'
                : 'Mohon maaf, alamat kantor belum tersedia pada website.';

            return [
                'intent'        => 'office_address',
                'reply'         => $reply,
                'packages'      => [],
                'quick_replies' => [
                    ['label' => 'Jam Operasional', 'message' => 'Kantor buka jam berapa?'],
                    ['label' => 'Cari Paket', 'message' => 'Paket terdekat kapan?'],
                ],
                'handoff_url'   => $this->mapsUrl($address),
                'handoff_label' => $address !== '' ? 'Buka Google Maps' : null,
                'unanswered'    => $address === '',
            ];
        }

        if ($this->containsAny($normalized, ['jam buka', 'jam operasional', 'kantor buka', 'hari buka', 'operasional'])) {
            $hours = trim((string) ($this->settings['business_hours'] ?? ''));

            return [
                'intent'        => 'business_hours',
                'reply'         => $hours !== ''
                    ? 'Jam operasional ' . $this->siteName() . ': ' . $hours . '.'
                    : 'Mohon maaf, jam operasional belum tersedia pada website.',
                'packages'      => [],
                'quick_replies' => $defaultQuickReplies,
                'unanswered'    => $hours === '',
            ];
        }

        if ($this->containsAny($normalized, [
            'nomor whatsapp',
            'whatsapp admin',
            'nomor admin',
            'kontak',
            'telepon',
            'no wa',
            'wa admin',
            'hubungi admin',
            'kontak admin',
        ])) {
            return $this->contactResponse($defaultQuickReplies);
        }

        if ($this->containsAny($effectiveNormalized, ['syarat', 'dokumen', 'paspor', 'passport', 'ktp', 'kartu keluarga', 'vaksin'])
            || $this->containsWholeWord($effectiveNormalized, 'kk')) {
            $faqResult = $this->findFaqAnswer($effectiveNormalized, ['syarat', 'dokumen', 'paspor', 'passport', 'ktp', 'kk', 'kartu keluarga', 'vaksin']);

            if ($faqResult !== null) {
                return [
                    'intent'        => 'documents',
                    'reply'         => $faqResult,
                    'packages'      => [],
                    'quick_replies' => $defaultQuickReplies,
                    'unanswered'    => false,
                ];
            }

            return $this->fallbackResponse($original, 'documents');
        }

        if ($this->isPackageQuestion($effectiveNormalized)) {
            $packageResponse = $this->answerPackageQuestion($effectiveNormalized);
            $packageResponse['_context_topic'] = 'package';
            $packageResponse['_context_focus'] = $analysis['focus'] ?? $context['focus'] ?? null;

            return $packageResponse;
        }

        $faq = $this->findFaqAnswer($normalized);
        if ($faq === null && $effectiveNormalized !== $normalized) {
            $faq = $this->findFaqAnswer($effectiveNormalized);
        }

        if ($faq !== null) {
            return [
                'intent'        => 'faq',
                'reply'         => $faq,
                'packages'      => [],
                'quick_replies' => $defaultQuickReplies,
                'unanswered'    => false,
                '_context_topic'=> $analysis['topic'] ?? 'faq',
                '_context_focus'=> $analysis['focus'] ?? null,
            ];
        }

        if (!empty($analysis['is_follow_up'])) {
            return $this->contextClarificationResponse($context, $analysis);
        }

        return $this->fallbackResponse($original, 'out_of_scope');
    }

    private function officialContextResponse(
        string $normalized,
        string $effectiveNormalized,
        array $context,
        array $analysis
    ): ?array {
        $focus = $analysis['focus'] ?? $context['focus'] ?? null;
        $topic = $analysis['topic'] ?? $context['topic'] ?? null;

        // Operational policies must come from this client's knowledge base/FAQ,
        // not from vendor-specific constants embedded in source code.
        if (in_array($focus, ['document_deadline', 'passport_requirements'], true)) {
            return null;
        }

        if ($focus === 'payment_method' || $focus === 'deposit' || $topic === 'payment') {
            if ($this->containsAny($effectiveNormalized, ['transfer', 'rekening', 'cara bayar', 'metode pembayaran'])) {
                return [
                    'intent'         => 'payment_transfer',
                    'reply'          => $this->paymentTransferReply(),
                    'packages'       => [],
                    'quick_replies'  => [
                        ['label' => 'Hubungi Admin', 'message' => 'Hubungi admin'],
                        ['label' => 'Lihat Paket', 'message' => 'Paket apa saja yang tersedia?'],
                    ],
                    'unanswered'     => false,
                    '_context_topic' => 'payment',
                    '_context_focus' => 'payment_method',
                ];
            }

            return [
                'intent'         => 'payment_policy_unavailable',
                'reply'          => 'Ketentuan pembayaran tersebut belum tersedia pada knowledge base instance ini. Silakan konfirmasi kepada admin agar informasi yang diberikan sesuai kebijakan travel saat ini.',
                'packages'       => [],
                'quick_replies'  => [
                    ['label' => 'Hubungi Admin', 'message' => 'Hubungi admin'],
                    ['label' => 'Info Transfer', 'message' => 'Bagaimana cara transfer?'],
                ],
                'unanswered'     => true,
                '_context_topic' => 'payment',
                '_context_focus' => $focus ?: 'payment_method',
            ];
        }

        if ($focus === 'equipment') {
            return [
                'intent'         => 'equipment_policy_unavailable',
                'reply'          => 'Detail perlengkapan belum tersedia pada knowledge base instance ini. Silakan cek detail paket yang dipilih atau hubungi admin untuk informasi resmi terbaru.',
                'packages'       => [],
                'quick_replies'  => [
                    ['label' => 'Lihat Paket', 'message' => 'Paket apa saja yang tersedia?'],
                    ['label' => 'Hubungi Admin', 'message' => 'Hubungi admin'],
                ],
                'unanswered'     => true,
                '_context_topic' => 'package',
                '_context_focus' => 'equipment',
            ];
        }

        return null;
    }

    private function answerPackageQuestion(string $normalized): array
    {
        $participants = $this->extractParticipants($normalized);
        $month        = $this->extractMonth($normalized);
        $program      = $this->extractProgram($normalized);
        $searchTerm   = $this->extractPackageKeyword($normalized);
        $requestedPackagePhrase = $this->extractRequestedPackagePhrase($normalized);
        $wantsNearest = $this->containsAny($normalized, ['terdekat', 'paling dekat', 'segera', 'kapan berangkat', 'jadwal terdekat']);
        $wantsCheapest = $this->containsAny($normalized, ['termurah', 'paling murah', 'murah', 'harga terendah']);
        $wantsSeat = $participants > 1 || $this->containsAny($normalized, ['seat', 'kursi', 'kuota', 'rombongan', 'orang', 'jamaah', 'jemaah']);
        $wantsFacility = $this->containsAny($normalized, ['fasilitas', 'include', 'termasuk apa', 'dapat apa']);
        $wantsHotel = $this->containsAny($normalized, ['hotel', 'menginap', 'akomodasi']);
        $wantsAirline = $this->containsAny($normalized, ['maskapai', 'airline', 'penerbangan pakai', 'pesawat apa']);
        $wantsDuration = $this->containsAny($normalized, ['berapa hari', 'durasi', 'berapa malam']);
        $wantsProgramList = str_contains($normalized, 'program')
            && $this->containsAny($normalized, ['apa saja', 'daftar', 'pilihan', 'tersedia']);

        $packages = $this->activePackages();

        if ($wantsProgramList && $program === null) {
            $availablePrograms = [];
            foreach ($packages as $package) {
                $programKey = mb_strtolower(trim((string) ($package['program'] ?? '')));
                if ($programKey === '') {
                    continue;
                }
                $availablePrograms[$programKey] = PackageModel::getProgramLabel($programKey);
            }

            $programLabels = array_values($availablePrograms);
            $quickReplies = [];
            foreach (array_slice($availablePrograms, 0, 2, true) as $programKey => $programLabel) {
                $quickReplies[] = [
                    'label' => 'Program ' . $programLabel,
                    'message' => 'Tampilkan paket program ' . $programLabel,
                ];
            }
            if ($quickReplies === []) {
                $quickReplies = $this->defaultQuickReplies();
            }

            return [
                'intent'        => 'program_catalog',
                'reply'         => $programLabels !== []
                    ? 'Program yang tersedia saat ini: ' . implode(', ', $programLabels) . '. Detail harga dan jadwal mengikuti paket aktif yang terdaftar.'
                    : 'Belum ada kategori program aktif yang terdaftar saat ini. Silakan lihat paket aktif atau hubungi admin.',
                'packages'      => $this->packageCards(array_slice($packages, 0, 3), [], $participants),
                'quick_replies' => $quickReplies,
                'unanswered'    => $programLabels === [],
            ];
        }

        if ($program !== null) {
            $packages = array_values(array_filter($packages, function (array $package) use ($program): bool {
                return $this->normalize((string) ($package['program'] ?? '')) === $program;
            }));
        }

        // Nama paket bebas dicocokkan ke data paket aktif, bukan ke daftar vendor tertentu.
        $namedPackages = $this->matchNamedPackages($packages, $normalized);
        $hasNamedPackage = !empty($namedPackages);
        if ($hasNamedPackage) {
            $packages = $namedPackages;
        } elseif ($program === null && $searchTerm !== null) {
            $packages = array_values(array_filter($packages, function (array $package) use ($searchTerm): bool {
                $haystack = $this->normalize(
                    ($package['name'] ?? '') . ' ' .
                    ($package['badge'] ?? '') . ' ' .
                    ($package['program'] ?? '')
                );

                return str_contains($haystack, $searchTerm);
            }));
        }

        if ($requestedPackagePhrase !== null && !$hasNamedPackage) {
            return [
                'intent'        => 'package_unavailable',
                'reply'         => 'Mohon maaf, paket ' . $requestedPackagePhrase . ' belum ditemukan pada katalog aktif saat ini. Silakan konfirmasi jadwal terbarunya kepada admin.',
                'packages'      => $this->packageCards(array_slice($this->activePackages(), 0, 3), [], $participants),
                'quick_replies' => [
                    ['label' => 'Hubungi Admin', 'message' => 'Hubungi admin'],
                    ['label' => 'Lihat Paket Aktif', 'message' => 'Paket apa saja yang tersedia?'],
                ],
                'unanswered'    => true,
            ];
        }

        if (empty($packages)) {
            $label = $program !== null
                ? 'Program ' . ucwords($program)
                : ($searchTerm !== null ? ucwords($searchTerm) : 'yang Anda cari');

            return [
                'intent'        => 'package_unavailable',
                'reply'         => 'Mohon maaf, paket untuk ' . $label . ' belum tersedia saat ini. Silakan lihat paket aktif yang tersedia atau tanyakan jadwal lainnya.',
                'packages'      => $this->packageCards(array_slice($this->activePackages(), 0, 3), [], $participants),
                'quick_replies' => $this->defaultQuickReplies(),
                'unanswered'    => false,
            ];
        }

        $subject = $this->packageSelectionLabel($packages, $program, $hasNamedPackage);
        $wantsSpecificDetail = $wantsFacility || $wantsHotel || $wantsAirline;
        $hasSpecificSelection = $program !== null
            || $hasNamedPackage
            || count($packages) === 1;

        if ($wantsSpecificDetail && !$hasSpecificSelection) {
            return [
                'intent'         => 'package_detail_clarification',
                'reply'          => 'Agar informasinya tepat, fasilitas, hotel, dan maskapai perlu dicek berdasarkan paket atau program. Silakan sebutkan nama paket atau program yang dimaksud.',
                'packages'       => [],
                'quick_replies'  => $this->defaultQuickReplies(),
                'unanswered'     => false,
                '_context_topic' => 'package',
                '_context_focus' => $wantsAirline ? 'airline' : ($wantsHotel ? 'hotel' : 'facilities'),
            ];
        }

        $detailDepartures = $wantsSpecificDetail
            ? $this->availableDepartures(array_column($packages, 'id'))
            : [];

        if ($wantsFacility) {
            $textDetails = [];
            foreach ($packages as $package) {
                $facilityText = trim((string) ($package['facilities_text'] ?? ''));
                if ($facilityText === '') {
                    continue;
                }
                $textDetails[] = (string) ($package['name'] ?? 'Paket') . ': ' . $facilityText;
            }

            if ($textDetails !== []) {
                return [
                    'intent'        => 'package_facilities',
                    'reply'         => 'Fasilitas untuk ' . $subject . ': ' . implode('; ', $textDetails) . '.',
                    'packages'      => $this->packageCards($packages, $detailDepartures, $participants),
                    'quick_replies' => $this->defaultQuickReplies(),
                    'unanswered'    => false,
                ];
            }

            $facilities = [];
            if ($this->db->tableExists('package_facilities')) {
                $facilities = $this->db->table('package_facilities')
                    ->select('package_id, facility_name')
                    ->whereIn('package_id', array_column($packages, 'id'))
                    ->where('type', 'included')
                    ->orderBy('package_id', 'ASC')
                    ->orderBy('id', 'ASC')
                    ->get()
                    ->getResultArray();
            }

            if (!empty($facilities)) {
                $packageNames = [];
                foreach ($packages as $package) {
                    $packageNames[(int) $package['id']] = (string) ($package['name'] ?? 'Paket');
                }

                $grouped = [];
                foreach ($facilities as $facility) {
                    $packageId = (int) ($facility['package_id'] ?? 0);
                    $grouped[$packageId][] = (string) ($facility['facility_name'] ?? '');
                }

                $details = [];
                foreach ($grouped as $packageId => $names) {
                    $names = array_values(array_filter(array_unique($names)));
                    if ($names === []) {
                        continue;
                    }
                    $details[] = ($packageNames[$packageId] ?? 'Paket') . ': ' . implode(', ', $names);
                }

                return [
                    'intent'        => 'package_facilities',
                    'reply'         => 'Fasilitas yang tercatat untuk ' . $subject . ' meliputi ' . implode('; ', $details) . '.',
                    'packages'      => $this->packageCards($packages, $detailDepartures, $participants),
                    'quick_replies' => $this->defaultQuickReplies(),
                    'unanswered'    => false,
                ];
            }

            return [
                'intent'        => 'package_facilities_unavailable',
                'reply'         => 'Rincian fasilitas untuk ' . $subject . ' belum tercatat pada data paket. Silakan hubungi admin agar informasi fasilitas yang diberikan sesuai paket terbaru.',
                'packages'      => $this->packageCards($packages, $detailDepartures, $participants),
                'quick_replies' => [
                    ['label' => 'Hubungi Admin', 'message' => 'Hubungi admin'],
                    ['label' => 'Lihat Jadwal', 'message' => 'Kapan jadwal keberangkatannya?'],
                ],
                'unanswered'    => true,
            ];
        }

        if ($wantsHotel) {
            $hotelDetails = [];
            foreach ($packages as $package) {
                $locations = [];
                $makkah = trim((string) ($package['hotel_makkah'] ?? ''));
                $madinah = trim((string) ($package['hotel_madinah'] ?? ''));
                if ($makkah !== '') {
                    $locations[] = 'Makkah: ' . $makkah;
                }
                if ($madinah !== '') {
                    $locations[] = 'Madinah: ' . $madinah;
                }
                if ($locations !== []) {
                    $hotelDetails[] = (string) ($package['name'] ?? 'Paket') . ' - ' . implode(', ', $locations);
                }
            }

            if ($hotelDetails !== []) {
                return [
                    'intent'        => 'package_hotels',
                    'reply'         => 'Hotel untuk ' . $subject . ': ' . implode('; ', $hotelDetails) . '. Hotel dapat berubah ke standar setaraf mengikuti ketersediaan dan kondisi operasional.',
                    'packages'      => $this->packageCards($packages, $detailDepartures, $participants),
                    'quick_replies' => [
                        ['label' => 'Lihat Fasilitas', 'message' => 'Fasilitas yang didapat apa saja?'],
                        ['label' => 'Cek Maskapai', 'message' => 'Maskapai yang dipakai apa?'],
                    ],
                    'unanswered'    => false,
                ];
            }

            return [
                'intent'        => 'package_hotel_unavailable',
                'reply'         => 'Rincian hotel untuk ' . $subject . ' belum tercatat pada data paket. Agar nama hotel tidak tertukar, silakan konfirmasi kepada admin.',
                'packages'      => $this->packageCards($packages, $detailDepartures, $participants),
                'quick_replies' => [
                    ['label' => 'Hubungi Admin', 'message' => 'Hubungi admin'],
                    ['label' => 'Lihat Fasilitas', 'message' => 'Fasilitas yang didapat apa saja?'],
                ],
                'unanswered'    => true,
            ];
        }

        if ($wantsAirline) {
            $airlineDetails = [];
            foreach ($packages as $package) {
                $airline = trim((string) ($package['airline'] ?? ''));
                if ($airline === '') {
                    continue;
                }
                $airlineDetails[] = (string) ($package['name'] ?? 'Paket') . ': ' . $airline;
            }

            if ($airlineDetails !== []) {
                return [
                    'intent'        => 'package_airline',
                    'reply'         => 'Maskapai untuk ' . $subject . ': ' . implode('; ', $airlineDetails) . '. Jadwal dan maskapai dapat mengalami penyesuaian mengikuti kebijakan penerbangan dan sistem group booking.',
                    'packages'      => $this->packageCards($packages, $detailDepartures, $participants),
                    'quick_replies' => [
                        ['label' => 'Cek Jadwal', 'message' => 'Kapan jadwal keberangkatannya?'],
                        ['label' => 'Tanya Hotel', 'message' => 'Hotel yang digunakan apa?'],
                    ],
                    'unanswered'    => false,
                ];
            }

            return [
                'intent'        => 'package_airline_unavailable',
                'reply'         => 'Maskapai untuk ' . $subject . ' belum tercatat pada data paket. Agar informasinya tidak keliru, silakan konfirmasi kepada admin.',
                'packages'      => $this->packageCards($packages, $detailDepartures, $participants),
                'quick_replies' => [
                    ['label' => 'Hubungi Admin', 'message' => 'Hubungi admin'],
                    ['label' => 'Cek Jadwal', 'message' => 'Kapan jadwal keberangkatannya?'],
                ],
                'unanswered'    => true,
            ];
        }

        if ($wantsDuration && count($packages) === 1) {
            $package = $packages[0];
            $days = (int) ($package['duration_days'] ?? 0);
            $nights = (int) ($package['duration_nights'] ?? 0);

            $duration = $days > 0 ? $days . ' hari' : 'durasi belum tersedia';
            if ($nights > 0) {
                $duration .= ' dan ' . $nights . ' malam';
            }

            return [
                'intent'        => 'package_duration',
                'reply'         => 'Durasi ' . $package['name'] . ' adalah ' . $duration . '.',
                'packages'      => $this->packageCards([$package], [], $participants),
                'quick_replies' => $this->defaultQuickReplies(),
                'unanswered'    => false,
            ];
        }

        $departures = $this->availableDepartures(array_column($packages, 'id'));

        if ($month !== null) {
            $departures = array_values(array_filter($departures, static function (array $departure) use ($month): bool {
                return (int) date('n', strtotime($departure['departure_date'])) === $month['number']
                    && (int) date('Y', strtotime($departure['departure_date'])) === $month['year'];
            }));
        }

        if ($wantsSeat) {
            $departures = array_values(array_filter($departures, static function (array $departure) use ($participants): bool {
                return (int) $departure['remaining_seat'] >= $participants;
            }));
        }

        if ($wantsCheapest) {
            usort($packages, static function (array $a, array $b): int {
                return ((float) ($a['price'] ?? 0)) <=> ((float) ($b['price'] ?? 0));
            });
        }

        if ($wantsNearest || $month !== null || $wantsSeat) {
            if (empty($departures)) {
                $availabilitySubject = $month !== null
                    ? 'bulan ' . $month['label'] . ' ' . $month['year']
                    : 'untuk ' . $participants . ' jamaah';

                return [
                    'intent'        => 'departure_unavailable',
                    'reply'         => 'Mohon maaf, belum ada jadwal keberangkatan ' . $availabilitySubject . ' dengan seat yang tersedia pada data website saat ini.',
                    'packages'      => $this->packageCards(array_slice($packages, 0, 3), [], $participants),
                    'quick_replies' => [
                        ['label' => 'Lihat Paket Aktif', 'message' => 'Paket apa saja yang tersedia?'],
                        ['label' => 'Hubungi Admin', 'message' => 'Nomor WhatsApp admin berapa?'],
                    ],
                    'unanswered'    => false,
                ];
            }

            usort($departures, static fn(array $a, array $b): int => strcmp($a['departure_date'], $b['departure_date']));
            $departures = array_slice($departures, 0, 3);
            $packageMap = [];
            foreach ($packages as $package) {
                $packageMap[(int) $package['id']] = $package;
            }

            $selectedPackages = [];
            foreach ($departures as $departure) {
                $packageId = (int) $departure['package_id'];
                if (isset($packageMap[$packageId])) {
                    $package = $packageMap[$packageId];
                    $package['_departure'] = $departure;
                    $selectedPackages[] = $package;
                }
            }

            $reply = $participants > 1
                ? 'Berikut rekomendasi jadwal yang masih memiliki minimal ' . $participants . ' seat.'
                : 'Berikut jadwal keberangkatan terdekat yang tersedia.';

            if ($month !== null) {
                $reply = $hasSpecificSelection
                    ? 'Berikut jadwal ' . $subject . ' untuk bulan ' . $month['label'] . ' ' . $month['year'] . '.'
                    : 'Berikut jadwal yang tersedia untuk bulan ' . $month['label'] . ' ' . $month['year'] . '.';
            } elseif ($hasSpecificSelection && !$wantsSeat) {
                $reply = 'Berikut jadwal keberangkatan terdekat untuk ' . $subject . '.';
            }

            return [
                'intent'        => 'package_recommendation',
                'reply'         => $reply,
                'packages'      => $this->packageCards($selectedPackages, $departures, $participants),
                'quick_replies' => [
                    ['label' => 'Paket Termurah', 'message' => 'Paket paling murah apa?'],
                    ['label' => 'Syarat Dokumen', 'message' => 'Dokumen apa saja yang diperlukan?'],
                ],
                'unanswered'    => false,
            ];
        }

        $packages = array_slice($packages, 0, 3);
        if ($wantsCheapest) {
            $reply = 'Berikut paket aktif dengan harga paling rendah berdasarkan data website.';
        } elseif ($program !== null) {
            $reply = 'Berikut paket aktif dalam Program ' . ucwords($program) . '.';
        } elseif ($hasNamedPackage && count($packages) === 1) {
            $reply = 'Berikut informasi paket ' . ($packages[0]['name'] ?? 'yang Anda cari') . '.';
        } elseif ($hasNamedPackage) {
            $reply = 'Berikut pilihan paket yang paling sesuai dengan nama yang Anda sebutkan.';
        } else {
            $reply = 'Berikut paket aktif yang tersedia di ' . $this->siteName() . '.';
        }

        return [
            'intent'        => 'package_list',
            'reply'         => $reply,
            'packages'      => $this->packageCards($packages, $departures, $participants),
            'quick_replies' => [
                ['label' => 'Jadwal Terdekat', 'message' => 'Paket terdekat kapan?'],
                ['label' => 'Cek 10 Seat', 'message' => 'Ada paket untuk 10 orang?'],
            ],
            'unanswered'    => false,
        ];
    }

    private function matchNamedPackages(array $packages, string $normalized): array
    {
        $queryTokens = $this->packageNameTokens($normalized);
        if ($queryTokens === []) {
            return [];
        }

        $bestScore = 0;
        $matches = [];

        foreach ($packages as $package) {
            $name = $this->normalize(
                (string) ($package['name'] ?? '') . ' ' .
                (string) ($package['slug'] ?? '') . ' ' .
                (string) ($package['badge'] ?? '')
            );
            $nameTokens = $this->packageNameTokens($name);
            if ($nameTokens === []) {
                continue;
            }

            $intersection = count(array_intersect($queryTokens, $nameTokens));
            $minimum = count($nameTokens) >= 2 ? 2 : 1;
            if ($intersection < $minimum) {
                continue;
            }

            $score = ($intersection * 10)
                + (int) round(($intersection / max(1, count($nameTokens))) * 5);

            if ($score > $bestScore) {
                $bestScore = $score;
                $matches = [$package];
            } elseif ($score === $bestScore) {
                $matches[] = $package;
            }
        }

        return $matches;
    }

    private function packageNameTokens(string $value): array
    {
        $generic = [
            'paket', 'program', 'umroh', 'umrah', 'haji', 'reguler', 'wisata',
            'info', 'informasi', 'mau', 'ingin', 'dengan', 'untuk',
        ];

        return array_values(array_filter(
            $this->tokens($this->normalize($value)),
            static fn(string $token): bool => !in_array($token, $generic, true)
        ));
    }

    private function extractRequestedPackagePhrase(string $normalized): ?string
    {
        // Tidak ada nama paket vendor yang di-hardcode pada master white-label.
        // Nama paket aktif tetap dicocokkan oleh matchNamedPackages().
        return null;
    }

    private function packageSelectionLabel(array $packages, ?string $program, bool $hasNamedPackage): string
    {
        if ($hasNamedPackage && count($packages) === 1) {
            return 'paket ' . (string) ($packages[0]['name'] ?? 'yang dipilih');
        }

        if ($program !== null) {
            return 'Program ' . ucwords($program);
        }

        if (count($packages) === 1) {
            return 'paket ' . (string) ($packages[0]['name'] ?? 'yang dipilih');
        }

        return 'paket yang dipilih';
    }

    private function activePackages(): array
    {
        return $this->db->table('packages')
            ->where('status', 'active')
            ->where('deleted_at', null)
            ->orderBy('price', 'ASC')
            ->get()
            ->getResultArray();
    }

    private function availableDepartures(array $packageIds): array
    {
        if (empty($packageIds)) {
            return [];
        }

        return $this->db->table('package_departures')
            ->select('package_departures.*, (quota - booked) AS remaining_seat', false)
            ->whereIn('package_id', $packageIds)
            ->where('status', 'available')
            ->where('departure_date >=', date('Y-m-d'))
            ->where('(quota - booked) > 0', null, false)
            ->orderBy('departure_date', 'ASC')
            ->get()
            ->getResultArray();
    }

    private function packageCards(array $packages, array $departures = [], int $participants = 1): array
    {
        $cards = [];

        foreach ($packages as $index => $package) {
            $departure = $package['_departure'] ?? null;

            if ($departure === null) {
                foreach ($departures as $candidate) {
                    if ((int) $candidate['package_id'] === (int) $package['id']) {
                        $departure = $candidate;
                        break;
                    }
                }
            }

            $price = (float) ($package['price'] ?? 0);
            $durationDays = (int) ($package['duration_days'] ?? 0);
            $durationNights = (int) ($package['duration_nights'] ?? 0);

            $cards[] = [
                'id'                 => (int) $package['id'],
                'name'               => (string) ($package['name'] ?? 'Paket'),
                'slug'               => (string) ($package['slug'] ?? ''),
                'badge'              => (string) ($package['badge'] ?? ''),
                'program'            => (string) ($package['program'] ?? ''),
                'program_label'      => !empty($package['program']) ? ucwords((string) $package['program']) : null,
                'price'              => $price,
                'price_label'        => $price > 0 ? 'Rp ' . number_format($price, 0, ',', '.') : 'Hubungi Admin',
                'duration_label'     => $durationDays > 0
                    ? $durationDays . ' hari' . ($durationNights > 0 ? ' / ' . $durationNights . ' malam' : '')
                    : 'Durasi belum tersedia',
                'departure_id'       => $departure !== null ? (int) $departure['id'] : null,
                'departure_label'    => $departure !== null && !empty($departure['departure_date'])
                    ? $this->formatIndonesianDate((string) $departure['departure_date'])
                    : 'Jadwal belum tersedia',
                'remaining_seat'     => $departure !== null ? (int) ($departure['remaining_seat'] ?? 0) : null,
                'participants'       => max($participants, 1),
                'detail_url'         => base_url('/paket/' . ($package['slug'] ?? '')),
            ];

            if ($index >= 2) {
                break;
            }
        }

        return $cards;
    }

    private function formatIndonesianDate(string $date): string
    {
        $timestamp = strtotime($date);
        if ($timestamp === false) {
            return $date;
        }

        $months = [
            1 => 'Jan',
            2 => 'Feb',
            3 => 'Mar',
            4 => 'Apr',
            5 => 'Mei',
            6 => 'Jun',
            7 => 'Jul',
            8 => 'Agu',
            9 => 'Sep',
            10 => 'Okt',
            11 => 'Nov',
            12 => 'Des',
        ];

        return date('d', $timestamp)
            . ' '
            . $months[(int) date('n', $timestamp)]
            . ' '
            . date('Y', $timestamp);
    }

    private function findFaqAnswer(string $normalized, array $extraKeywords = []): ?string
    {
        $rows = $this->db->table('faqs')
            ->select('question, answer')
            ->where('status', 'active')
            ->where('deleted_at', null)
            ->orderBy('sort_order', 'ASC')
            ->get()
            ->getResultArray();

        $queryTokens = array_unique(array_merge($this->tokens($normalized), $extraKeywords));
        $bestScore = 0;
        $bestAnswer = null;

        foreach ($rows as $row) {
            $questionTokens = $this->tokens($this->normalize((string) ($row['question'] ?? '')));
            $score = count(array_intersect($queryTokens, $questionTokens));

            if ($score > $bestScore) {
                $bestScore = $score;
                $bestAnswer = trim(strip_tags((string) ($row['answer'] ?? '')));
            }
        }

        return $bestScore >= 1 && $bestAnswer !== '' ? $bestAnswer : null;
    }

    private function findKnowledgeMatch(
        string $normalized,
        bool $contextual = false,
        ?string $desiredFocus = null,
        ?string $desiredProgram = null
    ): ?array
    {
        if (!$this->db->tableExists('chatbot_knowledge')) {
            return null;
        }

        $rows = $this->knowledgeModel
            ->where('status', 'active')
            ->orderBy('id', 'DESC')
            ->findAll(250);

        $queryTokens = $this->tokens($normalized);
        if ($queryTokens === []) {
            return null;
        }

        $bestScore = 0.0;
        $bestMatch = null;

        foreach ($rows as $row) {
            $answer = trim((string) ($row['answer'] ?? ''));
            if ($answer === '') {
                continue;
            }

            $question = $this->normalize((string) ($row['question'] ?? ''));
            $variants = preg_split('/[\r\n,;|]+/u', (string) ($row['keywords'] ?? '')) ?: [];
            $rowSource = $this->normalize(
                (string) ($row['question'] ?? '') . ' ' . (string) ($row['keywords'] ?? '')
            );
            $rowFocus = $this->detectFocus($rowSource);
            $rowPrograms = $this->extractPrograms($rowSource);

            if ($desiredFocus !== null
                && $rowFocus !== null
                && $rowFocus !== $desiredFocus) {
                continue;
            }

            // Knowledge program lain tidak boleh dipakai hanya karena beberapa
            // kata umumnya mirip. Knowledge generik tetap boleh dipakai untuk
            // aturan umum seperti dokumen, pembayaran, dan perlengkapan.
            if ($desiredProgram !== null) {
                if ($rowPrograms !== [] && !in_array($desiredProgram, $rowPrograms, true)) {
                    continue;
                }

                $programSpecificFocus = in_array($desiredFocus, [
                    'program',
                    'price',
                    'schedule',
                    'duration',
                    'availability',
                    'hotel',
                    'airline',
                    'facilities',
                ], true);
                if ($rowPrograms === [] && $programSpecificFocus) {
                    continue;
                }

                // Pertanyaan untuk satu program tidak boleh
                // dijawab oleh baris katalog yang sekaligus membahas banyak program.
                if ($desiredFocus === 'program' && count($rowPrograms) !== 1) {
                    continue;
                }
            }

            array_unshift($variants, $question);

            foreach ($variants as $variant) {
                $candidate = $this->normalize((string) $variant);
                if ($candidate === '') {
                    continue;
                }

                if ($normalized === $candidate) {
                    return [
                        'id'       => (int) ($row['id'] ?? 0),
                        'question' => (string) ($row['question'] ?? ''),
                        'answer'   => $answer,
                        'score'    => 1000.0,
                    ];
                }

                $candidateTokens = $this->tokens($candidate);
                if ($candidateTokens === []) {
                    continue;
                }

                $intersection = count(array_intersect($queryTokens, $candidateTokens));
                if ($intersection === 0) {
                    continue;
                }

                $queryCoverage = $intersection / max(1, count($queryTokens));
                $candidateCoverage = $intersection / max(1, count($candidateTokens));
                $phraseMatch = count($candidateTokens) >= 2
                    && (str_contains($normalized, $candidate) || str_contains($candidate, $normalized));

                $tokenMatch = $contextual
                    ? $intersection >= 2 && $queryCoverage >= 0.25 && $candidateCoverage >= 0.55
                    : $intersection >= 2 && $queryCoverage >= 0.60 && $candidateCoverage >= 0.40;
                $isConfident = $phraseMatch || $tokenMatch;

                if (!$isConfident) {
                    continue;
                }

                $score = ($intersection * 20)
                    + ($queryCoverage * 20)
                    + ($candidateCoverage * 10)
                    + ($phraseMatch ? 25 : 0);

                if ($score > $bestScore) {
                    $bestScore = $score;
                    $bestMatch = [
                        'id'       => (int) ($row['id'] ?? 0),
                        'question' => (string) ($row['question'] ?? ''),
                        'answer'   => $answer,
                        'score'    => $score,
                    ];
                }
            }
        }

        return $bestScore >= 30 && $bestMatch !== null ? $bestMatch : null;
    }

    private function decodeConversationContext(?string $json, ?string $updatedAt): array
    {
        $default = $this->defaultConversationContext();

        if (empty($json)) {
            return $default;
        }

        $decoded = json_decode($json, true);
        if (!is_array($decoded)) {
            return $default;
        }

        $lastUpdate = $updatedAt ?: ($decoded['updated_at'] ?? null);
        if (!empty($lastUpdate) && strtotime((string) $lastUpdate) < strtotime('-45 minutes')) {
            return $default;
        }

        foreach ($default as $key => $value) {
            if (array_key_exists($key, $decoded)) {
                $default[$key] = $decoded[$key];
            }
        }

        return $default;
    }

    private function defaultConversationContext(): array
    {
        return [
            'topic'                   => null,
            'focus'                   => null,
            'program'                 => null,
            'package_id'              => null,
            'package_name'            => null,
            'month_number'            => null,
            'month_label'             => null,
            'year'                    => null,
            'airline'                 => null,
            'room_type'               => null,
            'participants'            => null,
            'last_knowledge_id'       => null,
            'last_knowledge_question' => null,
            'last_question'           => null,
            'last_answer'             => null,
            'turn_count'              => 0,
            'updated_at'              => null,
        ];
    }

    private function analyzeMessage(string $normalized, array $context): array
    {
        $month = $this->extractMonth($normalized);
        $program = $this->extractProgram($normalized);
        $roomType = $this->extractRoomType($normalized);
        $airline = $this->extractAirline($normalized);
        $participants = $this->extractParticipantsEntity($normalized);
        $focus = $this->detectFocus($normalized);
        $topic = $this->detectExplicitTopic($normalized);

        if (($context['topic'] ?? null) === 'package'
            && ($focus === null || $focus === 'facilities')
            && $this->containsAny($normalized, ['termasuk visa', 'include visa', 'sudah termasuk', 'dapat apa'])) {
            $topic = 'package';
            $focus = 'facilities';
        }

        $resetContext = $this->containsAny($normalized, [
            'reset percakapan',
            'hapus konteks',
            'mulai ulang',
            'mulai dari awal',
            'topik baru',
        ]);

        $analysis = [
            'topic'           => $topic,
            'focus'           => $focus,
            'program'         => $program,
            'program_changed' => $program !== null && $program !== ($context['program'] ?? null),
            'package_phrase'  => $this->extractRequestedPackagePhrase($normalized),
            'month'           => $month,
            'airline'         => $airline,
            'room_type'       => $roomType,
            'participants'    => $participants,
            'clear_program'   => $this->containsAny($normalized, ['paket lain', 'program lain', 'yang lain']),
            'reset_context'   => $resetContext,
            'is_follow_up'    => false,
        ];

        $analysis['is_follow_up'] = !$resetContext
            && $this->isContextualFollowUp($normalized, $context, $analysis);

        if ($analysis['is_follow_up'] && $analysis['focus'] === null) {
            $analysis['focus'] = $context['focus'] ?? null;
        }

        return $analysis;
    }

    private function detectExplicitTopic(string $normalized): ?string
    {
        if ($this->containsAny($normalized, ['alamat', 'lokasi', 'kantor dimana', 'kantor di mana', 'maps', 'map'])) {
            return 'office';
        }

        if ($this->containsAny($normalized, ['jam buka', 'jam operasional', 'kantor buka', 'hari buka', 'operasional'])) {
            return 'business_hours';
        }

        if ($this->containsAny($normalized, [
            'nomor whatsapp',
            'whatsapp admin',
            'nomor admin',
            'kontak',
            'telepon',
            'no wa',
            'wa admin',
            'hubungi admin',
            'kontak admin',
            'bicara dengan admin',
            'chat admin',
            'sambungkan admin',
            'sambungkan ke admin',
        ])) {
            return 'contact';
        }

        if ($this->containsAny($normalized, ['pembatalan', 'refund', 'mengundurkan diri', 'batal umroh', 'batal paket'])) {
            return 'cancellation';
        }

        if ($this->containsAny($normalized, [
            'pembayaran',
            'transfer',
            'rekening',
            'down payment',
            'bayar dp',
            'dp berapa',
            'uang muka',
            'qris',
            'pelunasan',
            'invoice',
            'kwitansi',
        ])) {
            return 'payment';
        }

        if ($this->containsAny($normalized, ['keluhan', 'komplain', 'pengaduan', 'perselisihan'])) {
            return 'complaint';
        }

        if ($this->containsAny($normalized, ['syarat', 'dokumen', 'paspor', 'passport', 'ktp', 'kartu keluarga', 'vaksin', 'buku kuning'])
            || $this->containsWholeWord($normalized, 'kk')) {
            return 'documents';
        }

        if (str_contains($normalized, 'raudhah') || str_contains($normalized, 'nusuk')) {
            return 'raudhah';
        }

        if ($this->containsAny($normalized, ['kesehatan', 'riwayat penyakit', 'asuransi', 'tenaga medis', 'rumah sakit'])) {
            return 'health';
        }

        if (str_contains($normalized, 'visa')) {
            return 'visa';
        }

        if ($this->isPackageQuestion($normalized)) {
            return 'package';
        }

        return null;
    }

    private function detectFocus(string $normalized): ?string
    {
        if ($this->isRoomTypeExplanationQuestion($normalized)) {
            return 'room_type_explanation';
        }

        if ($this->containsAny($normalized, [
            'kapan dokumen',
            'batas dokumen',
            'dokumen dikumpulkan',
            'dokumen diserahkan',
            'pengumpulan dokumen',
            'batas pengumpulan',
        ])) {
            return 'document_deadline';
        }

        if ($this->containsAny($normalized, [
            'ketentuan paspor',
            'syarat paspor',
            'masa berlaku paspor',
            'nama di paspor',
            'ketentuan passport',
        ])) {
            return 'passport_requirements';
        }

        if ($this->containsAny($normalized, [
            'perlengkapan',
            'koper',
            'kain ihram',
            'sabuk ihram',
            'buku doa',
            'peci',
            'syal',
            'id card',
        ])) {
            return 'equipment';
        }

        if ($this->containsAny($normalized, ['maskapai', 'airline', 'pesawat apa', 'penerbangan pakai'])) {
            return 'airline';
        }

        if ($this->containsAny($normalized, ['qris', 'transfer', 'rekening', 'cara bayar', 'metode pembayaran'])) {
            return 'payment_method';
        }

        if ($this->containsAny($normalized, ['down payment', 'dp berapa', 'bayar dp', 'uang muka'])) {
            return 'deposit';
        }

        if ($this->containsAny($normalized, ['kapan', 'tanggal', 'jadwal', 'berangkat', 'keberangkatan', 'terdekat'])) {
            return 'schedule';
        }

        if ($this->containsAny($normalized, ['berapa hari', 'berapa malam', 'durasi', 'lama perjalanan'])) {
            return 'duration';
        }

        if ($this->containsAny($normalized, ['seat', 'kursi', 'kuota', 'rombongan', 'orang', 'jamaah', 'jemaah'])) {
            return 'availability';
        }

        if ($this->containsAny($normalized, ['hotel', 'menginap', 'akomodasi'])) {
            return 'hotel';
        }

        if ($this->containsAny($normalized, ['fasilitas', 'include', 'exclude', 'termasuk', 'dapat apa'])) {
            return 'facilities';
        }

        if ($this->containsAny($normalized, ['harga', 'biaya', 'tarif', 'termurah', 'paling murah'])
            || (str_contains($normalized, 'berapa') && $this->extractProgram($normalized) !== null)) {
            return 'price';
        }

        if ($this->extractRoomType($normalized) !== null) {
            return 'price';
        }

        if (str_contains($normalized, 'program')) {
            return 'program';
        }

        return null;
    }

    private function isContextualFollowUp(string $normalized, array $context, array $analysis): bool
    {
        if (!$this->hasConversationContext($context)) {
            return false;
        }

        $currentTopic = $context['topic'] ?? null;
        $explicitTopic = $analysis['topic'] ?? null;

        if ($explicitTopic !== null
            && $explicitTopic !== $currentTopic
            && !($explicitTopic === 'package' && $currentTopic === 'package')) {
            if ($explicitTopic === 'package'
                && (!empty($context['package_name']) || !empty($context['program']))
                && $this->isPackageDetailReference($normalized, $analysis)) {
                return true;
            }

            return false;
        }

        $followUpMarkers = [
            'kalau',
            'yang tadi',
            'yang itu',
            'tersebut',
            'bagaimana dengan',
            'terus',
            'lalu',
            'untuk yang',
            'kalau yang',
            'sudah termasuk',
            'dan kalau',
        ];

        if ($this->containsAny($normalized, $followUpMarkers)) {
            return true;
        }

        if ($explicitTopic === null
            && preg_match('/^(siapa|apa itu|mengapa|kenapa|di mana|dimana)\b/u', $normalized)
            && !$this->containsAny($normalized, ['tadi', 'tersebut', 'yang itu', 'nya'])) {
            return false;
        }

        $hasEntity = ($analysis['program'] ?? null) !== null
            || ($analysis['month'] ?? null) !== null
            || ($analysis['airline'] ?? null) !== null
            || ($analysis['room_type'] ?? null) !== null
            || ($analysis['participants'] ?? null) !== null;

        $wordCount = count(preg_split('/\s+/u', $normalized) ?: []);

        if ($explicitTopic === $currentTopic && ($hasEntity || $wordCount <= 6)) {
            return true;
        }

        return $explicitTopic === null && ($hasEntity || $wordCount <= 4);
    }

    private function isPackageDetailReference(string $normalized, array $analysis): bool
    {
        if (($analysis['participants'] ?? null) !== null
            || !empty($analysis['program_changed'])
            || !empty($analysis['package_phrase'])
            || $this->containsAny($normalized, [
                'ada paket',
                'paket apa',
                'paket untuk',
                'program apa',
                'paket lain',
                'program lain',
            ])) {
            return false;
        }

        $focus = $analysis['focus'] ?? null;

        return in_array($focus, [
            'price',
            'schedule',
            'duration',
            'hotel',
            'airline',
            'facilities',
            'equipment',
            'room_type_explanation',
        ], true);
    }

    private function hasConversationContext(array $context): bool
    {
        return !empty($context['topic'])
            || !empty($context['program'])
            || !empty($context['last_knowledge_question']);
    }

    private function buildContextualQuery(string $normalized, array $context, array $analysis): string
    {
        $parts = [$normalized];
        $focus = $analysis['focus'] ?? $context['focus'] ?? null;
        $programChanged = !empty($analysis['program_changed']);

        if (empty($analysis['clear_program'])) {
            $program = $analysis['program'] ?? $context['program'] ?? null;
            if (!empty($program) && !str_contains($normalized, (string) $program)) {
                $parts[] = 'program ' . $program;
            }
        }

        $month = $analysis['month'] ?? null;
        $usesPackagePeriod = in_array($focus, [
            'price',
            'schedule',
            'duration',
            'availability',
            'hotel',
            'airline',
            'facilities',
            'program',
        ], true);
        if ($month === null && $usesPackagePeriod && !$programChanged && !empty($context['month_label'])) {
            $month = [
                'label' => $context['month_label'],
                'year'  => $context['year'] ?? date('Y'),
            ];
        }
        if ($month !== null && $usesPackagePeriod) {
            $parts[] = (string) ($month['label'] ?? '') . ' ' . (string) ($month['year'] ?? '');
        }

        if (in_array($focus, ['price', 'schedule', 'availability', 'airline'], true)) {
            $airline = $analysis['airline']
                ?? (!$programChanged ? ($context['airline'] ?? null) : null);
            if (!empty($airline)) {
                $parts[] = (string) $airline;
            }
        }

        if ($focus === 'price') {
            $roomType = $analysis['room_type']
                ?? (!$programChanged ? ($context['room_type'] ?? null) : null);
            if (!empty($roomType)) {
                $parts[] = (string) $roomType;
            }
        }

        if ($focus === 'availability') {
            $participants = $analysis['participants']
                ?? (!$programChanged ? ($context['participants'] ?? null) : null);
            if (!empty($participants)) {
                $parts[] = (int) $participants . ' jamaah';
            }
        }

        if (!$programChanged
            && $focus !== 'program'
            && empty($analysis['package_phrase'])
            && !empty($context['package_name'])
            && !str_contains($normalized, $this->normalize((string) $context['package_name']))) {
            $parts[] = 'paket ' . (string) $context['package_name'];
        }

        if (in_array($focus, [
            'equipment',
            'document_deadline',
            'passport_requirements',
            'payment_method',
            'deposit',
        ], true) && !empty($context['last_knowledge_question'])) {
            $parts[] = (string) $context['last_knowledge_question'];
        }

        if (!empty($focus)) {
            $focusTerms = [
                'price'        => 'harga biaya',
                'schedule'     => 'jadwal tanggal keberangkatan',
                'duration'     => 'durasi berapa hari',
                'availability' => 'seat kuota tersedia',
                'hotel'        => 'hotel akomodasi',
                'airline'      => 'maskapai airline penerbangan',
                'facilities'   => 'fasilitas termasuk',
                'equipment'    => 'perlengkapan koper gratis full paket tambahan biaya',
                'document_deadline' => 'batas pengumpulan dokumen',
                'passport_requirements' => 'ketentuan syarat paspor',
                'payment_method' => 'metode pembayaran transfer rekening qris',
                'deposit'      => 'down payment dp uang muka',
                'program'      => 'program paket',
                'room_type_explanation' => 'perbedaan tipe kamar',
            ];
            $parts[] = $focusTerms[$focus] ?? (string) $focus;
        }

        return $this->normalize(mb_substr(implode(' ', array_filter($parts)), 0, 700));
    }

    private function evolveConversationContext(
        array $context,
        array $analysis,
        array $result,
        string $normalized
    ): array {
        $now = date('Y-m-d H:i:s');

        if (($result['intent'] ?? '') === 'context_reset' || !empty($analysis['reset_context'])) {
            $reset = $this->defaultConversationContext();
            $reset['updated_at'] = $now;

            return $reset;
        }

        $intent = (string) ($result['intent'] ?? '');
        $previousTopic = $context['topic'] ?? null;
        $topic = $result['_context_topic']
            ?? $analysis['topic']
            ?? $this->topicFromIntent($intent)
            ?? $context['topic']
            ?? null;

        if (in_array($intent, ['greeting', 'thanks', 'empty'], true)) {
            $context['last_question'] = $normalized;
            $context['last_answer'] = $result['reply'] ?? null;
            $context['turn_count'] = (int) ($context['turn_count'] ?? 0) + 1;
            $context['updated_at'] = $now;

            return $context;
        }

        $isNewTopic = empty($analysis['is_follow_up'])
            && !empty($topic)
            && !empty($context['topic'])
            && $topic !== $context['topic'];

        if ($isNewTopic) {
            $turnCount = (int) ($context['turn_count'] ?? 0);
            $packageMemory = [];
            foreach ([
                'program',
                'package_id',
                'package_name',
                'month_number',
                'month_label',
                'year',
                'airline',
                'room_type',
            ] as $packageKey) {
                $packageMemory[$packageKey] = $context[$packageKey] ?? null;
            }

            $context = $this->defaultConversationContext();
            $context['turn_count'] = $turnCount;
            foreach ($packageMemory as $packageKey => $packageValue) {
                $context[$packageKey] = $packageValue;
            }
        }

        if (!empty($result['_knowledge_question'])) {
            $knowledgeQuestion = $this->normalize((string) $result['_knowledge_question']);
            $knowledgeMonth = $this->extractMonth($knowledgeQuestion);
            $knowledgePrograms = $this->extractPrograms($knowledgeQuestion);

            if (($analysis['program'] ?? null) === null && count($knowledgePrograms) === 1) {
                $analysis['program'] = $knowledgePrograms[0];
            }
            $analysis['month'] = $analysis['month'] ?? $knowledgeMonth;
            $analysis['airline'] = $analysis['airline'] ?? $this->extractAirline($knowledgeQuestion);
            $analysis['room_type'] = $analysis['room_type'] ?? $this->extractRoomType($knowledgeQuestion);
            $analysis['focus'] = $analysis['focus'] ?? $this->detectFocus($knowledgeQuestion);
        }

        if (empty($analysis['is_follow_up'])
            && $topic === 'package'
            && $previousTopic !== 'package'
            && !$this->isPackageDetailReference($normalized, $analysis)) {
            $context['program'] = null;
            $context['package_id'] = null;
            $context['package_name'] = null;
            $context['month_number'] = null;
            $context['month_label'] = null;
            $context['year'] = null;
            $context['airline'] = null;
            $context['room_type'] = null;
            $context['participants'] = null;
        }

        if (!empty($analysis['program_changed'])) {
            $context['month_number'] = null;
            $context['month_label'] = null;
            $context['year'] = null;
            $context['airline'] = null;
            $context['room_type'] = null;
            $context['participants'] = null;
            $context['package_id'] = null;
            $context['package_name'] = null;
        }

        if (!empty($analysis['clear_program'])) {
            $context['program'] = null;
            $context['package_id'] = null;
            $context['package_name'] = null;
        } elseif (!empty($analysis['program'])) {
            $context['program'] = $analysis['program'];
        }

        if (!empty($analysis['month'])) {
            $context['month_number'] = $analysis['month']['number'] ?? null;
            $context['month_label'] = $analysis['month']['label'] ?? null;
            $context['year'] = $analysis['month']['year'] ?? null;
        }

        foreach (['airline', 'room_type', 'participants'] as $entity) {
            if (($analysis[$entity] ?? null) !== null) {
                $context[$entity] = $analysis[$entity];
            }
        }

        if (!empty($result['_ai_context']['package_name'])) {
            $context['package_name'] = mb_substr(
                (string) $result['_ai_context']['package_name'],
                0,
                255
            );
        }

        $resultPackages = $result['packages'] ?? [];
        if (is_array($resultPackages)
            && count($resultPackages) === 1
            && ($result['intent'] ?? '') !== 'package_unavailable') {
            $context['package_id'] = $resultPackages[0]['id'] ?? null;
            $context['package_name'] = $resultPackages[0]['name'] ?? null;
            if (!empty($resultPackages[0]['program'])) {
                $context['program'] = $this->normalize((string) $resultPackages[0]['program']);
            }
        } elseif (($analysis['focus'] ?? null) === 'program' && !empty($analysis['program'])) {
            $context['package_id'] = null;
            $context['package_name'] = null;
        }

        $context['topic'] = $topic;
        $context['focus'] = $result['_context_focus']
            ?? $analysis['focus']
            ?? $context['focus']
            ?? null;
        $context['last_knowledge_id'] = $result['_knowledge_id'] ?? null;
        $context['last_knowledge_question'] = $result['_knowledge_question'] ?? null;
        $context['last_question'] = $normalized;
        $context['last_answer'] = $result['reply'] ?? null;
        $context['turn_count'] = (int) ($context['turn_count'] ?? 0) + 1;
        $context['updated_at'] = $now;

        return $context;
    }

    private function topicFromIntent(string $intent): ?string
    {
        if (str_starts_with($intent, 'package_') || str_starts_with($intent, 'departure_') || $intent === 'program_catalog') {
            return 'package';
        }

        return [
            'office_address'   => 'office',
            'business_hours'   => 'business_hours',
            'contact'          => 'contact',
            'documents'        => 'documents',
            'faq'              => 'faq',
            'custom_knowledge' => 'knowledge',
            'out_of_scope'     => 'out_of_scope',
        ][$intent] ?? null;
    }

    private function applyNaturalContextTone(
        string $reply,
        array $context,
        array $analysis,
        string $intent
    ): string {
        $selfContainedIntent = $intent === 'custom_knowledge'
            || $intent === 'documents'
            || $intent === 'document_deadline'
            || $intent === 'passport_requirements'
            || $intent === 'program_catalog'
            || str_starts_with($intent, 'package_')
            || str_starts_with($intent, 'departure_')
            || str_starts_with($intent, 'payment_')
            || str_starts_with($intent, 'equipment_');

        if (empty($analysis['is_follow_up'])
            || $selfContainedIntent
            || in_array($intent, [
                'context_clarification',
                'context_reset',
                'room_type_explanation',
                'contact',
                'greeting',
                'thanks',
                'empty',
            ], true)
            || str_starts_with($reply, 'Baik,')
            || str_starts_with($reply, 'Tentu,')) {
            return $reply;
        }

        $merged = $context;
        if (!empty($analysis['program_changed'])) {
            foreach ([
                'package_id',
                'package_name',
                'month_number',
                'month_label',
                'year',
                'airline',
                'room_type',
                'participants',
            ] as $staleEntity) {
                $merged[$staleEntity] = null;
            }
        }

        foreach (['program', 'airline', 'room_type', 'participants'] as $entity) {
            if (($analysis[$entity] ?? null) !== null) {
                $merged[$entity] = $analysis[$entity];
            }
        }
        if (!empty($analysis['month'])) {
            $merged['month_label'] = $analysis['month']['label'] ?? null;
            $merged['year'] = $analysis['month']['year'] ?? null;
        }

        if (!empty($analysis['program'])
            && str_contains($this->normalize($reply), (string) $analysis['program'])) {
            return $reply;
        }

        $subject = $this->contextSubject($merged, $analysis['focus'] ?? $context['focus'] ?? null);
        if ($subject === '') {
            return 'Baik, melanjutkan pembahasan sebelumnya. ' . $reply;
        }

        $turn = (int) ($context['turn_count'] ?? 0) % 3;
        $prefixes = [
            'Baik, untuk ' . $subject . '. ',
            'Tentu. Masih terkait ' . $subject . ', ',
            'Melanjutkan yang tadi tentang ' . $subject . '. ',
        ];

        return $prefixes[$turn] . $reply;
    }

    private function contextSubject(array $context, ?string $focus = null): string
    {
        $parts = [];

        if (!empty($context['package_name'])) {
            $parts[] = 'Paket ' . (string) $context['package_name'];
        } elseif (!empty($context['program'])) {
            $parts[] = 'Program ' . ucwords((string) $context['program']);
        }

        $packageName = $this->normalize((string) ($context['package_name'] ?? ''));
        $monthAlreadyInPackage = !empty($context['month_label'])
            && str_contains($packageName, $this->normalize((string) $context['month_label']));

        if (!empty($context['month_label']) && !$monthAlreadyInPackage) {
            $month = (string) $context['month_label'];
            if (!empty($context['year'])) {
                $month .= ' ' . $context['year'];
            }
            $parts[] = $month;
        }

        if ($focus === 'price' && !empty($context['room_type'])) {
            $parts[] = 'kamar ' . ucwords((string) $context['room_type']);
        }

        if (in_array($focus, ['price', 'schedule', 'availability'], true) && !empty($context['airline'])) {
            $parts[] = (string) $context['airline'];
        }

        if ($focus === 'availability' && !empty($context['participants'])) {
            $parts[] = (int) $context['participants'] . ' jamaah';
        }

        return implode(', ', $parts);
    }

    private function contextualQuickReplies(?string $topic, array $context, array $analysis): array
    {
        if ($topic === 'package') {
            $program = $analysis['program'] ?? $context['program'] ?? null;
            $programLabel = !empty($program) ? ' ' . ucwords((string) $program) : '';

            return [
                ['label' => 'Cek Jadwal', 'message' => 'Kapan jadwal' . $programLabel . '?'],
                ['label' => 'Lihat Harga', 'message' => 'Berapa harga' . $programLabel . '?'],
                ['label' => 'Fasilitas', 'message' => 'Apa saja fasilitasnya?'],
                ['label' => 'Cek 10 Seat', 'message' => 'Kalau untuk 10 jamaah?'],
            ];
        }

        if ($topic === 'documents') {
            return [
                ['label' => 'Syarat Paspor', 'message' => 'Apa ketentuan paspornya?'],
                ['label' => 'Batas Dokumen', 'message' => 'Kapan dokumen harus dikumpulkan?'],
                ['label' => 'Hubungi Admin', 'message' => 'Nomor WhatsApp admin berapa?'],
            ];
        }

        return $this->defaultQuickReplies();
    }

    private function contextClarificationResponse(array $context, array $analysis): array
    {
        $focus = $analysis['focus'] ?? $context['focus'] ?? null;
        $subject = $this->contextSubject($context, $focus);
        $subjectText = $subject !== '' ? ' mengenai ' . $subject : '';

        return [
            'intent'         => 'context_clarification',
            'reply'          => 'Saya masih mengikuti pembahasan sebelumnya' . $subjectText . '. Maksud pertanyaannya ingin mengetahui harga, jadwal, fasilitas, hotel, atau ketersediaan seat?',
            'packages'       => [],
            'quick_replies'  => $this->contextualQuickReplies($context['topic'] ?? null, $context, $analysis),
            'unanswered'     => false,
            '_context_topic' => $context['topic'] ?? null,
            '_context_focus' => $focus,
        ];
    }

    private function publicContextSummary(array $context): array
    {
        return [
            'topic'        => $context['topic'] ?? null,
            'focus'        => $context['focus'] ?? null,
            'program'      => $context['program'] ?? null,
            'package_id'   => $context['package_id'] ?? null,
            'package_name' => $context['package_name'] ?? null,
            'month'        => $context['month_label'] ?? null,
            'year'         => $context['year'] ?? null,
            'airline'      => $context['airline'] ?? null,
            'room_type'    => $context['room_type'] ?? null,
            'participants' => $context['participants'] ?? null,
        ];
    }

    private function siteName(): string
    {
        $siteName = trim((string) ($this->settings['site_name'] ?? ''));

        return $siteName !== '' ? $siteName : 'Travel Umroh & Haji';
    }

    private function paymentTransferReply(): string
    {
        $bankName = trim((string) ($this->settings['bank_name'] ?? ''));
        $accountNumber = trim((string) ($this->settings['bank_account_number'] ?? ''));
        $accountName = trim((string) ($this->settings['bank_account_name'] ?? ''));

        if ($accountNumber === '') {
            return 'Informasi rekening pembayaran belum tersedia. Silakan hubungi admin ' . $this->siteName() . ' untuk mendapatkan data pembayaran resmi.';
        }

        $parts = [];
        if ($bankName !== '') {
            $parts[] = $bankName;
        }
        $parts[] = 'nomor ' . $accountNumber;
        if ($accountName !== '') {
            $parts[] = 'atas nama ' . $accountName;
        }

        return 'Pembayaran dilakukan melalui transfer ke ' . implode(', ', $parts) . '. Setelah transfer, kirimkan bukti pembayaran kepada admin ' . $this->siteName() . ' untuk diperiksa oleh tim finance dan diterbitkan invoice terbaru.';
    }

    private function fallbackResponse(string $original, string $intent): array
    {
        $fallback = $this->settings['chatbot_fallback_message']
            ?? 'Mohon maaf, informasi tersebut belum tersedia. Saya hanya dapat membantu seputar paket, jadwal, layanan, dan informasi resmi ' . $this->siteName() . '.';

        return [
            'intent'        => $intent,
            'reply'         => $fallback,
            'packages'      => [],
            'quick_replies' => $this->defaultQuickReplies(),
            'handoff_url'   => null,
            'handoff_label' => null,
            'unanswered'    => true,
            'original'      => $original,
        ];
    }

    private function defaultQuickReplies(): array
    {
        return [
            ['label' => 'Paket Terdekat', 'message' => 'Paket terdekat kapan?'],
            ['label' => 'Cek Seat Rombongan', 'message' => 'Ada paket untuk 10 orang?'],
            ['label' => 'Harga Paket', 'message' => 'Paket paling murah apa?'],
            ['label' => 'Alamat Kantor', 'message' => 'Alamat kantor di mana?'],
        ];
    }

    private function contactResponse(array $quickReplies): array
    {
        $wa = $this->cleanWhatsapp($this->settings['social_whatsapp'] ?? $this->settings['site_whatsapp'] ?? '');
        $phone = trim((string) ($this->settings['contact_phone'] ?? $this->settings['site_phone'] ?? ''));

        $reply = 'Kontak ' . $this->siteName();
        if ($wa !== '') {
            $reply .= ': WhatsApp +' . $wa;
        }
        if ($phone !== '') {
            $reply .= ', telepon ' . $phone;
        }
        $reply .= '.';

        return [
            'intent'         => 'contact',
            'reply'          => $reply,
            'packages'       => [],
            'quick_replies'  => $quickReplies,
            'handoff_url'    => $wa !== '' ? 'https://wa.me/' . $wa : null,
            'handoff_label'  => $wa !== '' ? 'Hubungi Admin' : null,
            'unanswered'     => $wa === '' && $phone === '',
            '_context_topic' => 'contact',
            '_context_focus' => null,
        ];
    }

    private function getOrCreateSession(
        ?string $token,
        ?string $sourcePage,
        ?string $ipAddress,
        ?string $userAgent
    ): array {
        $token = preg_replace('/[^a-zA-Z0-9_-]/', '', (string) $token);
        $session = null;

        if ($token !== '') {
            $session = $this->sessionModel
                ->where('session_token', $token)
                ->first();
        }

        if ($session) {
            return $session;
        }

        $token = bin2hex(random_bytes(24));
        $now = date('Y-m-d H:i:s');

        $id = $this->sessionModel->insert([
            'session_token'   => $token,
            'source_page'     => mb_substr((string) $sourcePage, 0, 255),
            'ip_address'      => mb_substr((string) $ipAddress, 0, 64),
            'user_agent'      => mb_substr((string) $userAgent, 0, 1000),
            'last_message_at' => $now,
        ], true);

        return $this->sessionModel->find($id);
    }

    private function saveMessage(
        int $sessionId,
        string $sender,
        string $message,
        ?string $intent,
        ?array $metadata
    ): void {
        $this->messageModel->insert([
            'session_id'   => $sessionId,
            'sender'       => $sender,
            'message'      => $message,
            'intent'       => $intent,
            'metadata_json'=> $metadata !== null ? json_encode($metadata, JSON_UNESCAPED_UNICODE) : null,
            'created_at'   => date('Y-m-d H:i:s'),
        ]);
    }

    private function storeUnanswered(int $sessionId, string $question, string $normalized): void
    {
        if (!$this->db->tableExists('chatbot_unanswered')) {
            return;
        }

        $recent = $this->unansweredModel
            ->where('normalized_question', $normalized)
            ->where('status', 'new')
            ->where('created_at >=', date('Y-m-d H:i:s', strtotime('-1 day')))
            ->first();

        if ($recent) {
            return;
        }

        $this->unansweredModel->insert([
            'session_id'          => $sessionId,
            'question'            => $question,
            'normalized_question' => $normalized,
            'status'              => 'new',
        ]);
    }

    private function isPackageQuestion(string $normalized): bool
    {
        $needles = [
            'paket', 'umroh', 'umrah', 'haji', 'turki', 'turkiye', 'ramadhan', 'reguler',
            'plus', 'harga', 'biaya', 'jadwal', 'berangkat', 'keberangkatan', 'bulan',
            'seat', 'kursi', 'kuota', 'rombongan', 'jamaah', 'jemaah', 'orang',
            'fasilitas', 'durasi', 'berapa hari', 'berapa malam', 'program', 'hotel',
            'akomodasi', 'maskapai', 'airline', 'pesawat', 'perlengkapan', 'koper',
            'kain ihram', 'sabuk ihram', 'buku doa', 'peci', 'syal', 'full paket',
            'double', 'triple', 'quad', 'quint', 'garuda', 'qatar', 'oman air', 'saudia',
        ];

        return $this->containsAny(
            $normalized,
            array_values(array_unique(array_merge($needles, array_keys(PackageModel::getProgramOptions()))))
        );
    }

    private function extractParticipants(string $normalized): int
    {
        return $this->extractParticipantsEntity($normalized) ?? 1;
    }

    private function extractParticipantsEntity(string $normalized): ?int
    {
        if (preg_match('/(?:untuk|sekitar|sebanyak|rombongan|lebih dari)?\s*(\d{1,2})\s*(?:orang|jamaah|jemaah|seat|kursi)/u', $normalized, $matches)) {
            $participants = (int) $matches[1];
            if (str_contains($normalized, 'lebih dari ' . $matches[1])) {
                $participants++;
            }

            return max(1, min($participants, 50));
        }

        return null;
    }

    private function extractProgram(string $normalized): ?string
    {
        $programs = $this->extractPrograms($normalized);

        return count($programs) === 1 ? $programs[0] : null;
    }

    private function extractPrograms(string $normalized): array
    {
        $matches = [];

        foreach (array_keys(PackageModel::getProgramOptions()) as $program) {
            if (preg_match('/\b' . preg_quote($program, '/') . '\b/u', $normalized)) {
                $matches[] = $program;
            }
        }

        return $matches;
    }

    private function extractRoomType(string $normalized): ?string
    {
        $roomTypes = [
            'double'   => ['double', 'berdua', '2 orang sekamar'],
            'triple'   => ['triple', 'bertiga', '3 orang sekamar'],
            'quad'     => ['quad', 'berempat', '4 orang sekamar'],
            'quint'    => ['quint', 'quintuple', 'berlima', '5 orang sekamar'],
        ];

        $matches = [];
        foreach ($roomTypes as $type => $needles) {
            if ($this->containsAny($normalized, $needles)) {
                $matches[] = $type;
            }
        }

        return count($matches) === 1 ? $matches[0] : null;
    }

    private function isRoomTypeExplanationQuestion(string $normalized): bool
    {
        $mentioned = 0;
        foreach (['double', 'triple', 'quad', 'quint', 'quintuple'] as $roomType) {
            if ($this->containsWholeWord($normalized, $roomType)) {
                $mentioned++;
            }
        }

        $hasComparisonMarker = $this->containsAny($normalized, [
            'bedanya',
            'beda ',
            'perbedaan',
            'artinya',
            'arti ',
            'maksud',
            'itu apa',
        ]);

        if ($mentioned >= 1 && $hasComparisonMarker) {
            return true;
        }

        return $mentioned >= 2
            && !$this->containsAny($normalized, ['harga', 'biaya', 'tarif']);
    }

    private function extractAirline(string $normalized): ?string
    {
        $airlines = [
            'Garuda Indonesia' => ['garuda indonesia', 'garuda'],
            'Qatar Airways'    => ['qatar airways', 'qatar'],
            'Oman Air'         => ['oman air'],
            'Saudia'           => ['saudia', 'saudi airlines'],
            'Lion Air'         => ['lion air'],
            'Batik Air'        => ['batik air'],
        ];

        foreach ($airlines as $label => $needles) {
            if ($this->containsAny($normalized, $needles)) {
                return $label;
            }
        }

        return null;
    }

    private function extractMonth(string $normalized): ?array
    {
        $months = [
            'januari' => 1, 'jan' => 1,
            'februari' => 2, 'feb' => 2,
            'maret' => 3, 'mar' => 3,
            'april' => 4, 'apr' => 4,
            'mei' => 5,
            'juni' => 6, 'jun' => 6,
            'juli' => 7, 'jul' => 7,
            'agustus' => 8, 'agu' => 8, 'aug' => 8,
            'september' => 9, 'sep' => 9,
            'oktober' => 10, 'okt' => 10, 'oct' => 10,
            'november' => 11, 'nov' => 11,
            'desember' => 12, 'des' => 12, 'dec' => 12,
        ];

        foreach ($months as $label => $number) {
            if (preg_match('/\b' . preg_quote($label, '/') . '\b/u', $normalized)) {
                $year = (int) date('Y');
                if (preg_match('/\b(20\d{2})\b/', $normalized, $yearMatch)) {
                    $year = (int) $yearMatch[1];
                } elseif ($number < (int) date('n')) {
                    $year++;
                }

                $monthNames = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];

                return [
                    'number' => $number,
                    'year'   => $year,
                    'label'  => $monthNames[$number],
                ];
            }
        }

        return null;
    }

    private function extractPackageKeyword(string $normalized): ?string
    {
        $keywords = [
            'haji' => 'haji',
            'umroh' => 'umroh',
            'umrah' => 'umrah',
            'turkiye' => 'turkiye',
            'turki' => 'turki',
            'ramadhan' => 'ramadhan',
            'ramadan' => 'ramadan',
            'reguler' => 'reguler',
        ];

        foreach (array_keys(PackageModel::getProgramOptions()) as $programKey) {
            $keywords[$programKey] = $programKey;
        }

        foreach ($keywords as $needle => $term) {
            if (str_contains($normalized, $needle)) {
                return $term;
            }
        }

        return null;
    }

    private function normalize(string $value): string
    {
        $value = mb_strtolower(trim($value));
        $value = str_replace(['umrah', 'turkiye', 'ramadan'], ['umroh', 'turki', 'ramadhan'], $value);
        $value = preg_replace('/\b(ga|gak|nggak|enggak)\b/u', 'tidak', $value);
        $value = preg_replace('/\b(dapet|dpt)\b/u', 'dapat', (string) $value);
        $value = preg_replace('/\b(pake|pakek)\b/u', 'pakai', (string) $value);
        $value = preg_replace('/\byg\b/u', 'yang', (string) $value);
        $value = preg_replace('/\baja\b/u', 'saja', (string) $value);
        $value = str_replace(['’', "'", '"'], ' ', $value);
        $value = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $value);
        $value = preg_replace('/\s+/u', ' ', (string) $value);

        return trim((string) $value);
    }

    private function tokens(string $value): array
    {
        $stopWords = [
            'yang', 'dan', 'atau', 'di', 'ke', 'dari', 'untuk', 'saya', 'aku', 'kami',
            'mau', 'ingin', 'ada', 'apa', 'apakah', 'berapa', 'bisa', 'kah', 'ini', 'itu',
            'dengan', 'tentang', 'tolong', 'dong', 'nih', 'ya', 'nya', 'saja', 'sama',
        ];

        $siteName = $this->normalize(site_setting('site_name', ''));
        $siteNameTokens = preg_split('/\s+/u', $siteName) ?: [];
        $stopWords = array_values(array_unique(array_merge(
            $stopWords,
            array_filter($siteNameTokens, static fn(string $token): bool => mb_strlen($token) >= 3)
        )));

        $tokens = preg_split('/\s+/u', $value) ?: [];
        $tokens = array_filter($tokens, static function (string $token) use ($stopWords): bool {
            return mb_strlen($token) >= 3 && !in_array($token, $stopWords, true);
        });

        return array_values(array_unique($tokens));
    }

    private function containsAny(string $haystack, array $needles): bool
    {
        foreach ($needles as $needle) {
            if (str_contains($haystack, $needle)) {
                return true;
            }
        }

        return false;
    }

    private function containsWholeWord(string $haystack, string $needle): bool
    {
        return (bool) preg_match('/\b' . preg_quote($needle, '/') . '\b/u', $haystack);
    }

    private function isGreeting(string $normalized): bool
    {
        if (preg_match('/^(assalamualaikum|salam|halo|hai|hello)( admin| kak| semuanya)?$/u', $normalized)) {
            return true;
        }

        return (bool) preg_match('/^(selamat )?(pagi|siang|sore|malam)( admin| kak| semuanya)?$/u', $normalized);
    }

    private function cleanWhatsapp(string $number): string
    {
        $number = preg_replace('/[^0-9]/', '', $number);
        if (str_starts_with($number, '0')) {
            $number = '62' . substr($number, 1);
        }

        return $number;
    }

    private function mapsUrl(string $address): ?string
    {
        if ($address === '') {
            return null;
        }

        return 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($address);
    }
}
