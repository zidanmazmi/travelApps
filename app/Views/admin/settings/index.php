<?= $this->extend('templates/admin/layouts/main'); ?>

<?= $this->section('content_header'); ?>

<div class="admin-page-title">
    <h1>Pengaturan Website</h1>
    <p>Kelola identitas travel, branding, kontak, jam operasional, dan tautan sosial media website.</p>
</div>

<?= $this->endSection(); ?>

<?= $this->section('content'); ?>

<?php
$contactEmail = $settings['contact_email'] ?? $settings['site_email'] ?? '';
$contactPhone = $settings['contact_phone'] ?? $settings['site_phone'] ?? '';
$contactWhatsapp = $settings['social_whatsapp'] ?? $settings['site_whatsapp'] ?? '';
$contactAddress = $settings['contact_address'] ?? $settings['site_address'] ?? '';
$instagramUrl = $settings['social_instagram'] ?? $settings['instagram_url'] ?? '';
$facebookUrl = $settings['social_facebook'] ?? $settings['facebook_url'] ?? '';
?>

<?php if (session()->getFlashdata('success')) : ?>
    <div class="alert alert-success">
        <?= session()->getFlashdata('success'); ?>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')) : ?>
    <div class="alert alert-danger">
        <?= session()->getFlashdata('error'); ?>
    </div>
<?php endif; ?>

<?php if ($errors = session()->getFlashdata('errors')) : ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <?php foreach ($errors as $error) : ?>
                <li><?= esc($error); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form action="<?= base_url('/admin/settings/update'); ?>" method="post" enctype="multipart/form-data">
    <?= csrf_field(); ?>

    <div class="admin-card mb-4">
        <div class="admin-card-header">
            <div>
                <h2>Branding White-Label</h2>
                <p class="mb-0 text-muted small">Atur warna dan asset identitas tanpa mengubah source code.</p>
            </div>
        </div>

        <div class="admin-card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="admin-form-label">Warna Primary</label>
                    <div class="d-flex gap-2 align-items-center">
                        <input
                            type="color"
                            name="brand_primary_color"
                            class="form-control form-control-color"
                            value="<?= esc($settings['brand_primary_color'] ?? '#0D6EFD'); ?>"
                            title="Pilih warna primary">
                        <input
                            type="text"
                            class="form-control admin-form-control"
                            value="<?= esc($settings['brand_primary_color'] ?? '#0D6EFD'); ?>"
                            readonly>
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="admin-form-label">Warna Secondary</label>
                    <div class="d-flex gap-2 align-items-center">
                        <input
                            type="color"
                            name="brand_secondary_color"
                            class="form-control form-control-color"
                            value="<?= esc($settings['brand_secondary_color'] ?? '#6C757D'); ?>"
                            title="Pilih warna secondary">
                        <input
                            type="text"
                            class="form-control admin-form-control"
                            value="<?= esc($settings['brand_secondary_color'] ?? '#6C757D'); ?>"
                            readonly>
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="admin-form-label">Logo Utama</label>
                    <input type="file" name="site_logo" class="form-control admin-form-control" accept="image/png">
                    <div class="form-text">PNG, maksimal 2 MB. Rekomendasi minimal 300×80 px.</div>
                    <?php if (!empty($settings['site_logo'])) : ?>
                        <img src="<?= esc(site_asset('logo')); ?>" alt="Logo utama" class="img-fluid mt-2" style="max-height: 70px;">
                    <?php endif; ?>
                </div>

                <div class="col-md-6">
                    <label class="admin-form-label">Logo Versi Putih</label>
                    <input type="file" name="site_logo_white" class="form-control admin-form-control" accept="image/png">
                    <div class="form-text">PNG, maksimal 2 MB. Dipakai pada background gelap.</div>
                    <?php if (!empty($settings['site_logo_white'])) : ?>
                        <img src="<?= esc(site_asset('logo_white')); ?>" alt="Logo versi putih" class="img-fluid mt-2 bg-dark p-2 rounded" style="max-height: 70px;">
                    <?php endif; ?>
                </div>

                <div class="col-md-6">
                    <label class="admin-form-label">Favicon</label>
                    <input type="file" name="site_favicon" class="form-control admin-form-control" accept=".ico,image/png">
                    <div class="form-text">ICO atau PNG, maksimal 512 KB.</div>
                </div>

                <div class="col-md-6">
                    <label class="admin-form-label">Logo Header Email</label>
                    <input type="file" name="email_header_logo" class="form-control admin-form-control" accept="image/png">
                    <div class="form-text">PNG, maksimal 2 MB. Rekomendasi lebar maksimal 200 px.</div>
                    <?php if (!empty($settings['email_header_logo'])) : ?>
                        <img src="<?= esc(site_asset('email_header_logo')); ?>" alt="Logo email" class="img-fluid mt-2" style="max-height: 70px;">
                    <?php endif; ?>
                </div>

                <div class="col-12">
                    <label class="admin-form-label">Footer Text</label>
                    <textarea
                        name="footer_text"
                        class="form-control admin-form-control"
                        rows="3"
                        placeholder="Teks footer website"><?= esc($settings['footer_text'] ?? ''); ?></textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="admin-detail-grid">

        <div class="admin-card">
            <div class="admin-card-header">
                <h2>Informasi Travel</h2>
            </div>

            <div class="admin-card-body">

                <div class="mb-3">
                    <label class="admin-form-label">Nama Website / Travel</label>
                    <input
                        type="text"
                        name="site_name"
                        class="form-control admin-form-control"
                        value="<?= esc($settings['site_name'] ?? ''); ?>">
                </div>

                <div class="mb-3">
                    <label class="admin-form-label">Tagline</label>
                    <input
                        type="text"
                        name="site_tagline"
                        class="form-control admin-form-control"
                        value="<?= esc($settings['site_tagline'] ?? ''); ?>">
                </div>

                <div class="mb-3">
                    <label class="admin-form-label">Email</label>
                    <input
                        type="email"
                        name="contact_email"
                        class="form-control admin-form-control"
                        value="<?= esc($contactEmail); ?>">
                </div>

                <div class="mb-3">
                    <label class="admin-form-label">No. Telepon</label>
                    <input
                        type="text"
                        name="contact_phone"
                        class="form-control admin-form-control"
                        value="<?= esc($contactPhone); ?>">
                </div>

                <div class="mb-3">
                    <label class="admin-form-label">WhatsApp</label>
                    <input
                        type="text"
                        name="social_whatsapp"
                        class="form-control admin-form-control"
                        value="<?= esc($contactWhatsapp); ?>"
                        placeholder="Contoh: 6281234567890">
                </div>

                <div class="mb-3">
                    <label class="admin-form-label">Alamat</label>
                    <textarea
                        name="contact_address"
                        class="form-control admin-form-control"
                        rows="4"><?= esc($contactAddress); ?></textarea>
                </div>

                <div class="mb-3">
                    <label class="admin-form-label">Jam Operasional</label>
                    <input
                        type="text"
                        name="business_hours"
                        class="form-control admin-form-control"
                        value="<?= esc($settings['business_hours'] ?? 'Senin - Sabtu, 09.00 - 17.00'); ?>"
                        placeholder="Contoh: Senin - Sabtu, 09.00 - 17.00">
                </div>

            </div>
        </div>

        <div class="admin-card">
            <div class="admin-card-header">
                <h2>Sosial Media</h2>
            </div>

            <div class="admin-card-body">

                <div class="mb-3">
                    <label class="admin-form-label">Instagram URL</label>
                    <input
                        type="url"
                        name="social_instagram"
                        class="form-control admin-form-control"
                        value="<?= esc($instagramUrl); ?>"
                        placeholder="https://instagram.com/username">
                </div>

                <div class="mb-3">
                    <label class="admin-form-label">Facebook URL</label>
                    <input
                        type="url"
                        name="social_facebook"
                        class="form-control admin-form-control"
                        value="<?= esc($facebookUrl); ?>"
                        placeholder="https://facebook.com/username">
                </div>

                <div class="mb-4">
                    <label class="admin-form-label">YouTube URL</label>
                    <input
                        type="url"
                        name="youtube_url"
                        class="form-control admin-form-control"
                        value="<?= esc($settings['youtube_url'] ?? ''); ?>"
                        placeholder="https://youtube.com/@channel">
                </div>

            </div>
        </div>

    </div>

    <div class="admin-card mt-4">
        <div class="admin-card-header">
            <div>
                <h2>Section Tentang</h2>
                <p class="mb-0 text-muted small">Atur seluruh konten section Tentang pada landing page, termasuk gambar, judul, deskripsi, poin, dan CTA.</p>
            </div>
        </div>
        <div class="admin-card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="admin-form-label">Label / Kicker</label>
                    <input type="text" name="about_kicker" class="form-control admin-form-control" value="<?= esc($settings['about_kicker'] ?? 'Lorem Ipsum'); ?>" placeholder="Lorem Ipsum">
                </div>

                <div class="col-md-6">
                    <label class="admin-form-label">Gambar Section Tentang</label>
                    <input type="file" name="about_image" class="form-control admin-form-control" accept="image/png,image/jpeg,image/webp">
                    <div class="form-text">PNG/JPG/WEBP, maksimal 5 MB. Jika kosong, frontend menampilkan placeholder Lorem Ipsum.</div>
                    <?php if (!empty($settings['about_image'])) : ?>
                        <img src="<?= esc(base_url(ltrim($settings['about_image'], '/'))); ?>" alt="Preview gambar Tentang" class="img-fluid mt-2 rounded" style="max-height: 160px;">
                    <?php endif; ?>
                </div>

                <div class="col-12">
                    <label class="admin-form-label">Judul Utama</label>
                    <input type="text" name="about_title" class="form-control admin-form-control" value="<?= esc($settings['about_title'] ?? 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.'); ?>">
                </div>

                <div class="col-12">
                    <label class="admin-form-label">Deskripsi</label>
                    <textarea name="about_description" class="form-control admin-form-control" rows="4"><?= esc($settings['about_description'] ?? 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.'); ?></textarea>
                </div>

                <div class="col-md-6">
                    <label class="admin-form-label">Label Catatan Gambar</label>
                    <input type="text" name="about_note_label" class="form-control admin-form-control" value="<?= esc($settings['about_note_label'] ?? 'Lorem Ipsum'); ?>">
                </div>

                <div class="col-md-6">
                    <label class="admin-form-label">Judul Catatan Gambar</label>
                    <input type="text" name="about_note_title" class="form-control admin-form-control" value="<?= esc($settings['about_note_title'] ?? 'Lorem ipsum dolor sit amet'); ?>">
                </div>

                <?php for ($i = 1; $i <= 3; $i++) : ?>
                    <?php
                    $defaultTitles = [1 => 'Lorem Ipsum', 2 => 'Dolor Sit Amet', 3 => 'Consectetur'];
                    $defaultDescriptions = [
                        1 => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
                        2 => 'Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
                        3 => 'Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris.',
                    ];
                    ?>
                    <div class="col-md-6">
                        <label class="admin-form-label">Judul Poin <?= $i; ?></label>
                        <input type="text" name="about_point_<?= $i; ?>_title" class="form-control admin-form-control" value="<?= esc($settings['about_point_' . $i . '_title'] ?? $defaultTitles[$i]); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="admin-form-label">Deskripsi Poin <?= $i; ?></label>
                        <textarea name="about_point_<?= $i; ?>_description" class="form-control admin-form-control" rows="2"><?= esc($settings['about_point_' . $i . '_description'] ?? $defaultDescriptions[$i]); ?></textarea>
                    </div>
                <?php endfor; ?>

                <div class="col-md-6">
                    <label class="admin-form-label">Teks Tombol CTA</label>
                    <input type="text" name="about_cta_text" class="form-control admin-form-control" value="<?= esc($settings['about_cta_text'] ?? 'Lorem Ipsum'); ?>">
                </div>

                <div class="col-md-6">
                    <label class="admin-form-label">Catatan di Samping CTA</label>
                    <input type="text" name="about_cta_note" class="form-control admin-form-control" value="<?= esc($settings['about_cta_note'] ?? 'Lorem ipsum dolor sit amet'); ?>">
                </div>
            </div>
        </div>
    </div>

    <div class="admin-card mt-4">
        <div class="admin-card-header">
            <div>
                <h2>Informasi Rekening</h2>
                <p class="mb-0 text-muted small">Dipakai untuk informasi pembayaran manual. Kredensial Midtrans tetap disimpan di .env.</p>
            </div>
        </div>
        <div class="admin-card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="admin-form-label">Nama Bank</label>
                    <input type="text" name="bank_name" class="form-control admin-form-control" value="<?= esc($settings['bank_name'] ?? ''); ?>" placeholder="Contoh: Bank Syariah Indonesia">
                </div>
                <div class="col-md-4">
                    <label class="admin-form-label">Nomor Rekening</label>
                    <input type="text" name="bank_account_number" class="form-control admin-form-control" value="<?= esc($settings['bank_account_number'] ?? ''); ?>">
                </div>
                <div class="col-md-4">
                    <label class="admin-form-label">Atas Nama</label>
                    <input type="text" name="bank_account_name" class="form-control admin-form-control" value="<?= esc($settings['bank_account_name'] ?? ''); ?>">
                </div>
            </div>
        </div>
    </div>

    <div class="admin-card mt-4">
        <div class="admin-card-header">
            <div>
                <h2>Kategori Program Paket</h2>
                <p class="mb-0 text-muted small">Satu kategori per baris dengan format <code>slug:Label</code>. Contoh: <code>reguler:Reguler</code>.</p>
            </div>
        </div>
        <div class="admin-card-body">
            <textarea name="package_program_options" class="form-control admin-form-control" rows="5" placeholder="reguler:Reguler&#10;plus:Plus"><?= esc($settings['package_program_options'] ?? "reguler:Reguler
plus:Plus
ramadhan:Ramadhan
haji-khusus:Haji Khusus"); ?></textarea>
        </div>
    </div>

    <div class="admin-card mt-4">
        <div class="admin-card-header">
            <div>
                <h2>Pengaturan Chatbot</h2>
                <p class="mb-0 text-muted small">Atur status, nama asisten, sapaan awal, dan jawaban ketika informasi belum tersedia.</p>
            </div>
        </div>
        <div class="admin-card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="admin-form-label">Status Chatbot</label>
                    <select name="chatbot_enabled" class="form-select admin-form-control">
                        <option value="1" <?= ($settings['chatbot_enabled'] ?? '1') === '1' ? 'selected' : ''; ?>>Aktif</option>
                        <option value="0" <?= ($settings['chatbot_enabled'] ?? '1') === '0' ? 'selected' : ''; ?>>Nonaktif</option>
                    </select>
                </div>
                <div class="col-md-8">
                    <label class="admin-form-label">Nama Chatbot</label>
                    <input type="text" name="chatbot_name" class="form-control admin-form-control" value="<?= esc($settings['chatbot_name'] ?? 'Asisten Travel'); ?>">
                </div>
                <div class="col-12">
                    <label class="admin-form-label">Pesan Sambutan</label>
                    <textarea name="chatbot_welcome_message" class="form-control admin-form-control" rows="3"><?= esc($settings['chatbot_welcome_message'] ?? ''); ?></textarea>
                </div>
                <div class="col-12">
                    <label class="admin-form-label">Pesan Ketika Informasi Tidak Ditemukan</label>
                    <textarea name="chatbot_fallback_message" class="form-control admin-form-control" rows="3"><?= esc($settings['chatbot_fallback_message'] ?? ''); ?></textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end mt-4">
        <button type="submit" class="btn btn-admin-primary px-4">
            <i class="bi bi-check2-circle"></i> Simpan Semua Pengaturan
        </button>
    </div>
</form>

<?= $this->endSection(); ?>
