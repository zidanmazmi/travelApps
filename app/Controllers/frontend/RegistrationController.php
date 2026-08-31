<?php

namespace App\Controllers\Frontend;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\PackageModel;
use App\Models\PackageDepartureModel;
use App\Models\RegistrationModel;
use App\Models\PilgrimModel;

class RegistrationController extends BaseController
{
    public function create($slug)
    {
        if (!session()->get('jamaah_logged_in')) {
            session()->set('redirect_after_login', '/daftar/' . $slug);

            return redirect()
                ->to('/login')
                ->with('error', 'Silakan login atau register terlebih dahulu untuk mendaftar paket.');
        }

        $userModel = new UserModel();
        $packageModel = new PackageModel();
        $departureModel = new PackageDepartureModel();

        $user = $userModel->find(session()->get('jamaah_user_id'));
        $package = $packageModel->getPackageBySlug($slug);

        if (!$package) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Paket tidak ditemukan');
        }

        if (!$user) {
            return redirect()
                ->to('/login')
                ->with('error', 'Sesi login tidak valid. Silakan login ulang.');
        }

        if ((int) $user['is_email_verified'] !== 1) {
            session()->set('pending_verify_email', $user['email']);

            return redirect()
                ->to('/verifikasi-email')
                ->with('error', 'Silakan verifikasi email terlebih dahulu.');
        }

        $departures = $departureModel->getAvailableByPackage($package['id']);

        return view('frontend/registration/create', [
            'title'      => 'Daftar Jamaah - ' . site_setting('site_name', 'Travel Umroh & Haji'),
            'package'    => $package,
            'user'       => $user,
            'departures' => $departures,
        ]);
    }

    public function store()
    {
        if (!session()->get('jamaah_logged_in')) {
            return redirect()
                ->to('/login')
                ->with('error', 'Silakan login terlebih dahulu.');
        }

        $rules = [
            'package_id'    => 'required|numeric',
            'departure_id'  => 'required|numeric',
            'full_name'     => 'required|min_length[3]',
            'nik'           => 'required|numeric|exact_length[16]',
            'email'         => 'required|valid_email',
            'phone'         => 'required|min_length[10]',
            'address'       => 'required',
        ];

        $messages = [
            'departure_id' => [
                'required' => 'Tanggal keberangkatan wajib dipilih.',
                'numeric'  => 'Tanggal keberangkatan tidak valid.',
            ],
            'nik' => [
                'exact_length' => 'NIK harus 16 digit.',
            ],
        ];

        if (!$this->validate($rules, $messages)) {
            return redirect()
                ->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $userModel = new UserModel();
        $packageModel = new PackageModel();
        $departureModel = new PackageDepartureModel();
        $registrationModel = new RegistrationModel();
        $pilgrimModel = new PilgrimModel();

        $user = $userModel->find(session()->get('jamaah_user_id'));
        $package = $packageModel->find($this->request->getPost('package_id'));
        $departure = $departureModel->find($this->request->getPost('departure_id'));

        if (!$user || !$package) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Data user atau paket tidak ditemukan.');
        }

        if (!$departure) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Jadwal keberangkatan tidak ditemukan.');
        }

        if ((int) $departure['package_id'] !== (int) $package['id']) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Jadwal keberangkatan tidak sesuai dengan paket yang dipilih.');
        }

        if (($departure['status'] ?? '') !== 'available') {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Jadwal keberangkatan ini tidak tersedia.');
        }

        $quota = (int) ($departure['quota'] ?? 0);
        $booked = (int) ($departure['booked'] ?? 0);
        $totalParticipants = 1;

        if ($quota > 0 && ($booked + $totalParticipants) > $quota) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Kuota keberangkatan sudah penuh.');
        }

        if ($this->request->getPost('nik') !== $user['nik']) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'NIK tidak sesuai dengan akun yang sedang login.');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $registrationNo = $registrationModel->generateRegistrationNo();

        $registrationId = $registrationModel->insert([
            'user_id'             => $user['id'],
            'registration_no'     => $registrationNo,
            'package_id'          => $package['id'],
            'departure_id'        => $departure['id'],
            'total_participants'  => $totalParticipants,
            'total_amount'        => $package['price'],
            'registration_status' => 'Menunggu Verifikasi',
            'payment_status'      => 'Belum Bayar',
            'note'                => $this->request->getPost('note'),
        ], true);

        $pilgrimModel->insert([
            'registration_id' => $registrationId,
            'is_leader'       => 1,
            'full_name'       => $this->request->getPost('full_name'),
            'nik'             => $this->request->getPost('nik'),
            'birth_place'     => $this->request->getPost('birth_place'),
            'birth_date'      => $this->request->getPost('birth_date') ?: null,
            'gender'          => $this->request->getPost('gender') ?: null,
            'phone'           => $this->request->getPost('phone'),
            'email'           => $this->request->getPost('email'),
            'address'         => $this->request->getPost('address'),
            'passport_number' => $this->request->getPost('passport_number'),
        ]);

        $newBooked = $booked + $totalParticipants;

        $departureUpdate = [
            'booked' => $newBooked,
        ];

        if ($quota > 0 && $newBooked >= $quota) {
            $departureUpdate['status'] = 'full';
        }

        $departureModel->update($departure['id'], $departureUpdate);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Pendaftaran gagal disimpan. Silakan coba lagi.');
        }
        if (function_exists('create_notification')) {
            create_notification(
                'Pendaftaran Baru',
                'Jamaah baru telah melakukan pendaftaran paket.',
                'registration',
                (int) $registrationId
            );
        }
        $this->sendRegistrationSuccessEmail($registrationNo);
        return redirect()->to('/daftar/berhasil/' . $registrationNo);
    }

    public function success($registrationNo)
    {
        if (!session()->get('jamaah_logged_in')) {
            return redirect()->to('/login');
        }

        $registrationModel = new RegistrationModel();
        $pilgrimModel = new PilgrimModel();

        $registration = $registrationModel->getByRegistrationNo($registrationNo);

        if (!$registration) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Pendaftaran tidak ditemukan');
        }

        if ((int) $registration['user_id'] !== (int) session()->get('jamaah_user_id')) {
            return redirect()
                ->to('/')
                ->with('error', 'Anda tidak memiliki akses ke data pendaftaran ini.');
        }

        $pilgrims = $pilgrimModel->getByRegistration($registration['id']);

        $data = [
            'title'        => 'Pendaftaran Berhasil - ' . site_setting('site_name', 'Travel Umroh & Haji'),
            'registration' => $registration,
            'pilgrims'     => $pilgrims,
        ];

        return view('frontend/registration/success', $data);
    }

    private function getPackages()
    {
        return [
            'paket-umrah-reguler' => [
                'name'  => 'Paket Umrah Reguler',
                'price' => 'Rp 28.500.000',
            ],
            'paket-umrah-plus' => [
                'name'  => 'Paket Umrah Plus',
                'price' => 'Rp 42.000.000',
            ],
            'haji-khusus' => [
                'name'  => 'Haji Khusus',
                'price' => 'USD 15.500',
            ],
        ];
    }

    private function getRegistrationDetailForEmail($registrationNo)
    {
        $db = \Config\Database::connect();

        return $db->table('registrations')
            ->select("
            registrations.*,
            packages.name AS package_name,
            packages.slug AS package_slug,
            package_departures.departure_date,
            package_departures.return_date,
            users.name AS user_name,
            users.email AS user_email
        ", false)
            ->join('packages', 'packages.id = registrations.package_id', 'left')
            ->join('package_departures', 'package_departures.id = registrations.departure_id', 'left')
            ->join('users', 'users.id = registrations.user_id', 'left')
            ->where('registrations.registration_no', $registrationNo)
            ->get()
            ->getRowArray();
    }

    private function sendRegistrationSuccessEmail($registrationNo): bool
    {
        $registration = $this->getRegistrationDetailForEmail($registrationNo);

        if (!$registration || empty($registration['user_email'])) {
            return false;
        }

        try {
            $email = \Config\Services::email();

            $email->setTo($registration['user_email']);
            $email->setSubject('Pendaftaran Jamaah Berhasil - ' . site_setting('site_name', 'Travel Umroh & Haji'));
            $email->setMessage(view('emails/registration_success', [
                'registration' => $registration,
            ]));

            return $email->send(false);
        } catch (\Throwable $e) {
            log_message('error', 'Gagal kirim email pendaftaran berhasil: ' . $e->getMessage());
            return false;
        }
    }
}
