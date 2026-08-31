<?= $this->extend('templates/frontend/layouts/main'); ?>

<?= $this->section('content'); ?>

<section class="registration-page">
    <div class="container">

        <div class="row justify-content-center">
            <div class="col-lg-9">

                <div class="registration-success-card text-center">

                    <div class="success-icon">
                        <i class="bi bi-check-lg"></i>
                    </div>

                    <span class="section-label">
                        Pendaftaran Berhasil
                    </span>

                    <h1>Data Jamaah Berhasil Dikirim</h1>

                    <p>
                        Simpan nomor pendaftaran berikut. Nomor ini digunakan untuk
                        melakukan pembayaran dan mengecek status pendaftaran jamaah.
                    </p>

                    <div class="registration-code-box">
                        <?= esc($registration['registration_no']); ?>
                    </div>

                    <div class="registration-summary">

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
                            <strong><?= esc($registration['registration_status'] ?? 'Menunggu Verifikasi'); ?></strong>
                        </div>

                        <div>
                            <span>Status Pembayaran</span>
                            <strong><?= esc($registration['payment_status'] ?? 'Belum Bayar'); ?></strong>
                        </div>

                        <div>
                            <span>Total Peserta</span>
                            <strong><?= esc($registration['total_participants'] ?? 1); ?> Jamaah</strong>
                        </div>

                        <div>
                            <span>Total Tagihan</span>
                            <strong>
                                Rp <?= number_format((float) ($registration['total_amount'] ?? 0), 0, ',', '.'); ?>
                            </strong>
                        </div>

                    </div>

                    <?php if (!empty($pilgrims)) : ?>
                        <div class="registration-pilgrim-box mt-4 text-start">
                            <h2>Data Jamaah</h2>

                            <?php foreach ($pilgrims as $pilgrim) : ?>
                                <div class="pilgrim-item">

                                    <div>
                                        <span>Nama Jamaah</span>
                                        <strong><?= esc($pilgrim['full_name'] ?? '-'); ?></strong>
                                    </div>

                                    <div>
                                        <span>NIK</span>
                                        <strong><?= esc($pilgrim['nik'] ?? '-'); ?></strong>
                                    </div>

                                    <div>
                                        <span>Email</span>
                                        <strong><?= esc($pilgrim['email'] ?? '-'); ?></strong>
                                    </div>

                                    <div>
                                        <span>No. WhatsApp</span>
                                        <strong><?= esc($pilgrim['phone'] ?? '-'); ?></strong>
                                    </div>

                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <div class="registration-note mt-4">
                        <i class="bi bi-info-circle"></i>

                        <p>
                            Pendaftaran Anda sudah tersimpan. Silakan lanjutkan pembayaran.
                            Status pembayaran akan diperbarui setelah proses pembayaran berhasil.
                        </p>
                    </div>

                    <div class="registration-actions justify-content-center">

                        <a 
                            href="<?= base_url('/paket'); ?>" 
                            class="btn btn-outline-secondary">
                            Lihat Paket Lain
                        </a>

                        <?php if (($registration['payment_status'] ?? '') === 'Lunas') : ?>
                            <button type="button" class="btn btn-success" disabled>
                                Pembayaran Lunas
                            </button>
                        <?php else : ?>
                            <a 
                                href="<?= base_url('/pembayaran/' . $registration['registration_no']); ?>" 
                                class="btn btn-brand-primary">
                                Lanjut Pembayaran
                            </a>
                        <?php endif; ?>

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

<?= $this->endSection(); ?>