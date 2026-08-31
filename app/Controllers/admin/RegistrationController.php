<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\RegistrationModel;
use App\Models\PilgrimModel;
use App\Models\PaymentModel;

class RegistrationController extends BaseController
{
    public function index()
    {
        $registrationModel = new RegistrationModel();

        $registrations = $registrationModel
            ->select("
            registrations.*,
            packages.name AS package_name,
            users.name AS user_name,
            users.email AS user_email,
            package_departures.departure_date,
            package_departures.return_date
        ", false)
            ->join('packages', 'packages.id = registrations.package_id', 'left')
            ->join('users', 'users.id = registrations.user_id', 'left')
            ->join('package_departures', 'package_departures.id = registrations.departure_id', 'left')
            ->orderBy('registrations.id', 'DESC')
            ->findAll();

        return view('admin/registrations/index', [
            'title'         => 'Data Pendaftaran Jamaah',
            'registrations' => $registrations,
        ]);
    }

    public function detail($id)
    {
        $registrationModel = new RegistrationModel();
        $pilgrimModel      = new PilgrimModel();
        $paymentModel      = new PaymentModel();

        $registration = $registrationModel
            ->select("
        registrations.*,
        packages.name AS package_name,
        packages.slug AS package_slug,
        users.name AS user_name,
        users.email AS user_email,
        users.phone AS user_phone,
        users.nik AS user_nik,
        package_departures.departure_date,
        package_departures.return_date,
        package_departures.quota,
        package_departures.booked,
        package_departures.status AS departure_status
    ", false)
            ->join('packages', 'packages.id = registrations.package_id', 'left')
            ->join('users', 'users.id = registrations.user_id', 'left')
            ->join('package_departures', 'package_departures.id = registrations.departure_id', 'left')
            ->where('registrations.id', $id)
            ->first();

        if (!$registration) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Data pendaftaran tidak ditemukan');
        }

        $pilgrims = $pilgrimModel->getByRegistration($registration['id']);
        $payment  = $paymentModel->getByRegistration($registration['id']);

        return view('admin/registrations/detail', [
            'title'        => 'Detail Pendaftaran Jamaah',
            'registration' => $registration,
            'pilgrims'     => $pilgrims,
            'payment'      => $payment,
        ]);
    }

    public function updateStatus($id)
    {
        $registrationStatus = $this->request->getPost('registration_status');
        $paymentStatus      = $this->request->getPost('payment_status');

        $allowedRegistrationStatus = [
            'Menunggu Verifikasi',
            'Menunggu Kelengkapan Dokumen',
            'Menunggu Verifikasi Dokumen',
            'Revisi Dokumen',
            'Diproses',
            'Terverifikasi',
            'Ditolak',
            'Selesai',
        ];

        $allowedPaymentStatus = [
            'Belum Bayar',
            'Menunggu Pembayaran',
            'Menunggu Verifikasi',
            'Lunas',
            'Gagal',
        ];

        if (!in_array($registrationStatus, $allowedRegistrationStatus, true)) {
            return redirect()
                ->back()
                ->with('error', 'Status pendaftaran tidak valid.');
        }

        if (!in_array($paymentStatus, $allowedPaymentStatus, true)) {
            return redirect()
                ->back()
                ->with('error', 'Status pembayaran tidak valid.');
        }

        $registrationModel = new RegistrationModel();

        $registration = $registrationModel->find($id);

        if (!$registration) {
            return redirect()
                ->to('/admin/registrations')
                ->with('error', 'Data pendaftaran tidak ditemukan.');
        }

        $registrationModel->update($id, [
            'registration_status' => $registrationStatus,
            'payment_status'      => $paymentStatus,
        ]);

        log_admin_activity(
            'Pendaftaran Jamaah',
            'Update Status',
            'Admin mengubah status pendaftaran ID #' . $id . '.'
        );
        return redirect()
            ->back()
            ->with('success', 'Status pendaftaran berhasil diperbarui.');
    }
}
