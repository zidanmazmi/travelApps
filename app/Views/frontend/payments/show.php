<?= $this->extend('templates/frontend/layouts/main'); ?>

<?= $this->section('content'); ?>

<section class="payment-page">
    <div class="container">

        <div class="payment-header text-center">
            <span class="section-label">Pembayaran</span>

            <h1>Pembayaran Paket</h1>

            <p>
                Upload bukti transfer untuk nomor pendaftaran
                <strong><?= esc($registration['registration_no']); ?></strong>.
            </p>
        </div>

        <div class="row g-4 justify-content-center">

            <div class="col-lg-5">
                <div class="payment-card h-100">

                    <h2>Ringkasan Tagihan</h2>

                    <div class="payment-summary">
                        <div>
                            <span>Nomor Pendaftaran</span>
                            <strong><?= esc($registration['registration_no']); ?></strong>
                        </div>

                        <div>
                            <span>Paket</span>
                            <strong><?= esc($registration['package_name']); ?></strong>
                        </div>

                        <div>
                            <span>Status Pembayaran</span>
                            <strong><?= esc($registration['payment_status']); ?></strong>
                        </div>

                        <div>
                            <span>Total Tagihan</span>
                            <strong class="payment-total">
                                Rp <?= number_format($registration['total_amount'], 0, ',', '.'); ?>
                            </strong>
                        </div>
                    </div>

                    <?php
                    $bankName = trim(site_setting('bank_name'));
                    $bankAccountNumber = trim(site_setting('bank_account_number'));
                    $bankAccountName = trim(site_setting('bank_account_name'));
                    ?>
                    <div class="bank-info">
                        <h3>Transfer Ke Rekening</h3>

                        <?php if ($bankName !== '' && $bankAccountNumber !== '' && $bankAccountName !== '') : ?>
                            <p>
                                <strong><?= esc($bankName); ?></strong><br>
                                No. Rekening: <strong><?= esc($bankAccountNumber); ?></strong><br>
                                Atas Nama: <strong><?= esc($bankAccountName); ?></strong>
                            </p>
                        <?php else : ?>
                            <p class="mb-0">Informasi rekening belum dikonfigurasi. Silakan hubungi admin sebelum melakukan transfer.</p>
                        <?php endif; ?>
                    </div>

                </div>
            </div>

            <div class="col-lg-7">
                <div class="payment-card h-100">

                    <h2>Upload Bukti Pembayaran</h2>

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

                    <?php $errors = session()->getFlashdata('errors') ?? []; ?>

                    <?php if (!empty($payment)) : ?>
                        <div class="alert alert-info">
                            Bukti pembayaran sudah dikirim dengan status:
                            <strong><?= esc($payment['payment_status']); ?></strong>
                        </div>

                        <?php if (!empty($payment['proof_file'])) : ?>
                            <div class="payment-proof-preview">
                                <img 
                                    src="<?= base_url($payment['proof_file']); ?>" 
                                    alt="Bukti Pembayaran"
                                >
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php if (empty($payment) || $payment['payment_status'] !== 'Menunggu Verifikasi') : ?>
                        <form action="<?= base_url('/pembayaran/kirim'); ?>" method="post" enctype="multipart/form-data">
                            <?= csrf_field(); ?>

                            <input 
                                type="hidden" 
                                name="registration_no" 
                                value="<?= esc($registration['registration_no']); ?>"
                            >

                            <div class="mb-3">
                                <label class="form-label">Metode Pembayaran</label>
                                <select name="payment_method" class="form-control" required>
                                    <option value="">Pilih Metode</option>
                                    <option value="Transfer Bank" <?= old('payment_method') === 'Transfer Bank' ? 'selected' : ''; ?>>
                                        Transfer Bank
                                    </option>
                                </select>

                                <?php if (isset($errors['payment_method'])) : ?>
                                    <small class="text-danger"><?= esc($errors['payment_method']); ?></small>
                                <?php endif; ?>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Bank Pengirim</label>
                                <input 
                                    type="text" 
                                    name="bank_name" 
                                    class="form-control"
                                    value="<?= old('bank_name'); ?>"
                                    placeholder="Contoh: BCA / BRI / Mandiri"
                                >
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Nama Pemilik Rekening</label>
                                <input 
                                    type="text" 
                                    name="account_name" 
                                    class="form-control"
                                    value="<?= old('account_name'); ?>"
                                    required
                                >

                                <?php if (isset($errors['account_name'])) : ?>
                                    <small class="text-danger"><?= esc($errors['account_name']); ?></small>
                                <?php endif; ?>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Upload Bukti Transfer</label>
                                <input 
                                    type="file" 
                                    name="proof_file" 
                                    class="form-control"
                                    accept=".jpg,.jpeg,.png,.webp"
                                    required
                                >

                                <?php if (isset($errors['proof_file'])) : ?>
                                    <small class="text-danger"><?= esc($errors['proof_file']); ?></small>
                                <?php endif; ?>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Catatan</label>
                                <textarea 
                                    name="note" 
                                    rows="3" 
                                    class="form-control"
                                    placeholder="Opsional"><?= old('note'); ?></textarea>
                            </div>

                            <div class="d-flex flex-column flex-sm-row gap-3">
                                <button type="submit" class="btn btn-brand-primary">
                                    Kirim Bukti Pembayaran
                                </button>

                                <a href="<?= base_url('/daftar/berhasil/' . $registration['registration_no']); ?>" class="btn btn-outline-secondary">
                                    Kembali
                                </a>
                            </div>
                        </form>
                    <?php endif; ?>

                </div>
            </div>

        </div>

    </div>
</section>

<?= $this->endSection(); ?>