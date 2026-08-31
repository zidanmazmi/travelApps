<?= $this->extend('templates/admin/layouts/main'); ?>
<?= $this->section('content_header'); ?>
<div class="admin-page-title d-flex flex-column flex-lg-row align-items-lg-end justify-content-between gap-3">
    <div><span>Legacy Cleanup</span><h1>Sampah Dokumen Lama</h1><p>Modul dokumen jamaah tidak lagi menjadi alur utama. Halaman ini hanya untuk membersihkan atau memulihkan data lama.</p></div>
    <a href="<?= base_url('/admin/recycle-bin'); ?>" class="btn btn-admin-outline"><i class="bi bi-arrow-left"></i> Pusat Sampah</a>
</div>
<?= $this->endSection(); ?>
<?= $this->section('content'); ?>
<?php if (session()->getFlashdata('success')) : ?><div class="alert alert-success rounded-4"><?= esc(session()->getFlashdata('success')); ?></div><?php endif; ?>
<?php if (session()->getFlashdata('error')) : ?><div class="alert alert-danger rounded-4"><?= esc(session()->getFlashdata('error')); ?></div><?php endif; ?>
<div class="admin-card">
    <div class="admin-card-header">
        <div><span class="lead-card-kicker">Dokumen Terhapus</span><h2>Daftar Sampah</h2></div>
        <?php if (session()->get('admin_role') === 'super_admin' && !empty($documents)) : ?>
            <form action="<?= base_url('/admin/legacy-documents/trash/empty'); ?>" method="post" data-confirm="Hapus permanen seluruh dokumen lama di Sampah beserta file fisiknya?">
                <?= csrf_field(); ?><button type="submit" class="btn btn-danger btn-sm rounded-pill"><i class="bi bi-trash3-fill"></i> Hapus Semua Dokumen Sampah</button>
            </form>
        <?php endif; ?>
    </div>

    <form action="<?= base_url('/admin/legacy-documents/trash/bulk-action'); ?>" method="post" data-bulk-scope data-bulk-form>
        <?= csrf_field(); ?>
        <div class="bulk-action-bar" data-bulk-toolbar hidden>
            <div class="bulk-action-copy"><i class="bi bi-check2-square"></i><span><strong data-bulk-count>0</strong> dokumen dipilih</span></div>
            <div class="bulk-action-controls">
                <select name="bulk_action" class="form-select form-select-sm" required>
                    <option value="restore">Pulihkan dokumen</option>
                    <?php if (session()->get('admin_role') === 'super_admin') : ?><option value="force_delete">Hapus permanen + file</option><?php endif; ?>
                </select>
                <button type="submit" class="btn btn-admin-primary btn-sm" data-confirm="Jalankan aksi untuk {count} dokumen terpilih?"><i class="bi bi-play-fill"></i> Terapkan</button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="admin-table">
                <thead><tr><th class="admin-select-cell"><input type="checkbox" class="admin-bulk-check" data-check-all></th><th>Pendaftaran</th><th>Jamaah</th><th>Paket</th><th>Dokumen</th><th>Dihapus</th><th>Alasan</th></tr></thead>
                <tbody>
                <?php if (empty($documents)) : ?><tr><td colspan="7"><div class="admin-empty"><i class="bi bi-trash3"></i> Sampah dokumen lama masih kosong.</div></td></tr>
                <?php else : ?>
                    <?php foreach ($documents as $document) : ?>
                        <tr>
                            <td class="admin-select-cell"><input type="checkbox" name="selected_ids[]" value="<?= esc($document['id']); ?>" class="admin-bulk-check" data-bulk-item></td>
                            <td><strong><?= esc($document['registration_no'] ?? '-'); ?></strong></td>
                            <td><?= esc($document['pilgrim_name'] ?? $document['user_name'] ?? '-'); ?></td>
                            <td><?= esc($document['package_name'] ?? '-'); ?></td>
                            <td><strong><?= esc($document['document_type'] ?? '-'); ?></strong><br><small><?= esc($document['status'] ?? '-'); ?></small></td>
                            <td><?= !empty($document['deleted_at']) ? date('d M Y H:i', strtotime($document['deleted_at'])) : '-'; ?><br><small><?= esc($document['deleted_by_name'] ?? '-'); ?></small></td>
                            <td><?= esc($document['delete_reason'] ?? '-'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </form>
</div>
<?= $this->endSection(); ?>
