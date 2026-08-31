<?= $this->extend('templates/admin/layouts/main'); ?>
<?= $this->section('content_header'); ?>
<div class="admin-page-title d-flex flex-column flex-lg-row align-items-lg-end justify-content-between gap-3">
    <div>
        <span>Konten Website</span>
        <h1>Media Gallery</h1>
        <p>Kelola foto dan video Reels perjalanan yang tampil pada halaman publik.</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="<?= base_url('/admin/galleries/trash'); ?>" class="btn btn-admin-outline"><i class="bi bi-trash3"></i> Sampah</a>
        <a href="<?= base_url('/admin/galleries/create'); ?>" class="btn btn-admin-primary"><i class="bi bi-plus-lg"></i> Tambah Media</a>
    </div>
</div>
<?= $this->endSection(); ?>

<?= $this->section('content'); ?>
<?php if (session()->getFlashdata('success')) : ?><div class="alert alert-success rounded-4"><?= esc(session()->getFlashdata('success')); ?></div><?php endif; ?>
<?php if (session()->getFlashdata('error')) : ?><div class="alert alert-danger rounded-4"><?= esc(session()->getFlashdata('error')); ?></div><?php endif; ?>

<div class="media-stats-grid mb-4">
    <div class="media-stat-card"><i class="bi bi-collection-play"></i><div><span>Total Media</span><strong><?= count($galleries); ?></strong></div></div>
    <div class="media-stat-card"><i class="bi bi-images"></i><div><span>Foto</span><strong><?= esc($totalImages); ?></strong></div></div>
    <div class="media-stat-card"><i class="bi bi-camera-reels"></i><div><span>Video Reels</span><strong><?= esc($totalVideos); ?></strong></div></div>
</div>

<form action="<?= base_url('/admin/galleries/bulk-delete'); ?>" method="post" data-bulk-scope data-bulk-form>
    <?= csrf_field(); ?>
    <div class="admin-card">
        <div class="admin-card-header">
            <div><span class="lead-card-kicker">Media Aktif</span><h2>Daftar Foto & Video</h2></div>
            <span class="lead-live-badge"><span></span> <?= count($galleries); ?> media</span>
        </div>

        <div class="bulk-action-bar" data-bulk-toolbar hidden>
            <div class="bulk-action-copy"><i class="bi bi-check2-square"></i><span><strong data-bulk-count>0</strong> media dipilih</span></div>
            <button class="btn btn-danger btn-sm rounded-pill" data-confirm="Pindahkan {count} media ke Sampah?"><i class="bi bi-trash3"></i> Pindahkan ke Sampah</button>
        </div>

        <div class="table-responsive">
            <table class="admin-table media-admin-table">
                <thead><tr><th class="admin-select-cell"><input type="checkbox" class="admin-bulk-check" data-check-all></th><th>Preview</th><th>Informasi</th><th>Jenis</th><th>Ukuran</th><th>Urutan</th><th>Status</th><th>Aksi</th></tr></thead>
                <tbody>
                <?php if (empty($galleries)) : ?>
                    <tr><td colspan="8"><div class="admin-empty"><i class="bi bi-collection-play"></i> Belum ada media galeri.</div></td></tr>
                <?php else : ?>
                    <?php foreach ($galleries as $gallery) : ?>
                        <tr>
                            <td class="admin-select-cell"><input type="checkbox" name="selected_ids[]" value="<?= esc($gallery['id']); ?>" class="admin-bulk-check" data-bulk-item></td>
                            <td>
                                <div class="admin-media-preview is-<?= esc($gallery['media_type']); ?>">
                                    <?php if ($gallery['media_type'] === 'video' && !empty($gallery['preview_url'])) : ?>
                                        <video muted playsinline preload="metadata" poster="<?= esc($gallery['poster_url'] ?? ''); ?>"><source src="<?= esc($gallery['preview_url']); ?>"></video>
                                        <span class="admin-media-play"><i class="bi bi-play-fill"></i></span>
                                    <?php elseif (!empty($gallery['preview_url'])) : ?>
                                        <img src="<?= esc($gallery['preview_url']); ?>" alt="<?= esc($gallery['title']); ?>" loading="lazy">
                                    <?php else : ?>
                                        <span class="admin-media-placeholder"><i class="bi bi-file-earmark-x"></i></span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td><strong><?= esc($gallery['title']); ?></strong><br><small><?= esc($gallery['description'] ?: '-'); ?></small></td>
                            <td><span class="status-pill <?= $gallery['media_type'] === 'video' ? 'status-warning' : 'status-success'; ?>"><?= $gallery['media_type'] === 'video' ? 'Video' : 'Foto'; ?></span></td>
                            <td><?= esc($gallery['file_size_hr']); ?><?php if ($gallery['media_type'] === 'video' && $gallery['duration_hr'] !== '-') : ?><br><small><?= esc($gallery['duration_hr']); ?></small><?php endif; ?></td>
                            <td><?= esc($gallery['sort_order'] ?? 0); ?></td>
                            <td><span class="status-pill <?= ($gallery['status'] ?? '') === 'active' ? 'status-success' : 'status-muted'; ?>"><?= ($gallery['status'] ?? '') === 'active' ? 'Aktif' : 'Nonaktif'; ?></span></td>
                            <td><a href="<?= base_url('/admin/galleries/edit/' . $gallery['id']); ?>" class="btn btn-admin-primary btn-sm"><i class="bi bi-pencil-square"></i> Edit</a></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</form>
<?= $this->endSection(); ?>
