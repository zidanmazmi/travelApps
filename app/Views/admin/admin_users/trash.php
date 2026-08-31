<?= $this->extend('templates/admin/layouts/main'); ?>
<?= $this->section('content_header'); ?>
<div class="admin-page-title d-flex flex-column flex-lg-row align-items-lg-end justify-content-between gap-3">
    <div><span>Recycle Bin</span><h1>Sampah Admin</h1><p>Pulihkan beberapa akun sekaligus sebagai nonaktif atau hapus permanen.</p></div>
    <div class="d-flex flex-wrap gap-2"><a href="<?= base_url('/admin/recycle-bin'); ?>" class="btn btn-admin-outline"><i class="bi bi-trash3"></i> Pusat Sampah</a><a href="<?= base_url('/admin/admin-users'); ?>" class="btn btn-admin-outline"><i class="bi bi-arrow-left"></i> Kembali</a></div>
</div>
<?= $this->endSection(); ?>
<?= $this->section('content'); ?>
<?php if (session()->getFlashdata('success')) : ?><div class="alert alert-success rounded-4"><?= esc(session()->getFlashdata('success')); ?></div><?php endif; ?>
<?php if (session()->getFlashdata('error')) : ?><div class="alert alert-danger rounded-4"><?= esc(session()->getFlashdata('error')); ?></div><?php endif; ?>

<div class="admin-card">
    <div class="admin-card-header">
        <div><span class="lead-card-kicker">Akun Terhapus</span><h2>Daftar Sampah</h2></div>
        <?php if (!empty($admins)) : ?>
            <form action="<?= base_url('/admin/admin-users/trash/empty'); ?>" method="post" data-confirm="Hapus permanen seluruh akun admin di Sampah?">
                <?= csrf_field(); ?><button type="submit" class="btn btn-danger btn-sm rounded-pill"><i class="bi bi-trash3-fill"></i> Hapus Semua Sampah</button>
            </form>
        <?php endif; ?>
    </div>

    <form action="<?= base_url('/admin/admin-users/trash/bulk-action'); ?>" method="post" data-bulk-scope data-bulk-form>
        <?= csrf_field(); ?>
        <div class="bulk-action-bar" data-bulk-toolbar hidden>
            <div class="bulk-action-copy"><i class="bi bi-check2-square"></i><span><strong data-bulk-count>0</strong> akun dipilih</span></div>
            <div class="bulk-action-controls">
                <select name="bulk_action" class="form-select form-select-sm" required><option value="restore">Pulihkan sebagai nonaktif</option><option value="force_delete">Hapus permanen</option></select>
                <button type="submit" class="btn btn-admin-primary btn-sm" data-confirm="Jalankan aksi untuk {count} akun terpilih?"><i class="bi bi-play-fill"></i> Terapkan</button>
            </div>
        </div>
        <div class="table-responsive">
            <table class="admin-table">
                <thead><tr><th class="admin-select-cell"><input type="checkbox" class="admin-bulk-check" data-check-all></th><th>Admin</th><th>Role</th><th>Dihapus</th><th>Oleh</th><th>Alasan</th></tr></thead>
                <tbody>
                <?php if (empty($admins)) : ?><tr><td colspan="6"><div class="admin-empty"><i class="bi bi-trash3"></i> Sampah admin masih kosong.</div></td></tr>
                <?php else : ?>
                    <?php foreach ($admins as $admin) : ?>
                        <tr>
                            <td class="admin-select-cell"><input type="checkbox" name="selected_ids[]" value="<?= esc($admin['id']); ?>" class="admin-bulk-check" data-bulk-item></td>
                            <td><strong><?= esc($admin['name'] ?? '-'); ?></strong><br><small><?= esc($admin['email'] ?? '-'); ?></small></td>
                            <td><span class="status-pill <?= ($admin['role'] ?? '') === 'super_admin' ? 'status-success' : 'status-muted'; ?>"><?= ($admin['role'] ?? '') === 'super_admin' ? 'Super Admin' : 'Admin'; ?></span></td>
                            <td><?= !empty($admin['deleted_at']) ? date('d M Y H:i', strtotime($admin['deleted_at'])) : '-'; ?></td>
                            <td><?= esc($admin['deleted_by_name'] ?? '-'); ?></td>
                            <td><?= esc($admin['delete_reason'] ?? '-'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </form>
</div>
<?= $this->endSection(); ?>
