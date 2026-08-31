<?= $this->extend('templates/frontend/layouts/main'); ?>

<?= $this->section('content'); ?>

<?php
function maskNikStatus($nik)
{
    if (empty($nik)) {
        return '-';
    }

    return substr($nik, 0, 4) . '********' . substr($nik, -4);
}
?>

<section class="registration-page">
    <div class="container">

        <div class="row justify-content-center">
            <div class="col-lg-9">

                <div class="registration-success-card text-center">
                    <h1>Status Pendaftaran Jamaah</h1>
                    <p>
                        Berikut informasi pendaftaran dengan nomor:
                    </p>

                    <div class="registration-code-box">
                        <?= esc($registration['registration_no']); ?>
                    </div>

                    <div class="registration-summary">

                        <div>
                            <span>Paket</span>
                            <strong><?= esc($registration['package_name'] ?? '-'); ?></strong>
                        </div>

                        <div>
                            <span>Status Pendaftaran</span>
                            <strong><?= esc($registration['registration_status'] ?? '-'); ?></strong>
                        </div>

                        <div>
                            <span>Status Pembayaran</span>
                            <strong><?= esc($registration['payment_status'] ?? '-'); ?></strong>
                        </div>

                        <!-- <div>
                            <span>Status Midtrans</span>
                            <strong><?= esc($payment['transaction_status'] ?? 'Belum Ada Transaksi'); ?></strong>
                        </div> -->
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
                                        <strong><?= esc(maskNikStatus($pilgrim['nik'] ?? '')); ?></strong>
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
                            Status pembayaran akan mengikuti data terbaru dari sistem.
                            Jika pembayaran sudah dilakukan tetapi status belum berubah, silakan buka kembali halaman pembayaran untuk sinkronisasi status Midtrans.
                        </p>
                    </div>

                    <div class="registration-actions justify-content-center mt-4">

                        <?php if (($registration['payment_status'] ?? '') !== 'Lunas') : ?>
                            <a
                                href="<?= base_url('/pembayaran/' . $registration['registration_no']); ?>"
                                class="btn btn-brand-primary">
                                Lanjut Pembayaran
                            </a>
                        <?php else : ?>
                            <button type="button" class="btn btn-success" disabled>
                                Pembayaran Lunas
                            </button>
                        <?php endif; ?>

                        <a href="<?= base_url('/cek-status'); ?>" class="btn btn-outline-secondary">
                            Cek Nomor Lain
                        </a>

                        <a href="<?= base_url('/'); ?>" class="btn btn-brand-secondary">
                            Kembali ke Beranda
                        </a>

                    </div>

                </div>

            </div>
        </div>

    </div>
</section>

<?= $this->endSection(); ?>