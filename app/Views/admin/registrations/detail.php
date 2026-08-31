<?= $this->extend('templates/admin/layouts/main'); ?>

<?= $this->section('content_header'); ?>

<div class="admin-page-title">
    <h1>Detail Pendaftaran</h1>
    <p>Nomor pendaftaran: <strong><?= esc($registration['registration_no']); ?></strong></p>
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

<div class="mb-3">
    <a href="<?= base_url('/admin/registrations'); ?>" class="btn btn-admin-outline btn-sm">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
    <a href="<?= base_url('/admin/documents'); ?>" class="btn btn-admin-primary btn-sm">
        <i class="bi bi-file-earmark-check"></i> Lihat Dokumen Jamaah
    </a>
</div>


<div class="admin-detail-grid">

    <div class="admin-card">
        <div class="admin-card-header">
            <h2>Informasi Pendaftaran</h2>
        </div>

        <div class="admin-card-body">
            <div class="admin-info-grid">

                <div class="admin-info-item">
                    <span>No. Pendaftaran</span>
                    <strong><?= esc($registration['registration_no']); ?></strong>
                </div>

                <div class="admin-info-item">
                    <span>Paket</span>
                    <strong><?= esc($registration['package_name'] ?? '-'); ?></strong>
                </div>
                <div class="admin-info-item">
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
                <div class="admin-info-item">
                    <span>Nama Akun</span>
                    <strong><?= esc($registration['user_name'] ?? '-'); ?></strong>
                </div>

                <div class="admin-info-item">
                    <span>Email Akun</span>
                    <strong><?= esc($registration['user_email'] ?? '-'); ?></strong>
                </div>

                <div class="admin-info-item">
                    <span>No. HP Akun</span>
                    <strong><?= esc($registration['user_phone'] ?? '-'); ?></strong>
                </div>

                <div class="admin-info-item">
                    <span>NIK Akun</span>
                    <strong><?= esc($registration['user_nik'] ?? '-'); ?></strong>
                </div>

                <div class="admin-info-item">
                    <span>Total Peserta</span>
                    <strong><?= esc($registration['total_participants'] ?? 1); ?> Jamaah</strong>
                </div>

                <div class="admin-info-item">
                    <span>Total Tagihan</span>
                    <strong>
                        Rp <?= number_format((float) ($registration['total_amount'] ?? 0), 0, ',', '.'); ?>
                    </strong>
                </div>

                <div class="admin-info-item">
                    <span>Status Jadwal</span>
                    <strong><?= esc($registration['departure_status'] ?? '-'); ?></strong>
                </div>
                <div class="admin-info-item">
                    <span>Status Pendaftaran</span>
                    <strong><?= esc($registration['registration_status'] ?? '-'); ?></strong>
                </div>

                <div class="admin-info-item">
                    <span>Status Pembayaran</span>
                    <strong><?= esc($registration['payment_status'] ?? '-'); ?></strong>
                </div>

                <div class="admin-info-item">
                    <span>Tanggal Daftar</span>
                    <strong>
                        <?= !empty($registration['created_at']) ? date('d M Y H:i', strtotime($registration['created_at'])) : '-'; ?>
                    </strong>
                </div>

                <div class="admin-info-item">
                    <span>Catatan</span>
                    <strong><?= esc($registration['note'] ?? '-'); ?></strong>
                </div>

            </div>
        </div>
    </div>

    <div class="admin-card">
        <div class="admin-card-header">
            <h2>Update Status</h2>
        </div>

        <div class="admin-card-body">
            <form action="<?= base_url('/admin/registrations/update-status/' . $registration['id']); ?>" method="post">
                <?= csrf_field(); ?>

                <div class="mb-3">
                    <label class="admin-form-label">Status Pendaftaran</label>

                    <select name="registration_status" class="form-control admin-form-control">
                        <?php
                        $registrationStatuses = [
                            'Menunggu Verifikasi',
                            'Menunggu Kelengkapan Dokumen',
                            'Menunggu Verifikasi Dokumen',
                            'Revisi Dokumen',
                            'Diproses',
                            'Terverifikasi',
                            'Ditolak',
                            'Selesai',
                        ];
                        ?>

                        <?php foreach ($registrationStatuses as $status) : ?>
                            <option
                                value="<?= esc($status); ?>"
                                <?= ($registration['registration_status'] ?? '') === $status ? 'selected' : ''; ?>>
                                <?= esc($status); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="admin-form-label">Status Pembayaran</label>

                    <select name="payment_status" class="form-control admin-form-control">
                        <?php
                        $paymentStatuses = [
                            'Belum Bayar',
                            'Menunggu Pembayaran',
                            'Menunggu Verifikasi',
                            'Lunas',
                            'Gagal',
                        ];
                        ?>

                        <?php foreach ($paymentStatuses as $status) : ?>
                            <option
                                value="<?= esc($status); ?>"
                                <?= ($registration['payment_status'] ?? '') === $status ? 'selected' : ''; ?>>
                                <?= esc($status); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" class="btn btn-admin-primary w-100">
                    Simpan Perubahan
                </button>
                <div class="admin-note-box mt-3">
                    Setelah status dokumen disimpan, sistem akan otomatis memperbarui status pendaftaran jamaah berdasarkan kelengkapan dokumen.
                </div>
            </form>
        </div>
    </div>

</div>

<div class="admin-detail-grid mt-4">

    <div class="admin-card">
        <div class="admin-card-header">
            <h2>Data Jamaah</h2>
        </div>

        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>NIK</th>
                        <th>Email</th>
                        <th>Telepon</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if (empty($pilgrims)) : ?>
                        <tr>
                            <td colspan="4">
                                <div class="admin-empty">
                                    <i class="bi bi-person-x"></i>
                                    Belum ada data jamaah.
                                </div>
                            </td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($pilgrims as $pilgrim) : ?>
                            <tr>
                                <td><strong><?= esc($pilgrim['full_name'] ?? '-'); ?></strong></td>
                                <td><?= esc($pilgrim['nik'] ?? '-'); ?></td>
                                <td><?= esc($pilgrim['email'] ?? '-'); ?></td>
                                <td><?= esc($pilgrim['phone'] ?? '-'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>

            </table>
        </div>
    </div>

    <div class="admin-card">
        <div class="admin-card-header">
            <h2>Data Pembayaran</h2>
        </div>

        <div class="admin-card-body">
            <?php if (empty($payment)) : ?>

                <div class="admin-empty">
                    <i class="bi bi-credit-card-2-back"></i>
                    Belum ada transaksi pembayaran.
                </div>

            <?php else : ?>

                <div class="admin-info-grid single">

                    <div class="admin-info-item">
                        <span>Order ID</span>
                        <strong><?= esc($payment['order_id'] ?? '-'); ?></strong>
                    </div>

                    <div class="admin-info-item">
                        <span>Status Midtrans</span>
                        <strong><?= esc($payment['transaction_status'] ?? '-'); ?></strong>
                    </div>

                    <div class="admin-info-item">
                        <span>Payment Type</span>
                        <strong><?= esc($payment['payment_type'] ?? '-'); ?></strong>
                    </div>

                    <div class="admin-info-item">
                        <span>Gross Amount</span>
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

                </div>

                <a
                    href="<?= base_url('/admin/payments/detail/' . $payment['id']); ?>"
                    class="btn btn-admin-outline w-100 mt-3">
                    Lihat Detail Pembayaran
                </a>

            <?php endif; ?>
        </div>
    </div>

</div>

<?= $this->endSection(); ?>