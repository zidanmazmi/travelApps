<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class UnifiedAuthController extends BaseController
{
    public function login()
    {
        if (session()->get('admin_logged_in')) {
            return redirect()->to('/admin/dashboard');
        }

        if (session()->get('jamaah_logged_in')) {
            return redirect()->to('/dashboard-jamaah');
        }

        return view('/login', [
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

        if ($status === 'blocked') {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Akun Anda diblokir. Silakan hubungi admin.');
        }

        if ($status === 'inactive') {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Akun Anda sedang tidak aktif.');
        }

        if ($status !== 'active') {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Status akun tidak valid.');
        }

        if ($role === 'admin' || $role === 'super_admin') {
            return $this->loginAdmin($user);
        }

        if ($role === 'jamaah') {
            return $this->loginJamaah($user);
        }

        return redirect()
            ->back()
            ->withInput()
            ->with('error', 'Role akun tidak dikenali.');
    }

    private function loginAdmin(array $admin)
    {
        session()->set([
            'admin_logged_in' => true,
            'admin_user_id'   => $admin['id'],
            'admin_name'      => $admin['name'],
            'admin_email'     => $admin['email'],
            'admin_role'      => $admin['role'],
        ]);

        $db = \Config\Database::connect();

        $db->table('users')
            ->where('id', $admin['id'])
            ->update([
                'last_login' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
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

    private function loginJamaah(array $jamaah)
    {
        session()->set([
            'jamaah_logged_in' => true,
            'jamaah_user_id'   => $jamaah['id'],
            'jamaah_name'      => $jamaah['name'],
            'jamaah_email'     => $jamaah['email'],
            'jamaah_role'      => $jamaah['role'],

            // Fallback untuk controller jamaah lama kalau masih membaca session umum
            'user_id'          => $jamaah['id'],
            'user_name'        => $jamaah['name'],
            'user_email'       => $jamaah['email'],
            'user_role'        => $jamaah['role'],
        ]);

        $db = \Config\Database::connect();

        $db->table('users')
            ->where('id', $jamaah['id'])
            ->update([
                'last_login' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
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
            'admin_logged_in',
            'admin_user_id',
            'admin_name',
            'admin_email',
            'admin_role',

            'jamaah_logged_in',
            'jamaah_user_id',
            'jamaah_name',
            'jamaah_email',
            'jamaah_role',

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