<?php

namespace App\Controllers\Frontend;

use App\Controllers\BaseController;
use App\Models\RegistrationModel;
use App\Models\PaymentModel;
use App\Models\PilgrimModel;

class InvoiceController extends BaseController
{
    public function show($registrationNo)
    {
        if (!session()->get('jamaah_logged_in')) {
            return redirect()
                ->to('/login')
                ->with('error', 'Silakan login terlebih dahulu.');
        }

        $registrationModel = new RegistrationModel();
        $paymentModel      = new PaymentModel();
        $pilgrimModel      = new PilgrimModel();

        $registration = $registrationModel->getByRegistrationNo($registrationNo);

        if (!$registration) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Data pendaftaran tidak ditemukan');
        }

        if ((int) $registration['user_id'] !== (int) session()->get('jamaah_user_id')) {
            return redirect()
                ->to('/dashboard-jamaah')
                ->with('error', 'Anda tidak memiliki akses ke invoice ini.');
        }

        if (($registration['payment_status'] ?? '') !== 'Lunas') {
            return redirect()
                ->to('/pembayaran/' . $registrationNo)
                ->with('error', 'Invoice hanya tersedia setelah pembayaran lunas.');
        }

        $payment = $paymentModel->getByRegistration($registration['id']);

        if (!$payment) {
            return redirect()
                ->to('/dashboard-jamaah')
                ->with('error', 'Data pembayaran tidak ditemukan.');
        }

        $pilgrims = $pilgrimModel->getByRegistration($registration['id']);

        return view('frontend/invoices/show', [
            'title'        => 'Bukti Pembayaran - ' . site_setting('site_name', 'Travel Umroh & Haji'),
            'registration' => $registration,
            'payment'      => $payment,
            'pilgrims'     => $pilgrims,
        ]);
    }
}