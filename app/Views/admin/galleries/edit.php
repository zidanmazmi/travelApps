<?= $this->extend('templates/admin/layouts/main'); ?>

<?= $this->section('content_header'); ?>
<div class="admin-page-title">
    <span>Media Gallery</span>
    <h1>Edit Media</h1>
    <p>Perbarui caption, jenis media, file utama, atau thumbnail video.</p>
</div>
<?= $this->endSection(); ?>

<?= $this->section('content'); ?>
<?php $errors = session()->getFlashdata('errors') ?? []; ?>

<?php if (session()->getFlashdata('error')) : ?>
    <div class="alert alert-danger rounded-4"><?= esc(session()->getFlashdata('error')); ?></div>
<?php endif; ?>

<div class="mb-3">
    <a href="<?= base_url('/admin/galleries'); ?>" class="btn btn-admin-outline btn-sm"><i class="bi bi-arrow-left"></i> Kembali</a>
</div>

<form action="<?= base_url('/admin/galleries/update/' . $gallery['id']); ?>" method="post" enctype="multipart/form-data" id="mediaGalleryForm">
    <?= csrf_field(); ?>
    <input type="hidden" name="duration_seconds" value="<?= old('duration_seconds', $gallery['duration_seconds'] ?? ''); ?>" data-duration-input>

    <div class="admin-detail-grid">
        <div class="admin-card">
            <div class="admin-card-header"><h2>Informasi Media</h2></div>
            <div class="admin-card-body">
                <div class="mb-4">
                    <label class="admin-form-label">Jenis Media</label>
                    <div class="media-type-selector">
                        <label class="media-type-option">
                            <input type="radio" name="media_type" value="image" <?= old('media_type', $gallery['media_type'] ?? 'image') === 'image' ? 'checked' : ''; ?>>
                            <span><i class="bi bi-image"></i><strong>Foto</strong><small>JPG, PNG, WEBP</small></span>
                        </label>
                        <label class="media-type-option">
                            <input type="radio" name="media_type" value="video" <?= old('media_type', $gallery['media_type'] ?? '') === 'video' ? 'checked' : ''; ?>>
                            <span><i class="bi bi-camera-reels"></i><strong>Video Reels</strong><small>MP4 atau WEBM</small></span>
                        </label>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="admin-form-label">Judul Media</label>
                    <input type="text" name="title" class="form-control admin-form-control" value="<?= old('title', $gallery['title'] ?? ''); ?>">
                    <?php if (isset($errors['title'])) : ?><small class="text-danger"><?= esc($errors['title']); ?></small><?php endif; ?>
                </div>

                <div class="mb-3">
                    <label class="admin-form-label">Caption / Deskripsi</label>
                    <textarea name="description" class="form-control admin-form-control" rows="5"><?= old('description', $gallery['description'] ?? ''); ?></textarea>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="admin-form-label">Rasio Tampilan</label>
                        <select name="aspect_ratio" class="form-control admin-form-control" data-aspect-select>
                            <option value="portrait" <?= old('aspect_ratio', $gallery['aspect_ratio'] ?? 'portrait') === 'portrait' ? 'selected' : ''; ?>>Portrait 9:16 (Reels)</option>
                            <option value="landscape" <?= old('aspect_ratio', $gallery['aspect_ratio'] ?? '') === 'landscape' ? 'selected' : ''; ?>>Landscape 16:9</option>
                            <option value="square" <?= old('aspect_ratio', $gallery['aspect_ratio'] ?? '') === 'square' ? 'selected' : ''; ?>>Square 1:1</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="admin-form-label">Urutan Tampil</label>
                        <input type="number" name="sort_order" class="form-control admin-form-control" value="<?= old('sort_order', $gallery['sort_order'] ?? 0); ?>">
                    </div>
                </div>

                <div class="mt-3 mb-4">
                    <label class="admin-form-label">Status</label>
                    <select name="status" class="form-control admin-form-control">
                        <option value="active" <?= old('status', $gallery['status'] ?? 'active') === 'active' ? 'selected' : ''; ?>>Aktif</option>
                        <option value="inactive" <?= old('status', $gallery['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Nonaktif</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="admin-card">
            <div class="admin-card-header"><h2>File Media</h2></div>
            <div class="admin-card-body">
                <?php if (!empty($gallery['preview_url'])) : ?>
                    <div class="current-media-card mb-4">
                        <?php if (($gallery['media_type'] ?? 'image') === 'video') : ?>
                            <video controls muted playsinline preload="metadata" poster="<?= esc($gallery['poster_url'] ?? ''); ?>">
                                <source src="<?= esc($gallery['preview_url']); ?>" type="<?= esc($gallery['mime_type'] ?? 'video/mp4'); ?>">
                            </video>
                        <?php else : ?>
                            <img src="<?= esc($gallery['preview_url']); ?>" alt="<?= esc($gallery['title'] ?? 'Media'); ?>">
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <div class="local-upload-zone">
                    <label class="admin-form-label" data-file-label>Ganti File Media</label>
                    <input type="file" name="media_file" class="form-control admin-form-control" data-media-file>
                    <?php if (isset($errors['media_file'])) : ?><small class="text-danger d-block mt-2"><?= esc($errors['media_file']); ?></small><?php endif; ?>
                    <small class="local-upload-help" data-file-help>Kosongkan jika file utama tidak ingin diganti.</small>
                </div>

                <div class="media-live-preview mt-4" data-media-preview hidden></div>

                <div class="mt-4" data-thumbnail-group <?= ($gallery['media_type'] ?? 'image') === 'video' ? '' : 'hidden'; ?>>
                    <?php if (!empty($gallery['poster_url'])) : ?>
                        <div class="mb-3">
                            <label class="admin-form-label">Thumbnail Saat Ini</label><br>
                            <img src="<?= esc($gallery['poster_url']); ?>" alt="Thumbnail" class="video-thumbnail-current">
                        </div>
                    <?php endif; ?>

                    <label class="admin-form-label">Ganti Thumbnail Video</label>
                    <input type="file" name="thumbnail_file" class="form-control admin-form-control" accept="image/jpeg,image/png,image/webp" data-thumbnail-file>
                    <?php if (isset($errors['thumbnail_file'])) : ?><small class="text-danger"><?= esc($errors['thumbnail_file']); ?></small><?php endif; ?>

                    <?php if (!empty($gallery['thumbnail'])) : ?>
                        <label class="d-flex align-items-center gap-2 mt-3 small">
                            <input type="checkbox" name="remove_thumbnail" value="1">
                            Hapus thumbnail saat ini
                        </label>
                    <?php endif; ?>
                </div>

                <div class="admin-note-box mt-4">
                    Saat jenis media diubah dari foto ke video atau sebaliknya, file utama baru wajib dipilih.
                </div>

                <button type="submit" class="btn btn-admin-primary w-100 mt-4">
                    <i class="bi bi-check2-circle"></i> Simpan Perubahan
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
