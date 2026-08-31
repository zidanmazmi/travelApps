<?php

use App\Models\AdminActivityLogModel;

if (!function_exists('log_admin_activity')) {
    function log_admin_activity(string $module, string $action, string $description = null): void
    {
        try {
            $request = service('request');
            $session = session();

            $logModel = new AdminActivityLogModel();

            $logModel->insert([
                'admin_id'    => $session->get('admin_user_id'),
                'admin_name'  => $session->get('admin_name'),
                'admin_email' => $session->get('admin_email'),
                'module'      => $module,
                'action'      => $action,
                'description' => $description,
                'ip_address'  => $request->getIPAddress(),
                'user_agent'  => $request->getUserAgent()->getAgentString(),
                'created_at'  => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Gagal menyimpan admin activity log: ' . $e->getMessage());
        }
    }
}