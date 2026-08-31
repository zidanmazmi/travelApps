<?= $this->extend('templates/admin/layouts/main'); ?>
<?= $this->section('content_header'); ?>
<div class="admin-page-title d-flex flex-column flex-lg-row align-items-lg-end justify-content-between gap-3">
    <div><span>Recycle Bin</span><h1>Sampah Paket</h1><p>Paket dapat dipulihkan. Hapus permanen akan menghapus paket beserta lead, pendaftaran, pembayaran, dokumen, jadwal, dan data uji terkait.</p></div>
    <div class="d-flex flex-wrap gap-2"><a href="<?= base_url('/admin/recycle-bin'); ?>" class="btn btn-admin-outline"><i class="bi bi-trash3"></i> Pusat Sampah</a><a href="<?= base_url('/admin/packages'); ?>" class="btn btn-admin-outline"><i class="bi bi-arrow-left"></i> Kembali</a></div>
</div>
<?= $this->endSection(); ?>
<?= $this->section('content'); ?>
<?php if (session()->getFlashdata('success')) : ?><div class="alert alert-success rounded-4"><?= esc(session()->getFlashdata('success')); ?></div><?php endif; ?>
<?php if (session()->getFlashdata('error')) : ?><div class="alert alert-danger rounded-4"><?= esc(session()->getFlashdata('error')); ?></div><?php endif; ?>
<div class="admin-card">
    <div class="admin-card-header"><div><span class="lead-card-kicker">Paket Terhapus</span><h2>Daftar Sampah</h2></div><?php if (session()->get('admin_role') === 'super_admin' && !empty($packages)) : ?><form action="<?= base_url('/admin/packages/trash/empty'); ?>" method="post" data-confirm="HAPUS SEMUA paket di Sampah beserta seluruh data uji terkait? Tindakan ini tidak dapat dibatalkan."><?= csrf_field(); ?><button class="btn btn-danger btn-sm rounded-pill"><i class="bi bi-trash3-fill"></i> Hapus Semua Sampah</button></form><?php endif; ?></div>
    <form action="<?= base_url('/admin/packages/trash/bulk-action'); ?>" method="post" data-bulk-scope data-bulk-form>
        <?= csrf_field(); ?>
        <div class="bulk-action-bar" data-bulk-toolbar hidden><div class="bulk-action-copy"><i class="bi bi-check2-square"></i><span><strong data-bulk-count>0</strong> paket dipilih</span></div><div class="bulk-action-controls"><select name="bulk_action" class="form-select form-select-sm"><option value="restore">Pulihkan paket</option><?php if (session()->get('admin_role') === 'super_admin') : ?><option value="force_delete">Hapus permanen</option><?php endif; ?></select><button class="btn btn-admin-primary btn-sm" data-confirm="Jalankan aksi untuk {count} paket terpilih? Untuk hapus permanen, seluruh data uji terkait juga akan dihapus.">Terapkan</button></div></div>
        <div class="table-responsive"><table class="admin-table"><thead><tr><th class="admin-select-cell"><input type="checkbox" class="admin-bulk-check" data-check-all></th><th>Paket</th><th>Harga</th><th>Status</th><th>Dihapus</th></tr></thead><tbody>
        <?php if (empty($packages)) : ?><tr><td colspan="5"><div class="admin-empty"><i class="bi bi-trash3"></i> Sampah paket masih kosong.</div></td></tr><?php else : ?>
            <?php foreach ($packages as $package) : ?><tr><td class="admin-select-cell"><input type="checkbox" name="selected_ids[]" value="<?= esc($package['id']); ?>" class="admin-bulk-check" data-bulk-item></td><td><strong><?= esc($package['name']); ?></strong><br><small><?= esc($package['slug']); ?></small></td><td>Rp <?= number_format((float) ($package['price'] ?? 0), 0, ',', '.'); ?></td><td><span class="status-pill status-muted"><?= esc($package['status'] ?? '-'); ?></span></td><td><?= !empty($package['deleted_at']) ? date('d M Y H:i', strtotime($package['deleted_at'])) : '-'; ?></td></tr><?php endforeach; ?>
        <?php endif; ?></tbody></table></div>
    </form>
</div>
<?= $this->endSection(); ?>
