<?= $this->extend('templates/admin/layouts/main'); ?>
<?= $this->section('content_header'); ?>
<div class="admin-page-title d-flex flex-column flex-lg-row align-items-lg-end justify-content-between gap-3">
    <div><span>Recycle Bin</span><h1>Sampah Media Gallery</h1><p>Pulihkan media atau hapus foto, video, dan thumbnail secara permanen.</p></div>
    <div class="d-flex gap-2 flex-wrap"><a href="<?= base_url('/admin/recycle-bin'); ?>" class="btn btn-admin-outline"><i class="bi bi-trash3"></i> Pusat Sampah</a><a href="<?= base_url('/admin/galleries'); ?>" class="btn btn-admin-outline"><i class="bi bi-arrow-left"></i> Kembali</a></div>
</div>
<?= $this->endSection(); ?>

<?= $this->section('content'); ?>
<?php if (session()->getFlashdata('success')) : ?><div class="alert alert-success rounded-4"><?= esc(session()->getFlashdata('success')); ?></div><?php endif; ?>
<?php if (session()->getFlashdata('error')) : ?><div class="alert alert-danger rounded-4"><?= esc(session()->getFlashdata('error')); ?></div><?php endif; ?>

<div class="admin-card">
    <div class="admin-card-header">
        <div><span class="lead-card-kicker">Media Terhapus</span><h2>Daftar Sampah</h2></div>
        <?php if (session()->get('admin_role') === 'super_admin' && !empty($galleries)) : ?>
            <form action="<?= base_url('/admin/galleries/trash/empty'); ?>" method="post" data-confirm="Hapus permanen seluruh media di Sampah beserta file foto, video, dan thumbnailnya?">
                <?= csrf_field(); ?>
                <button class="btn btn-danger btn-sm rounded-pill"><i class="bi bi-trash3-fill"></i> Hapus Semua Sampah</button>
            </form>
        <?php endif; ?>
    </div>

    <form action="<?= base_url('/admin/galleries/trash/bulk-action'); ?>" method="post" data-bulk-scope data-bulk-form>
        <?= csrf_field(); ?>
        <div class="bulk-action-bar" data-bulk-toolbar hidden>
            <div class="bulk-action-copy"><i class="bi bi-check2-square"></i><span><strong data-bulk-count>0</strong> media dipilih</span></div>
            <div class="bulk-action-controls">
                <select name="bulk_action" class="form-select form-select-sm">
                    <option value="restore">Pulihkan</option>
                    <?php if (session()->get('admin_role') === 'super_admin') : ?><option value="force_delete">Hapus permanen</option><?php endif; ?>
                </select>
                <button class="btn btn-admin-primary btn-sm" data-confirm="Jalankan aksi untuk {count} media terpilih?">Terapkan</button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="admin-table media-admin-table">
                <thead><tr><th class="admin-select-cell"><input type="checkbox" class="admin-bulk-check" data-check-all></th><th>Preview</th><th>Judul</th><th>Jenis</th><th>Ukuran</th><th>Dihapus</th></tr></thead>
                <tbody>
                <?php if (empty($galleries)) : ?>
                    <tr><td colspan="6"><div class="admin-empty"><i class="bi bi-trash3"></i> Sampah media masih kosong.</div></td></tr>
                <?php else : ?>
                    <?php foreach ($galleries as $gallery) : ?>
                        <tr>
                            <td class="admin-select-cell"><input type="checkbox" name="selected_ids[]" value="<?= esc($gallery['id']); ?>" class="admin-bulk-check" data-bulk-item></td>
                            <td>
                                <div class="admin-media-preview is-<?= esc($gallery['media_type']); ?>">
                                    <?php if ($gallery['media_type'] === 'video' && !empty($gallery['preview_url'])) : ?>
                                        <video muted playsinline preload="metadata" poster="<?= esc($gallery['poster_url'] ?? ''); ?>"><source src="<?= esc($gallery['preview_url']); ?>"></video><span class="admin-media-play"><i class="bi bi-play-fill"></i></span>
                                    <?php elseif (!empty($gallery['preview_url'])) : ?>
                                        <img src="<?= esc($gallery['preview_url']); ?>" alt="" loading="lazy">
                                    <?php else : ?>
                                        <span class="admin-media-placeholder"><i class="bi bi-file-earmark-x"></i></span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td><strong><?= esc($gallery['title']); ?></strong><br><small><?= esc($gallery['description'] ?: '-'); ?></small></td>
                            <td><span class="status-pill <?= $gallery['media_type'] === 'video' ? 'status-warning' : 'status-success'; ?>"><?= $gallery['media_type'] === 'video' ? 'Video' : 'Foto'; ?></span></td>
                            <td><?= esc($gallery['file_size_hr']); ?></td>
                            <td><?= !empty($gallery['deleted_at']) ? date('d M Y H:i', strtotime($gallery['deleted_at'])) : '-'; ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </form>
</div>
<?= $this->endSection(); ?>
