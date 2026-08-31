<?php

namespace App\Controllers\Frontend;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\EmailVerificationModel;

class AuthController extends BaseController
{
    public function login()
    {
        if (session()->get('admin_logged_in')) {
            return redirect()->to('/admin/dashboard');
        }

        if (session()->get('jamaah_logged_in')) {
            return redirect()->to('/');
        }

        return view('frontend/auth/login', [
            'title' => 'Login - ' . site_setting('site_name', 'Travel Umroh & Haji'),
        ]);
    }

    public function attemptLogin()
    {
        $rules = [
            'email'    => 'required|valid_email',
            'password' => 'required',
        ];

        if (!$this->validate($rules)) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Email dan password wajib diisi.');
        }

        $email    = trim($this->request->getPost('email'));
        $password = $this->request->getPost('password');

        $db = \Config\Database::connect();

        $user = $db->table('users')
            ->where('email', $email)
            ->where('deleted_at', null)
            ->get()
            ->getRowArray();

        if (!$user) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Email atau password salah.');
        }

        if (!password_verify($password, $user['password'])) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Email atau password salah.');
        }

        $role   = strtolower(trim($user['role'] ?? ''));
        $status = strtolower(trim($user['status'] ?? ''));

        if ($status !== 'active') {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Akun Anda tidak aktif atau diblokir.');
        }

        $db->table('users')
            ->where('id', $user['id'])
            ->update([
                'last_login' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

        if ($role === 'admin' || $role === 'super_admin') {
            session()->remove([
                'jamaah_logged_in',
                'jamaah_user_id',
                'jamaah_name',
                'jamaah_email',
                'jamaah_role',
                'user_id',
                'user_name',
                'user_email',
                'user_role',
            ]);

            session()->set([
                'admin_logged_in' => true,
                'admin_user_id'   => $user['id'],
                'admin_name'      => $user['name'],
                'admin_email'     => $user['email'],
                'admin_role'      => $role,
            ]);

            if (function_exists('log_admin_activity')) {
                log_admin_activity(
                    'Authentication',
                    'Login',
                    'Admin login melalui halaman login utama.'
                );
            }

            return redirect()
                ->to('/admin/dashboard')
                ->with('success', 'Berhasil login sebagai admin.');
        }

        if ($role === 'jamaah') {
            session()->remove([
                'admin_logged_in',
                'admin_user_id',
                'admin_name',
                'admin_email',
                'admin_role',
            ]);

            session()->set([
                'jamaah_logged_in' => true,
                'jamaah_user_id'   => $user['id'],
                'jamaah_name'      => $user['name'],
                'jamaah_email'     => $user['email'],
                'jamaah_role'      => $role,

                // fallback kalau controller jamaah lama masih membaca session user_*
                'user_id'          => $user['id'],
                'user_name'        => $user['name'],
                'user_email'       => $user['email'],
                'user_role'        => $role,
            ]);

            $redirectAfterLogin = session()->get('redirect_after_login');

            if (!empty($redirectAfterLogin)) {
                session()->remove('redirect_after_login');

                return redirect()
                    ->to($redirectAfterLogin)
                    ->with('success', 'Berhasil login.');
            }

            return redirect()
                ->to('/dashboard-jamaah')
                ->with('success', 'Berhasil login.');
        }

        return redirect()
            ->back()
            ->withInput()
            ->with('error', 'Role akun tidak dikenali.');
    }

    public function register()
    {
        if (session()->get('jamaah_logged_in')) {
            return redirect()->to('/');
        }

        return view('frontend/auth/register', [
            'title' => 'Register Jamaah - ' . site_setting('site_name', 'Travel Umroh & Haji'),
        ]);
    }

    public function storeRegister()
    {
        $rules = [
            'name'                  => 'required|min_length[3]',
            'nik'                   => 'required|numeric|exact_length[16]|is_unique[users.nik]',
            'email'                 => 'required|valid_email|is_unique[users.email]',
            'phone'                 => 'required|min_length[10]',
            'password'              => 'required|min_length[6]',
            'password_confirmation' => 'required|matches[password]',
        ];

        $messages = [
            'nik' => [
                'is_unique'    => 'NIK sudah terdaftar. Silakan login dengan akun yang sudah ada.',
                'exact_length' => 'NIK harus 16 digit.',
            ],
            'email' => [
                'is_unique' => 'Email sudah terdaftar. Silakan login.',
            ],
            'password_confirmation' => [
                'matches' => 'Konfirmasi password tidak sama.',
            ],
        ];

        if (!$this->validate($rules, $messages)) {
            return redirect()
                ->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $userModel = new UserModel();
        $verificationModel = new EmailVerificationModel();

        $email = $this->request->getPost('email');

        $userModel->insert([
            'name'              => $this->request->getPost('name'),
            'nik'               => $this->request->getPost('nik'),
            'email'             => $email,
            'phone'             => $this->request->getPost('phone'),
            'password'          => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
            'role'              => 'jamaah',
            'status'            => 'active',
            'is_email_verified' => 0,
        ]);

        $code = $verificationModel->createCode($email, 'registration');

        $emailSent = sendVerificationEmail($email, $code);

        session()->set('pending_verify_email', $email);

        if (!$emailSent) {
            return redirect()
                ->to('/verifikasi-email')
                ->with('error', 'Registrasi berhasil, tetapi email verifikasi gagal dikirim. Silakan cek konfigurasi SMTP.');
        }

        return redirect()
            ->to('/verifikasi-email')
            ->with('success', 'Registrasi berhasil. Kode verifikasi telah dikirim ke email Anda.');
    }

    public function verifyEmail()
    {
        $email = session()->get('pending_verify_email');

        if (!$email) {
            return redirect()->to('/login');
        }

        return view('frontend/auth/verify_email', [
            'title' => 'Verifikasi Email - ' . site_setting('site_name', 'Travel Umroh & Haji'),
            'email' => $email,
        ]);
    }

    public function checkVerification()
    {
        $email = session()->get('pending_verify_email');
        $code  = $this->request->getPost('verification_code');

        if (!$email) {
            return redirect()->to('/login');
        }

        if (!$code) {
            return redirect()
                ->back()
                ->with('error', 'Kode verifikasi wajib diisi.');
        }

        $verificationModel = new EmailVerificationModel();
        $userModel = new UserModel();

        $verification = $verificationModel->getValidCode($email, $code, 'registration');

        if (!$verification) {
            return redirect()
                ->back()
                ->with('error', 'Kode verifikasi salah atau sudah kedaluwarsa.');
        }

        $verificationModel->markAsVerified($verification['id']);

        $user = $userModel->where('email', $email)->first();

        if ($user) {
            $userModel->update($user['id'], [
                'is_email_verified'       => 1,
                'email_verified_at'       => date('Y-m-d H:i:s'),
                'email_verification_token' => null,
            ]);
        }

        session()->remove('pending_verify_email');

        return redirect()
            ->to('/login')
            ->with('success', 'Email berhasil diverifikasi. Silakan login.');
    }

    public function logout()
    {
        if (session()->get('admin_logged_in') && function_exists('log_admin_activity')) {
            log_admin_activity(
                'Authentication',
                'Logout',
                'Admin logout dari sistem.'
            );
        }

        session()->remove([
            'jamaah_logged_in',
            'jamaah_user_id',
            'jamaah_name',
            'jamaah_email',
            'jamaah_role',

            'admin_logged_in',
            'admin_user_id',
            'admin_name',
            'admin_email',
            'admin_role',

            'user_id',
            'user_name',
            'user_email',
            'user_role',

            'redirect_after_login',
        ]);

        return redirect()
            ->to('/')
            ->with('success', 'Anda berhasil logout.');
    }
}
