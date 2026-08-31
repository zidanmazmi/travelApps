<?php

namespace App\Controllers\Frontend;

use App\Controllers\BaseController;
use App\Models\LeadActivityModel;
use App\Models\LeadModel;
use App\Models\PackageDepartureModel;
use App\Models\PackageModel;

class LeadController extends BaseController
{
    public function store(string $slug)
    {
        $packageModel   = new PackageModel();
        $departureModel = new PackageDepartureModel();
        $leadModel      = new LeadModel();
        $activityModel  = new LeadActivityModel();

        $package = $packageModel->getPackageBySlug($slug);

        if (!$package) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Paket tidak ditemukan.');
        }

        $rules = [
            'full_name'          => 'required|min_length[3]|max_length[150]',
            'phone'              => 'required|min_length[9]|max_length[30]',
            'email'              => 'permit_empty|valid_email|max_length[150]',
            'city'               => 'permit_empty|max_length[100]',
            'total_participants' => 'required|is_natural_no_zero|less_than_equal_to[20]',
            'departure_id'       => 'permit_empty|is_natural_no_zero',
            'notes'              => 'permit_empty|max_length[1000]',
        ];

        if (!$this->validate($rules)) {
            return redirect()
                ->to('/paket/' . $slug . '#form-minat')
                ->withInput()
                ->with('lead_errors', $this->validator->getErrors());
        }

        $departureId = $this->request->getPost('departure_id');
        $departure   = null;

        if (!empty($departureId)) {
            $departure = $departureModel
                ->where('id', (int) $departureId)
                ->where('package_id', (int) $package['id'])
                ->where('status', 'available')
                ->first();

            if (!$departure) {
                return redirect()
                    ->to('/paket/' . $slug . '#form-minat')
                    ->withInput()
                    ->with('error', 'Jadwal keberangkatan yang dipilih tidak tersedia.');
            }
        }

        $cleanPhone = preg_replace('/[^0-9+]/', '', (string) $this->request->getPost('phone'));
        $leadNo     = $leadModel->generateLeadNo();

        $leadId = $leadModel->insert([
            'lead_no'            => $leadNo,
            'package_id'         => (int) $package['id'],
            'departure_id'       => $departure ? (int) $departure['id'] : null,
            'full_name'          => trim((string) $this->request->getPost('full_name')),
            'phone'              => $cleanPhone,
            'email'              => trim((string) $this->request->getPost('email')) ?: null,
            'city'               => trim((string) $this->request->getPost('city')) ?: null,
            'total_participants' => (int) $this->request->getPost('total_participants'),
            'notes'              => trim((string) $this->request->getPost('notes')) ?: null,
            'source'             => 'website-package-detail',
            'status'             => 'new',
            'lead_temperature'   => 'warm',
            'whatsapp_clicked_at'=> date('Y-m-d H:i:s'),
        ], true);

        if (!$leadId) {
            return redirect()
                ->to('/paket/' . $slug . '#form-minat')
                ->withInput()
                ->with('error', 'Data minat belum berhasil disimpan. Silakan coba kembali.');
        }

        $activityModel->record(
            (int) $leadId,
            'lead_created',
            'Lead dibuat dari halaman detail paket dan diarahkan ke WhatsApp.'
        );

        if (function_exists('create_notification')) {
            create_notification(
                'Lead Jamaah Baru',
                trim((string) $this->request->getPost('full_name')) . ' tertarik dengan paket ' . ($package['name'] ?? '-'),
                'lead',
                (int) $leadId
            );
        }

        $priceLabel = (float) ($package['price'] ?? 0) > 0
            ? 'Rp ' . number_format((float) $package['price'], 0, ',', '.')
            : 'Hubungi Admin';

        $scheduleLabel = 'Belum memilih jadwal';

        if ($departure) {
            $scheduleLabel = !empty($departure['departure_date'])
                ? date('d M Y', strtotime($departure['departure_date']))
                : 'Jadwal tersedia';

            if (!empty($departure['return_date'])) {
                $scheduleLabel .= ' - ' . date('d M Y', strtotime($departure['return_date']));
            }
        }

        $messageLines = [
            'Assalamualaikum ' . site_setting('site_name', 'Travel Umroh & Haji') . ',',
            '',
            'Saya tertarik dengan paket berikut:',
            '',
            'Nomor Lead: ' . $leadNo,
            'Nama Paket: ' . ($package['name'] ?? '-'),
            'Harga: ' . $priceLabel,
            'Jadwal: ' . $scheduleLabel,
            'Jumlah Jamaah: ' . (int) $this->request->getPost('total_participants') . ' orang',
            '',
            'Nama: ' . trim((string) $this->request->getPost('full_name')),
            'No. WhatsApp: ' . $cleanPhone,
            'Domisili: ' . (trim((string) $this->request->getPost('city')) ?: '-'),
        ];

        $notes = trim((string) $this->request->getPost('notes'));

        if ($notes !== '') {
            $messageLines[] = 'Catatan: ' . $notes;
        }

        $messageLines[] = '';
        $messageLines[] = 'Mohon informasi lebih lanjut mengenai paket tersebut. Terima kasih.';

        $whatsappNumber = preg_replace('/[^0-9]/', '', site_setting('social_whatsapp', site_setting('site_whatsapp', '')));
        if (str_starts_with($whatsappNumber, '0')) {
            $whatsappNumber = '62' . substr($whatsappNumber, 1);
        }

        if ($whatsappNumber === '') {
            return redirect()->back()->with('success', 'Data minat berhasil disimpan. Admin akan menindaklanjuti Anda.');
        }

        $whatsappUrl = 'https://wa.me/' . $whatsappNumber . '?text=' . rawurlencode(implode("\n", $messageLines));

        return redirect()->to($whatsappUrl);
    }
}
