<?= $this->extend('templates/frontend/layouts/main'); ?>

<?= $this->section('content'); ?>

<?php
$siteName = site_setting('site_name', 'Travel Umroh & Haji');
$siteWa = site_setting('social_whatsapp', site_setting('site_whatsapp', ''));
$cleanWhatsapp = preg_replace('/[^0-9]/', '', $siteWa);
$waText = urlencode('Assalamualaikum, saya ingin bertanya tentang paket umrah dan haji di ' . $siteName);
?>

<section class="packages-page">
    <div class="container">

        <div class="packages-page-hero">
            <div class="row align-items-center g-4">

                <div class="col-lg-8">
                    <div class="packages-page-header">

                        <h1>Pilih Paket Umrah & Haji Terbaik</h1>

                        <div class="heading-line"></div>

                        <p>
                            Temukan pilihan paket perjalanan ibadah yang sesuai dengan kebutuhan Anda.
                            Setiap paket dirancang untuk memberikan kenyamanan, ketenangan, dan pelayanan terbaik.
                        </p>

                        <div class="packages-hero-points">
                            <span><i class="bi bi-check-circle"></i> Jadwal Terarah</span>
                            <span><i class="bi bi-check-circle"></i> Pendampingan Jamaah</span>
                            <span><i class="bi bi-check-circle"></i> Proses Mudah</span>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="packages-help-card">
                        <div class="packages-help-icon">
                            <i class="bi bi-headset"></i>
                        </div>

                        <h2>Butuh Bantuan Memilih Paket?</h2>

                        <p>
                            Konsultasikan kebutuhan perjalanan ibadah Anda dengan tim kami.
                        </p>

                        <?php if (!empty($cleanWhatsapp)) : ?>
                            <a 
                                href="https://wa.me/<?= esc($cleanWhatsapp); ?>?text=<?= esc($waText); ?>"
                                target="_blank"
                                rel="noopener"
                                class="packages-help-btn">
                                <i class="bi bi-whatsapp"></i>
                                Konsultasi WhatsApp
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>

        <?php if (empty($packages)) : ?>

            <div class="package-empty-box text-center">
                <i class="bi bi-box-seam"></i>
                <h3>Paket belum tersedia</h3>
                <p>
                    Saat ini belum ada paket yang tersedia. Silakan cek kembali nanti
                    atau hubungi admin untuk informasi lebih lanjut.
                </p>
            </div>

        <?php else : ?>

            <div class="packages-grid-header">
                <div>
                    <span>Daftar Paket</span>
                    <h2>Paket Tersedia</h2>
                </div>
            </div>

            <div class="row g-4">
                <?php foreach ($packages as $package) : ?>

                    <?php
                    $name        = $package['name'] ?? 'Paket Umroh';
                    $slug        = $package['slug'] ?? '';
                    $badge       = $package['badge'] ?? 'Paket Pilihan';
                    $program     = trim((string) ($package['program'] ?? ''));
                    $programLabel = $program !== '' ? ucwords($program) : '';
                    $description = $package['description'] ?? 'Paket perjalanan ibadah yang disiapkan untuk jamaah.';
                    $price       = (float) ($package['price'] ?? 0);

                    $priceLabel = $price > 0
                        ? 'Rp ' . number_format($price, 0, ',', '.')
                        : 'Hubungi Admin';

                    $coverImage = $package['cover_image'] ?? '';

                    if (empty($coverImage)) {
                        $imagePath = 'assets/frontend/images/paket-1.jpg';
                    } elseif (strpos($coverImage, 'assets/') === 0 || strpos($coverImage, 'uploads/') === 0) {
                        $imagePath = $coverImage;
                    } else {
                        $imagePath = 'uploads/packages/' . $coverImage;
                    }

                    $imageUrl = filter_var($imagePath, FILTER_VALIDATE_URL)
                        ? $imagePath
                        : base_url($imagePath);

                    $durationDays   = (int) ($package['duration_days'] ?? 0);
                    $durationNights = (int) ($package['duration_nights'] ?? 0);
                    ?>

                    <div class="col-md-6 col-lg-4">
                        <div class="package-list-card h-100">

                            <div class="package-list-image">
                                <img
                                    src="<?= esc($imageUrl); ?>"
                                    alt="<?= esc($name); ?>"
                                    loading="lazy">

                                <?php if (!empty($badge)) : ?>
                                    <span class="package-list-badge">
                                        <?= esc($badge); ?>
                                    </span>
                                <?php endif; ?>
                            </div>

                            <div class="package-list-body">

                                <?php if ($programLabel !== '') : ?>
                                    <span class="package-program-label">Program <?= esc($programLabel); ?></span>
                                <?php endif; ?>

                                <div class="package-list-meta">
                                    <?php if ($durationDays > 0) : ?>
                                        <span>
                                            <i class="bi bi-calendar-check"></i>
                                            <?= esc($durationDays); ?> Hari
                                        </span>
                                    <?php endif; ?>

                                    <?php if ($durationNights > 0) : ?>
                                        <span>
                                            <i class="bi bi-moon-stars"></i>
                                            <?= esc($durationNights); ?> Malam
                                        </span>
                                    <?php endif; ?>

                                    <span>
                                        <i class="bi bi-shield-check"></i>
                                        Terpercaya
                                    </span>
                                </div>

                                <h2><?= esc($name); ?></h2>

                                <p>
                                    <?= esc(mb_strimwidth($description, 0, 125, '...')); ?>
                                </p>

                                <div class="package-list-footer">
                                    <div class="package-list-price">
                                        <span>Mulai dari</span>
                                        <strong><?= esc($priceLabel); ?></strong>
                                    </div>

                                    <?php if (!empty($slug)) : ?>
                                        <a href="<?= base_url('/paket/' . $slug); ?>" class="package-list-btn">
                                            Detail
                                            <i class="bi bi-arrow-right"></i>
                                        </a>
                                    <?php else : ?>
                                        <button type="button" class="package-list-btn" disabled>
                                            Detail
                                            <i class="bi bi-arrow-right"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>

                            </div>

                        </div>
                    </div>

                <?php endforeach; ?>
            </div>

        <?php endif; ?>

    </div>
</section>

<?= $this->endSection(); ?>
