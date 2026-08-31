<?= $this->extend('templates/frontend/layouts/main'); ?>

<?= $this->section('content'); ?>

<section class="jamaah-dashboard-page">
    <div class="container">

        <div class="dashboard-header text-center">
            <h1>Assalamu’alaikum, <?= esc(session()->get('jamaah_name')); ?></h1>

            <p>
                Berikut daftar pendaftaran paket umroh Anda di <?= esc(site_setting('site_name', 'Travel Umroh & Haji')); ?>.
            </p>
        </div>

        <?php if (empty($registrations)) : ?>

            <div class="row justify-content-center">
                <div class="col-lg-7">
                    <div class="dashboard-empty-card text-center">

                        <div class="empty-icon">
                            <i class="bi bi-journal-text"></i>
                        </div>

                        <h2>Belum Ada Pendaftaran</h2>

                        <p>
                            Anda belum memiliki data pendaftaran paket umroh.
                            Silakan pilih paket terlebih dahulu untuk melakukan pendaftaran.
                        </p>

                        <a href="<?= base_url('/paket'); ?>" class="btn btn-brand-primary">
                            Lihat Paket Umroh
                        </a>

                    </div>
                </div>
            </div>

        <?php else : ?>

            <div class="row g-4">

                <?php foreach ($registrations as $registration) : ?>

                    <?php
                    $paymentStatus = $registration['payment_status'] ?? 'Belum Bayar';
                    $transactionStatus = $registration['payment']['transaction_status'] ?? 'Belum Ada Transaksi';

                    $badgeClass = 'status-warning';

                    if ($paymentStatus === 'Lunas') {
                        $badgeClass = 'status-success';
                    } elseif ($paymentStatus === 'Gagal') {
                        $badgeClass = 'status-danger';
                    }
                    ?>

                    <div class="col-lg-6">
                        <div class="jamaah-registration-card h-100">

                            <div class="card-top">
                                <div>
                                    <span class="registration-label">Nomor Pendaftaran</span>
                                    <h2><?= esc($registration['registration_no']); ?></h2>
                                </div>

                                <span class="status-badge <?= esc($badgeClass); ?>">
                                    <?= esc($paymentStatus); ?>
                                </span>
                            </div>

                            <div class="dashboard-summary">

                                <div>
                                    <span>Paket</span>
                                    <strong><?= esc($registration['package_name'] ?? '-'); ?></strong>
                                </div>

                                <div>
                                    <span>Status Pendaftaran</span>
                                    <strong><?= esc($registration['registration_status'] ?? '-'); ?></strong>
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
                                <!-- <div>
                                    <span>Status Midtrans</span>
                                    <strong><?= esc($transactionStatus); ?></strong>
                                </div> -->

                                <div>
                                    <span>Total Tagihan</span>
                                    <strong>
                                        Rp <?= number_format((float) ($registration['total_amount'] ?? 0), 0, ',', '.'); ?>
                                    </strong>
                                </div>

                                <div>
                                    <span>Total Peserta</span>
                                    <strong><?= esc($registration['total_participants'] ?? 1); ?> Jamaah</strong>
                                </div>

                                <div>
                                    <span>Tanggal Daftar</span>
                                    <strong>
                                        <?= !empty($registration['created_at']) ? date('d M Y', strtotime($registration['created_at'])) : '-'; ?>
                                    </strong>
                                </div>

                            </div>

                            <div class="dashboard-actions">

                                <?php if ($paymentStatus !== 'Lunas') : ?>
                                    <a
                                        href="<?= base_url('/pembayaran/' . $registration['registration_no']); ?>"
                                        class="btn btn-brand-primary">
                                        Lanjut Pembayaran
                                    </a>
                            
                                <?php endif; ?>

                                <form action="<?= base_url('/cek-status'); ?>" method="post" class="d-inline">
                                    <?= csrf_field(); ?>
                                    <?php if (($registration['payment_status'] ?? '') === 'Lunas') : ?>
                                        <a
                                            href="<?= base_url('/invoice/' . $registration['registration_no']); ?>"
                                            class="btn btn-brand-primary">
                                            Bukti Pembayaran
                                        </a>
                                    <?php endif; ?>
                                    <input
                                        type="hidden"
                                        name="registration_no"
                                        value="<?= esc($registration['registration_no']); ?>">
                                    </input>
                                    <button type="submit" class="btn btn-outline-secondary">
                                        Lihat Status
                                    </button>
                                    <a
                                        href="<?= base_url('/dokumen/' . $registration['registration_no']); ?>"
                                        class="btn btn-outline-secondary">
                                        Upload Dokumen
                                    </a>
                                </form>

                            </div>

                        </div>
                    </div>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>

    </div>
</section>

<?= $this->endSection(); ?>