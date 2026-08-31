<?= $this->extend('templates/admin/layouts/main'); ?>

<?= $this->section('content_header'); ?>

<div class="admin-page-title">
    <h1>Tambah Admin</h1>
    <p>Buat akun admin baru untuk mengakses panel administrasi.</p>
</div>

<?= $this->endSection(); ?>

<?= $this->section('content'); ?>

<?php $errors = session()->getFlashdata('errors') ?? []; ?>

<div class="mb-3">
    <a href="<?= base_url('/admin/admin-users'); ?>" class="btn btn-admin-outline btn-sm">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
</div>

<form action="<?= base_url('/admin/admin-users/store'); ?>" method="post">
    <?= csrf_field(); ?>

    <div class="admin-detail-grid">

        <div class="admin-card">
            <div class="admin-card-header">
                <h2>Informasi Admin</h2>
            </div>

            <div class="admin-card-body">

                <div class="mb-3">
                    <label class="admin-form-label">Nama Admin</label>
                    <input 
                        type="text" 
                        name="name" 
                        class="form-control admin-form-control"
                        value="<?= old('name'); ?>"
                        placeholder="Contoh: Admin Operasional"
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
                        value="<?= old('email'); ?>"
                        placeholder="admin@email.com"
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
                        value="<?= old('phone'); ?>"
                        placeholder="08xxxxxxxxxx"
                    >

                    <?php if (isset($errors['phone'])) : ?>
                        <small class="text-danger"><?= esc($errors['phone']); ?></small>
                    <?php endif; ?>
                </div>

            </div>
        </div>

        <div class="admin-card">
            <div class="admin-card-header">
                <h2>Akses Login</h2>
            </div>

            <div class="admin-card-body">

                <div class="mb-3">
                    <label class="admin-form-label">Role</label>
                    <select name="role" class="form-control admin-form-control">
                        <option value="admin" <?= old('role') === 'admin' ? 'selected' : ''; ?>>
                            Admin
                        </option>
                        <option value="super_admin" <?= old('role') === 'super_admin' ? 'selected' : ''; ?>>
                            Super Admin
                        </option>
                    </select>

                    <?php if (isset($errors['role'])) : ?>
                        <small class="text-danger"><?= esc($errors['role']); ?></small>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label class="admin-form-label">Status</label>
                    <select name="status" class="form-control admin-form-control">
                        <option value="active" <?= old('status') === 'active' ? 'selected' : ''; ?>>
                            Aktif
                        </option>
                        <option value="inactive" <?= old('status') === 'inactive' ? 'selected' : ''; ?>>
                            Nonaktif
                        </option>
                        <option value="blocked" <?= old('status') === 'blocked' ? 'selected' : ''; ?>>
                            Blocked
                        </option>
                    </select>

                    <?php if (isset($errors['status'])) : ?>
                        <small class="text-danger"><?= esc($errors['status']); ?></small>
                    <?php endif; ?>
                </div>

                <div class="mb-4">
                    <label class="admin-form-label">Password</label>
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

                <button type="submit" class="btn btn-admin-primary w-100">
                    Simpan Admin
                </button>

            </div>
        </div>

    </div>
</form>

<?= $this->endSection(); ?>