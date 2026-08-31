<?php
$siteName = site_setting('site_name', 'Travel Umroh & Haji');
$siteTagline = site_setting('site_tagline', 'Umroh & Haji');
?>
<nav class="navbar navbar-expand-lg navbar-dark site-navbar wl-navbar fixed-top">
    <div class="container">
        <a class="navbar-brand site-brand wl-brand" href="<?= base_url('/'); ?>" aria-label="Beranda <?= esc($siteName); ?>">
            <span class="wl-brand-logo-wrap">
                <img src="<?= esc(site_asset('logo')); ?>" alt="Logo <?= esc($siteName); ?>" class="wl-brand-logo">
            </span>
            <span class="wl-brand-copy">
                <strong><?= esc($siteName); ?></strong>
                <small><?= esc($siteTagline); ?></small>
            </span>
        </a>

        <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Buka navigasi">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNavbar">
            <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="<?= base_url('/paket'); ?>">Paket Umroh</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= base_url('/#layanan'); ?>">Layanan</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= base_url('/#testimoni'); ?>">Testimoni</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= base_url('/#galeri'); ?>">Galeri</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= base_url('/#tentang'); ?>">Tentang</a></li>
            </ul>

            <div class="wl-navbar-actions">
                <?php if (session()->get('jamaah_logged_in')) : ?>
                    <a href="<?= base_url('/dashboard-jamaah'); ?>" class="btn btn-wl-ghost btn-sm">
                        <i class="bi bi-grid"></i> Dashboard
                    </a>
                <?php endif; ?>
                <a href="<?= base_url('/paket'); ?>" class="btn btn-brand-primary btn-sm">
                    <i class="bi bi-box-seam"></i> Pilih Paket
                </a>
            </div>
        </div>
    </div>
</nav>
