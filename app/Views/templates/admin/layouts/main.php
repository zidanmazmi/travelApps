<?php
$siteName = site_setting('site_name', 'Travel Umroh & Haji');
$brandPrimary = trim(site_setting('brand_primary_color'));
$brandSecondary = trim(site_setting('brand_secondary_color'));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= esc($title ?? 'Admin Panel'); ?> | <?= esc($siteName); ?></title>

    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <?php if ($brandPrimary !== '') : ?>
    <meta name="theme-color" content="<?= esc($brandPrimary); ?>">
    <?php endif; ?>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >

    <link rel="icon" href="<?= esc(site_asset('favicon')); ?>">

    <link rel="stylesheet" href="<?= base_url('assets/admin/css/admin.css'); ?>?v=6.4.0">
    <link rel="stylesheet" href="<?= base_url('assets/admin/css/admin-brand.css'); ?>?v=2.0.0">
    <link rel="stylesheet" href="<?= base_url('assets/admin/css/admin-clean-v5.css'); ?>">
    <link rel="stylesheet" href="<?= base_url('assets/admin/css/admin-responsive-v6-1.css'); ?>?v=7.0.0">

    <?php if ($brandPrimary !== '' || $brandSecondary !== '') : ?>
    <style>
    :root {
    <?php if ($brandPrimary !== '') : ?>
        --brand-primary: <?= esc($brandPrimary); ?>;
            --brand-primary-soft: color-mix(in srgb, var(--brand-primary) 82%, white);
    <?php endif; ?>
    <?php if ($brandSecondary !== '') : ?>
        --brand-secondary: <?= esc($brandSecondary); ?>;
            --brand-secondary-light: color-mix(in srgb, var(--brand-secondary) 62%, white);
        --brand-secondary-dark: color-mix(in srgb, var(--brand-secondary) 72%, black);
    <?php endif; ?>
        --brand-gradient: linear-gradient(135deg, var(--brand-primary) 0%, var(--brand-secondary) 100%);
    }
    </style>
    <?php endif; ?>
</head>

<body class="admin-body">

<div class="admin-wrapper" id="adminWrapper">

    <?= $this->include('templates/admin/partials/sidebar'); ?>

    <button
        type="button"
        class="admin-sidebar-overlay"
        id="adminSidebarOverlay"
        data-admin-sidebar-close
        aria-label="Tutup menu navigasi">
    </button>

    <main class="admin-main">

        <?= $this->include('templates/admin/partials/navbar'); ?>

        <div class="admin-content">
            <div class="admin-content-inner">
                <?= $this->renderSection('content_header'); ?>
                <?= $this->renderSection('content'); ?>
            </div>
        </div>

        <?= $this->include('templates/admin/partials/footer'); ?>

    </main>

</div>

<!-- Command palette / navigasi cepat -->
<div class="modal fade admin-command-modal" id="adminCommandModal" tabindex="-1" aria-labelledby="adminCommandLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="admin-command-search-wrap">
                <i class="bi bi-search"></i>
                <input
                    type="search"
                    class="form-control"
                    id="adminCommandSearch"
                    placeholder="Cari menu atau tindakan..."
                    autocomplete="off"
                    aria-label="Cari menu admin">
                <kbd>Esc</kbd>
            </div>

            <div class="admin-command-list" id="adminCommandList">
                <span class="admin-command-label">Navigasi</span>

                <a href="<?= base_url('/admin/dashboard'); ?>" class="admin-command-item" data-command-keywords="dashboard ringkasan overview">
                    <span><i class="bi bi-grid-1x2"></i></span>
                    <div><strong>Dashboard</strong><small>Ringkasan operasional dan leads</small></div>
                    <i class="bi bi-arrow-return-left"></i>
                </a>

                <a href="<?= base_url('/admin/chatbot'); ?>" class="admin-command-item" data-command-keywords="chatbot chat percakapan pertanyaan paket asisten website">
                    <span><i class="bi bi-robot"></i></span>
                    <div><strong>Chatbot Website</strong><small>Pantau percakapan dan jawaban chatbot</small></div>
                    <i class="bi bi-arrow-return-left"></i>
                </a>

                <a href="<?= base_url('/admin/chatbot/sources'); ?>" class="admin-command-item" data-command-keywords="document knowledge training data gambar pdf flyer itinerary booklet ocr ai">
                    <span><i class="bi bi-file-earmark-richtext"></i></span>
                    <div><strong>Document Knowledge</strong><small>Ekstrak gambar dan PDF menjadi jawaban chatbot</small></div>
                    <i class="bi bi-arrow-return-left"></i>
                </a>

                <a href="<?= base_url('/admin/leads'); ?>" class="admin-command-item" data-command-keywords="lead calon jamaah follow up whatsapp sales">
                    <span><i class="bi bi-person-lines-fill"></i></span>
                    <div><strong>Reporting Leads</strong><small>Pantau calon jamaah dan follow-up</small></div>
                    <i class="bi bi-arrow-return-left"></i>
                </a>

                <a href="<?= base_url('/admin/packages'); ?>" class="admin-command-item" data-command-keywords="paket umroh harga jadwal keberangkatan">
                    <span><i class="bi bi-box-seam"></i></span>
                    <div><strong>Paket & Jadwal</strong><small>Kelola katalog dan keberangkatan</small></div>
                    <i class="bi bi-arrow-return-left"></i>
                </a>

                <a href="<?= base_url('/admin/galleries'); ?>" class="admin-command-item" data-command-keywords="galeri media foto video reels">
                    <span><i class="bi bi-play-btn"></i></span>
                    <div><strong>Galeri Media</strong><small>Kelola foto dan video Reels</small></div>
                    <i class="bi bi-arrow-return-left"></i>
                </a>

                <a href="<?= base_url('/admin/testimonials'); ?>" class="admin-command-item" data-command-keywords="testimoni ulasan jamaah">
                    <span><i class="bi bi-chat-quote"></i></span>
                    <div><strong>Testimoni</strong><small>Kelola ulasan jamaah</small></div>
                    <i class="bi bi-arrow-return-left"></i>
                </a>

                <a href="<?= base_url('/admin/faqs'); ?>" class="admin-command-item" data-command-keywords="faq pertanyaan jawaban">
                    <span><i class="bi bi-question-circle"></i></span>
                    <div><strong>FAQ</strong><small>Kelola pertanyaan umum</small></div>
                    <i class="bi bi-arrow-return-left"></i>
                </a>

                <?php if (session()->get('admin_role') === 'super_admin') : ?>
                    <span class="admin-command-label mt-2">Super Admin</span>

                    <a href="<?= base_url('/admin/admin-users'); ?>" class="admin-command-item" data-command-keywords="admin akun pengguna role akses">
                        <span><i class="bi bi-person-gear"></i></span>
                        <div><strong>Manajemen Admin</strong><small>Kelola akun dan hak akses</small></div>
                        <i class="bi bi-arrow-return-left"></i>
                    </a>

                    <a href="<?= base_url('/admin/recycle-bin'); ?>" class="admin-command-item" data-command-keywords="sampah recycle hapus restore pulihkan">
                        <span><i class="bi bi-trash3"></i></span>
                        <div><strong>Sampah Data</strong><small>Pulihkan atau hapus permanen</small></div>
                        <i class="bi bi-arrow-return-left"></i>
                    </a>

                    <a href="<?= base_url('/admin/settings'); ?>" class="admin-command-item" data-command-keywords="pengaturan website kontak whatsapp sosial media">
                        <span><i class="bi bi-sliders"></i></span>
                        <div><strong>Pengaturan Website</strong><small>Identitas, kontak, dan sosial media</small></div>
                        <i class="bi bi-arrow-return-left"></i>
                    </a>
                <?php endif; ?>

                <div class="admin-command-empty" id="adminCommandEmpty" hidden>
                    <i class="bi bi-search"></i>
                    <strong>Menu tidak ditemukan</strong>
                    <span>Coba gunakan kata kunci lain.</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal konfirmasi global -->
<div class="modal fade admin-confirm-modal" id="adminConfirmModal" tabindex="-1" aria-labelledby="adminConfirmTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="admin-confirm-icon" id="adminConfirmIcon">
                <i class="bi bi-exclamation-triangle"></i>
            </div>
            <h2 id="adminConfirmTitle">Konfirmasi tindakan</h2>
            <p id="adminConfirmMessage">Apakah Anda yakin ingin melanjutkan?</p>
            <div class="admin-confirm-actions">
                <button type="button" class="btn btn-admin-outline" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" id="adminConfirmButton">Ya, lanjutkan</button>
            </div>
        </div>
    </div>
</div>

<button type="button" class="admin-back-to-top" id="adminBackToTop" aria-label="Kembali ke atas">
    <i class="bi bi-arrow-up"></i>
</button>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= base_url('assets/admin/js/admin-clean-v5.js'); ?>?v=7.0.0"></script>

<script>
// Fallback penutup drawer agar tombol X/overlay tetap bekerja saat cache skrip lama masih aktif.
document.addEventListener('click', function (event) {
    const closeTrigger = event.target.closest('[data-admin-sidebar-close]');

    if (!closeTrigger) {
        return;
    }

    const wrapper = document.getElementById('adminWrapper');
    const toggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('adminSidebar');

    wrapper?.classList.remove('sidebar-open');
    document.body.classList.remove('admin-menu-lock');
    toggle?.setAttribute('aria-expanded', 'false');

    if (window.matchMedia('(max-width: 1199.98px)').matches) {
        sidebar?.setAttribute('aria-hidden', 'true');
    }
}, true);
</script>

<?= $this->renderSection('script'); ?>

</body>
</html>
