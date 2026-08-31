<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class ActivityLogController extends BaseController
{
    private function onlySuperAdmin()
    {
        if (session()->get('admin_role') !== 'super_admin') {
            return redirect()
                ->to('/admin/dashboard')
                ->with('error', 'Akses ditolak. Fitur ini hanya untuk Super Admin.');
        }

        return null;
    }

    public function index()
    {
        if ($redirect = $this->onlySuperAdmin()) {
            return $redirect;
        }

        $db = \Config\Database::connect();

        $logs = $db->table('admin_activity_logs')
            ->orderBy('id', 'DESC')
            ->limit(300)
            ->get()
            ->getResultArray();

        return view('admin/activity_logs/index', [
            'title' => 'Activity Log Admin',
            'logs'  => $logs,
        ]);
    }

    public function clear()
    {
        if ($redirect = $this->onlySuperAdmin()) {
            return $redirect;
        }

        $db = \Config\Database::connect();

        $db->table('admin_activity_logs')->truncate();

        log_admin_activity(
            'Activity Log',
            'Clear Logs',
            'Super Admin menghapus seluruh data activity log.'
        );

        return redirect()
            ->to('/admin/activity-logs')
            ->with('success', 'Activity log berhasil dibersihkan.');
    }
}