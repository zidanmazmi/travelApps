<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class AuthController extends BaseController
{
    public function login()
    {
        if (session()->get('admin_logged_in')) {
            return redirect()->to('/admin/dashboard');
        }

        return view('admin/auth/login', [
            'title' => 'Login Admin - ' . site_setting('site_name', 'Travel Umroh & Haji'),
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
                ->with('error', 'Email dan password wajib diisi dengan benar.');
        }

        $email    = trim($this->request->getPost('email'));
        $password = $this->request->getPost('password');

        $db = \Config\Database::connect();

        $admin = $db->table('users')
            ->where('email', $email)
            ->whereIn('role', ['super_admin', 'admin'])
            ->where('deleted_at', null)
            ->get()
            ->getRowArray();

        if (!$admin) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Email atau password admin salah.');
        }

        if (!password_verify($password, $admin['password'])) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Email atau password admin salah.');
        }

        $status = strtolower(trim($admin['status'] ?? ''));

        if ($status === 'blocked') {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Akun admin diblokir. Silakan hubungi Super Admin.');
        }

        if ($status === 'inactive') {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Akun admin sedang tidak aktif.');
        }

        if ($status !== 'active') {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Status akun admin tidak valid. Silakan hubungi Super Admin.');
        }

        $db->table('users')
            ->where('id', $admin['id'])
            ->update([
                'last_login' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

        session()->set([
            'admin_logged_in' => true,
            'admin_user_id'   => $admin['id'],
            'admin_name'      => $admin['name'],
            'admin_email'     => $admin['email'],
            'admin_role'      => $admin['role'],

            'user_id'         => $admin['id'],
            'user_name'       => $admin['name'],
            'user_email'      => $admin['email'],
            'user_role'       => $admin['role'],
        ]);

        log_admin_activity(
            'Authentication',
            'Login',
            'Admin login ke sistem.'
        );
        return redirect()
            ->to('/admin/dashboard')
            ->with('success', 'Berhasil login sebagai admin.');
    }

    public function logout()
    {
        log_admin_activity(
            'Authentication',
            'Logout',
            'Admin logout dari sistem.'
        );
        session()->remove([
            'admin_logged_in',
            'admin_user_id',
            'admin_name',
            'admin_email',
            'admin_role',

            'user_id',
            'user_name',
            'user_email',
            'user_role',
        ]);

        return redirect()
            ->to('/')
            ->with('success', 'Anda berhasil logout.');
    }
}
