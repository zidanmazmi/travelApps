<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AdminAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        $isAdminLoggedIn  = $session->get('admin_logged_in') === true;
        $adminRole        = $session->get('admin_role');

        $isJamaahLoggedIn = $session->get('jamaah_logged_in') === true
            || $session->get('user_role') === 'jamaah';

        /*
         * Kondisi 1:
         * User sudah login sebagai jamaah,
         * tapi memaksa akses halaman admin.
         */
        if ($isJamaahLoggedIn && !$isAdminLoggedIn) {
            return redirect()
                ->to('/')
                ->with('error', 'Anda sedang login sebagai jamaah. Halaman admin hanya untuk admin.');
        }

        /*
         * Kondisi 2:
         * User belum login sama sekali,
         * lalu akses /admin/dashboard atau route admin lain.
         */
        if (!$isAdminLoggedIn) {
            return redirect()
                ->to('/login')
                ->with('error', 'Silakan login terlebih dahulu untuk mengakses admin panel.');
        }

        /*
         * Kondisi 3:
         * Sudah login admin, tapi role tidak valid.
         */
        if (!in_array($adminRole, ['admin', 'super_admin'], true)) {
            $session->remove([
                'admin_logged_in',
                'admin_user_id',
                'admin_name',
                'admin_email',
                'admin_role',
            ]);

            return redirect()
                ->to('/login')
                ->with('error', 'Role akun tidak memiliki akses ke admin panel.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        //
    }
}