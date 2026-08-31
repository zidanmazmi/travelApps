<?= $this->extend('templates/admin/layouts/main'); ?>

<?= $this->section('content_header'); ?>

<div class="admin-page-title">
    <h1>Activity Log Admin</h1>
    <p>Riwayat aktivitas admin di dalam sistem <?= esc(site_setting('site_name', 'Travel Umroh & Haji')); ?>.</p>
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
        <h2>Riwayat Aktivitas</h2>

        <form 
            action="<?= base_url('/admin/activity-logs/clear'); ?>" 
            method="post"
            data-confirm="Yakin ingin menghapus seluruh activity log secara permanen?">
            <?= csrf_field(); ?>

            <button type="submit" class="btn btn-danger btn-sm">
                Bersihkan Log
            </button>
        </form>
    </div>

    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Admin</th>
                    <th>Module</th>
                    <th>Action</th>
                    <th>Deskripsi</th>
                    <th>IP Address</th>
                    <th>Waktu</th>
                </tr>
            </thead>

            <tbody>
                <?php if (empty($logs)) : ?>
                    <tr>
                        <td colspan="7">
                            <div class="admin-empty">
                                <i class="bi bi-clock-history"></i>
                                Belum ada activity log.
                            </div>
                        </td>
                    </tr>
                <?php else : ?>
                    <?php foreach ($logs as $index => $log) : ?>
                        <tr>
                            <td><?= $index + 1; ?></td>

                            <td>
                                <strong><?= esc($log['admin_name'] ?? '-'); ?></strong><br>
                                <small><?= esc($log['admin_email'] ?? '-'); ?></small>
                            </td>

                            <td>
                                <span class="status-pill status-muted">
                                    <?= esc($log['module'] ?? '-'); ?>
                                </span>
                            </td>

                            <td>
                                <strong><?= esc($log['action'] ?? '-'); ?></strong>
                            </td>

                            <td>
                                <?= esc($log['description'] ?? '-'); ?>
                            </td>

                            <td>
                                <small><?= esc($log['ip_address'] ?? '-'); ?></small>
                            </td>

                            <td>
                                <?= !empty($log['created_at']) ? date('d M Y H:i', strtotime($log['created_at'])) : '-'; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>

        </table>
    </div>
</div>

<?= $this->endSection(); ?>