<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ChatbotSourceAnswerModel;
use App\Models\ChatbotSourceModel;
use App\Models\PackageModel;
use App\Services\ChatbotDocumentTrainingService;
use App\Services\GeminiService;
use CodeIgniter\Exceptions\PageNotFoundException;
use Throwable;

class ChatbotSourceController extends BaseController
{
    private ChatbotSourceModel $sourceModel;
    private ChatbotSourceAnswerModel $answerModel;
    private ChatbotDocumentTrainingService $trainingService;
    private GeminiService $gemini;

    public function __construct()
    {
        $this->sourceModel = new ChatbotSourceModel();
        $this->answerModel = new ChatbotSourceAnswerModel();
        $this->gemini = new GeminiService();
        $this->trainingService = new ChatbotDocumentTrainingService($this->gemini);
    }

    public function index()
    {
        $db = \Config\Database::connect();
        $sources = $db->table('chatbot_sources')
            ->select('chatbot_sources.*, packages.name AS package_name')
            ->join('packages', 'packages.id = chatbot_sources.package_id', 'left')
            ->where('chatbot_sources.deleted_at', null)
            ->orderBy('chatbot_sources.id', 'DESC')
            ->limit(100)
            ->get()
            ->getResultArray();

        $stats = [
            'total'     => count($sources),
            'review'    => count(array_filter($sources, static fn(array $row): bool => ($row['status'] ?? '') === 'review')),
            'published' => count(array_filter($sources, static fn(array $row): bool => ($row['status'] ?? '') === 'published')),
            'failed'    => count(array_filter($sources, static fn(array $row): bool => ($row['status'] ?? '') === 'failed')),
        ];

        return view('admin/chatbot/sources/index', [
            'title'       => 'Document Knowledge Chatbot',
            'sources'     => $sources,
            'stats'       => $stats,
            'packages'    => (new PackageModel())->orderBy('name', 'ASC')->findAll(),
            'typeOptions' => ChatbotSourceModel::TYPE_OPTIONS,
            'apiStatus'   => $this->gemini->status(),
        ]);
    }

    public function upload()
    {
        if (!$this->gemini->isConfigured()) {
            return redirect()
                ->to('/admin/chatbot/sources')
                ->with('error', 'GEMINI_API_KEY belum aktif. Ikuti file PANDUAN-GEMINI-API.md terlebih dahulu.');
        }

        $documentType = (string) $this->request->getPost('document_type');
        if (!array_key_exists($documentType, ChatbotSourceModel::TYPE_OPTIONS)) {
            return redirect()->back()->withInput()->with('error', 'Jenis dokumen tidak valid.');
        }

        $files = $this->request->getFileMultiple('source_files');
        if (!is_array($files) || $files === []) {
            return redirect()->back()->withInput()->with('error', 'Pilih minimal satu gambar atau PDF.');
        }

        $packageId = (int) $this->request->getPost('package_id');
        if ($packageId <= 0) {
            $packageId = null;
        } elseif (!(new PackageModel())->find($packageId)) {
            return redirect()->back()->withInput()->with('error', 'Paket yang dipilih tidak ditemukan.');
        }

        $baseTitle = trim((string) $this->request->getPost('title'));
        $adminNotes = trim((string) $this->request->getPost('admin_notes'));
        $priority = max(1, min((int) $this->request->getPost('priority'), 100));
        $validFrom = $this->safeDate($this->request->getPost('valid_from'));
        $validUntil = $this->safeDate($this->request->getPost('valid_until'));

        if ($this->request->getPost('valid_from') && $validFrom === null) {
            return redirect()->back()->withInput()->with('error', 'Tanggal mulai berlaku tidak valid.');
        }
        if ($this->request->getPost('valid_until') && $validUntil === null) {
            return redirect()->back()->withInput()->with('error', 'Tanggal akhir berlaku tidak valid.');
        }
        if ($validFrom !== null && $validUntil !== null && $validUntil < $validFrom) {
            return redirect()->back()->withInput()->with('error', 'Tanggal akhir berlaku tidak boleh lebih awal.');
        }

        $uploadDirectory = WRITEPATH . 'uploads/chatbot_sources';
        if (!is_dir($uploadDirectory) && !mkdir($uploadDirectory, 0775, true) && !is_dir($uploadDirectory)) {
            return redirect()->back()->withInput()->with('error', 'Folder upload chatbot tidak dapat dibuat.');
        }

        $processed = 0;
        $failed = 0;
        $firstSourceId = null;
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'pdf'];
        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'];

        foreach (array_slice($files, 0, 10) as $index => $file) {
            if (!$file || !$file->isValid() || $file->hasMoved()) {
                $failed++;
                continue;
            }

            $extension = strtolower((string) $file->getClientExtension());
            $mimeType = strtolower((string) $file->getMimeType());
            $fileSize = (int) $file->getSize();

            if (!in_array($extension, $allowedExtensions, true)
                || !in_array($mimeType, $allowedMimes, true)
                || $fileSize <= 0
                || $fileSize > $this->gemini->maxDocumentBytes()) {
                $failed++;
                continue;
            }

            $originalName = mb_substr((string) $file->getClientName(), 0, 255);
            $newName = bin2hex(random_bytes(16)) . '.' . $extension;
            $file->move($uploadDirectory, $newName);

            $title = $baseTitle !== ''
                ? $baseTitle . (count($files) > 1 ? ' ' . ($index + 1) : '')
                : pathinfo($originalName, PATHINFO_FILENAME);

            $sourceId = (int) $this->sourceModel->insert([
                'package_id'    => $packageId,
                'title'         => mb_substr($title, 0, 255),
                'document_type' => $documentType,
                'original_name' => $originalName,
                'stored_path'   => 'uploads/chatbot_sources/' . $newName,
                'mime_type'     => $mimeType,
                'file_size'     => $fileSize,
                'status'        => 'uploaded',
                'valid_from'    => $validFrom,
                'valid_until'   => $validUntil,
                'priority'      => $priority,
                'admin_notes'   => $adminNotes !== '' ? $adminNotes : null,
                'created_by'    => session()->get('admin_user_id'),
            ], true);

            $firstSourceId ??= $sourceId;

            try {
                $this->trainingService->processSource($sourceId);
                $processed++;
            } catch (Throwable $e) {
                $failed++;
                log_message('error', 'Ekstraksi document knowledge gagal: ' . $e->getMessage());
            }
        }

        log_admin_activity(
            'Chatbot Website',
            'Upload Document Knowledge',
            $processed . ' dokumen berhasil diekstrak dan ' . $failed . ' dokumen gagal.'
        );

        if ($processed === 1 && $failed === 0 && $firstSourceId !== null) {
            return redirect()
                ->to('/admin/chatbot/sources/' . $firstSourceId)
                ->with('success', 'Dokumen berhasil dibaca. Periksa hasil ekstraksi sebelum dipublikasikan.');
        }

        $message = $processed . ' dokumen siap ditinjau.';
        if ($failed > 0) {
            $message .= ' ' . $failed . ' dokumen gagal; periksa format, ukuran, API key, atau kuota Gemini.';
        }

        return redirect()
            ->to('/admin/chatbot/sources')
            ->with($processed > 0 ? 'success' : 'error', $message);
    }

    public function show($id)
    {
        $source = $this->sourceModel->find((int) $id);
        if (!$source) {
            throw PageNotFoundException::forPageNotFound('Sumber dokumen tidak ditemukan.');
        }

        return view('admin/chatbot/sources/show', [
            'title'         => 'Tinjau Document Knowledge',
            'source'        => $source,
            'answers'       => $this->answerModel
                ->where('source_id', (int) $id)
                ->orderBy('confidence', 'DESC')
                ->orderBy('id', 'ASC')
                ->findAll(),
            'package'       => !empty($source['package_id'])
                ? (new PackageModel())->find((int) $source['package_id'])
                : null,
            'packages'      => (new PackageModel())->orderBy('name', 'ASC')->findAll(),
            'typeOptions'   => ChatbotSourceModel::TYPE_OPTIONS,
            'apiStatus'     => $this->gemini->status(),
            'structured'    => !empty($source['structured_json'])
                ? json_decode($source['structured_json'], true)
                : null,
        ]);
    }

    public function update($id)
    {
        $sourceId = (int) $id;
        $source = $this->sourceModel->find($sourceId);
        if (!$source) {
            return redirect()->to('/admin/chatbot/sources')->with('error', 'Sumber dokumen tidak ditemukan.');
        }

        if (($source['status'] ?? '') === 'published') {
            return redirect()
                ->to('/admin/chatbot/sources/' . $sourceId)
                ->with('error', 'Batalkan publikasi terlebih dahulu sebelum mengubah isi dokumen.');
        }

        $title = trim((string) $this->request->getPost('title'));
        $documentType = (string) $this->request->getPost('document_type');
        $structuredJson = trim((string) $this->request->getPost('structured_json'));
        $packageId = (int) $this->request->getPost('package_id');
        $packageId = $packageId > 0 ? $packageId : null;
        $validFrom = $this->safeDate($this->request->getPost('valid_from'));
        $validUntil = $this->safeDate($this->request->getPost('valid_until'));

        if (mb_strlen($title) < 3 || mb_strlen($title) > 255) {
            return redirect()->back()->withInput()->with('error', 'Judul dokumen harus 3–255 karakter.');
        }
        if (!array_key_exists($documentType, ChatbotSourceModel::TYPE_OPTIONS)) {
            return redirect()->back()->withInput()->with('error', 'Jenis dokumen tidak valid.');
        }
        if ($structuredJson !== '' && !is_array(json_decode($structuredJson, true))) {
            return redirect()->back()->withInput()->with('error', 'Structured JSON tidak valid.');
        }
        if ($packageId !== null && !(new PackageModel())->find($packageId)) {
            return redirect()->back()->withInput()->with('error', 'Paket yang dipilih tidak ditemukan.');
        }
        if ($this->request->getPost('valid_from') && $validFrom === null) {
            return redirect()->back()->withInput()->with('error', 'Tanggal mulai berlaku tidak valid.');
        }
        if ($this->request->getPost('valid_until') && $validUntil === null) {
            return redirect()->back()->withInput()->with('error', 'Tanggal akhir berlaku tidak valid.');
        }
        if ($validFrom !== null && $validUntil !== null && $validUntil < $validFrom) {
            return redirect()->back()->withInput()->with('error', 'Tanggal akhir berlaku tidak boleh lebih awal.');
        }

        $answers = $this->request->getPost('answers');
        $selectedIds = array_map('intval', (array) $this->request->getPost('selected_answers'));
        if (!is_array($answers)) {
            $answers = [];
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $this->sourceModel->update($sourceId, [
            'title'           => $title,
            'document_type'   => $documentType,
            'package_id'      => $packageId,
            'valid_from'      => $validFrom,
            'valid_until'     => $validUntil,
            'priority'        => max(1, min((int) $this->request->getPost('priority'), 100)),
            'admin_notes'     => trim((string) $this->request->getPost('admin_notes')) ?: null,
            'summary'         => trim((string) $this->request->getPost('summary')) ?: null,
            'extracted_text'  => trim((string) $this->request->getPost('extracted_text')) ?: null,
            'structured_json' => $structuredJson !== '' ? $structuredJson : null,
            'status'          => 'review',
        ]);

        foreach ($answers as $answerId => $answerData) {
            if (!is_array($answerData)) {
                continue;
            }

            $answerId = (int) $answerId;
            $existing = $this->answerModel
                ->where('id', $answerId)
                ->where('source_id', $sourceId)
                ->first();
            if (!$existing) {
                continue;
            }

            $question = trim((string) ($answerData['question'] ?? ''));
            $officialAnswer = trim((string) ($answerData['answer'] ?? ''));
            if (mb_strlen($question) < 3 || mb_strlen($officialAnswer) < 5) {
                continue;
            }

            $this->answerModel->update($answerId, [
                'question'    => mb_substr($question, 0, 255),
                'keywords'    => mb_substr(trim((string) ($answerData['keywords'] ?? '')), 0, 3000) ?: null,
                'answer'      => mb_substr($officialAnswer, 0, 10_000),
                'category'    => mb_substr(trim((string) ($answerData['category'] ?? 'other')), 0, 50),
                'program'     => $this->normalizeProgram($answerData['program'] ?? null),
                'is_selected' => in_array($answerId, $selectedIds, true) ? 1 : 0,
                'status'      => 'draft',
            ]);
        }

        $db->transComplete();

        if (!$db->transStatus()) {
            return redirect()
                ->to('/admin/chatbot/sources/' . $sourceId)
                ->with('error', 'Perubahan hasil review gagal disimpan.');
        }

        log_admin_activity(
            'Chatbot Website',
            'Review Document Knowledge',
            'Admin memperbarui hasil ekstraksi dokumen #' . $sourceId . '.'
        );

        return redirect()
            ->to('/admin/chatbot/sources/' . $sourceId)
            ->with('success', 'Hasil ekstraksi dan jawaban resmi berhasil disimpan.');
    }

    public function reprocess($id)
    {
        $sourceId = (int) $id;

        try {
            $result = $this->trainingService->processSource($sourceId);

            log_admin_activity(
                'Chatbot Website',
                'Proses Ulang Document Knowledge',
                'Dokumen #' . $sourceId . ' diproses ulang menjadi ' . $result['answer_count'] . ' jawaban.'
            );

            return redirect()
                ->to('/admin/chatbot/sources/' . $sourceId)
                ->with('success', 'Dokumen berhasil diproses ulang. Periksa kembali seluruh jawabannya.');
        } catch (Throwable $e) {
            return redirect()
                ->to('/admin/chatbot/sources/' . $sourceId)
                ->with('error', $e->getMessage());
        }
    }

    public function publish($id)
    {
        $sourceId = (int) $id;

        try {
            $result = $this->trainingService->publishSource(
                $sourceId,
                (int) session()->get('admin_user_id') ?: null,
                (bool) $this->request->getPost('replace_previous')
            );

            $message = $result['published'] . ' jawaban resmi berhasil dipublikasikan.';
            if ($result['embedding_failures'] > 0) {
                $message .= ' Beberapa semantic index gagal dibuat, tetapi jawaban tetap aktif.';
            }

            log_admin_activity(
                'Chatbot Website',
                'Publikasi Document Knowledge',
                'Dokumen #' . $sourceId . ' menerbitkan ' . $result['published'] . ' jawaban chatbot.'
            );

            return redirect()
                ->to('/admin/chatbot/sources/' . $sourceId)
                ->with('success', $message);
        } catch (Throwable $e) {
            return redirect()
                ->to('/admin/chatbot/sources/' . $sourceId)
                ->with('error', $e->getMessage());
        }
    }

    public function unpublish($id)
    {
        $sourceId = (int) $id;

        try {
            $this->trainingService->unpublishSource($sourceId);
            log_admin_activity(
                'Chatbot Website',
                'Batalkan Publikasi Document Knowledge',
                'Jawaban dari dokumen #' . $sourceId . ' dinonaktifkan.'
            );

            return redirect()
                ->to('/admin/chatbot/sources/' . $sourceId)
                ->with('success', 'Publikasi dibatalkan. Jawaban dari dokumen ini tidak lagi digunakan chatbot.');
        } catch (Throwable $e) {
            return redirect()
                ->to('/admin/chatbot/sources/' . $sourceId)
                ->with('error', $e->getMessage());
        }
    }

    public function delete($id)
    {
        if (session()->get('admin_role') !== 'super_admin') {
            return redirect()->back()->with('error', 'Hanya Super Admin yang dapat menghapus sumber dokumen.');
        }

        $sourceId = (int) $id;
        $source = $this->sourceModel->find($sourceId);
        if (!$source) {
            return redirect()->to('/admin/chatbot/sources')->with('error', 'Sumber dokumen tidak ditemukan.');
        }

        try {
            if (($source['status'] ?? '') === 'published') {
                $this->trainingService->unpublishSource($sourceId);
            }
            $this->sourceModel->delete($sourceId);

            log_admin_activity(
                'Chatbot Website',
                'Hapus Document Knowledge',
                'Sumber dokumen #' . $sourceId . ' dipindahkan ke sampah.'
            );

            return redirect()
                ->to('/admin/chatbot/sources')
                ->with('success', 'Sumber dokumen dipindahkan ke sampah. File fisik tetap disimpan untuk audit.');
        } catch (Throwable $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function file($id)
    {
        $source = $this->sourceModel->find((int) $id);
        if (!$source) {
            throw PageNotFoundException::forPageNotFound('File sumber tidak ditemukan.');
        }

        $storedPath = ltrim(str_replace(['\\', '..'], ['/', ''], (string) $source['stored_path']), '/');
        $path = WRITEPATH . $storedPath;
        $base = realpath(WRITEPATH . 'uploads/chatbot_sources');
        $realPath = realpath($path);
        $basePrefix = $base === false
            ? null
            : rtrim($base, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        if ($basePrefix === null
            || $realPath === false
            || !str_starts_with($realPath, $basePrefix)
            || !is_file($realPath)) {
            throw PageNotFoundException::forPageNotFound('File sumber tidak ditemukan.');
        }

        $downloadName = preg_replace(
            '/[\r\n"]+/',
            '',
            (string) $source['original_name']
        ) ?: 'document-knowledge';

        return $this->response
            ->setHeader('Content-Type', (string) $source['mime_type'])
            ->setHeader('Content-Disposition', 'inline; filename="' .
                $downloadName . '"')
            ->setHeader('X-Content-Type-Options', 'nosniff')
            ->setBody((string) file_get_contents($realPath));
    }

    private function safeDate($value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $value);
        if (!$date || $date->format('Y-m-d') !== $value) {
            return null;
        }

        return $value;
    }

    private function normalizeProgram($value): ?string
    {
        $program = mb_strtolower(trim((string) $value));

        return in_array($program, array_keys(PackageModel::getProgramOptions()), true)
            ? $program
            : null;
    }
}
