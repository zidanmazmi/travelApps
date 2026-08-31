<?= $this->extend('templates/admin/layouts/main'); ?>

<?= $this->section('content_header'); ?>
<div class="admin-page-title d-flex flex-column flex-lg-row align-items-lg-end justify-content-between gap-3">
    <div><span>Access Control</span><h1>Manajemen Admin</h1><p>Kelola akses tim, pilih beberapa akun sekaligus, dan pindahkan akun yang tidak digunakan ke Sampah.</p></div>
    <div class="d-flex flex-wrap gap-2">
        <a href="<?= base_url('/admin/admin-users/trash'); ?>" class="btn btn-admin-outline"><i class="bi bi-trash3"></i> Sampah</a>
        <a href="<?= base_url('/admin/admin-users/create'); ?>" class="btn btn-admin-primary"><i class="bi bi-person-plus"></i> Tambah Admin</a>
    </div>
</div>
<?= $this->endSection(); ?>

<?= $this->section('content'); ?>
<?php if (session()->getFlashdata('success')) : ?><div class="alert alert-success rounded-4"><?= esc(session()->getFlashdata('success')); ?></div><?php endif; ?>
<?php if (session()->getFlashdata('error')) : ?><div class="alert alert-danger rounded-4"><?= esc(session()->getFlashdata('error')); ?></div><?php endif; ?>

<form action="<?= base_url('/admin/admin-users/bulk-delete'); ?>" method="post" data-bulk-scope data-bulk-form>
    <?= csrf_field(); ?>
    <div class="admin-card">
        <div class="admin-card-header"><div><span class="lead-card-kicker">Akun Aktif</span><h2>Daftar Admin</h2></div><span class="lead-live-badge"><span></span> <?= count($admins); ?> akun</span></div>

        <div class="bulk-action-bar" data-bulk-toolbar hidden>
            <div class="bulk-action-copy"><i class="bi bi-check2-square"></i><span><strong data-bulk-count>0</strong> akun dipilih</span></div>
            <div class="bulk-action-controls"><button type="submit" class="btn btn-danger btn-sm rounded-pill" data-confirm="Pindahkan {count} akun admin terpilih ke Sampah?"><i class="bi bi-trash3"></i> Pindahkan ke Sampah</button></div>
        </div>

        <div class="table-responsive">
            <table class="admin-table">
                <thead><tr><th class="admin-select-cell"><input type="checkbox" class="admin-bulk-check" data-check-all></th><th>Admin</th><th>Kontak</th><th>Role</th><th>Status</th><th>Last Login</th><th>Aksi</th></tr></thead>
                <tbody>
                <?php if (empty($admins)) : ?>
                    <tr><td colspan="7"><div class="admin-empty"><i class="bi bi-person-gear"></i> Belum ada data admin.</div></td></tr>
                <?php else : ?>
                    <?php foreach ($admins as $admin) : ?>
                        <?php $isSelf = (int) ($admin['id'] ?? 0) === (int) session()->get('admin_user_id'); ?>
                        <tr>
                            <td class="admin-select-cell">
                                <?php if (!$isSelf) : ?><input type="checkbox" name="selected_ids[]" value="<?= esc($admin['id']); ?>" class="admin-bulk-check" data-bulk-item><?php else : ?><i class="bi bi-shield-lock text-muted" title="Akun sendiri dilindungi"></i><?php endif; ?>
                            </td>
                            <td>
                                <div class="admin-user-cell">
                                    <div class="admin-user-avatar"><?= esc(strtoupper(substr((string) ($admin['name'] ?? 'A'), 0, 1))); ?></div>
                                    <div><strong><?= esc($admin['name'] ?? '-'); ?></strong><?php if ($isSelf) : ?><span class="admin-self-badge">Akun Anda</span><?php endif; ?><small>Dibuat <?= !empty($admin['created_at']) ? date('d M Y', strtotime($admin['created_at'])) : '-'; ?></small></div>
                                </div>
                            </td>
                            <td><strong><?= esc($admin['email'] ?? '-'); ?></strong><br><small><?= esc($admin['phone'] ?? '-'); ?></small></td>
                            <td><span class="status-pill <?= ($admin['role'] ?? '') === 'super_admin' ? 'status-success' : 'status-muted'; ?>"><?= ($admin['role'] ?? '') === 'super_admin' ? 'Super Admin' : 'Admin'; ?></span></td>
                            <td>
                                <?php if (($admin['status'] ?? '') === 'active') : ?><span class="status-pill status-success">Aktif</span>
                                <?php elseif (($admin['status'] ?? '') === 'blocked') : ?><span class="status-pill status-danger">Diblokir</span>
                                <?php else : ?><span class="status-pill status-muted">Nonaktif</span><?php endif; ?>
                            </td>
                            <td><?= !empty($admin['last_login']) ? date('d M Y H:i', strtotime($admin['last_login'])) : '-'; ?></td>
                            <td><a href="<?= base_url('/admin/admin-users/edit/' . $admin['id']); ?>" class="btn btn-admin-primary btn-sm"><i class="bi bi-pencil-square"></i> Kelola</a></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</form>
<?= $this->endSection(); ?>
