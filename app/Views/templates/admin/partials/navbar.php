<?php
$currentUri = trim(uri_string(), '/');
$pageName = 'Admin Panel';
$pageSection = 'Workspace';

$pageMap = [
    'admin/dashboard'     => ['Dashboard', 'Workspace'],
    'admin/leads'         => ['Reporting Leads', 'Penjualan'],
    'admin/packages'      => ['Paket & Jadwal', 'Penjualan'],
    'admin/galleries'     => ['Galeri Media', 'Konten Website'],
    'admin/testimonials'  => ['Testimoni', 'Konten Website'],
    'admin/faqs'          => ['FAQ', 'Konten Website'],
    'admin/chatbot/sources' => ['Document Knowledge', 'Aktivitas'],
    'admin/chatbot'       => ['Chatbot Website', 'Aktivitas'],
    'admin/notifications' => ['Notifikasi', 'Aktivitas'],
    'admin/admin-users'   => ['Manajemen Admin', 'Sistem'],
    'admin/recycle-bin'   => ['Sampah Data', 'Sistem'],
    'admin/legacy-documents' => ['Dokumen Lama', 'Sampah Data'],
    'admin/activity-logs' => ['Activity Log', 'Sistem'],
    'admin/settings'      => ['Pengaturan Website', 'Sistem'],
];

foreach ($pageMap as $prefix => $meta) {
    if ($currentUri === $prefix || str_starts_with($currentUri, $prefix . '/')) {
        [$pageName, $pageSection] = $meta;
        break;
    }
}
?>

<header class="admin-topbar">
    <div class="topbar-left">
        <button
            type="button"
            class="sidebar-toggle"
            id="sidebarToggle"
            aria-label="Buka menu navigasi"
            aria-controls="adminSidebar"
            aria-expanded="false">
            <i class="bi bi-layout-sidebar-inset"></i>
        </button>

        <div class="admin-topbar-context">
            <div class="admin-breadcrumb">
                <span>Admin</span>
                <i class="bi bi-chevron-right"></i>
                <span><?= esc($pageSection); ?></span>
            </div>
            <h2><?= esc($pageName); ?></h2>
        </div>
    </div>

    <button
        type="button"
        class="admin-command-trigger"
        data-bs-toggle="modal"
        data-bs-target="#adminCommandModal"
        aria-label="Buka pencarian menu">
        <i class="bi bi-search"></i>
        <span>Cari menu atau tindakan...</span>
        <kbd>Ctrl K</kbd>
    </button>

    <div class="topbar-right">
        <div class="dropdown admin-quick-dropdown">
            <button type="button" class="admin-icon-button" data-bs-toggle="dropdown" aria-expanded="false" title="Tindakan cepat">
                <i class="bi bi-plus-lg"></i>
            </button>

            <div class="dropdown-menu dropdown-menu-end admin-quick-menu">
                <span class="admin-dropdown-label">Tindakan cepat</span>
                <a href="<?= base_url('/admin/packages/create'); ?>"><i class="bi bi-box-seam"></i><span><strong>Tambah Paket</strong><small>Buat paket travel baru</small></span></a>
                <a href="<?= base_url('/admin/galleries/create'); ?>"><i class="bi bi-cloud-arrow-up"></i><span><strong>Upload Media</strong><small>Foto atau video Reels</small></span></a>
                <a href="<?= base_url('/admin/testimonials/create'); ?>"><i class="bi bi-chat-quote"></i><span><strong>Tambah Testimoni</strong><small>Publikasikan ulasan jamaah</small></span></a>
                <a href="<?= base_url('/admin/faqs/create'); ?>"><i class="bi bi-question-circle"></i><span><strong>Tambah FAQ</strong><small>Buat pertanyaan umum</small></span></a>
            </div>
        </div>

        <?= $this->include('templates/admin/partials/notification_dropdown'); ?>

        <a href="<?= base_url('/'); ?>" target="_blank" rel="noopener" class="admin-icon-button admin-website-button" title="Lihat website">
            <i class="bi bi-globe2"></i>
        </a>

        <div class="dropdown admin-profile-dropdown">
            <button type="button" class="admin-profile-button" data-bs-toggle="dropdown" aria-expanded="false">
                <span class="profile-avatar">
                    <?= esc(strtoupper(substr(session()->get('admin_name') ?? session()->get('name') ?? 'A', 0, 1))); ?>
                </span>
                <span class="admin-profile-copy">
                    <strong><?= esc(session()->get('admin_name') ?? session()->get('name') ?? 'Admin'); ?></strong>
                    <small><?= session()->get('admin_role') === 'super_admin' ? 'Super Admin' : 'Admin'; ?></small>
                </span>
                <i class="bi bi-chevron-down"></i>
            </button>

            <div class="dropdown-menu dropdown-menu-end admin-profile-menu">
                <div class="admin-profile-menu-head">
                    <span class="profile-avatar">
                        <?= esc(strtoupper(substr(session()->get('admin_name') ?? session()->get('name') ?? 'A', 0, 1))); ?>
                    </span>
                    <div>
                        <strong><?= esc(session()->get('admin_name') ?? session()->get('name') ?? 'Admin'); ?></strong>
                        <span><?= esc(session()->get('admin_email') ?? ''); ?></span>
                    </div>
                </div>

                <?php if (session()->get('admin_role') === 'super_admin') : ?>
                    <a href="<?= base_url('/admin/settings'); ?>"><i class="bi bi-sliders"></i> Pengaturan Website</a>
                    <a href="<?= base_url('/admin/admin-users'); ?>"><i class="bi bi-person-gear"></i> Manajemen Admin</a>
                <?php endif; ?>

                <a href="<?= base_url('/'); ?>" target="_blank" rel="noopener"><i class="bi bi-box-arrow-up-right"></i> Lihat Website</a>
                <div class="dropdown-divider"></div>
                <a href="<?= base_url('/logout'); ?>" class="is-danger"><i class="bi bi-box-arrow-right"></i> Logout</a>
            </div>
        </div>
    </div>
</header>
