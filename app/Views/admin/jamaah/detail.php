<?= $this->extend('templates/admin/layouts/main'); ?>

<?= $this->section('content_header'); ?>

<div class="admin-page-title">
    <span>Master Data</span>
    <h1>Detail Jamaah</h1>
    <p>Data akun: <strong><?= esc($jamaah['name'] ?? '-'); ?></strong></p>
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
    <a href="<?= base_url('/admin/jamaah'); ?>" class="btn btn-admin-outline btn-sm">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
</div>

<div class="admin-detail-grid">

    <div class="admin-card">
        <div class="admin-card-header">
            <h2>Informasi Akun</h2>

            <?php if (($jamaah['status'] ?? '') === 'active') : ?>
                <span class="status-pill status-success">Aktif</span>
            <?php elseif (($jamaah['status'] ?? '') === 'blocked') : ?>
                <span class="status-pill status-danger">Blocked</span>
            <?php else : ?>
                <span class="status-pill status-muted">
                    <?= esc($jamaah['status'] ?? '-'); ?>
                </span>
            <?php endif; ?>
        </div>

        <div class="admin-card-body">
            <div class="admin-info-grid">

                <div class="admin-info-item">
                    <span>Nama Jamaah</span>
                    <strong><?= esc($jamaah['name'] ?? '-'); ?></strong>
                </div>

                <div class="admin-info-item">
                    <span>Email</span>
                    <strong><?= esc($jamaah['email'] ?? '-'); ?></strong>
                </div>

                <div class="admin-info-item">
                    <span>No. HP</span>
                    <strong><?= esc($jamaah['phone'] ?? '-'); ?></strong>
                </div>

                <div class="admin-info-item">
                    <span>NIK</span>
                    <strong><?= esc($jamaah['nik'] ?? '-'); ?></strong>
                </div>

                <div class="admin-info-item">
                    <span>Verifikasi Email</span>
                    <strong>
                        <?= ((int) ($jamaah['is_email_verified'] ?? 0) === 1) ? 'Terverifikasi' : 'Belum Terverifikasi'; ?>
                    </strong>
                </div>

                <div class="admin-info-item">
                    <span>Tanggal Daftar Akun</span>
                    <strong>
                        <?= !empty($jamaah['created_at']) ? date('d M Y H:i', strtotime($jamaah['created_at'])) : '-'; ?>
                    </strong>
                </div>

                <div class="admin-info-item">
                    <span>Last Login</span>
                    <strong>
                        <?= !empty($jamaah['last_login']) ? date('d M Y H:i', strtotime($jamaah['last_login'])) : '-'; ?>
                    </strong>
                </div>

                <div class="admin-info-item">
                    <span>Role</span>
                    <strong><?= esc($jamaah['role'] ?? '-'); ?></strong>
                </div>

            </div>
        </div>
    </div>

    <div class="admin-card">
        <div class="admin-card-header">
            <h2>Update Status Akun</h2>
        </div>

        <div class="admin-card-body">
            <form action="<?= base_url('/admin/jamaah/update-status/' . $jamaah['id']); ?>" method="post">
                <?= csrf_field(); ?>

                <div class="mb-4">
                    <label class="admin-form-label">Status Akun</label>

                    <select name="status" class="form-control admin-form-control">
                        <option value="active" <?= ($jamaah['status'] ?? '') === 'active' ? 'selected' : ''; ?>>
                            Aktif
                        </option>

                        <option value="inactive" <?= ($jamaah['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>
                            Nonaktif
                        </option>

                        <option value="blocked" <?= ($jamaah['status'] ?? '') === 'blocked' ? 'selected' : ''; ?>>
                            Blocked
                        </option>
                    </select>
                </div>

                <button type="submit" class="btn btn-admin-primary w-100">
                    Simpan Status
                </button>
            </form>

            <div class="admin-note-box mt-3">
                Status <strong>blocked</strong> dapat dipakai untuk membatasi akun jamaah yang bermasalah.
                Pastikan login controller memeriksa status akun agar akun blocked tidak bisa masuk.
            </div>
        </div>
    </div>

</div>

<div class="admin-card mt-4">
    <div class="admin-card-header">
        <h2>Riwayat Pendaftaran</h2>
    </div>

    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>No. Pendaftaran</th>
                    <th>Paket</th>
                    <th>Jadwal</th>
                    <th>Total</th>
                    <th>Status Daftar</th>
                    <th>Status Bayar</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                <?php if (empty($registrations)) : ?>
                    <tr>
                        <td colspan="8">
                            <div class="admin-empty">
                                <i class="bi bi-journal-x"></i>
                                Belum ada riwayat pendaftaran.
                            </div>
                        </td>
                    </tr>
                <?php else : ?>
                    <?php foreach ($registrations as $index => $registration) : ?>
                        <tr>
                            <td><?= $index + 1; ?></td>

                            <td>
                                <strong><?= esc($registration['registration_no'] ?? '-'); ?></strong>
                            </td>

                            <td><?= esc($registration['package_name'] ?? '-'); ?></td>

                            <td>
                                <?php if (!empty($registration['departure_date'])) : ?>
                                    <?= date('d M Y', strtotime($registration['departure_date'])); ?>

                                    <?php if (!empty($registration['return_date'])) : ?>
                                        <br><small>Pulang: <?= date('d M Y', strtotime($registration['return_date'])); ?></small>
                                    <?php endif; ?>
                                <?php else : ?>
                                    -
                                <?php endif; ?>
                            </td>

                            <td>
                                <strong>
                                    Rp <?= number_format((float) ($registration['total_amount'] ?? 0), 0, ',', '.'); ?>
                                </strong>
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
                                <a 
                                    href="<?= base_url('/admin/registrations/detail/' . $registration['id']); ?>" 
                                    class="btn btn-admin-outline btn-sm">
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

<div class="admin-detail-grid mt-4">

    <div class="admin-card">
        <div class="admin-card-header">
            <h2>Riwayat Pembayaran</h2>
        </div>

        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Paket</th>
                        <th>Nominal</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if (empty($payments)) : ?>
                        <tr>
                            <td colspan="5">
                                <div class="admin-empty">
                                    <i class="bi bi-credit-card"></i>
                                    Belum ada pembayaran.
                                </div>
                            </td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($payments as $payment) : ?>
                            <tr>
                                <td><?= esc($payment['order_id'] ?? '-'); ?></td>
                                <td><?= esc($payment['package_name'] ?? '-'); ?></td>
                                <td>
                                    Rp <?= number_format((float) ($payment['gross_amount'] ?? 0), 0, ',', '.'); ?>
                                </td>
                                <td><?= esc($payment['transaction_status'] ?? '-'); ?></td>
                                <td>
                                    <a 
                                        href="<?= base_url('/admin/payments/detail/' . $payment['id']); ?>" 
                                        class="btn btn-admin-outline btn-sm">
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

    <div class="admin-card">
        <div class="admin-card-header">
            <h2>Riwayat Dokumen</h2>
        </div>

        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Dokumen</th>
                        <th>No. Pendaftaran</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if (empty($documents)) : ?>
                        <tr>
                            <td colspan="4">
                                <div class="admin-empty">
                                    <i class="bi bi-file-earmark-x"></i>
                                    Belum ada dokumen.
                                </div>
                            </td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($documents as $document) : ?>
                            <tr>
                                <td><?= esc($document['document_type'] ?? '-'); ?></td>
                                <td><?= esc($document['registration_no'] ?? '-'); ?></td>
                                <td>
                                    <?php if (($document['status'] ?? '') === 'Diterima') : ?>
                                        <span class="status-pill status-success">Diterima</span>
                                    <?php elseif (($document['status'] ?? '') === 'Ditolak') : ?>
                                        <span class="status-pill status-danger">Ditolak</span>
                                    <?php else : ?>
                                        <span class="status-pill status-warning">
                                            <?= esc($document['status'] ?? '-'); ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a 
                                        href="<?= base_url('/admin/documents/detail/' . $document['id']); ?>" 
                                        class="btn btn-admin-outline btn-sm">
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

</div>

<?= $this->endSection(); ?>