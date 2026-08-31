<?php
$siteName = site_setting('site_name', 'Travel Umroh & Haji');
$currentUri = trim(uri_string(), '/');
$isSuperAdmin = session()->get('admin_role') === 'super_admin';
$unreadNotifications = function_exists('unread_notification_count') ? unread_notification_count() : 0;

$isActive = static function (array|string $paths) use ($currentUri): string {
    $paths = is_array($paths) ? $paths : [$paths];

    foreach ($paths as $path) {
        $path = trim($path, '/');

        if ($currentUri === $path || str_starts_with($currentUri, $path . '/')) {
            return 'active';
        }
    }

    return '';
};
?>

<aside class="admin-sidebar" id="adminSidebar" aria-label="Navigasi admin" tabindex="-1">
    <div class="admin-sidebar-brand">
        <a href="<?= base_url('/admin/dashboard'); ?>" aria-label="<?= esc($siteName); ?> Admin">
            <span class="admin-brand-logo-wrap">
                <img
                    src="<?= site_asset('logo'); ?>"
                    alt="Logo <?= esc($siteName); ?>"
                    class="brand-logo">
            </span>

            <span class="brand-text">
                <strong><?= esc($siteName); ?></strong>
                <small>Admin Workspace</small>
            </span>
        </a>

        <button
            type="button"
            class="admin-sidebar-close"
            id="adminSidebarClose"
            data-admin-sidebar-close
            aria-label="Tutup menu navigasi"
            title="Tutup menu">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>

    <nav class="admin-menu" id="adminMenu">
        <div class="admin-menu-section">
            <span class="menu-label">Workspace</span>

            <a
                href="<?= base_url('/admin/dashboard'); ?>"
                class="admin-menu-link <?= $isActive('admin/dashboard'); ?>"
                title="Dashboard">
                <i class="bi bi-grid-1x2"></i>
                <span>Dashboard</span>
            </a>

            <a
                href="<?= base_url('/admin/leads'); ?>"
                class="admin-menu-link <?= $isActive('admin/leads'); ?>"
                title="Reporting Leads">
                <i class="bi bi-person-lines-fill"></i>
                <span>Reporting Leads</span>
            </a>

            <a
                href="<?= base_url('/admin/packages'); ?>"
                class="admin-menu-link <?= $isActive(['admin/packages', 'admin/package-departures']); ?>"
                title="Paket dan Jadwal">
                <i class="bi bi-box-seam"></i>
                <span>Paket &amp; Jadwal</span>
            </a>
        </div>

        <div class="admin-menu-section">
            <span class="menu-label">Konten Website</span>

            <a
                href="<?= base_url('/admin/galleries'); ?>"
                class="admin-menu-link <?= $isActive('admin/galleries'); ?>"
                title="Galeri Media">
                <i class="bi bi-play-btn"></i>
                <span>Galeri Media</span>
            </a>

            <a
                href="<?= base_url('/admin/testimonials'); ?>"
                class="admin-menu-link <?= $isActive('admin/testimonials'); ?>"
                title="Testimoni">
                <i class="bi bi-chat-quote"></i>
                <span>Testimoni</span>
            </a>

            <a
                href="<?= base_url('/admin/faqs'); ?>"
                class="admin-menu-link <?= $isActive('admin/faqs'); ?>"
                title="FAQ">
                <i class="bi bi-question-circle"></i>
                <span>FAQ</span>
            </a>
        </div>

        <div class="admin-menu-section">
            <span class="menu-label">Aktivitas</span>

            <a
                href="<?= base_url('/admin/chatbot'); ?>"
                class="admin-menu-link <?= ($currentUri === 'admin/chatbot' || str_starts_with($currentUri, 'admin/chatbot/conversation/')) ? 'active' : ''; ?>"
                title="Chatbot Website">
                <i class="bi bi-robot"></i>
                <span>Chatbot Website</span>
            </a>

            <a
                href="<?= base_url('/admin/chatbot/sources'); ?>"
                class="admin-menu-link <?= $isActive('admin/chatbot/sources'); ?>"
                title="Document Knowledge">
                <i class="bi bi-file-earmark-richtext"></i>
                <span>Document Knowledge</span>
            </a>

            <a
                href="<?= base_url('/admin/notifications'); ?>"
                class="admin-menu-link <?= $isActive('admin/notifications'); ?>"
                title="Notifikasi">
                <i class="bi bi-bell"></i>
                <span>Notifikasi</span>

                <?php if ($unreadNotifications > 0) : ?>
                    <span class="admin-menu-badge">
                        <?= $unreadNotifications > 99 ? '99+' : esc($unreadNotifications); ?>
                    </span>
                <?php endif; ?>
            </a>
        </div>

        <?php if ($isSuperAdmin) : ?>
            <div class="admin-menu-section">
                <span class="menu-label">Sistem</span>

                <a
                    href="<?= base_url('/admin/admin-users'); ?>"
                    class="admin-menu-link <?= $isActive('admin/admin-users'); ?>"
                    title="Manajemen Admin">
                    <i class="bi bi-person-gear"></i>
                    <span>Manajemen Admin</span>
                </a>

                <a
                    href="<?= base_url('/admin/recycle-bin'); ?>"
                    class="admin-menu-link <?= $isActive(['admin/recycle-bin', 'admin/legacy-documents']); ?>"
                    title="Sampah Data">
                    <i class="bi bi-trash3"></i>
                    <span>Sampah Data</span>
                </a>

                <a
                    href="<?= base_url('/admin/activity-logs'); ?>"
                    class="admin-menu-link <?= $isActive('admin/activity-logs'); ?>"
                    title="Activity Log">
                    <i class="bi bi-clock-history"></i>
                    <span>Activity Log</span>
                </a>

                <a
                    href="<?= base_url('/admin/settings'); ?>"
                    class="admin-menu-link <?= $isActive('admin/settings'); ?>"
                    title="Pengaturan Website">
                    <i class="bi bi-sliders"></i>
                    <span>Pengaturan Website</span>
                </a>
            </div>
        <?php endif; ?>
    </nav>

    <div class="admin-sidebar-footer">
        <div class="admin-sidebar-account">
            <span class="admin-account-avatar">
                <?= esc(strtoupper(substr(session()->get('admin_name') ?? session()->get('name') ?? 'A', 0, 1))); ?>
            </span>

            <div class="admin-account-copy">
                <strong><?= esc(session()->get('admin_name') ?? session()->get('name') ?? 'Administrator'); ?></strong>
                <span><?= $isSuperAdmin ? 'Super Admin' : 'Admin'; ?></span>
            </div>

            <a href="<?= base_url('/logout'); ?>" class="admin-logout-button" title="Logout" aria-label="Logout">
                <i class="bi bi-box-arrow-right"></i>
            </a>
        </div>
    </div>
</aside>
