<?= $this->extend('templates/admin/layouts/main'); ?>

<?= $this->section('content_header'); ?>


<?= $this->endSection(); ?>

<?= $this->section('content'); ?>

<?php
    $startDate = $filters['start_date'] ?? '';
    $endDate   = $filters['end_date'] ?? '';

    $queryString = http_build_query([
        'start_date' => $startDate,
        'end_date'   => $endDate,
    ]);
?>

<div class="admin-card mb-4">
    <div class="admin-card-header">
        <h2>Filter Periode Laporan</h2>
    </div>

    <div class="admin-card-body">
        <form action="<?= base_url('/admin/reports'); ?>" method="get">
            <div class="row g-3 align-items-end">

                <div class="col-md-4">
                    <label class="admin-form-label">Tanggal Mulai</label>
                    <input 
                        type="date" 
                        name="start_date" 
                        class="form-control admin-form-control"
                        value="<?= esc($startDate); ?>"
                    >
                </div>

                <div class="col-md-4">
                    <label class="admin-form-label">Tanggal Akhir</label>
                    <input 
                        type="date" 
                        name="end_date" 
                        class="form-control admin-form-control"
                        value="<?= esc($endDate); ?>"
                    >
                </div>

                <div class="col-md-4">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-admin-primary w-100">
                            Terapkan Filter
                        </button>

                        <a href="<?= base_url('/admin/reports'); ?>" class="btn btn-admin-outline w-100">
                            Reset
                        </a>
                    </div>
                </div>

            </div>
        </form>

        <?php if (!empty($startDate) || !empty($endDate)) : ?>
            <div class="admin-note-box mt-3">
                Laporan akan diexport berdasarkan periode:
                <strong>
                    <?= !empty($startDate) ? date('d M Y', strtotime($startDate)) : 'Awal Data'; ?>
                    -
                    <?= !empty($endDate) ? date('d M Y', strtotime($endDate)) : 'Sekarang'; ?>
                </strong>
            </div>
        <?php endif; ?>
    </div>
</div>
<div class="admin-card mb-4">
    <div class="admin-card-header">
        <h2>Export Laporan Lengkap</h2>
    </div>

    <div class="admin-card-body">
        <div class="row g-3 align-items-center">
            <div class="col-lg-8">
                <p class="admin-report-text mb-0">
                    Download seluruh laporan dalam satu file Excel dengan beberapa sheet:
                    Ringkasan, Pendaftaran, Pembayaran, dan Dokumen.
                </p>
            </div>

            <div class="col-lg-4">
                <a 
                    href="<?= base_url('/admin/reports/export-all') . (!empty($queryString) ? '?' . $queryString : ''); ?>" 
                    class="btn btn-admin-primary w-100">
                    Export Semua Laporan
                </a>
            </div>
        </div>
    </div>
</div>
<div class="admin-detail-grid">

    <div class="admin-card">
        <div class="admin-card-header">
            <h2>Laporan Pendaftaran</h2>
        </div>

        <div class="admin-card-body">
            <div class="admin-report-icon">
                <i class="bi bi-journal-check"></i>
            </div>

            <p class="admin-report-text">
                Berisi data pendaftaran jamaah, nama akun, email, paket, jadwal keberangkatan, total tagihan, status pendaftaran, dan status pembayaran.
            </p>

            <a 
                href="<?= base_url('/admin/reports/export-registrations') . (!empty($queryString) ? '?' . $queryString : ''); ?>" 
                class="btn btn-admin-primary w-100">
                Export Pendaftaran
            </a>
        </div>
    </div>

    <div class="admin-card">
        <div class="admin-card-header">
            <h2>Laporan Pembayaran</h2>
        </div>

        <div class="admin-card-body">
            <div class="admin-report-icon">
                <i class="bi bi-credit-card-2-front"></i>
            </div>

            <p class="admin-report-text">
                Berisi data transaksi pembayaran, order ID, transaction ID, metode pembayaran, nominal, status Midtrans, dan status pembayaran sistem.
            </p>

            <a 
                href="<?= base_url('/admin/reports/export-payments') . (!empty($queryString) ? '?' . $queryString : ''); ?>" 
                class="btn btn-admin-primary w-100">
                Export Pembayaran
            </a>
        </div>
    </div>

    <div class="admin-card">
        <div class="admin-card-header">
            <h2>Laporan Dokumen</h2>
        </div>

        <div class="admin-card-body">
            <div class="admin-report-icon">
                <i class="bi bi-file-earmark-check"></i>
            </div>

            <p class="admin-report-text">
                Berisi data dokumen jamaah, jenis dokumen, status verifikasi, catatan admin, file dokumen, dan status pendaftaran terkait.
            </p>

            <a 
                href="<?= base_url('/admin/reports/export-documents') . (!empty($queryString) ? '?' . $queryString : ''); ?>"
                class="btn btn-admin-primary w-100">
                Export Dokumen
            </a>
        </div>
    </div>

</div>

<?= $this->endSection(); ?>