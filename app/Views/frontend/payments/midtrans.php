<?= $this->extend('templates/frontend/layouts/main'); ?>

<?= $this->section('content'); ?>

<section class="payment-page">
    <div class="container">

        <div class="payment-header text-center">
            <span class="section-label">Pembayaran</span>

            <h1>Pembayaran Midtrans</h1>

            <p>
                Selesaikan pembayaran untuk nomor pendaftaran
                <strong><?= esc($registration['registration_no']); ?></strong>.
            </p>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-7">

                <div class="payment-card text-center">

                    <h2>Ringkasan Pembayaran</h2>

                    <?php if (session()->getFlashdata('success')) : ?>
                        <div class="alert alert-success text-start">
                            <?= session()->getFlashdata('success'); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (session()->getFlashdata('error')) : ?>
                        <div class="alert alert-danger text-start">
                            <?= session()->getFlashdata('error'); ?>
                        </div>
                    <?php endif; ?>

                    <div class="payment-summary text-start">

                        <div>
                            <span>Nomor Pendaftaran</span>
                            <strong><?= esc($registration['registration_no']); ?></strong>
                        </div>

                        <div>
                            <span>Paket</span>
                            <strong><?= esc($registration['package_name'] ?? '-'); ?></strong>
                        </div>

                        <div>
                            <span>Status Pendaftaran</span>
                            <strong>
                                <?= esc($registration['registration_status'] ?? 'Menunggu Verifikasi'); ?>
                            </strong>
                        </div>

                        <div>
                            <span>Status Pembayaran</span>
                            <strong>
                                <?= esc($registration['payment_status'] ?? 'Menunggu Pembayaran'); ?>
                            </strong>
                        </div>

                        <div>
                            <span>Status Transaksi Midtrans</span>
                            <strong>
                                <?= esc($payment['transaction_status'] ?? 'pending'); ?>
                            </strong>
                        </div>

                        <div>
                            <span>Total Tagihan</span>
                            <strong class="payment-total">
                                Rp <?= number_format((float) ($payment['gross_amount'] ?? $registration['total_amount'] ?? 0), 0, ',', '.'); ?>
                            </strong>
                        </div>

                    </div>

                    <?php if (($registration['payment_status'] ?? '') === 'Lunas') : ?>

                        <div class="alert alert-success mt-4 mb-0">
                            Pembayaran sudah lunas.
                        </div>

                    <?php else : ?>

                        <?php if (($registration['payment_status'] ?? '') !== 'Lunas') : ?>
                            <button id="pay-button" class="btn btn-brand-primary w-100">
                                Bayar Sekarang
                            </button>
                        <?php else : ?>
                            <a
                                href="<?= base_url('/invoice/' . $registration['registration_no']); ?>"
                                class="btn btn-brand-primary w-100">
                                Lihat Bukti Pembayaran
                            </a>
                        <?php endif; ?>

                    <?php endif; ?>

                    <div class="mt-3 d-flex flex-column flex-sm-row justify-content-center gap-2">

                        <a
                            href="<?= base_url('/daftar/berhasil/' . $registration['registration_no']); ?>"
                            class="btn btn-outline-secondary">
                            Kembali
                        </a>

                        <a
                            href="<?= base_url('/cek-status'); ?>"
                            class="btn btn-brand-secondary">
                            Cek Status
                        </a>

                    </div>

                </div>

            </div>
        </div>

    </div>
</section>

<script
    src="<?= !empty($isProduction) ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js'; ?>"
    data-client-key="<?= esc($clientKey); ?>">
</script>

<script>
    const payButton = document.getElementById('pay-button');

    if (payButton) {
        payButton.addEventListener('click', function() {
            window.snap.pay('<?= esc($payment['snap_token'] ?? ''); ?>', {
                onSuccess: function(result) {
                    window.location.href = '<?= base_url('/pembayaran/' . $registration['registration_no']); ?>';
                },
                onPending: function(result) {
                    window.location.href = '<?= base_url('/pembayaran/' . $registration['registration_no']); ?>';
                },
                onError: function(result) {
                    alert('Pembayaran gagal diproses.');
                    console.log(result);
                },
                onClose: function() {
                    window.location.href = '<?= base_url('/pembayaran/' . $registration['registration_no']); ?>';
                }
            });
        });
    }
</script>

<?= $this->endSection(); ?>