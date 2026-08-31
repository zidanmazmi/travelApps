<?= $this->extend('templates/admin/layouts/main'); ?>

<?= $this->section('content_header'); ?>
<div class="admin-page-title d-flex flex-column flex-lg-row align-items-lg-end justify-content-between gap-3">
    <div><span>Recycle Bin</span><h1>Sampah Leads</h1><p>Pilih beberapa lead untuk dipulihkan atau dihapus permanen sekaligus.</p></div>
    <div class="d-flex flex-wrap gap-2">
        <a href="<?= base_url('/admin/recycle-bin'); ?>" class="btn btn-admin-outline"><i class="bi bi-trash3"></i> Pusat Sampah</a>
        <a href="<?= base_url('/admin/leads'); ?>" class="btn btn-admin-outline"><i class="bi bi-arrow-left"></i> Kembali ke Leads</a>
    </div>
</div>
<?= $this->endSection(); ?>

<?= $this->section('content'); ?>
<?php if (session()->getFlashdata('success')) : ?><div class="alert alert-success rounded-4"><?= esc(session()->getFlashdata('success')); ?></div><?php endif; ?>
<?php if (session()->getFlashdata('error')) : ?><div class="alert alert-danger rounded-4"><?= esc(session()->getFlashdata('error')); ?></div><?php endif; ?>

<div class="admin-card">
    <div class="admin-card-header">
        <div><span class="lead-card-kicker">Data Terhapus</span><h2>Lead di Sampah</h2></div>
        <?php if (session()->get('admin_role') === 'super_admin' && !empty($leads)) : ?>
            <form action="<?= base_url('/admin/leads/trash/empty'); ?>" method="post" data-confirm="Kosongkan seluruh Sampah leads? Semua data dan histori lead akan dihapus permanen.">
                <?= csrf_field(); ?>
                <button type="submit" class="btn btn-danger btn-sm rounded-pill"><i class="bi bi-trash3-fill"></i> Hapus Semua Sampah</button>
            </form>
        <?php endif; ?>
    </div>

    <form action="<?= base_url('/admin/leads/trash/bulk-action'); ?>" method="post" data-bulk-scope data-bulk-form>
        <?= csrf_field(); ?>
        <div class="bulk-action-bar" data-bulk-toolbar hidden>
            <div class="bulk-action-copy"><i class="bi bi-check2-square"></i><span><strong data-bulk-count>0</strong> lead dipilih</span></div>
            <div class="bulk-action-controls">
                <select name="bulk_action" class="form-select form-select-sm" required>
                    <option value="restore">Pulihkan data terpilih</option>
                    <?php if (session()->get('admin_role') === 'super_admin') : ?><option value="force_delete">Hapus permanen</option><?php endif; ?>
                </select>
                <button type="submit" class="btn btn-admin-primary btn-sm" data-confirm="Jalankan aksi untuk {count} lead terpilih?"><i class="bi bi-play-fill"></i> Terapkan</button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="admin-table">
                <thead><tr><th class="admin-select-cell"><input type="checkbox" class="admin-bulk-check" data-check-all></th><th>Lead</th><th>Calon Jamaah</th><th>Paket</th><th>Status</th><th>Dihapus</th><th>Alasan</th></tr></thead>
                <tbody>
                <?php if (empty($leads)) : ?>
                    <tr><td colspan="7"><div class="admin-empty"><i class="bi bi-trash3"></i> Sampah leads masih kosong.</div></td></tr>
                <?php else : ?>
                    <?php foreach ($leads as $lead) : ?>
                        <tr>
                            <td class="admin-select-cell"><input type="checkbox" name="selected_ids[]" value="<?= esc($lead['id']); ?>" class="admin-bulk-check" data-bulk-item></td>
                            <td><strong><?= esc($lead['lead_no'] ?? '-'); ?></strong></td>
                            <td><strong><?= esc($lead['full_name'] ?? '-'); ?></strong><br><small><?= esc($lead['phone'] ?? '-'); ?></small></td>
                            <td><?= esc($lead['package_name'] ?? '-'); ?></td>
                            <td><span class="lead-status lead-status-<?= esc($lead['status'] ?? 'new'); ?>"><?= esc($statusLabels[$lead['status'] ?? 'new'] ?? $lead['status']); ?></span></td>
                            <td><?= !empty($lead['deleted_at']) ? date('d M Y H:i', strtotime($lead['deleted_at'])) : '-'; ?><br><small><?= esc($lead['deleted_by_name'] ?? '-'); ?></small></td>
                            <td><?= esc($lead['delete_reason'] ?? '-'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </form>
</div>
<?= $this->endSection(); ?>
