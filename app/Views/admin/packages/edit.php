<?= $this->extend('templates/admin/layouts/main'); ?>

<?= $this->section('content_header'); ?>

<div class="admin-page-title">
    <span>Master Data</span>
    <h1>Edit Paket Umroh</h1>
    <p>Perbarui data paket: <strong><?= esc($package['name']); ?></strong></p>
</div>

<?= $this->endSection(); ?>

<?= $this->section('content'); ?>

<?php $errors = session()->getFlashdata('errors') ?? []; ?>

<div class="mb-3">
    <a href="<?= base_url('/admin/packages'); ?>" class="btn btn-admin-outline btn-sm">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
</div>

<form action="<?= base_url('/admin/packages/update/' . $package['id']); ?>" method="post" enctype="multipart/form-data">
    <?= csrf_field(); ?>

    <div class="admin-detail-grid">

        <div class="admin-card">
            <div class="admin-card-header">
                <h2>Informasi Paket</h2>
            </div>

            <div class="admin-card-body">

                <div class="mb-3">
                    <label class="admin-form-label">Nama Paket</label>
                    <input 
                        type="text" 
                        name="name" 
                        class="form-control admin-form-control"
                        value="<?= old('name', $package['name']); ?>"
                    >

                    <?php if (isset($errors['name'])) : ?>
                        <small class="text-danger"><?= esc($errors['name']); ?></small>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label class="admin-form-label">Program Katalog</label>
                    <select name="program" class="form-control admin-form-control" required>
                        <option value="">Pilih program katalog</option>
                        <?php foreach ($programOptions as $value => $label) : ?>
                            <option
                                value="<?= esc($value); ?>"
                                <?= old('program', $package['program'] ?? '') === $value ? 'selected' : ''; ?>>
                                <?= esc($label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="text-muted d-block mt-2">
                        Program adalah label katalog untuk mengelompokkan paket, bukan paket baru.
                    </small>

                    <?php if (isset($errors['program'])) : ?>
                        <small class="text-danger"><?= esc($errors['program']); ?></small>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label class="admin-form-label">Badge</label>
                    <input 
                        type="text" 
                        name="badge" 
                        class="form-control admin-form-control"
                        value="<?= old('badge', $package['badge'] ?? ''); ?>"
                    >
                </div>

                <div class="mb-3">
                    <label class="admin-form-label">Deskripsi</label>
                    <textarea 
                        name="description" 
                        rows="6" 
                        class="form-control admin-form-control"><?= old('description', $package['description']); ?></textarea>

                    <?php if (isset($errors['description'])) : ?>
                        <small class="text-danger"><?= esc($errors['description']); ?></small>
                    <?php endif; ?>
                </div>

                <div class="admin-form-section-title mt-4 mb-3">
                    <span>Data operasional untuk chatbot</span>
                    <small>Isi sesuai paket agar bot dapat menjawab tanpa tertukar dengan program lain.</small>
                </div>

                <div class="mb-3">
                    <label class="admin-form-label">Maskapai</label>
                    <input
                        type="text"
                        name="airline"
                        class="form-control admin-form-control"
                        value="<?= esc(old('airline', $package['airline'] ?? '')); ?>"
                        placeholder="Contoh: Garuda Indonesia / Qatar Airways">
                    <?php if (isset($errors['airline'])) : ?>
                        <small class="text-danger"><?= esc($errors['airline']); ?></small>
                    <?php endif; ?>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="admin-form-label">Hotel Makkah</label>
                        <textarea
                            name="hotel_makkah"
                            rows="3"
                            class="form-control admin-form-control"
                            placeholder="Nama hotel atau beberapa pilihan hotel"><?= esc(old('hotel_makkah', $package['hotel_makkah'] ?? '')); ?></textarea>
                        <?php if (isset($errors['hotel_makkah'])) : ?>
                            <small class="text-danger"><?= esc($errors['hotel_makkah']); ?></small>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="admin-form-label">Hotel Madinah</label>
                        <textarea
                            name="hotel_madinah"
                            rows="3"
                            class="form-control admin-form-control"
                            placeholder="Nama hotel atau beberapa pilihan hotel"><?= esc(old('hotel_madinah', $package['hotel_madinah'] ?? '')); ?></textarea>
                        <?php if (isset($errors['hotel_madinah'])) : ?>
                            <small class="text-danger"><?= esc($errors['hotel_madinah']); ?></small>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="admin-form-label">Fasilitas Paket</label>
                    <textarea
                        name="facilities_text"
                        rows="4"
                        class="form-control admin-form-control"
                        placeholder="Contoh: tiket pesawat, visa umroh, hotel, makan 3x sehari, handling, tour leader"><?= esc(old('facilities_text', $package['facilities_text'] ?? '')); ?></textarea>
                    <small class="text-muted d-block mt-2">Pisahkan dengan koma atau baris baru agar jawaban chatbot mudah dibaca.</small>
                    <?php if (isset($errors['facilities_text'])) : ?>
                        <small class="text-danger"><?= esc($errors['facilities_text']); ?></small>
                    <?php endif; ?>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="admin-form-label">Durasi Hari</label>
                        <input 
                            type="number" 
                            name="duration_days" 
                            class="form-control admin-form-control"
                            value="<?= old('duration_days', $package['duration_days']); ?>"
                        >

                        <?php if (isset($errors['duration_days'])) : ?>
                            <small class="text-danger"><?= esc($errors['duration_days']); ?></small>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="admin-form-label">Durasi Malam</label>
                        <input 
                            type="number" 
                            name="duration_nights" 
                            class="form-control admin-form-control"
                            value="<?= old('duration_nights', $package['duration_nights']); ?>"
                        >

                        <?php if (isset($errors['duration_nights'])) : ?>
                            <small class="text-danger"><?= esc($errors['duration_nights']); ?></small>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>

        <div class="admin-card">
            <div class="admin-card-header">
                <h2>Harga & Tampilan</h2>
            </div>

            <div class="admin-card-body">

                <div class="mb-3">
                    <label class="admin-form-label">Harga Paket</label>
                    <input 
                        type="number" 
                        name="price" 
                        class="form-control admin-form-control"
                        value="<?= old('price', $package['price']); ?>"
                    >

                    <?php if (isset($errors['price'])) : ?>
                        <small class="text-danger"><?= esc($errors['price']); ?></small>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label class="admin-form-label">Status</label>
                    <select name="status" class="form-control admin-form-control">
                        <option value="active" <?= old('status', $package['status']) === 'active' ? 'selected' : ''; ?>>
                            Aktif
                        </option>
                        <option value="inactive" <?= old('status', $package['status']) === 'inactive' ? 'selected' : ''; ?>>
                            Nonaktif
                        </option>
                    </select>

                    <?php if (isset($errors['status'])) : ?>
                        <small class="text-danger"><?= esc($errors['status']); ?></small>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label class="admin-form-label">Cover Saat Ini</label>

                    <?php if (!empty($package['cover_image'])) : ?>
                        <img 
                            src="<?= base_url($package['cover_image']); ?>" 
                            alt="<?= esc($package['name']); ?>"
                            class="admin-package-preview"
                        >
                    <?php else : ?>
                        <div class="admin-empty border rounded-4">
                            <i class="bi bi-image"></i>
                            Belum ada cover.
                        </div>
                    <?php endif; ?>
                </div>

                <div class="mb-4">
                    <label class="admin-form-label">Ganti Cover Image</label>
                    <input 
                        type="file" 
                        name="cover_image" 
                        class="form-control admin-form-control"
                        accept=".jpg,.jpeg,.png,.webp"
                    >

                    <?php if (isset($errors['cover_image'])) : ?>
                        <small class="text-danger"><?= esc($errors['cover_image']); ?></small>
                    <?php endif; ?>

                    <small class="text-muted d-block mt-2">
                        Kosongkan jika tidak ingin mengganti cover.
                    </small>
                </div>

                <button type="submit" class="btn btn-admin-primary w-100">
                    Simpan Perubahan
                </button>

            </div>
        </div>

    </div>
</form>

<?= $this->endSection(); ?>
