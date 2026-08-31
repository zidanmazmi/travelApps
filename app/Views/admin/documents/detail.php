<?= $this->extend('templates/admin/layouts/main'); ?>

<?= $this->section('content_header'); ?>

<div class="admin-page-title">
    <h1>Detail Dokumen Jamaah</h1>
    <p>Jenis dokumen: <strong><?= esc($document['document_type'] ?? '-'); ?></strong></p>
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
    <div class="d-flex flex-wrap gap-2">
        <a href="<?= base_url('/admin/documents'); ?>" class="btn btn-admin-outline btn-sm"><i class="bi bi-arrow-left"></i> Kembali</a>
        <form action="<?= base_url('/admin/documents/delete/' . $document['id']); ?>" method="post" onsubmit="return confirm('Pindahkan dokumen ini ke Sampah?');">
            <?= csrf_field(); ?>
            <input type="hidden" name="delete_reason" value="Dihapus dari halaman detail dokumen">
            <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill"><i class="bi bi-trash3"></i> Pindahkan ke Sampah</button>
        </form>
    </div>
</div>

<div class="admin-detail-grid">

    <div class="admin-card">
        <div class="admin-card-header">
            <h2>Informasi Dokumen</h2>

            <?php if (($document['status'] ?? '') === 'Diterima') : ?>
                <span class="status-pill status-success">Diterima</span>
            <?php elseif (($document['status'] ?? '') === 'Ditolak') : ?>
                <span class="status-pill status-danger">Ditolak</span>
            <?php else : ?>
                <span class="status-pill status-warning">
                    <?= esc($document['status'] ?? 'Menunggu Verifikasi'); ?>
                </span>
            <?php endif; ?>
        </div>

        <div class="admin-card-body">
            <div class="admin-info-grid">

                <div class="admin-info-item">
                    <span>No. Pendaftaran</span>
                    <strong><?= esc($document['registration_no'] ?? '-'); ?></strong>
                </div>

                <div class="admin-info-item">
                    <span>Paket</span>
                    <strong><?= esc($document['package_name'] ?? '-'); ?></strong>
                </div>

                <div class="admin-info-item">
                    <span>Nama Jamaah</span>
                    <strong><?= esc($document['pilgrim_name'] ?? $document['user_name'] ?? '-'); ?></strong>
                </div>

                <div class="admin-info-item">
                    <span>NIK Jamaah</span>
                    <strong><?= esc($document['pilgrim_nik'] ?? '-'); ?></strong>
                </div>

                <div class="admin-info-item">
                    <span>Email</span>
                    <strong><?= esc($document['pilgrim_email'] ?? $document['user_email'] ?? '-'); ?></strong>
                </div>

                <div class="admin-info-item">
                    <span>No. HP</span>
                    <strong><?= esc($document['pilgrim_phone'] ?? $document['user_phone'] ?? '-'); ?></strong>
                </div>

                <div class="admin-info-item">
                    <span>Jenis Dokumen</span>
                    <strong><?= esc($document['document_type'] ?? '-'); ?></strong>
                </div>

                <div class="admin-info-item">
                    <span>Status Dokumen</span>
                    <strong><?= esc($document['status'] ?? '-'); ?></strong>
                </div>

                <div class="admin-info-item">
                    <span>Tanggal Upload</span>
                    <strong>
                        <?= !empty($document['created_at']) ? date('d M Y H:i', strtotime($document['created_at'])) : '-'; ?>
                    </strong>
                </div>

                <div class="admin-info-item">
                    <span>Verified At</span>
                    <strong>
                        <?= !empty($document['verified_at']) ? date('d M Y H:i', strtotime($document['verified_at'])) : '-'; ?>
                    </strong>
                </div>

            </div>

            <?php if (!empty($document['note'])) : ?>
                <div class="admin-note-box mt-3">
                    <strong>Catatan Admin:</strong><br>
                    <?= esc($document['note']); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="admin-card">
        <div class="admin-card-header">
            <h2>Verifikasi Dokumen</h2>
        </div>

        <div class="admin-card-body">

            <form action="<?= base_url('/admin/documents/update-status/' . $document['id']); ?>" method="post">
                <?= csrf_field(); ?>

                <div class="mb-3">
                    <label class="admin-form-label">Status Dokumen</label>

                    <select name="status" class="form-control admin-form-control">
                        <?php
                            $statuses = [
                                'Menunggu Verifikasi',
                                'Diterima',
                                'Ditolak',
                            ];
                        ?>

                        <?php foreach ($statuses as $status) : ?>
                            <option 
                                value="<?= esc($status); ?>"
                                <?= ($document['status'] ?? '') === $status ? 'selected' : ''; ?>>
                                <?= esc($status); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="admin-form-label">Catatan Admin</label>

                    <textarea 
                        name="note" 
                        rows="5" 
                        class="form-control admin-form-control"
                        placeholder="Contoh: Foto kurang jelas, mohon upload ulang."><?= old('note', $document['note'] ?? ''); ?></textarea>

                    <small class="text-muted d-block mt-2">
                        Catatan ini akan terlihat oleh jamaah.
                    </small>
                </div>

                <button type="submit" class="btn btn-admin-primary w-100">
                    Simpan Status Dokumen
                </button>
            </form>

            <a 
                href="<?= base_url($document['document_file']); ?>" 
                target="_blank"
                class="btn btn-admin-outline w-100 mt-3">
                Buka File di Tab Baru
            </a>

        </div>
    </div>

</div>

<div class="admin-card mt-4">
    <div class="admin-card-header">
        <h2>Preview File</h2>
    </div>

    <div class="admin-card-body">
        <?php
            $filePath = $document['document_file'] ?? '';
            $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            $fileUrl = base_url($filePath);
        ?>

        <?php if (in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) : ?>

            <div class="admin-document-preview">
                <img src="<?= esc($fileUrl); ?>" alt="Preview Dokumen">
            </div>

        <?php elseif ($extension === 'pdf') : ?>

            <div class="admin-document-preview">
                <iframe src="<?= esc($fileUrl); ?>"></iframe>
            </div>

        <?php else : ?>

            <div class="admin-empty">
                <i class="bi bi-file-earmark"></i>
                Preview tidak tersedia. Silakan buka file di tab baru.
            </div>

        <?php endif; ?>
    </div>
</div>

<?= $this->endSection(); ?>