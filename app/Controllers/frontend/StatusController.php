<?php

namespace App\Controllers\Frontend;

use App\Controllers\BaseController;
use App\Models\RegistrationModel;
use App\Models\PilgrimModel;
use App\Models\PaymentModel;

class StatusController extends BaseController
{
    public function index()
    {
        return view('frontend/status/index', [
            'title' => 'Cek Status Pendaftaran - ' . site_setting('site_name', 'Travel Umroh & Haji'),
        ]);
    }

    public function check()
    {
        $registrationNo = trim($this->request->getPost('registration_no'));

        if (empty($registrationNo)) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Nomor pendaftaran wajib diisi.');
        }

        $registrationModel = new RegistrationModel();
        $pilgrimModel      = new PilgrimModel();
        $paymentModel      = new PaymentModel();

        $registration = $registrationModel->getByRegistrationNo($registrationNo);

        if (!$registration) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Nomor pendaftaran tidak ditemukan.');
        }

        $pilgrims = $pilgrimModel->getByRegistration($registration['id']);
        $payment  = $paymentModel->getByRegistration($registration['id']);

        return view('frontend/status/result', [
            'title'        => 'Hasil Cek Status - ' . site_setting('site_name', 'Travel Umroh & Haji'),
            'registration' => $registration,
            'pilgrims'     => $pilgrims,
            'payment'      => $payment,
        ]);
    }
}