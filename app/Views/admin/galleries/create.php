<?= $this->extend('templates/admin/layouts/main'); ?>

<?= $this->section('content_header'); ?>
<div class="admin-page-title">
    <span>Media Gallery</span>
    <h1>Tambah Foto atau Video</h1>
    <p>Upload dokumentasi perjalanan. Video portrait akan tampil seperti Reels di halaman depan.</p>
</div>
<?= $this->endSection(); ?>

<?= $this->section('content'); ?>
<?php $errors = session()->getFlashdata('errors') ?? []; ?>

<?php if (session()->getFlashdata('error')) : ?>
    <div class="alert alert-danger rounded-4"><?= esc(session()->getFlashdata('error')); ?></div>
<?php endif; ?>

<div class="mb-3">
    <a href="<?= base_url('/admin/galleries'); ?>" class="btn btn-admin-outline btn-sm">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
</div>

<form action="<?= base_url('/admin/galleries/store'); ?>" method="post" enctype="multipart/form-data" id="mediaGalleryForm">
    <?= csrf_field(); ?>
    <input type="hidden" name="duration_seconds" value="<?= old('duration_seconds'); ?>" data-duration-input>

    <div class="admin-detail-grid">
        <div class="admin-card">
            <div class="admin-card-header"><h2>Informasi Media</h2></div>
            <div class="admin-card-body">
                <div class="mb-4">
                    <label class="admin-form-label">Jenis Media</label>
                    <div class="media-type-selector">
                        <label class="media-type-option">
                            <input type="radio" name="media_type" value="image" <?= old('media_type', 'image') === 'image' ? 'checked' : ''; ?>>
                            <span><i class="bi bi-image"></i><strong>Foto</strong><small>JPG, PNG, WEBP</small></span>
                        </label>
                        <label class="media-type-option">
                            <input type="radio" name="media_type" value="video" <?= old('media_type') === 'video' ? 'checked' : ''; ?>>
                            <span><i class="bi bi-camera-reels"></i><strong>Video Reels</strong><small>MP4 atau WEBM</small></span>
                        </label>
                    </div>
                    <?php if (isset($errors['media_type'])) : ?><small class="text-danger"><?= esc($errors['media_type']); ?></small><?php endif; ?>
                </div>

                <div class="mb-3">
                    <label class="admin-form-label">Judul Media</label>
                    <input type="text" name="title" class="form-control admin-form-control" value="<?= old('title'); ?>" placeholder="Contoh: Suasana Jamaah di Masjid Nabawi">
                    <?php if (isset($errors['title'])) : ?><small class="text-danger"><?= esc($errors['title']); ?></small><?php endif; ?>
                </div>

                <div class="mb-3">
                    <label class="admin-form-label">Caption / Deskripsi</label>
                    <textarea name="description" class="form-control admin-form-control" rows="5" placeholder="Caption yang tampil pada foto atau Reels"><?= old('description'); ?></textarea>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="admin-form-label">Rasio Tampilan</label>
                        <select name="aspect_ratio" class="form-control admin-form-control" data-aspect-select>
                            <option value="portrait" <?= old('aspect_ratio', 'landscape') === 'portrait' ? 'selected' : ''; ?>>Portrait 9:16 (Reels)</option>
                            <option value="landscape" <?= old('aspect_ratio', 'landscape') === 'landscape' ? 'selected' : ''; ?>>Landscape 16:9</option>
                            <option value="square" <?= old('aspect_ratio') === 'square' ? 'selected' : ''; ?>>Square 1:1</option>
                        </select>
                        <?php if (isset($errors['aspect_ratio'])) : ?><small class="text-danger"><?= esc($errors['aspect_ratio']); ?></small><?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <label class="admin-form-label">Urutan Tampil</label>
                        <input type="number" name="sort_order" class="form-control admin-form-control" value="<?= old('sort_order', 0); ?>">
                        <?php if (isset($errors['sort_order'])) : ?><small class="text-danger"><?= esc($errors['sort_order']); ?></small><?php endif; ?>
                    </div>
                </div>

                <div class="mt-3 mb-4">
                    <label class="admin-form-label">Status</label>
                    <select name="status" class="form-control admin-form-control">
                        <option value="active" <?= old('status', 'active') === 'active' ? 'selected' : ''; ?>>Aktif</option>
                        <option value="inactive" <?= old('status') === 'inactive' ? 'selected' : ''; ?>>Nonaktif</option>
                    </select>
                    <?php if (isset($errors['status'])) : ?><small class="text-danger"><?= esc($errors['status']); ?></small><?php endif; ?>
                </div>
            </div>
        </div>

        <div class="admin-card">
            <div class="admin-card-header"><h2>Upload File</h2></div>
            <div class="admin-card-body">
                <div class="local-upload-zone">
                    <div class="local-upload-icon"><i class="bi bi-cloud-arrow-up"></i></div>
                    <label class="admin-form-label" data-file-label>File Foto</label>
                    <input type="file" name="media_file" class="form-control admin-form-control" accept="image/jpeg,image/png,image/webp" data-media-file>
                    <?php if (isset($errors['media_file'])) : ?><small class="text-danger d-block mt-2"><?= esc($errors['media_file']); ?></small><?php endif; ?>
                    <small class="local-upload-help" data-file-help>JPG, PNG, atau WEBP. Maksimal <?= esc($imageMaxMb); ?> MB.</small>
                </div>

                <div class="media-live-preview mt-4" data-media-preview hidden></div>

                <div class="mt-4" data-thumbnail-group hidden>
                    <label class="admin-form-label">Cover / Thumbnail Video <span class="text-muted">(opsional)</span></label>
                    <input type="file" name="thumbnail_file" class="form-control admin-form-control" accept="image/jpeg,image/png,image/webp" data-thumbnail-file>
                    <?php if (isset($errors['thumbnail_file'])) : ?><small class="text-danger"><?= esc($errors['thumbnail_file']); ?></small><?php endif; ?>
                    <div class="admin-note-box mt-3">Gunakan cover portrait 1080 × 1920. Maksimal <?= esc($thumbnailMaxMb); ?> MB.</div>
                </div>

                <div class="admin-note-box mt-4">
                    <i class="bi bi-info-circle me-1"></i>
                    Video maksimal <?= esc($videoMaxMb); ?> MB. Format MP4/WEBM, portrait 9:16, dan durasi ideal 15–90 detik.
                </div>

                <button type="submit" class="btn btn-admin-primary w-100 mt-4">
                    <i class="bi bi-cloud-arrow-up"></i> Upload & Simpan Media
                </button>
            </div>
        </div>
    </div>
</form>
<?= $this->endSection(); ?>

<?= $this->section('script'); ?>
<script>
window.mediaGalleryLimits = {
    image: <?= json_encode((int) $imageMaxMb); ?>,
    video: <?= json_encode((int) $videoMaxMb); ?>
};
</script>
<script src="<?= base_url('assets/admin/js/media-gallery-form.js'); ?>"></script>
<?= $this->endSection(); ?>
