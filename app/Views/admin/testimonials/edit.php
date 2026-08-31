<?= $this->extend('templates/admin/layouts/main'); ?>

<?= $this->section('content_header'); ?>


<?= $this->endSection(); ?>

<?= $this->section('content'); ?>

<?php $errors = session()->getFlashdata('errors') ?? []; ?>

<div class="mb-3">
    <a href="<?= base_url('/admin/testimonials'); ?>" class="btn btn-admin-outline btn-sm">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
</div>

<form action="<?= base_url('/admin/testimonials/update/' . $testimonial['id']); ?>" method="post">
    <?= csrf_field(); ?>

    <div class="admin-detail-grid">

        <div class="admin-card">
            <div class="admin-card-header">
                <h2>Informasi Testimoni</h2>
            </div>

            <div class="admin-card-body">

                <div class="mb-3">
                    <label class="admin-form-label">Nama Jamaah</label>
                    <input 
                        type="text" 
                        name="name" 
                        class="form-control admin-form-control"
                        value="<?= old('name', $testimonial['name'] ?? ''); ?>">

                    <?php if (isset($errors['name'])) : ?>
                        <small class="text-danger"><?= esc($errors['name']); ?></small>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label class="admin-form-label">Label</label>
                    <input 
                        type="text" 
                        name="label" 
                        class="form-control admin-form-control"
                        value="<?= old('label', $testimonial['label'] ?? ''); ?>">

                    <?php if (isset($errors['label'])) : ?>
                        <small class="text-danger"><?= esc($errors['label']); ?></small>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label class="admin-form-label">Pesan Testimoni</label>
                    <textarea 
                        name="message" 
                        class="form-control admin-form-control"
                        rows="5"><?= old('message', $testimonial['message'] ?? ''); ?></textarea>

                    <?php if (isset($errors['message'])) : ?>
                        <small class="text-danger"><?= esc($errors['message']); ?></small>
                    <?php endif; ?>
                </div>

            </div>
        </div>

        <div class="admin-card">
            <div class="admin-card-header">
                <h2>Pengaturan Tampil</h2>
            </div>

            <div class="admin-card-body">

                <div class="mb-3">
                    <label class="admin-form-label">Rating</label>
                    <select name="rating" class="form-control admin-form-control">
                        <?php for ($i = 5; $i >= 1; $i--) : ?>
                            <option value="<?= $i; ?>" <?= (string) old('rating', $testimonial['rating'] ?? 5) === (string) $i ? 'selected' : ''; ?>>
                                <?= $i; ?> Bintang
                            </option>
                        <?php endfor; ?>
                    </select>

                    <?php if (isset($errors['rating'])) : ?>
                        <small class="text-danger"><?= esc($errors['rating']); ?></small>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label class="admin-form-label">Urutan Tampil</label>
                    <input 
                        type="number" 
                        name="sort_order" 
                        class="form-control admin-form-control"
                        value="<?= old('sort_order', $testimonial['sort_order'] ?? 0); ?>">

                    <?php if (isset($errors['sort_order'])) : ?>
                        <small class="text-danger"><?= esc($errors['sort_order']); ?></small>
                    <?php endif; ?>
                </div>

                <div class="mb-4">
                    <label class="admin-form-label">Status</label>
                    <select name="status" class="form-control admin-form-control">
                        <option value="active" <?= old('status', $testimonial['status'] ?? '') === 'active' ? 'selected' : ''; ?>>
                            Aktif
                        </option>
                        <option value="inactive" <?= old('status', $testimonial['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>
                            Nonaktif
                        </option>
                    </select>

                    <?php if (isset($errors['status'])) : ?>
                        <small class="text-danger"><?= esc($errors['status']); ?></small>
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn btn-admin-primary w-100">
                    Simpan Perubahan
                </button>

            </div>
        </div>

    </div>
</form>

<?= $this->endSection(); ?>