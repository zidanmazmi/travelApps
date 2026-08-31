<?= $this->extend('templates/admin/layouts/main'); ?>

<?= $this->section('content_header'); ?>

<div class="admin-page-title">
    <h1>Edit Admin</h1>
    <p>Kelola akun admin: <strong><?= esc($admin['name'] ?? '-'); ?></strong></p>
</div>

<?= $this->endSection(); ?>

<?= $this->section('content'); ?>

<?php $errors = session()->getFlashdata('errors') ?? []; ?>

<?php if (session()->getFlashdata('success')) : ?>
    <div class="alert alert-success">
        <?= session()->getFlashdata('success'); ?>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')) : ?>
    <div class="alert alert-danger">
        <?= session()->getFlashdata('error'); ?>
    </div>
<?php endif; ?>

<div class="mb-3">
    <a href="<?= base_url('/admin/admin-users'); ?>" class="btn btn-admin-outline btn-sm">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
</div>

<div class="admin-detail-grid">

    <div class="admin-card">
        <div class="admin-card-header">
            <h2>Informasi Admin</h2>
        </div>

        <div class="admin-card-body">
            <form action="<?= base_url('/admin/admin-users/update/' . $admin['id']); ?>" method="post">
                <?= csrf_field(); ?>

                <div class="mb-3">
                    <label class="admin-form-label">Nama Admin</label>
                    <input 
                        type="text" 
                        name="name" 
                        class="form-control admin-form-control"
                        value="<?= old('name', $admin['name'] ?? ''); ?>"
                    >

                    <?php if (isset($errors['name'])) : ?>
                        <small class="text-danger"><?= esc($errors['name']); ?></small>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label class="admin-form-label">Email</label>
                    <input 
                        type="email" 
                        name="email" 
                        class="form-control admin-form-control"
                        value="<?= old('email', $admin['email'] ?? ''); ?>"
                    >

                    <?php if (isset($errors['email'])) : ?>
                        <small class="text-danger"><?= esc($errors['email']); ?></small>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label class="admin-form-label">No. HP</label>
                    <input 
                        type="text" 
                        name="phone" 
                        class="form-control admin-form-control"
                        value="<?= old('phone', $admin['phone'] ?? ''); ?>"
                    >

                    <?php if (isset($errors['phone'])) : ?>
                        <small class="text-danger"><?= esc($errors['phone']); ?></small>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label class="admin-form-label">Role</label>
                    <select name="role" class="form-control admin-form-control">
                        <option value="admin" <?= old('role', $admin['role'] ?? '') === 'admin' ? 'selected' : ''; ?>>
                            Admin
                        </option>
                        <option value="super_admin" <?= old('role', $admin['role'] ?? '') === 'super_admin' ? 'selected' : ''; ?>>
                            Super Admin
                        </option>
                    </select>

                    <?php if (isset($errors['role'])) : ?>
                        <small class="text-danger"><?= esc($errors['role']); ?></small>
                    <?php endif; ?>
                </div>

                <div class="mb-4">
                    <label class="admin-form-label">Status</label>
                    <select name="status" class="form-control admin-form-control">
                        <option value="active" <?= old('status', $admin['status'] ?? '') === 'active' ? 'selected' : ''; ?>>
                            Aktif
                        </option>
                        <option value="inactive" <?= old('status', $admin['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>
                            Nonaktif
                        </option>
                        <option value="blocked" <?= old('status', $admin['status'] ?? '') === 'blocked' ? 'selected' : ''; ?>>
                            Blocked
                        </option>
                    </select>

                    <?php if (isset($errors['status'])) : ?>
                        <small class="text-danger"><?= esc($errors['status']); ?></small>
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn btn-admin-primary w-100">
                    Simpan Perubahan
                </button>
            </form>
        </div>
    </div>

    <div class="admin-card">
        <div class="admin-card-header">
            <h2>Reset Password</h2>
        </div>

        <div class="admin-card-body">
            <form action="<?= base_url('/admin/admin-users/update-password/' . $admin['id']); ?>" method="post">
                <?= csrf_field(); ?>

                <div class="mb-3">
                    <label class="admin-form-label">Password Baru</label>
                    <input 
                        type="password" 
                        name="password" 
                        class="form-control admin-form-control"
                        placeholder="Minimal 6 karakter"
                    >

                    <?php if (isset($errors['password'])) : ?>
                        <small class="text-danger"><?= esc($errors['password']); ?></small>
                    <?php endif; ?>
                </div>

                <div class="mb-4">
                    <label class="admin-form-label">Konfirmasi Password</label>
                    <input 
                        type="password" 
                        name="password_confirmation" 
                        class="form-control admin-form-control"
                        placeholder="Ulangi password baru"
                    >

                    <?php if (isset($errors['password_confirmation'])) : ?>
                        <small class="text-danger"><?= esc($errors['password_confirmation']); ?></small>
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn btn-admin-outline w-100">
                    Reset Password
                </button>
            </form>

            <div class="admin-note-box mt-3">
                Password yang sudah direset harus diberikan secara manual kepada admin terkait.
            </div>
        </div>
    </div>

</div>

<?= $this->endSection(); ?>