<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Controllers\Admin\Concerns\BulkActionSupport;
use App\Models\NotificationModel;

class NotificationController extends BaseController
{
    use BulkActionSupport;

    protected NotificationModel $notificationModel;

    public function __construct()
    {
        $this->notificationModel = new NotificationModel();
    }

    public function index()
    {
        $notifications = $this->notificationModel
            ->orderBy('id', 'DESC')
            ->findAll(100);

        return view('admin/notifications/index', [
            'title'         => 'Notification Center',
            'notifications' => $notifications,
        ]);
    }

    public function read($id)
    {
        $notification = $this->notificationModel->find($id);

        if (!$notification) {
            return redirect()
                ->to('/admin/notifications')
                ->with('error', 'Notifikasi tidak ditemukan.');
        }

        $this->notificationModel->update($id, ['is_read' => 1]);

        return redirect()->to($this->getRedirectUrl($notification));
    }

    public function readAll()
    {
        $this->notificationModel
            ->where('is_read', 0)
            ->set([
                'is_read'    => 1,
                'updated_at' => date('Y-m-d H:i:s'),
            ])
            ->update();

        return redirect()
            ->back()
            ->with('success', 'Semua notifikasi berhasil ditandai sudah dibaca.');
    }

    public function delete($id)
    {
        $notification = $this->notificationModel->find($id);

        if (!$notification) {
            return redirect()
                ->to('/admin/notifications')
                ->with('error', 'Notifikasi tidak ditemukan.');
        }

        $this->notificationModel->delete($id, true);

        return redirect()
            ->to('/admin/notifications')
            ->with('success', 'Notifikasi berhasil dihapus.');
    }

    public function bulkAction()
    {
        $ids = $this->selectedIds();
        $action = (string) $this->request->getPost('bulk_action');

        if ($ids === []) {
            return redirect()->back()->with('error', 'Pilih minimal satu notifikasi.');
        }

        if (!in_array($action, ['read', 'delete'], true)) {
            return redirect()->back()->with('error', 'Aksi massal tidak valid.');
        }

        $processed = 0;

        foreach ($ids as $id) {
            $notification = $this->notificationModel->find($id);
            if (!$notification) {
                continue;
            }

            if ($action === 'read') {
                $this->notificationModel->update($id, ['is_read' => 1]);
            } else {
                $this->notificationModel->delete($id, true);
            }

            $processed++;
        }

        $label = $action === 'read' ? 'ditandai sudah dibaca' : 'dihapus';

        return redirect()
            ->to('/admin/notifications')
            ->with('success', $processed . ' notifikasi berhasil ' . $label . '.');
    }

    public function deleteAll()
    {
        if (!$this->isSuperAdmin()) {
            return redirect()->back()->with('error', 'Hanya Super Admin yang dapat menghapus semua notifikasi.');
        }

        $total = $this->notificationModel->countAllResults();

        if ($total > 0) {
            $this->notificationModel->builder()->truncate();
            log_admin_activity('Notifikasi', 'Hapus Semua', $total . ' notifikasi dihapus dari sistem.');
        }

        return redirect()
            ->to('/admin/notifications')
            ->with('success', 'Semua notifikasi berhasil dihapus.');
    }

    private function getRedirectUrl(array $notification): string
    {
        $type        = $notification['type'] ?? 'system';
        $referenceId = $notification['reference_id'] ?? null;

        if (empty($referenceId)) {
            return '/admin/notifications';
        }

        switch ($type) {
            case 'registration':
            case 'payment':
            case 'document':
                return '/admin/dashboard';

            case 'package':
                return '/admin/packages/edit/' . $referenceId;

            case 'gallery':
                return '/admin/galleries/edit/' . $referenceId;

            case 'testimonial':
                return '/admin/testimonials/edit/' . $referenceId;

            case 'faq':
                return '/admin/faqs/edit/' . $referenceId;

            case 'lead':
                return '/admin/leads/detail/' . $referenceId;

            default:
                return '/admin/notifications';
        }
    }
}
