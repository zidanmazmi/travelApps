<?= $this->extend('templates/admin/layouts/main'); ?>
<?= $this->section('content_header'); ?>
<div class="admin-page-title d-flex flex-column flex-lg-row align-items-lg-end justify-content-between gap-3">
    <div><span>Produk Travel</span><h1>Paket &amp; Jadwal</h1><p>Kelola paket yang tampil di website, jadwal keberangkatan, harga, dan status ketersediaannya.</p></div>
    <div class="d-flex flex-wrap gap-2"><a href="<?= base_url('/admin/packages/trash'); ?>" class="btn btn-admin-outline"><i class="bi bi-trash3"></i> Sampah</a><a href="<?= base_url('/admin/packages/create'); ?>" class="btn btn-admin-primary"><i class="bi bi-plus-lg"></i> Tambah Paket</a></div>
</div>
<?= $this->endSection(); ?>
<?= $this->section('content'); ?>
<?php if (session()->getFlashdata('success')) : ?><div class="alert alert-success rounded-4"><?= esc(session()->getFlashdata('success')); ?></div><?php endif; ?>
<?php if (session()->getFlashdata('error')) : ?><div class="alert alert-danger rounded-4"><?= esc(session()->getFlashdata('error')); ?></div><?php endif; ?>

<form action="<?= base_url('/admin/packages/bulk-delete'); ?>" method="post" data-bulk-scope data-bulk-form>
    <?= csrf_field(); ?>
    <div class="admin-card">
        <div class="admin-card-header"><div><span class="lead-card-kicker">Katalog Aktif</span><h2>Daftar Paket</h2></div><span class="lead-live-badge"><span></span> <?= count($packages); ?> paket</span></div>
        <div class="bulk-action-bar" data-bulk-toolbar hidden>
            <div class="bulk-action-copy"><i class="bi bi-check2-square"></i><span><strong data-bulk-count>0</strong> paket dipilih</span></div>
            <div class="bulk-action-controls"><button type="submit" class="btn btn-danger btn-sm rounded-pill" data-confirm="Pindahkan {count} paket terpilih ke Sampah?"><i class="bi bi-trash3"></i> Pindahkan ke Sampah</button></div>
        </div>
        <div class="table-responsive">
            <table class="admin-table">
                <thead><tr><th class="admin-select-cell"><input type="checkbox" class="admin-bulk-check" data-check-all></th><th>Cover</th><th>Nama Paket</th><th>Program</th><th>Harga</th><th>Durasi</th><th>Status</th><th>Slug</th><th>Aksi</th></tr></thead>
                <tbody>
                <?php if (empty($packages)) : ?><tr><td colspan="9"><div class="admin-empty"><i class="bi bi-box-seam"></i> Belum ada data paket.</div></td></tr>
                <?php else : ?>
                    <?php foreach ($packages as $package) : ?>
                        <tr>
                            <td class="admin-select-cell"><input type="checkbox" name="selected_ids[]" value="<?= esc($package['id']); ?>" class="admin-bulk-check" data-bulk-item></td>
                            <td><?php if (!empty($package['cover_image'])) : ?><img src="<?= base_url($package['cover_image']); ?>" alt="<?= esc($package['name']); ?>" class="admin-package-thumb"><?php else : ?><div class="admin-package-placeholder"><i class="bi bi-image"></i></div><?php endif; ?></td>
                            <td><strong><?= esc($package['name']); ?></strong><br><small><?= esc($package['badge'] ?: '-'); ?></small></td>
                            <td><span class="package-program-pill"><?= esc($programOptions[$package['program'] ?? ''] ?? 'Belum dipilih'); ?></span></td>
                            <td><strong>Rp <?= number_format((float) ($package['price'] ?? 0), 0, ',', '.'); ?></strong></td>
                            <td><?= esc($package['duration_days'] ?? 0); ?> Hari · <?= esc($package['duration_nights'] ?? 0); ?> Malam</td>
                            <td><span class="status-pill <?= ($package['status'] ?? '') === 'active' ? 'status-success' : 'status-muted'; ?>"><?= ($package['status'] ?? '') === 'active' ? 'Aktif' : ucfirst((string) ($package['status'] ?? 'nonaktif')); ?></span></td>
                            <td><small><?= esc($package['slug']); ?></small></td>
                            <td><div class="admin-table-action"><a href="<?= base_url('/admin/packages/departures/' . $package['id']); ?>" class="btn btn-admin-outline btn-sm">Jadwal</a><a href="<?= base_url('/paket/' . $package['slug']); ?>" target="_blank" class="btn btn-admin-outline btn-sm">Lihat</a><a href="<?= base_url('/admin/packages/edit/' . $package['id']); ?>" class="btn btn-admin-primary btn-sm">Edit</a></div></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</form>
<?= $this->endSection(); ?>
