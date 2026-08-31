<?= $this->extend('templates/admin/layouts/main'); ?>

<?= $this->section('content_header'); ?>
<?= $this->endSection(); ?>

<?= $this->section('content'); ?>

<div class="admin-card">
    <div class="admin-card-header">
        <h2>Daftar Pembayaran</h2>

        <a href="<?= base_url('/admin/dashboard'); ?>" class="btn btn-admin-outline btn-sm">
            Kembali Dashboard
        </a>
    </div>

    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Order ID</th>
                    <th>No. Pendaftaran</th>
                    <th>Jamaah</th>
                    <th>Paket</th>
                    <th>Status Midtrans</th>
                    <th>Status Sistem</th>
                    <th>Total</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                <?php if (empty($payments)) : ?>
                    <tr>
                        <td colspan="9">
                            <div class="admin-empty">
                                <i class="bi bi-credit-card-2-back"></i>
                                Belum ada data pembayaran.
                            </div>
                        </td>
                    </tr>
                <?php else : ?>
                    <?php foreach ($payments as $index => $payment) : ?>
                        <tr>
                            <td><?= $index + 1; ?></td>

                            <td>
                                <strong><?= esc($payment['order_id'] ?? '-'); ?></strong>
                            </td>

                            <td><?= esc($payment['registration_no'] ?? '-'); ?></td>

                            <td>
                                <strong><?= esc($payment['user_name'] ?? '-'); ?></strong><br>
                                <small><?= esc($payment['user_email'] ?? '-'); ?></small>
                            </td>

                            <td><?= esc($payment['package_name'] ?? '-'); ?></td>

                            <td>
                                <?php if (($payment['transaction_status'] ?? '') === 'settlement') : ?>
                                    <span class="status-pill status-success">settlement</span>
                                <?php elseif (($payment['transaction_status'] ?? '') === 'pending') : ?>
                                    <span class="status-pill status-warning">pending</span>
                                <?php elseif (in_array(($payment['transaction_status'] ?? ''), ['expire', 'cancel', 'deny'], true)) : ?>
                                    <span class="status-pill status-danger">
                                        <?= esc($payment['transaction_status']); ?>
                                    </span>
                                <?php else : ?>
                                    <span class="status-pill status-muted">
                                        <?= esc($payment['transaction_status'] ?? '-'); ?>
                                    </span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?php if (($payment['payment_status'] ?? '') === 'Lunas') : ?>
                                    <span class="status-pill status-success">Lunas</span>
                                <?php elseif (($payment['payment_status'] ?? '') === 'Gagal') : ?>
                                    <span class="status-pill status-danger">Gagal</span>
                                <?php else : ?>
                                    <span class="status-pill status-muted">
                                        <?= esc($payment['payment_status'] ?? '-'); ?>
                                    </span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <strong>
                                    Rp <?= number_format((float) ($payment['gross_amount'] ?? 0), 0, ',', '.'); ?>
                                </strong>
                            </td>

                            <td>
                                <a 
                                    href="<?= base_url('/admin/payments/detail/' . $payment['id']); ?>" 
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