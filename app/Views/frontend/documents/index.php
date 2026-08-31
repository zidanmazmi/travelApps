<?= $this->extend('templates/frontend/layouts/main'); ?>

<?= $this->section('content'); ?>

<section class="document-page">
    <div class="container">

        <div class="document-header text-center">
            <span class="section-label">Dokumen Jamaah</span>

            <h1>Upload Dokumen Jamaah</h1>

            <p>
                Lengkapi dokumen untuk nomor pendaftaran
                <strong><?= esc($registration['registration_no']); ?></strong>.
            </p>
        </div>

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

        <div class="row g-4">

            <div class="col-lg-5">
                <div class="document-card h-100">

                    <h2>Upload Dokumen</h2>

                    <div class="document-summary mb-4">
                        <div>
                            <span>No. Pendaftaran</span>
                            <strong><?= esc($registration['registration_no']); ?></strong>
                        </div>

                        <div>
                            <span>Paket</span>
                            <strong><?= esc($registration['package_name'] ?? '-'); ?></strong>
                        </div>

                        <div>
                            <span>Status Pendaftaran</span>
                            <strong><?= esc($registration['registration_status'] ?? '-'); ?></strong>
                        </div>
                    </div>

                    <form action="<?= base_url('/dokumen/upload'); ?>" method="post" enctype="multipart/form-data">
                        <?= csrf_field(); ?>

                        <input 
                            type="hidden" 
                            name="registration_no" 
                            value="<?= esc($registration['registration_no']); ?>"
                        >

                        <div class="mb-3">
                            <label class="form-label">Jenis Dokumen</label>

                            <select name="document_type" class="form-control" required>
                                <option value="">Pilih Dokumen</option>

                                <?php foreach ($documentTypes as $type) : ?>
                                    <option 
                                        value="<?= esc($type); ?>"
                                        <?= old('document_type') === $type ? 'selected' : ''; ?>>
                                        <?= esc($type); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                            <?php if (isset($errors['document_type'])) : ?>
                                <small class="text-danger"><?= esc($errors['document_type']); ?></small>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">File Dokumen</label>

                            <input 
                                type="file" 
                                name="document_file" 
                                class="form-control"
                                accept=".jpg,.jpeg,.png,.webp,.pdf"
                                required
                            >

                            <?php if (isset($errors['document_file'])) : ?>
                                <small class="text-danger"><?= esc($errors['document_file']); ?></small>
                            <?php endif; ?>

                            <small class="text-muted d-block mt-2">
                                Format: JPG, JPEG, PNG, WEBP, atau PDF. Maksimal 3MB.
                            </small>
                        </div>

                        <button type="submit" class="btn btn-brand-primary w-100">
                            Upload Dokumen
                        </button>
                    </form>

                </div>
            </div>

            <div class="col-lg-7">
                <div class="document-card h-100">

                    <h2>Daftar Dokumen</h2>

                    <?php if (empty($documents)) : ?>

                        <div class="document-empty">
                            <i class="bi bi-file-earmark-x"></i>
                            <p>Belum ada dokumen yang diupload.</p>
                        </div>

                    <?php else : ?>

                        <div class="document-list">

                            <?php foreach ($documents as $document) : ?>

                                <div class="document-item">

                                    <div>
                                        <span><?= esc($document['document_type']); ?></span>

                                        <strong>
                                            <?= esc($document['status']); ?>
                                        </strong>

                                        <?php if (!empty($document['note'])) : ?>
                                            <small><?= esc($document['note']); ?></small>
                                        <?php endif; ?>
                                    </div>

                                    <div class="document-actions">
                                        <a 
                                            href="<?= base_url($document['document_file']); ?>" 
                                            target="_blank"
                                            class="btn btn-outline-secondary btn-sm">
                                            Lihat
                                        </a>

                                        <a 
                                            href="<?= base_url('/dokumen/delete/' . $document['id']); ?>" 
                                            class="btn btn-danger btn-sm"
                                            onclick="return confirm('Yakin ingin menghapus dokumen ini?')">
                                            Hapus
                                        </a>
                                    </div>

                                </div>

                            <?php endforeach; ?>

                        </div>

                    <?php endif; ?>

                </div>
            </div>

        </div>

        <div class="text-center mt-4">
            <a href="<?= base_url('/dashboard-jamaah'); ?>" class="btn btn-outline-secondary">
                Kembali ke Dashboard
            </a>
        </div>

    </div>
</section>

<?= $this->endSection(); ?>