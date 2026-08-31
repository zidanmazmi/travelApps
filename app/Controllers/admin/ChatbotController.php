<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ChatbotKnowledgeModel;
use App\Models\ChatbotMessageModel;
use App\Models\ChatbotSessionModel;
use App\Models\ChatbotUnansweredModel;
use App\Services\ChatbotDocumentTrainingService;
use App\Services\GeminiService;
use Throwable;

class ChatbotController extends BaseController
{
    private ChatbotSessionModel $sessionModel;
    private ChatbotMessageModel $messageModel;
    private ChatbotUnansweredModel $unansweredModel;
    private ChatbotKnowledgeModel $knowledgeModel;

    public function __construct()
    {
        $this->sessionModel    = new ChatbotSessionModel();
        $this->messageModel    = new ChatbotMessageModel();
        $this->unansweredModel = new ChatbotUnansweredModel();
        $this->knowledgeModel  = new ChatbotKnowledgeModel();
    }

    public function index()
    {
        $db = \Config\Database::connect();

        $stats = [
            'sessions' => $db->table('chatbot_sessions')->countAllResults(),
            'messages' => $db->table('chatbot_messages')->where('sender', 'visitor')->countAllResults(),
            'unanswered' => $db->table('chatbot_unanswered')->where('status', 'new')->countAllResults(),
            'leads' => $db->table('travel_leads')
                ->where('source', 'chatbot')
                ->where('deleted_at', null)
                ->countAllResults(),
        ];

        $latestSessions = $db->table('chatbot_sessions')
            ->select("chatbot_sessions.*, COUNT(chatbot_messages.id) AS total_messages, MAX(CASE WHEN chatbot_messages.sender = 'visitor' THEN chatbot_messages.message END) AS last_visitor_message", false)
            ->join('chatbot_messages', 'chatbot_messages.session_id = chatbot_sessions.id', 'left')
            ->groupBy('chatbot_sessions.id')
            ->orderBy('chatbot_sessions.last_message_at', 'DESC')
            ->limit(20)
            ->get()
            ->getResultArray();

        $topIntents = $db->table('chatbot_messages')
            ->select('intent, COUNT(*) AS total')
            ->where('sender', 'bot')
            ->where('intent IS NOT NULL', null, false)
            ->groupBy('intent')
            ->orderBy('total', 'DESC')
            ->limit(8)
            ->get()
            ->getResultArray();

        $unanswered = $this->unansweredModel
            ->where('status', 'new')
            ->orderBy('id', 'DESC')
            ->findAll(15);

        $knowledge = $this->knowledgeModel
            ->orderBy('id', 'DESC')
            ->findAll(50);

        return view('admin/chatbot/index', [
            'title'          => 'Chatbot Website',
            'stats'          => $stats,
            'latestSessions' => $latestSessions,
            'topIntents'     => $topIntents,
            'unanswered'     => $unanswered,
            'knowledge'      => $knowledge,
        ]);
    }

    public function storeKnowledge()
    {
        if (!$this->validateKnowledge()) {
            return redirect()
                ->to('/admin/chatbot#chatbot-knowledge')
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $question = trim((string) $this->request->getPost('question'));

        $knowledgeId = (int) $this->knowledgeModel->insert([
            'question'   => mb_substr($question, 0, 255),
            'keywords'   => $this->normalizeKeywords((string) $this->request->getPost('keywords')),
            'answer'     => trim((string) $this->request->getPost('answer')),
            'status'     => (string) $this->request->getPost('status'),
            'created_by' => session()->get('admin_user_id'),
        ], true);
        $this->refreshEmbeddingQuietly($knowledgeId);

        log_admin_activity(
            'Chatbot Website',
            'Tambah Knowledge Manual',
            'Admin menambahkan jawaban proaktif chatbot: ' . mb_strimwidth($question, 0, 100, '...') . '.'
        );

        return redirect()
            ->to('/admin/chatbot#chatbot-knowledge')
            ->with('success', 'Jawaban manual berhasil ditambahkan dan siap digunakan chatbot.');
    }

    public function updateKnowledge($id)
    {
        $knowledge = $this->knowledgeModel->find($id);

        if (!$knowledge) {
            return redirect()
                ->to('/admin/chatbot#chatbot-knowledge')
                ->with('error', 'Pengetahuan chatbot tidak ditemukan.');
        }

        if (!$this->validateKnowledge()) {
            return redirect()
                ->to('/admin/chatbot#knowledge-' . $id)
                ->withInput()
                ->with('edit_knowledge_id', (int) $id)
                ->with('errors', $this->validator->getErrors());
        }

        $question = trim((string) $this->request->getPost('question'));

        $this->knowledgeModel->update($id, [
            'question' => mb_substr($question, 0, 255),
            'keywords' => $this->normalizeKeywords((string) $this->request->getPost('keywords')),
            'answer'   => trim((string) $this->request->getPost('answer')),
            'status'   => (string) $this->request->getPost('status'),
        ]);
        $this->refreshEmbeddingQuietly((int) $id);

        log_admin_activity(
            'Chatbot Website',
            'Update Knowledge',
            'Admin memperbarui pengetahuan chatbot #' . $id . '.'
        );

        return redirect()
            ->to('/admin/chatbot#knowledge-' . $id)
            ->with('success', 'Jawaban chatbot berhasil diperbarui.');
    }

    public function toggleKnowledge($id)
    {
        $knowledge = $this->knowledgeModel->find($id);

        if (!$knowledge) {
            return redirect()
                ->to('/admin/chatbot#chatbot-knowledge')
                ->with('error', 'Pengetahuan chatbot tidak ditemukan.');
        }

        $newStatus = ($knowledge['status'] ?? 'inactive') === 'active' ? 'inactive' : 'active';
        $this->knowledgeModel->update($id, ['status' => $newStatus]);

        log_admin_activity(
            'Chatbot Website',
            $newStatus === 'active' ? 'Aktifkan Knowledge' : 'Nonaktifkan Knowledge',
            'Status pengetahuan chatbot #' . $id . ' diubah menjadi ' . $newStatus . '.'
        );

        return redirect()
            ->to('/admin/chatbot#knowledge-' . $id)
            ->with('success', $newStatus === 'active'
                ? 'Jawaban kembali aktif dan dapat digunakan chatbot.'
                : 'Jawaban dinonaktifkan dan tidak akan digunakan chatbot.');
    }

    public function conversation($id)
    {
        $chatSession = $this->sessionModel->find($id);

        if (!$chatSession) {
            return redirect()
                ->to('/admin/chatbot')
                ->with('error', 'Percakapan chatbot tidak ditemukan.');
        }

        $messages = $this->messageModel
            ->where('session_id', $id)
            ->orderBy('id', 'ASC')
            ->findAll();

        return view('admin/chatbot/conversation', [
            'title'       => 'Detail Percakapan Chatbot',
            'chatSession' => $chatSession,
            'messages'    => $messages,
        ]);
    }

    public function resolveUnanswered($id)
    {
        $unanswered = $this->unansweredModel->find($id);

        if (!$unanswered || ($unanswered['status'] ?? '') !== 'new') {
            return redirect()
                ->back()
                ->with('error', 'Pertanyaan tidak ditemukan atau sudah diproses.');
        }

        $answer = trim((string) $this->request->getPost('answer'));
        $keywords = $this->normalizeKeywords((string) $this->request->getPost('keywords'));

        if (mb_strlen($answer) < 5) {
            return redirect()
                ->back()
                ->with('error', 'Jawaban minimal 5 karakter.');
        }

        $knowledgeId = (int) $this->knowledgeModel->insert([
            'question'   => mb_substr((string) $unanswered['question'], 0, 255),
            'keywords'   => $keywords !== null ? $keywords : $this->normalizeKeywords((string) $unanswered['normalized_question']),
            'answer'     => $answer,
            'status'     => 'active',
            'created_by' => session()->get('admin_user_id'),
        ], true);
        $this->refreshEmbeddingQuietly($knowledgeId);

        $this->unansweredModel->update($id, [
            'status'      => 'resolved',
            'resolved_by' => session()->get('admin_user_id'),
            'resolved_at' => date('Y-m-d H:i:s'),
        ]);

        log_admin_activity(
            'Chatbot Website',
            'Tambah Knowledge',
            'Admin menambahkan jawaban chatbot dari pertanyaan tidak terjawab #' . $id . '.'
        );

        return redirect()
            ->to('/admin/chatbot#chatbot-knowledge')
            ->with('success', 'Jawaban berhasil ditambahkan ke pengetahuan chatbot.');
    }

    public function ignoreUnanswered($id)
    {
        $unanswered = $this->unansweredModel->find($id);

        if (!$unanswered) {
            return redirect()->back()->with('error', 'Pertanyaan tidak ditemukan.');
        }

        $this->unansweredModel->update($id, [
            'status'      => 'ignored',
            'resolved_by' => session()->get('admin_user_id'),
            'resolved_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect()
            ->to('/admin/chatbot')
            ->with('success', 'Pertanyaan ditandai diabaikan.');
    }

    public function deleteKnowledge($id)
    {
        if (session()->get('admin_role') !== 'super_admin') {
            return redirect()
                ->back()
                ->with('error', 'Hanya Super Admin yang dapat menghapus pengetahuan chatbot.');
        }

        $knowledge = $this->knowledgeModel->find($id);
        if (!$knowledge) {
            return redirect()->back()->with('error', 'Pengetahuan chatbot tidak ditemukan.');
        }

        $this->knowledgeModel->delete($id);

        log_admin_activity(
            'Chatbot Website',
            'Hapus Knowledge',
            'Pengetahuan chatbot #' . $id . ' dipindahkan ke sampah.'
        );

        return redirect()
            ->to('/admin/chatbot#chatbot-knowledge')
            ->with('success', 'Pengetahuan chatbot berhasil dihapus.');
    }

    public function rebuildEmbeddings()
    {
        if (session()->get('admin_role') !== 'super_admin') {
            return redirect()->back()->with('error', 'Hanya Super Admin yang dapat membangun ulang semantic index.');
        }

        $gemini = new GeminiService();
        if (!$gemini->isConfigured()) {
            return redirect()
                ->to('/admin/chatbot#chatbot-knowledge')
                ->with('error', 'GEMINI_API_KEY belum aktif. Semantic index belum dapat dibuat.');
        }

        $training = new ChatbotDocumentTrainingService($gemini);
        $rows = $this->knowledgeModel
            ->where('status', 'active')
            ->orderBy('id', 'DESC')
            ->findAll(150);

        $success = 0;
        $failed = 0;
        foreach ($rows as $row) {
            try {
                if ($training->refreshManualKnowledgeEmbedding((int) $row['id'])) {
                    $success++;
                } else {
                    $failed++;
                }
            } catch (Throwable $e) {
                $failed++;
                log_message('warning', 'Rebuild embedding #' . $row['id'] . ': ' . $e->getMessage());
            }
        }

        log_admin_activity(
            'Chatbot Website',
            'Bangun Ulang Semantic Index',
            $success . ' knowledge berhasil dan ' . $failed . ' gagal.'
        );

        return redirect()
            ->to('/admin/chatbot#chatbot-knowledge')
            ->with(
                $success > 0 ? 'success' : 'error',
                $success . ' knowledge berhasil diberi semantic index; ' . $failed . ' gagal.'
            );
    }

    private function validateKnowledge(): bool
    {
        return $this->validate([
            'question' => 'required|min_length[3]|max_length[255]',
            'keywords' => 'permit_empty|max_length[1500]',
            'answer'   => 'required|min_length[5]|max_length[5000]',
            'status'   => 'required|in_list[active,inactive]',
        ], [
            'question' => [
                'required'   => 'Pertanyaan acuan wajib diisi.',
                'min_length' => 'Pertanyaan acuan minimal 3 karakter.',
                'max_length' => 'Pertanyaan acuan maksimal 255 karakter.',
            ],
            'keywords' => [
                'max_length' => 'Variasi pertanyaan maksimal 1.500 karakter.',
            ],
            'answer' => [
                'required'   => 'Jawaban resmi wajib diisi.',
                'min_length' => 'Jawaban resmi minimal 5 karakter.',
                'max_length' => 'Jawaban resmi maksimal 5.000 karakter.',
            ],
            'status' => [
                'required' => 'Status jawaban wajib dipilih.',
                'in_list'  => 'Status jawaban tidak valid.',
            ],
        ]);
    }

    private function normalizeKeywords(string $keywords): ?string
    {
        $parts = preg_split('/[\r\n,;|]+/u', $keywords) ?: [];
        $parts = array_map(static fn(string $part): string => trim($part), $parts);
        $parts = array_values(array_unique(array_filter($parts, static fn(string $part): bool => $part !== '')));

        if ($parts === []) {
            return null;
        }

        return implode(', ', array_slice($parts, 0, 40));
    }

    private function refreshEmbeddingQuietly(int $knowledgeId): void
    {
        $db = \Config\Database::connect();
        if ($knowledgeId <= 0
            || !$db->fieldExists('embedding_json', 'chatbot_knowledge')) {
            return;
        }

        try {
            $gemini = new GeminiService();
            if (!$gemini->isConfigured()) {
                return;
            }

            (new ChatbotDocumentTrainingService($gemini))
                ->refreshManualKnowledgeEmbedding($knowledgeId);
        } catch (Throwable $e) {
            log_message('warning', 'Embedding knowledge manual gagal: ' . $e->getMessage());
        }
    }
}
