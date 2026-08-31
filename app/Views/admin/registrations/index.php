<?= $this->extend('templates/admin/layouts/main'); ?>

<?= $this->section('content_header'); ?>

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
        <h2>Daftar Pendaftaran</h2>

        <a href="<?= base_url('/admin/dashboard'); ?>" class="btn btn-admin-outline btn-sm">
            Kembali Dashboard
        </a>
    </div>

    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>No. Pendaftaran</th>
                    <th>Jamaah</th>
                    <th>Paket</th>
                    <th>Keberangkatan</th>
                    <th>Status Daftar</th>
                    <th>Status Bayar</th>
                    <th>Total</th>
                    <th>Tanggal</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                <?php if (empty($registrations)) : ?>
                    <tr>
                        <td colspan="10">
                            <div class="admin-empty">
                                <i class="bi bi-journal-x"></i>
                                Belum ada data pendaftaran.
                            </div>
                        </td>
                    </tr>
                <?php else : ?>
                    <?php foreach ($registrations as $index => $registration) : ?>
                        <tr>
                            <td><?= $index + 1; ?></td>

                            <td>
                                <strong><?= esc($registration['registration_no']); ?></strong>
                            </td>

                            <td>
                                <strong><?= esc($registration['user_name'] ?? '-'); ?></strong><br>
                                <small><?= esc($registration['user_email'] ?? '-'); ?></small>
                            </td>

                            <td><?= esc($registration['package_name'] ?? '-'); ?></td>
                            <td>
                                <?php if (!empty($registration['departure_date'])) : ?>
                                    <strong><?= date('d M Y', strtotime($registration['departure_date'])); ?></strong><br>

                                    <?php if (!empty($registration['return_date'])) : ?>
                                        <small>Pulang: <?= date('d M Y', strtotime($registration['return_date'])); ?></small>
                                    <?php endif; ?>
                                <?php else : ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="status-pill status-warning">
                                    <?= esc($registration['registration_status'] ?? '-'); ?>
                                </span>
                            </td>

                            <td>
                                <?php if (($registration['payment_status'] ?? '') === 'Lunas') : ?>
                                    <span class="status-pill status-success">Lunas</span>
                                <?php elseif (($registration['payment_status'] ?? '') === 'Gagal') : ?>
                                    <span class="status-pill status-danger">Gagal</span>
                                <?php else : ?>
                                    <span class="status-pill status-muted">
                                        <?= esc($registration['payment_status'] ?? '-'); ?>
                                    </span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <strong>
                                    Rp <?= number_format((float) ($registration['total_amount'] ?? 0), 0, ',', '.'); ?>
                                </strong>
                            </td>

                            <td>
                                <?= !empty($registration['created_at']) ? date('d M Y', strtotime($registration['created_at'])) : '-'; ?>
                            </td>

                            <td>
                                <div class="admin-table-action">
                                    <a
                                        href="<?= base_url('/admin/registrations/detail/' . $registration['id']); ?>"
                                        class="btn btn-admin-primary btn-sm">
                                        Detail
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>

        </table>
    </div>
</div>

<?= $this->endSection(); ?>