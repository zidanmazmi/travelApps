<?= $this->extend('templates/admin/layouts/main'); ?>

<?= $this->section('content_header'); ?>

<div class="admin-page-title">
    <h1>Detail Pembayaran</h1>
    <p>Order ID: <strong><?= esc($payment['order_id'] ?? '-'); ?></strong></p>
</div>

<?= $this->endSection(); ?>

<?= $this->section('content'); ?>

<div class="mb-3">
    <a href="<?= base_url('/admin/payments'); ?>" class="btn btn-admin-outline btn-sm">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
</div>

<div class="admin-card">
    <div class="admin-card-header">
        <h2>Informasi Pembayaran</h2>

        <?php if (($payment['transaction_status'] ?? '') === 'settlement') : ?>
            <span class="status-pill status-success">settlement</span>
        <?php elseif (($payment['transaction_status'] ?? '') === 'pending') : ?>
            <span class="status-pill status-warning">pending</span>
        <?php else : ?>
            <span class="status-pill status-muted">
                <?= esc($payment['transaction_status'] ?? '-'); ?>
            </span>
        <?php endif; ?>
    </div>

    <div class="admin-card-body">
        <div class="admin-info-grid">

            <div class="admin-info-item">
                <span>Order ID</span>
                <strong><?= esc($payment['order_id'] ?? '-'); ?></strong>
            </div>

            <div class="admin-info-item">
                <span>Transaction ID</span>
                <strong><?= esc($payment['transaction_id'] ?? '-'); ?></strong>
            </div>

            <div class="admin-info-item">
                <span>No. Pendaftaran</span>
                <strong><?= esc($payment['registration_no'] ?? '-'); ?></strong>
            </div>

            <div class="admin-info-item">
                <span>Nama Jamaah</span>
                <strong><?= esc($payment['user_name'] ?? '-'); ?></strong>
            </div>

            <div class="admin-info-item">
                <span>Email</span>
                <strong><?= esc($payment['user_email'] ?? '-'); ?></strong>
            </div>

            <div class="admin-info-item">
                <span>No. HP</span>
                <strong><?= esc($payment['user_phone'] ?? '-'); ?></strong>
            </div>

            <div class="admin-info-item">
                <span>Paket</span>
                <strong><?= esc($payment['package_name'] ?? '-'); ?></strong>
            </div>

            <div class="admin-info-item">
                <span>Payment Type</span>
                <strong><?= esc($payment['payment_type'] ?? '-'); ?></strong>
            </div>

            <div class="admin-info-item">
                <span>Status Midtrans</span>
                <strong><?= esc($payment['transaction_status'] ?? '-'); ?></strong>
            </div>

            <div class="admin-info-item">
                <span>Status Sistem</span>
                <strong><?= esc($payment['payment_status'] ?? '-'); ?></strong>
            </div>

            <div class="admin-info-item">
                <span>VA Number</span>
                <strong><?= esc($payment['va_number'] ?? '-'); ?></strong>
            </div>

            <div class="admin-info-item">
                <span>Payment Code</span>
                <strong><?= esc($payment['payment_code'] ?? '-'); ?></strong>
            </div>

            <div class="admin-info-item">
                <span>Total Bayar</span>
                <strong>
                    Rp <?= number_format((float) ($payment['gross_amount'] ?? 0), 0, ',', '.'); ?>
                </strong>
            </div>

            <div class="admin-info-item">
                <span>Paid At</span>
                <strong>
                    <?= !empty($payment['paid_at']) ? date('d M Y H:i', strtotime($payment['paid_at'])) : '-'; ?>
                </strong>
            </div>

            <div class="admin-info-item">
                <span>Expired At</span>
                <strong>
                    <?= !empty($payment['expired_at']) ? date('d M Y H:i', strtotime($payment['expired_at'])) : '-'; ?>
                </strong>
            </div>

            <div class="admin-info-item">
                <span>Created At</span>
                <strong>
                    <?= !empty($payment['created_at']) ? date('d M Y H:i', strtotime($payment['created_at'])) : '-'; ?>
                </strong>
            </div>

        </div>
    </div>
</div>

<div class="admin-card mt-4">
    <div class="admin-card-header">
        <h2>Raw Response Midtrans</h2>
    </div>

    <div class="admin-card-body">
        <?php if (!empty($payment['raw_response'])) : ?>
            <pre class="admin-raw-response"><?= esc(json_encode(json_decode($payment['raw_response'], true), JSON_PRETTY_PRINT)); ?></pre>
        <?php else : ?>
            <div class="admin-empty">
                <i class="bi bi-file-earmark-code"></i>
                Belum ada raw response dari Midtrans.
            </div>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection(); ?>