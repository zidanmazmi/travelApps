<?= $this->extend('templates/admin/layouts/main'); ?>

<?= $this->section('content_header'); ?>

<div class="admin-page-title">
    <span>Master Data</span>
    <h1>Data Jamaah</h1>
    <p>Kelola akun jamaah yang terdaftar di sistem <?= esc(site_setting('site_name', 'Travel Umroh & Haji')); ?>.</p>
</div>

<?= $this->endSection(); ?>

<?= $this->section('content'); ?>

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

<div class="admin-card">
    <div class="admin-card-header">
        <h2>Daftar Jamaah</h2>

        <a href="<?= base_url('/admin/dashboard'); ?>" class="btn btn-admin-outline btn-sm">
            Dashboard
        </a>
    </div>

    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Jamaah</th>
                    <th>Email</th>
                    <th>No. HP</th>
                    <th>NIK</th>
                    <th>Verifikasi Email</th>
                    <th>Status Akun</th>
                    <th>Pendaftaran</th>
                    <th>Dokumen</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                <?php if (empty($jamaah)) : ?>
                    <tr>
                        <td colspan="10">
                            <div class="admin-empty">
                                <i class="bi bi-people"></i>
                                Belum ada data jamaah.
                            </div>
                        </td>
                    </tr>
                <?php else : ?>
                    <?php foreach ($jamaah as $index => $item) : ?>
                        <tr>
                            <td><?= $index + 1; ?></td>

                            <td>
                                <strong><?= esc($item['name'] ?? '-'); ?></strong><br>
                                <small>
                                    Terdaftar:
                                    <?= !empty($item['created_at']) ? date('d M Y', strtotime($item['created_at'])) : '-'; ?>
                                </small>
                            </td>

                            <td><?= esc($item['email'] ?? '-'); ?></td>

                            <td><?= esc($item['phone'] ?? '-'); ?></td>

                            <td><?= esc($item['nik'] ?? '-'); ?></td>

                            <td>
                                <?php if ((int) ($item['is_email_verified'] ?? 0) === 1) : ?>
                                    <span class="status-pill status-success">Terverifikasi</span>
                                <?php else : ?>
                                    <span class="status-pill status-warning">Belum</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?php if (($item['status'] ?? '') === 'active') : ?>
                                    <span class="status-pill status-success">Aktif</span>
                                <?php elseif (($item['status'] ?? '') === 'blocked') : ?>
                                    <span class="status-pill status-danger">Blocked</span>
                                <?php else : ?>
                                    <span class="status-pill status-muted">
                                        <?= esc($item['status'] ?? '-'); ?>
                                    </span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <strong><?= esc($item['total_registrations'] ?? 0); ?></strong>
                            </td>

                            <td>
                                <strong><?= esc($item['total_documents'] ?? 0); ?></strong>
                            </td>

                            <td>
                                <a 
                                    href="<?= base_url('/admin/jamaah/detail/' . $item['id']); ?>" 
                                    class="btn btn-admin-primary btn-sm">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>

        </table>
    </div>
</div>

<?= $this->endSection(); ?>