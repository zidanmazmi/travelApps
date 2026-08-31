<?php

use App\Models\NotificationModel;

if (!function_exists('create_notification')) {
    function create_notification(
        string $title,
        string $message,
        string $type = 'system',
        ?int $referenceId = null
    ): bool {
        try {
            $allowedTypes = [
                'registration',
                'payment',
                'document',
                'package',
                'gallery',
                'testimonial',
                'faq',
                'lead',
                'system',
            ];

            if (!in_array($type, $allowedTypes, true)) {
                $type = 'system';
            }

            $model = new NotificationModel();

            $model->insert([
                'title'        => $title,
                'message'      => $message,
                'type'         => $type,
                'reference_id' => $referenceId,
                'is_read'      => 0,
            ]);

            return true;
        } catch (\Throwable $e) {
            log_message('error', 'Gagal membuat notifikasi: ' . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('unread_notification_count')) {
    function unread_notification_count(): int
    {
        try {
            $model = new NotificationModel();

            return $model
                ->where('is_read', 0)
                ->countAllResults();
        } catch (\Throwable $e) {
            return 0;
        }
    }
}

if (!function_exists('latest_notifications')) {
    function latest_notifications(int $limit = 5): array
    {
        try {
            $model = new NotificationModel();

            return $model
                ->orderBy('id', 'DESC')
                ->findAll($limit);
        } catch (\Throwable $e) {
            return [];
        }
    }
}