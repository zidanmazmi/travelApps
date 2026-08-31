<?= $this->extend('templates/frontend/layouts/main'); ?>

<?= $this->section('content'); ?>

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
} elseif (str_starts_with($coverImage, 'assets/') || str_starts_with($coverImage, 'uploads/')) {
    $imagePath = $coverImage;
} else {
    $imagePath = 'uploads/packages/' . $coverImage;
}

$imageUrl = filter_var($imagePath, FILTER_VALIDATE_URL)
    ? $imagePath
    : base_url($imagePath);

$durationDays   = (int) ($package['duration_days'] ?? 0);
$durationNights = (int) ($package['duration_nights'] ?? 0);
$leadErrors     = session()->getFlashdata('lead_errors') ?? [];
?>

<section class="package-detail-page">
    <div class="container">

        <div class="package-detail-breadcrumb" data-reveal="up">
            <a href="<?= base_url('/'); ?>">Home</a>
            <i class="bi bi-chevron-right"></i>
            <a href="<?= base_url('/paket'); ?>">Paket</a>
            <i class="bi bi-chevron-right"></i>
            <span><?= esc($name); ?></span>
        </div>

        <?php if (session()->getFlashdata('error')) : ?>
            <div class="alert alert-danger rounded-4 mb-4">
                <?= esc(session()->getFlashdata('error')); ?>
            </div>
        <?php endif; ?>

        <div class="package-detail-hero" data-parallax-root>
            <div class="package-detail-morph package-detail-morph-one" aria-hidden="true"></div>
            <div class="package-detail-morph package-detail-morph-two" aria-hidden="true"></div>

            <div class="row g-4 align-items-stretch position-relative">
                <div class="col-lg-7" data-reveal="left">
                    <div class="package-detail-image">
                        <img src="<?= esc($imageUrl); ?>" alt="<?= esc($name); ?>" data-parallax-speed="0.05">

                        <?php if (!empty($badge)) : ?>
                            <span class="package-detail-badge"><?= esc($badge); ?></span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-lg-5" data-reveal="right">
                    <div class="package-detail-summary">
                        <span class="package-detail-label"><?= $programLabel !== '' ? 'Program ' . esc($programLabel) : 'Detail Paket'; ?></span>
                        <h1><?= esc($name); ?></h1>
                        <p><?= esc(mb_strimwidth($description, 0, 190, '...')); ?></p>

                        <div class="package-detail-info-grid">
                            <?php if ($durationDays > 0) : ?>
                                <div class="package-detail-info-item">
                                    <i class="bi bi-calendar-check"></i>
                                    <div>
                                        <span>Durasi</span>
                                        <strong><?= esc($durationDays); ?> Hari</strong>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if ($durationNights > 0) : ?>
                                <div class="package-detail-info-item">
                                    <i class="bi bi-moon-stars"></i>
                                    <div>
                                        <span>Malam</span>
                                        <strong><?= esc($durationNights); ?> Malam</strong>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <div class="package-detail-info-item">
                                <i class="bi bi-shield-check"></i>
                                <div>
                                    <span>Layanan</span>
                                    <strong>Pendampingan</strong>
                                </div>
                            </div>
                        </div>

                        <div class="package-detail-price-box">
                            <span>Harga mulai dari</span>
                            <strong><?= esc($priceLabel); ?></strong>
                        </div>

                        <div class="package-detail-actions">
                            <a href="#form-minat" class="btn-package-primary">
                                Pesan via WhatsApp
                                <i class="bi bi-whatsapp"></i>
                            </a>
                            <a href="#jadwal-keberangkatan" class="btn-package-whatsapp">
                                <i class="bi bi-calendar3"></i>
                                Lihat Jadwal
                            </a>
                        </div>

                        <div class="package-payment-note">
                            <i class="bi bi-info-circle"></i>
                            <span>Pemesanan saat ini dilayani melalui WhatsApp. Pembayaran online sementara tidak digunakan.</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="package-detail-content">
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="package-detail-card" data-reveal="up">
                        <h2>Deskripsi Paket</h2>
                        <div class="package-detail-description">
                            <?= nl2br(esc($description)); ?>
                        </div>
                    </div>

                    <div class="package-detail-card" data-reveal="up">
                        <h2>Keunggulan Paket</h2>

                        <div class="package-benefit-grid">
                            <div class="package-benefit-item">
                                <i class="bi bi-person-check"></i>
                                <div>
                                    <h3>Pendampingan Jamaah</h3>
                                    <p>Jamaah mendapatkan arahan dan pendampingan selama proses perjalanan ibadah.</p>
                                </div>
                            </div>

                            <div class="package-benefit-item">
                                <i class="bi bi-building-check"></i>
                                <div>
                                    <h3>Akomodasi Nyaman</h3>
                                    <p>Perjalanan dirancang dengan fasilitas yang mendukung kenyamanan jamaah.</p>
                                </div>
                            </div>

                            <div class="package-benefit-item">
                                <i class="bi bi-clipboard-check"></i>
                                <div>
                                    <h3>Proses Terarah</h3>
                                    <p>Informasi paket, jadwal, dan dokumen dijelaskan secara transparan.</p>
                                </div>
                            </div>

                            <div class="package-benefit-item">
                                <i class="bi bi-headset"></i>
                                <div>
                                    <h3>Layanan Responsif</h3>
                                    <p>Tim <?= esc(site_setting('site_name', 'Travel Umroh & Haji')); ?> siap membantu konsultasi melalui WhatsApp.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="package-lead-card" id="form-minat" data-reveal="up">
                        <div class="package-lead-heading">
                            <span>Konsultasi Paket</span>
                            <h2>Isi data singkat, lalu lanjutkan ke WhatsApp.</h2>
                            <p>Data ini membantu tim kami memahami paket dan jadwal yang Anda minati sebelum melakukan follow-up.</p>
                        </div>

                        <?php if (!empty($leadErrors)) : ?>
                            <div class="alert alert-danger rounded-4">
                                <strong>Periksa kembali data berikut:</strong>
                                <ul class="mb-0 mt-2">
                                    <?php foreach ($leadErrors as $error) : ?>
                                        <li><?= esc($error); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <form action="<?= base_url('/paket/' . $slug . '/pesan-whatsapp'); ?>" method="post" class="package-lead-form">
                            <?= csrf_field(); ?>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="full_name" class="form-label">Nama Lengkap <span>*</span></label>
                                    <input
                                        type="text"
                                        name="full_name"
                                        id="full_name"
                                        class="form-control"
                                        value="<?= esc(old('full_name')); ?>"
                                        placeholder="Nama calon jamaah"
                                        required>
                                </div>

                                <div class="col-md-6">
                                    <label for="phone" class="form-label">Nomor WhatsApp <span>*</span></label>
                                    <input
                                        type="tel"
                                        name="phone"
                                        id="phone"
                                        class="form-control"
                                        value="<?= esc(old('phone')); ?>"
                                        placeholder="08xxxxxxxxxx"
                                        required>
                                </div>

                                <div class="col-md-6">
                                    <label for="email" class="form-label">Email</label>
                                    <input
                                        type="email"
                                        name="email"
                                        id="email"
                                        class="form-control"
                                        value="<?= esc(old('email')); ?>"
                                        placeholder="email@contoh.com">
                                </div>

                                <div class="col-md-6">
                                    <label for="city" class="form-label">Domisili</label>
                                    <input
                                        type="text"
                                        name="city"
                                        id="city"
                                        class="form-control"
                                        value="<?= esc(old('city')); ?>"
                                        placeholder="Kota Anda">
                                </div>

                                <div class="col-md-6">
                                    <label for="total_participants" class="form-label">Jumlah Jamaah <span>*</span></label>
                                    <input
                                        type="number"
                                        name="total_participants"
                                        id="total_participants"
                                        class="form-control"
                                        min="1"
                                        max="20"
                                        value="<?= esc(old('total_participants') ?: 1); ?>"
                                        required>
                                </div>

                                <div class="col-md-6">
                                    <label for="departure_id" class="form-label">Jadwal yang Diminati</label>
                                    <select name="departure_id" id="departure_id" class="form-select">
                                        <option value="">Belum menentukan jadwal</option>
                                        <?php foreach ($departures as $departure) : ?>
                                            <?php
                                            $quota     = (int) ($departure['quota'] ?? 0);
                                            $booked    = (int) ($departure['booked'] ?? 0);
                                            $remaining = max($quota - $booked, 0);
                                            $label     = !empty($departure['departure_date'])
                                                ? date('d M Y', strtotime($departure['departure_date']))
                                                : 'Jadwal tersedia';
                                            ?>
                                            <option
                                                value="<?= esc($departure['id']); ?>"
                                                <?= (string) old('departure_id') === (string) $departure['id'] ? 'selected' : ''; ?>>
                                                <?= esc($label); ?> — Sisa <?= esc($remaining); ?> kuota
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-12">
                                    <label for="notes" class="form-label">Catatan</label>
                                    <textarea
                                        name="notes"
                                        id="notes"
                                        class="form-control"
                                        rows="4"
                                        placeholder="Pertanyaan khusus, kebutuhan keluarga, atau informasi lain."><?= esc(old('notes')); ?></textarea>
                                </div>
                            </div>

                            <div class="package-lead-submit">
                                <div>
                                    <strong><i class="bi bi-shield-check"></i> Data tersimpan sebagai lead</strong>
                                    <small>Setelah dikirim, WhatsApp akan terbuka dengan pesan otomatis sesuai paket ini.</small>
                                </div>

                                <button type="submit" class="btn-package-primary">
                                    <i class="bi bi-whatsapp"></i>
                                    Simpan &amp; Buka WhatsApp
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="package-detail-side-card" data-reveal="right">
                        <h2>Ringkasan Paket</h2>
                        <ul>
                            <li><span>Nama Paket</span><strong><?= esc($name); ?></strong></li>
                            <?php if ($programLabel !== '') : ?>
                                <li><span>Program Katalog</span><strong><?= esc($programLabel); ?></strong></li>
                            <?php endif; ?>
                            <li><span>Harga</span><strong><?= esc($priceLabel); ?></strong></li>

                            <?php if ($durationDays > 0) : ?>
                                <li><span>Durasi</span><strong><?= esc($durationDays); ?> Hari</strong></li>
                            <?php endif; ?>

                            <?php if ($durationNights > 0) : ?>
                                <li><span>Malam</span><strong><?= esc($durationNights); ?> Malam</strong></li>
                            <?php endif; ?>

                            <li><span>Pemesanan</span><strong>WhatsApp</strong></li>
                        </ul>

                        <a href="#form-minat" class="btn-package-primary w-100">
                            Pesan Paket Ini
                            <i class="bi bi-arrow-down"></i>
                        </a>
                    </div>

                    <div class="package-detail-side-card mt-4" id="jadwal-keberangkatan" data-reveal="right">
                        <h2>Jadwal Keberangkatan</h2>

                        <?php if (empty($departures)) : ?>
                            <div class="package-schedule-empty">
                                <i class="bi bi-calendar2-week"></i>
                                <p>Jadwal akan diinformasikan oleh tim <?= esc(site_setting('site_name', 'Travel Umroh & Haji')); ?>.</p>
                            </div>
                        <?php else : ?>
                            <div class="departure-list">
                                <?php foreach ($departures as $departure) : ?>
                                    <?php
                                    $departureDate = $departure['departure_date'] ?? null;
                                    $returnDate    = $departure['return_date'] ?? null;
                                    $quota         = (int) ($departure['quota'] ?? 0);
                                    $booked        = (int) ($departure['booked'] ?? 0);
                                    $remaining     = max($quota - $booked, 0);
                                    ?>

                                    <div class="departure-item">
                                        <div>
                                            <strong><?= !empty($departureDate) ? date('d M Y', strtotime($departureDate)) : '-'; ?></strong>
                                            <?php if (!empty($returnDate)) : ?>
                                                <span>Pulang <?= date('d M Y', strtotime($returnDate)); ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <small>Sisa kuota</small>
                                            <b><?= esc($remaining); ?></b>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?= $this->endSection(); ?>
