<?php
$unreadCount = unread_notification_count();
$notifications = latest_notifications(5);
?>

<div class="admin-notification-dropdown dropdown">
    <button 
        type="button" 
        class="admin-notification-btn"
        data-bs-toggle="dropdown"
        aria-expanded="false">

        <i class="bi bi-bell"></i>

        <?php if ($unreadCount > 0) : ?>
            <span class="admin-notification-badge">
                <?= $unreadCount > 99 ? '99+' : esc($unreadCount); ?>
            </span>
        <?php endif; ?>
    </button>

    <div class="dropdown-menu dropdown-menu-end admin-notification-menu">
        <div class="admin-notification-header">
            <div>
                <strong>Notifikasi</strong>
                <span><?= esc($unreadCount); ?> belum dibaca</span>
            </div>

            <a href="<?= base_url('/admin/notifications'); ?>">
                Lihat Semua
            </a>
        </div>

        <div class="admin-notification-body">

            <?php if (empty($notifications)) : ?>

                <div class="admin-notification-empty">
                    Belum ada notifikasi.
                </div>

            <?php else : ?>

                <?php foreach ($notifications as $notification) : ?>

                    <?php
                    $type = $notification['type'] ?? 'system';

                    $icons = [
                        'registration' => 'bi-person-plus',
                        'payment'      => 'bi-credit-card',
                        'document'     => 'bi-file-earmark-text',
                        'package'      => 'bi-box-seam',
                        'gallery'      => 'bi-images',
                        'testimonial'  => 'bi-chat-quote',
                        'faq'          => 'bi-question-circle',
                    'lead'         => 'bi-person-lines-fill',
                        'system'       => 'bi-bell',
                    ];

                    $icon = $icons[$type] ?? 'bi-bell';
                    $isRead = (int) ($notification['is_read'] ?? 0) === 1;
                    ?>

                    <a 
                        href="<?= base_url('/admin/notifications/read/' . $notification['id']); ?>"
                        class="admin-notification-item <?= $isRead ? 'is-read' : 'is-unread'; ?>">

                        <div class="admin-notification-item-icon">
                            <i class="bi <?= esc($icon); ?>"></i>
                        </div>

                        <div class="admin-notification-item-content">
                            <strong><?= esc($notification['title'] ?? '-'); ?></strong>
                            <p><?= esc(mb_strimwidth($notification['message'] ?? '-', 0, 70, '...')); ?></p>
                            <small>
                                <?= !empty($notification['created_at']) ? date('d M Y H:i', strtotime($notification['created_at'])) : '-'; ?>
                            </small>
                        </div>
                    </a>

                <?php endforeach; ?>

            <?php endif; ?>

        </div>
    </div>
</div>