<?= $this->extend('templates/frontend/layouts/main'); ?>

<?= $this->section('content'); ?>

<section class="invoice-page">
    <div class="container">

        <div class="invoice-action no-print">
            <a href="<?= base_url('/dashboard-jamaah'); ?>" class="btn btn-outline-secondary">
                Kembali ke Dashboard
            </a>

            <button type="button" class="btn btn-brand-primary" onclick="window.print()">
                Cetak / Simpan PDF
            </button>
        </div>

        <div class="invoice-card">

            <div class="invoice-ribbon">
                <span>LUNAS</span>
            </div>

            <div class="invoice-header">
                <div class="invoice-brand-wrap">
                    <img
                        src="<?= site_asset('logo'); ?>"
                        alt="Logo <?= esc(site_setting('site_name', 'Travel Umroh & Haji')); ?>"
                        class="invoice-brand-logo">
                    <div>
                        <h1><?= esc(site_setting('site_name', 'Travel Umroh & Haji')); ?></h1>
                        <p>Bukti Pembayaran Jamaah</p>
                    </div>
                </div>
            </div>

            <div class="invoice-meta">
                <div>
                    <span>No. Invoice</span>
                    <strong>INV-<?= esc($registration['registration_no']); ?></strong>
                </div>

                <div>
                    <span>No. Pendaftaran</span>
                    <strong><?= esc($registration['registration_no']); ?></strong>
                </div>

                <div>
                    <span>Tanggal Bayar</span>
                    <strong>
                        <?= !empty($payment['paid_at']) ? date('d M Y H:i', strtotime($payment['paid_at'])) : '-'; ?>
                    </strong>
                </div>

                <div>
                    <span>Status</span>
                    <strong><?= esc($registration['payment_status'] ?? '-'); ?></strong>
                </div>
            </div>

            <div class="invoice-section">
                <h2>Informasi Jamaah</h2>

                <div class="invoice-grid">
                    <div>
                        <span>Nama Akun</span>
                        <strong><?= esc($registration['user_name'] ?? '-'); ?></strong>
                    </div>

                    <div>
                        <span>Email</span>
                        <strong><?= esc($registration['user_email'] ?? '-'); ?></strong>
                    </div>

                    <div>
                        <span>Paket</span>
                        <strong><?= esc($registration['package_name'] ?? '-'); ?></strong>
                    </div>

                    <div>
                        <span>Jadwal Keberangkatan</span>
                        <strong>
                            <?php if (!empty($registration['departure_date'])) : ?>
                                <?= date('d M Y', strtotime($registration['departure_date'])); ?>

                                <?php if (!empty($registration['return_date'])) : ?>
                                    - <?= date('d M Y', strtotime($registration['return_date'])); ?>
                                <?php endif; ?>
                            <?php else : ?>
                                -
                            <?php endif; ?>
                        </strong>
                    </div>
                </div>
            </div>

            <div class="invoice-section">
                <h2>Data Peserta</h2>

                <div class="table-responsive">
                    <table class="invoice-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Jamaah</th>
                                <th>NIK</th>
                                <th>No. HP</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php if (empty($pilgrims)) : ?>
                                <tr>
                                    <td colspan="4" class="text-center">Data jamaah tidak tersedia.</td>
                                </tr>
                            <?php else : ?>
                                <?php foreach ($pilgrims as $index => $pilgrim) : ?>
                                    <tr>
                                        <td><?= $index + 1; ?></td>
                                        <td><?= esc($pilgrim['full_name'] ?? '-'); ?></td>
                                        <td><?= esc($pilgrim['nik'] ?? '-'); ?></td>
                                        <td><?= esc($pilgrim['phone'] ?? '-'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="invoice-section">
                <h2>Detail Pembayaran</h2>

                <table class="invoice-payment-table">
                    <tr>
                        <td>Order ID</td>
                        <td><?= esc($payment['order_id'] ?? '-'); ?></td>
                    </tr>

                    <tr>
                        <td>Transaction ID</td>
                        <td><?= esc($payment['transaction_id'] ?? '-'); ?></td>
                    </tr>

                    <tr>
                        <td>Metode Pembayaran</td>
                        <td><?= esc($payment['payment_type'] ?? '-'); ?></td>
                    </tr>
<!-- 
                    <tr>
                        <td>Status Midtrans</td>
                        <td><?= esc($payment['transaction_status'] ?? '-'); ?></td>
                    </tr> -->

                    <tr class="invoice-total">
                        <td>Total Pembayaran</td>
                        <td>
                            Rp <?= number_format((float) ($payment['gross_amount'] ?? $registration['total_amount'] ?? 0), 0, ',', '.'); ?>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="invoice-footer">
                <div>
                    <p>
                        Bukti pembayaran ini diterbitkan otomatis oleh sistem <?= esc(site_setting('site_name', 'Travel Umroh & Haji')); ?>.
                    </p>
                    <p>
                        Simpan bukti ini sebagai arsip pembayaran Anda.
                    </p>
                </div>

                <div class="invoice-sign">
                    <span><?= esc(site_setting('site_name', 'Travel Umroh & Haji')); ?></span>

                    <img
                        src="<?= base_url('assets/frontend/images/ttd.png'); ?>"
                        alt="Tanda Tangan"
                        class="invoice-sign-img">
                    <strong>Admin</strong>
                </div>
            </div>

        </div>

    </div>
</section>

<?= $this->endSection(); ?>