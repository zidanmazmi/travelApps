<?= $this->extend('templates/admin/layouts/main'); ?>

<?= $this->section('content_header'); ?>
<div class="admin-page-title d-flex flex-column flex-lg-row align-items-lg-end justify-content-between gap-3">
    <div><span>Document Control</span><h1>Dokumen Jamaah</h1><p>Verifikasi, arsipkan, pulihkan, atau hapus permanen dokumen jamaah.</p></div>
    <a href="<?= base_url('/admin/documents/trash'); ?>" class="btn btn-admin-outline"><i class="bi bi-trash3"></i> Sampah Dokumen</a>
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

<div class="admin-card">
    <div class="admin-card-header">
    <h2>Daftar Dokumen</h2>

    <div class="d-flex flex-wrap gap-2">
        <a href="<?= base_url('/admin/documents/summary'); ?>" class="btn btn-admin-primary btn-sm">
            Rekap Dokumen
        </a>

        <a href="<?= base_url('/admin/documents/trash'); ?>" class="btn btn-admin-outline btn-sm">
            Sampah
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
                    <th>Jenis Dokumen</th>
                    <th>Status Dokumen</th>
                    <th>Tanggal Upload</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                <?php if (empty($documents)) : ?>
                    <tr>
                        <td colspan="8">
                            <div class="admin-empty">
                                <i class="bi bi-file-earmark-x"></i>
                                Belum ada dokumen jamaah.
                            </div>
                        </td>
                    </tr>
                <?php else : ?>
                    <?php foreach ($documents as $index => $document) : ?>
                        <tr>
                            <td><?= $index + 1; ?></td>

                            <td>
                                <strong><?= esc($document['registration_no'] ?? '-'); ?></strong><br>
                                <small><?= esc($document['payment_status'] ?? '-'); ?></small>
                            </td>

                            <td>
                                <strong>
                                    <?= esc($document['pilgrim_name'] ?? $document['user_name'] ?? '-'); ?>
                                </strong><br>
                                <small><?= esc($document['user_email'] ?? '-'); ?></small>
                            </td>

                            <td><?= esc($document['package_name'] ?? '-'); ?></td>

                            <td>
                                <strong><?= esc($document['document_type'] ?? '-'); ?></strong>
                            </td>

                            <td>
                                <?php if (($document['status'] ?? '') === 'Diterima') : ?>
                                    <span class="status-pill status-success">Diterima</span>
                                <?php elseif (($document['status'] ?? '') === 'Ditolak') : ?>
                                    <span class="status-pill status-danger">Ditolak</span>
                                <?php else : ?>
                                    <span class="status-pill status-warning">
                                        <?= esc($document['status'] ?? 'Menunggu Verifikasi'); ?>
                                    </span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?= !empty($document['created_at']) ? date('d M Y H:i', strtotime($document['created_at'])) : '-'; ?>
                            </td>

                            <td>
                                <div class="admin-table-action">
                                    <a 
                                        href="<?= base_url($document['document_file']); ?>" 
                                        target="_blank"
                                        class="btn btn-admin-outline btn-sm">
                                        Lihat File
                                    </a>

                                    <a href="<?= base_url('/admin/documents/detail/' . $document['id']); ?>" class="btn btn-admin-primary btn-sm">Detail</a>
                                    <form action="<?= base_url('/admin/documents/delete/' . $document['id']); ?>" method="post" onsubmit="return confirm('Pindahkan dokumen ini ke Sampah?');">
                                        <?= csrf_field(); ?>
                                        <input type="hidden" name="delete_reason" value="Dihapus dari daftar dokumen admin">
                                        <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill"><i class="bi bi-trash3"></i></button>
                                    </form>
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