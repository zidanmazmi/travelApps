<?= $this->extend('templates/admin/layouts/main'); ?>

<?= $this->section('content_header'); ?>
<?= $this->endSection(); ?>

<?= $this->section('content'); ?>

<div class="admin-card">
    <div class="admin-card-header">
        <h2>Rekap Dokumen Pendaftaran</h2>

        <div class="d-flex flex-wrap gap-2">
            <a href="<?= base_url('/admin/documents'); ?>" class="btn btn-admin-outline btn-sm">
                Daftar Dokumen
            </a>

            <a href="<?= base_url('/admin/dashboard'); ?>" class="btn btn-admin-outline btn-sm">
                Dashboard
            </a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>No. Pendaftaran</th>
                    <th>Jamaah</th>
                    <th>Paket</th>
                    <th>Progress</th>
                    <th>Status Dokumen</th>
                    <th>Rincian</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                <?php if (empty($registrations)) : ?>

                    <tr>
                        <td colspan="8">
                            <div class="admin-empty">
                                <i class="bi bi-folder-x"></i>
                                Belum ada data pendaftaran.
                            </div>
                        </td>
                    </tr>

                <?php else : ?>

                    <?php foreach ($registrations as $index => $registration) : ?>

                        <?php
                            $completion = $registration['document_completion'];

                            $requiredTotal = (int) $completion['required_total'];
                            $acceptedTotal = (int) $completion['accepted_total'];
                            $uploadedTotal = (int) $completion['uploaded_total'];
                            $pendingTotal  = (int) $completion['pending_total'];
                            $rejectedTotal = (int) $completion['rejected_total'];
                            $missingTotal  = (int) $completion['missing_total'];

                            $percent = $requiredTotal > 0
                                ? round(($acceptedTotal / $requiredTotal) * 100)
                                : 0;
                        ?>

                        <tr>
                            <td><?= $index + 1; ?></td>

                            <td>
                                <strong><?= esc($registration['registration_no']); ?></strong><br>
                                <small><?= esc($registration['payment_status'] ?? '-'); ?></small>
                            </td>

                            <td>
                                <strong><?= esc($registration['user_name'] ?? '-'); ?></strong><br>
                                <small><?= esc($registration['user_email'] ?? '-'); ?></small>
                            </td>

                            <td><?= esc($registration['package_name'] ?? '-'); ?></td>

                            <td>
                                <div class="admin-document-progress">
                                    <div class="progress-label">
                                        <span><?= esc($acceptedTotal); ?>/<?= esc($requiredTotal); ?> diterima</span>
                                        <strong><?= esc($percent); ?>%</strong>
                                    </div>

                                    <div class="progress">
                                        <div 
                                            class="progress-bar" 
                                            style="width: <?= esc($percent); ?>%;">
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <td>
                                <?php if ($completion['is_complete']) : ?>

                                    <span class="status-pill status-success">
                                        Lengkap
                                    </span>

                                <?php elseif ($rejectedTotal > 0) : ?>

                                    <span class="status-pill status-danger">
                                        Ada Ditolak
                                    </span>

                                <?php elseif ($pendingTotal > 0) : ?>

                                    <span class="status-pill status-warning">
                                        Menunggu Verifikasi
                                    </span>

                                <?php else : ?>

                                    <span class="status-pill status-muted">
                                        Belum Lengkap
                                    </span>

                                <?php endif; ?>
                            </td>

                            <td>
                                <div class="document-mini-list">
                                    <span>Diupload: <?= esc($uploadedTotal); ?></span>
                                    <span>Diterima: <?= esc($acceptedTotal); ?></span>
                                    <span>Pending: <?= esc($pendingTotal); ?></span>
                                    <span>Ditolak: <?= esc($rejectedTotal); ?></span>
                                    <span>Belum: <?= esc($missingTotal); ?></span>
                                </div>
                            </td>

                            <td>
                                <div class="admin-table-action">
                                    <a 
                                        href="<?= base_url('/admin/registrations/detail/' . $registration['id']); ?>" 
                                        class="btn btn-admin-outline btn-sm">
                                        Pendaftaran
                                    </a>

                                    <a 
                                        href="<?= base_url('/admin/documents'); ?>" 
                                        class="btn btn-admin-primary btn-sm">
                                        Dokumen
                                    </a>
                                </div>
                            </td>
                        </tr>

                    <?php endforeach; ?>

                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?= $this->endSection(); ?>