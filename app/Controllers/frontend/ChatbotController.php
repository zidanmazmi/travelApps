<?php

namespace App\Controllers\Frontend;

use App\Controllers\BaseController;
use App\Models\LeadActivityModel;
use App\Models\LeadModel;
use App\Services\ChatbotService;

class ChatbotController extends BaseController
{
    public function message()
    {
        if (site_setting('chatbot_enabled', '1') !== '1') {
            return $this->response
                ->setStatusCode(503)
                ->setJSON([
                    'success' => false,
                    'message' => 'Chatbot sedang tidak aktif.',
                ]);
        }

        if (!$this->allowRequest()) {
            return $this->response
                ->setStatusCode(429)
                ->setJSON([
                    'success' => false,
                    'message' => 'Terlalu banyak pesan. Silakan tunggu sebentar lalu coba lagi.',
                ]);
        }

        $payload = $this->request->getJSON(true);
        if (!is_array($payload)) {
            $payload = $this->request->getPost();
        }

        $message = trim((string) ($payload['message'] ?? ''));

        if ($message === '') {
            return $this->response
                ->setStatusCode(422)
                ->setJSON([
                    'success' => false,
                    'message' => 'Pesan tidak boleh kosong.',
                ]);
        }

        try {
            $service = new ChatbotService();
            $result = $service->processMessage(
                $message,
                $payload['session_token'] ?? null,
                $payload['source_page'] ?? (string) $this->request->getUri(),
                $this->request->getIPAddress(),
                $this->request->getUserAgent()->getAgentString()
            );

            return $this->response->setJSON([
                'success' => true,
                'data'    => $result,
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Chatbot message error: ' . $e->getMessage());

            return $this->response
                ->setStatusCode(500)
                ->setJSON([
                    'success' => false,
                    'message' => 'Chatbot sedang mengalami kendala. Silakan coba kembali.',
                ]);
        }
    }

    public function history($token)
    {
        $token = preg_replace('/[^a-zA-Z0-9_-]/', '', (string) $token);

        if ($token === '') {
            return $this->response->setJSON(['success' => true, 'data' => []]);
        }

        $db = \Config\Database::connect();
        $chatSession = $db->table('chatbot_sessions')
            ->where('session_token', $token)
            ->get()
            ->getRowArray();

        if (!$chatSession) {
            return $this->response->setJSON(['success' => true, 'data' => []]);
        }

        $messages = $db->table('chatbot_messages')
            ->where('session_id', $chatSession['id'])
            ->orderBy('id', 'DESC')
            ->limit(30)
            ->get()
            ->getResultArray();

        $messages = array_reverse($messages);
        foreach ($messages as &$message) {
            $message['metadata'] = !empty($message['metadata_json'])
                ? json_decode($message['metadata_json'], true)
                : null;
            unset($message['metadata_json']);
        }
        unset($message);

        return $this->response->setJSON([
            'success' => true,
            'data'    => $messages,
        ]);
    }

    public function createLead()
    {
        if (!$this->allowRequest()) {
            return $this->response
                ->setStatusCode(429)
                ->setJSON([
                    'success' => false,
                    'message' => 'Terlalu banyak permintaan. Silakan tunggu sebentar lalu coba lagi.',
                ]);
        }

        if (site_setting('chatbot_enabled', '1') !== '1') {
            return $this->response
                ->setStatusCode(503)
                ->setJSON([
                    'success' => false,
                    'message' => 'Chatbot sedang tidak aktif.',
                ]);
        }

        $payload = $this->request->getJSON(true);
        if (!is_array($payload)) {
            $payload = $this->request->getPost();
        }

        $rules = [
            'full_name'          => 'required|min_length[3]|max_length[150]',
            'phone'              => 'required|min_length[9]|max_length[30]',
            'city'               => 'permit_empty|max_length[100]',
            'total_participants' => 'required|integer|greater_than_equal_to[1]|less_than_equal_to[50]',
            'package_id'         => 'permit_empty|integer',
            'departure_id'       => 'permit_empty|integer',
        ];

        if (!$this->validateData($payload, $rules)) {
            return $this->response
                ->setStatusCode(422)
                ->setJSON([
                    'success' => false,
                    'message' => 'Mohon lengkapi data calon jamaah dengan benar.',
                    'errors'  => $this->validator->getErrors(),
                ]);
        }

        $db = \Config\Database::connect();
        $packageId = !empty($payload['package_id']) ? (int) $payload['package_id'] : null;
        $departureId = !empty($payload['departure_id']) ? (int) $payload['departure_id'] : null;
        $participants = max(1, min((int) $payload['total_participants'], 50));

        $package = null;
        if ($packageId !== null) {
            $package = $db->table('packages')
                ->where('id', $packageId)
                ->where('status', 'active')
                ->where('deleted_at', null)
                ->get()
                ->getRowArray();

            if (!$package) {
                return $this->response
                    ->setStatusCode(404)
                    ->setJSON([
                        'success' => false,
                        'message' => 'Paket yang dipilih sudah tidak tersedia.',
                    ]);
            }
        }

        $departure = null;
        if ($departureId !== null) {
            $departure = $db->table('package_departures')
                ->where('id', $departureId)
                ->where('status', 'available')
                ->get()
                ->getRowArray();

            if (!$departure || ($packageId !== null && (int) $departure['package_id'] !== $packageId)) {
                return $this->response
                    ->setStatusCode(404)
                    ->setJSON([
                        'success' => false,
                        'message' => 'Jadwal yang dipilih sudah tidak tersedia.',
                    ]);
            }

            $remaining = max((int) $departure['quota'] - (int) $departure['booked'], 0);
            if ($remaining < $participants) {
                return $this->response
                    ->setStatusCode(422)
                    ->setJSON([
                        'success' => false,
                        'message' => 'Sisa seat saat ini tidak cukup untuk ' . $participants . ' jamaah.',
                    ]);
            }
        }

        $leadModel = new LeadModel();
        $activityModel = new LeadActivityModel();
        $chatbotService = new ChatbotService();
        $chatSession = null;

        if (!empty($payload['session_token'])) {
            $chatSession = $chatbotService->findSessionByToken((string) $payload['session_token']);
        }

        $leadNo = $leadModel->generateLeadNo();
        $notes = trim((string) ($payload['notes'] ?? ''));
        if ($chatSession) {
            $notes .= ($notes !== '' ? "\n" : '') . 'Sumber percakapan chatbot #' . $chatSession['id'];
        }

        $leadId = $leadModel->insert([
            'lead_no'            => $leadNo,
            'package_id'         => $packageId,
            'departure_id'       => $departureId,
            'full_name'          => trim((string) $payload['full_name']),
            'phone'              => trim((string) $payload['phone']),
            'email'              => !empty($payload['email']) ? trim((string) $payload['email']) : null,
            'city'               => !empty($payload['city']) ? trim((string) $payload['city']) : null,
            'total_participants' => $participants,
            'notes'              => $notes !== '' ? $notes : null,
            'source'             => 'chatbot',
            'status'             => 'new',
            'lead_temperature'   => $participants >= 5 ? 'hot' : 'warm',
            'whatsapp_clicked_at'=> date('Y-m-d H:i:s'),
        ], true);

        $activityModel->insert([
            'lead_id'       => $leadId,
            'admin_id'      => null,
            'activity_type' => 'created',
            'description'   => 'Lead dibuat melalui chatbot website.',
            'old_status'    => null,
            'new_status'    => 'new',
            'created_at'    => date('Y-m-d H:i:s'),
        ]);

        if ($chatSession) {
            $chatbotService->attachLeadIdentity(
                (int) $chatSession['id'],
                trim((string) $payload['full_name']),
                trim((string) $payload['phone'])
            );

            $chatbotService->saveSystemMessage(
                (int) $chatSession['id'],
                'Data minat berhasil disimpan dengan nomor ' . $leadNo . '. Admin akan membantu menindaklanjuti kebutuhan Anda.',
                'lead_created',
                ['lead_id' => $leadId, 'lead_no' => $leadNo]
            );
        }

        if (function_exists('create_notification')) {
            create_notification(
                'Lead Chatbot Baru',
                trim((string) $payload['full_name']) . ' mengirim minat paket melalui chatbot.',
                'lead',
                (int) $leadId
            );
        }

        $siteName = site_setting('site_name', 'Travel Umroh & Haji');
        $wa = preg_replace('/[^0-9]/', '', site_setting('social_whatsapp', site_setting('site_whatsapp', '')));
        if (str_starts_with($wa, '0')) {
            $wa = '62' . substr($wa, 1);
        }

        $messageLines = [
            'Assalamualaikum ' . $siteName . ',',
            '',
            'Saya sudah berkonsultasi melalui chatbot website dan tertarik dengan:',
            'Nomor Lead: ' . $leadNo,
            'Nama: ' . trim((string) $payload['full_name']),
            'No. WhatsApp: ' . trim((string) $payload['phone']),
            'Jumlah Jamaah: ' . $participants . ' orang',
        ];

        if ($package) {
            $messageLines[] = 'Paket: ' . $package['name'];
            $messageLines[] = 'Harga: Rp ' . number_format((float) $package['price'], 0, ',', '.');
        }

        if ($departure && !empty($departure['departure_date'])) {
            $messageLines[] = 'Jadwal: ' . date('d M Y', strtotime($departure['departure_date']));
        }

        if (!empty($payload['city'])) {
            $messageLines[] = 'Domisili: ' . trim((string) $payload['city']);
        }

        $messageLines[] = '';
        $messageLines[] = 'Mohon dibantu informasi dan tindak lanjutnya. Terima kasih.';

        $whatsappUrl = $wa !== ''
            ? 'https://wa.me/' . $wa . '?text=' . rawurlencode(implode("\n", $messageLines))
            : null;

        return $this->response->setJSON([
            'success' => true,
            'data' => [
                'lead_id'      => $leadId,
                'lead_no'      => $leadNo,
                'message'      => 'Data minat berhasil disimpan. Admin ' . site_setting('site_name', 'Travel Umroh & Haji') . ' akan menindaklanjuti Anda.',
                'whatsapp_url' => $whatsappUrl,
            ],
        ]);
    }

    private function allowRequest(): bool
    {
        $session = session();
        $now = time();
        $windowStart = (int) $session->get('chatbot_rate_window');
        $count = (int) $session->get('chatbot_rate_count');

        if ($windowStart === 0 || ($now - $windowStart) > 300) {
            $session->set([
                'chatbot_rate_window' => $now,
                'chatbot_rate_count'  => 1,
            ]);
            return true;
        }

        if ($count >= 40) {
            return false;
        }

        $session->set('chatbot_rate_count', $count + 1);
        return true;
    }
}
