<?php

namespace App\Controllers\Frontend;

use App\Controllers\BaseController;
use App\Models\RegistrationModel;
use App\Models\PaymentModel;

class JamaahDashboardController extends BaseController
{
    public function index()
    {
        if (!session()->get('jamaah_logged_in')) {
            return redirect()
                ->to('/login')
                ->with('error', 'Silakan login terlebih dahulu.');
        }

        $registrationModel = new RegistrationModel();
        $paymentModel      = new PaymentModel();

        $userId = session()->get('jamaah_user_id');

        $registrations = $registrationModel->getByUser($userId);

        foreach ($registrations as &$registration) {
            $registration['payment'] = $paymentModel->getByRegistration($registration['id']);
        }

        $data = [
            'title'         => 'Dashboard Jamaah - ' . site_setting('site_name', 'Travel Umroh & Haji'),
            'registrations' => $registrations,
        ];

        return view('frontend/jamaah/dashboard', $data);
    }
}