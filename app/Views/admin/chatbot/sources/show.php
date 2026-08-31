<?= $this->extend('templates/admin/layouts/main'); ?>

<?= $this->section('content_header'); ?>
<div class="admin-page-title d-flex flex-column flex-xl-row align-items-xl-end justify-content-between gap-3">
    <div>
        <span>Review &amp; Approval</span>
        <h1><?= esc($source['title']); ?></h1>
        <p>Bandingkan dokumen asli dengan hasil ekstraksi sebelum jawaban digunakan calon jamaah.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="<?= base_url('/admin/chatbot/sources'); ?>" class="btn btn-admin-outline">
            <i class="bi bi-arrow-left"></i> Daftar Dokumen
        </a>
        <a href="<?= base_url('/admin/chatbot/sources/' . $source['id'] . '/file'); ?>" target="_blank" class="btn btn-admin-outline">
            <i class="bi bi-box-arrow-up-right"></i> Buka File
        </a>
    </div>
</div>
<?= $this->endSection(); ?>

<?= $this->section('content'); ?>
<?php if (session()->getFlashdata('success')) : ?>
    <div class="alert alert-success rounded-4"><?= esc(session()->getFlashdata('success')); ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')) : ?>
    <div class="alert alert-danger rounded-4"><?= esc(session()->getFlashdata('error')); ?></div>
<?php endif; ?>

<?php
$statusMap = [
    'uploaded'   => ['Terunggah', 'status-muted'],
    'processing' => ['Sedang diproses', 'status-warning'],
    'review'     => ['Perlu ditinjau', 'status-warning'],
    'published'  => ['Published', 'status-success'],
    'archived'   => ['Diarsipkan', 'status-muted'],
    'failed'     => ['Gagal', 'status-danger'],
];
$sourceStatus = $statusMap[$source['status'] ?? 'uploaded'] ?? ['Unknown', 'status-muted'];
$isPublished = ($source['status'] ?? '') === 'published';
?>

<div class="document-review-summary mb-4">
    <div>
        <span>Status</span>
        <strong><span class="status-pill <?= esc($sourceStatus[1]); ?>"><?= esc($sourceStatus[0]); ?></span></strong>
    </div>
    <div>
        <span>Jenis</span>
        <strong><?= esc($typeOptions[$source['document_type']] ?? ucfirst($source['document_type'])); ?></strong>
    </div>
    <div>
        <span>Paket terkait</span>
        <strong><?= esc($package['name'] ?? 'Informasi umum'); ?></strong>
    </div>
    <div>
        <span>Confidence dokumen</span>
        <strong><?= $source['confidence'] !== null ? esc(number_format(((float) $source['confidence']) * 100, 0)) . '%' : '-'; ?></strong>
    </div>
    <div>
        <span>Jawaban dipilih</span>
        <strong><?= esc(count(array_filter($answers, static fn(array $item): bool => (int) ($item['is_selected'] ?? 0) === 1))); ?> / <?= esc(count($answers)); ?></strong>
    </div>
</div>

<?php if (!empty($source['error_message'])) : ?>
    <div class="alert alert-danger rounded-4">
        <strong>Proses ekstraksi gagal:</strong> <?= esc($source['error_message']); ?>
    </div>
<?php endif; ?>

<form action="<?= base_url('/admin/chatbot/sources/' . $source['id'] . '/update'); ?>" method="post">
    <?= csrf_field(); ?>

    <div class="document-review-layout">
        <div class="admin-card document-preview-card">
            <div class="admin-card-header">
                <div>
                    <span class="lead-card-kicker">Sumber Asli</span>
                    <h2>Preview Dokumen</h2>
                </div>
                <span class="status-pill status-muted"><?= esc(number_format(((int) $source['file_size']) / 1_048_576, 2, ',', '.')); ?> MB</span>
            </div>
            <div class="document-preview">
                <?php if ($source['mime_type'] === 'application/pdf') : ?>
                    <iframe
                        src="<?= base_url('/admin/chatbot/sources/' . $source['id'] . '/file'); ?>#toolbar=1"
                        title="Preview <?= esc($source['title']); ?>"></iframe>
                <?php else : ?>
                    <img
                        src="<?= base_url('/admin/chatbot/sources/' . $source['id'] . '/file'); ?>"
                        alt="Preview <?= esc($source['title']); ?>">
                <?php endif; ?>
            </div>
        </div>

        <div class="admin-card">
            <div class="admin-card-header">
                <div>
                    <span class="lead-card-kicker">Metadata</span>
                    <h2>Identitas Sumber</h2>
                </div>
            </div>
            <div class="admin-card-body">
                <fieldset <?= $isPublished ? 'disabled' : ''; ?>>
                    <div class="mb-3">
                        <label class="admin-form-label">Judul Dokumen</label>
                        <input type="text" name="title" class="form-control admin-form-control" value="<?= esc(old('title', $source['title'])); ?>" required>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="admin-form-label">Jenis</label>
                            <select name="document_type" class="form-control admin-form-control" required>
                                <?php foreach ($typeOptions as $value => $label) : ?>
                                    <option value="<?= esc($value); ?>" <?= $source['document_type'] === $value ? 'selected' : ''; ?>>
                                        <?= esc($label); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="admin-form-label">Paket Terkait</label>
                            <select name="package_id" class="form-control admin-form-control">
                                <option value="">Informasi umum</option>
                                <?php foreach ($packages as $item) : ?>
                                    <option value="<?= esc($item['id']); ?>" <?= (int) ($source['package_id'] ?? 0) === (int) $item['id'] ? 'selected' : ''; ?>>
                                        <?= esc(($item['program'] ? ucfirst($item['program']) . ' — ' : '') . $item['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="admin-form-label">Mulai Berlaku</label>
                            <input type="date" name="valid_from" class="form-control admin-form-control" value="<?= esc($source['valid_from']); ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="admin-form-label">Berlaku Sampai</label>
                            <input type="date" name="valid_until" class="form-control admin-form-control" value="<?= esc($source['valid_until']); ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="admin-form-label">Prioritas</label>
                            <input type="number" name="priority" min="1" max="100" class="form-control admin-form-control" value="<?= esc($source['priority']); ?>">
                        </div>
                    </div>
                    <div class="mt-3">
                        <label class="admin-form-label">Catatan Admin</label>
                        <textarea name="admin_notes" class="form-control admin-form-control" rows="3"><?= esc($source['admin_notes']); ?></textarea>
                    </div>
                </fieldset>
            </div>
        </div>
    </div>

    <div class="admin-card mt-4">
        <div class="admin-card-header">
            <div>
                <span class="lead-card-kicker">Hasil Ekstraksi</span>
                <h2>Ringkasan &amp; Teks Terbaca</h2>
                <p class="mb-0">Perbaiki kesalahan OCR sebelum meninjau pertanyaan dan jawaban.</p>
            </div>
        </div>
        <div class="admin-card-body">
            <fieldset <?= $isPublished ? 'disabled' : ''; ?>>
                <div class="mb-3">
                    <label class="admin-form-label">Ringkasan Dokumen</label>
                    <textarea name="summary" class="form-control admin-form-control" rows="4"><?= esc($source['summary']); ?></textarea>
                </div>
                <div class="accordion document-extraction-accordion" id="documentExtractionAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#rawText">
                                Teks lengkap hasil pembacaan
                            </button>
                        </h2>
                        <div id="rawText" class="accordion-collapse collapse" data-bs-parent="#documentExtractionAccordion">
                            <div class="accordion-body">
                                <textarea name="extracted_text" class="form-control admin-form-control document-code-area" rows="16"><?= esc($source['extracted_text']); ?></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#structuredJson">
                                Structured JSON dan fakta
                            </button>
                        </h2>
                        <div id="structuredJson" class="accordion-collapse collapse" data-bs-parent="#documentExtractionAccordion">
                            <div class="accordion-body">
                                <textarea name="structured_json" class="form-control admin-form-control document-code-area" rows="20"><?= esc($source['structured_json']); ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </fieldset>
        </div>
    </div>

    <div class="admin-card mt-4">
        <div class="admin-card-header">
            <div>
                <span class="lead-card-kicker">Official Answer Review</span>
                <h2>Pertanyaan &amp; Jawaban Customer</h2>
                <p class="mb-0">Centang hanya jawaban yang faktanya sudah sesuai dengan dokumen asli.</p>
            </div>
            <span class="status-pill status-muted"><?= esc(count($answers)); ?> kandidat</span>
        </div>
        <div class="admin-card-body">
            <?php if ($answers === []) : ?>
                <div class="admin-empty">
                    <i class="bi bi-question-diamond"></i>
                    Belum ada jawaban hasil ekstraksi. Tekan Proses Ulang setelah API key aktif.
                </div>
            <?php else : ?>
                <div class="document-answer-list">
                    <?php foreach ($answers as $index => $answer) : ?>
                        <?php $answerId = (int) $answer['id']; ?>
                        <article class="document-answer-item <?= (int) $answer['is_selected'] === 1 ? 'is-selected' : ''; ?>">
                            <div class="document-answer-number"><?= esc($index + 1); ?></div>
                            <div class="document-answer-content">
                                <div class="document-answer-toolbar">
                                    <label class="document-answer-check">
                                        <input
                                            type="checkbox"
                                            name="selected_answers[]"
                                            value="<?= esc($answerId); ?>"
                                            <?= (int) $answer['is_selected'] === 1 ? 'checked' : ''; ?>
                                            <?= $isPublished ? 'disabled' : ''; ?>>
                                        <span>Gunakan jawaban ini</span>
                                    </label>
                                    <span class="status-pill <?= (float) $answer['confidence'] >= 0.75 ? 'status-success' : 'status-warning'; ?>">
                                        Confidence <?= esc(number_format(((float) $answer['confidence']) * 100, 0)); ?>%
                                    </span>
                                </div>

                                <fieldset <?= $isPublished ? 'disabled' : ''; ?>>
                                    <div class="mb-3">
                                        <label class="admin-form-label">Pertanyaan Acuan</label>
                                        <input
                                            type="text"
                                            name="answers[<?= esc($answerId); ?>][question]"
                                            class="form-control admin-form-control"
                                            value="<?= esc($answer['question']); ?>"
                                            maxlength="255"
                                            required>
                                    </div>
                                    <div class="row g-3 mb-3">
                                        <div class="col-lg-8">
                                            <label class="admin-form-label">Variasi Pertanyaan</label>
                                            <textarea
                                                name="answers[<?= esc($answerId); ?>][keywords]"
                                                class="form-control admin-form-control"
                                                rows="3"><?= esc($answer['keywords']); ?></textarea>
                                        </div>
                                        <div class="col-sm-6 col-lg-2">
                                            <label class="admin-form-label">Kategori</label>
                                            <input
                                                type="text"
                                                name="answers[<?= esc($answerId); ?>][category]"
                                                class="form-control admin-form-control"
                                                value="<?= esc($answer['category']); ?>">
                                        </div>
                                        <div class="col-sm-6 col-lg-2">
                                            <label class="admin-form-label">Program</label>
                                            <select name="answers[<?= esc($answerId); ?>][program]" class="form-control admin-form-control">
                                                <option value="">Umum</option>
                                                <?php foreach (\App\Models\PackageModel::getProgramOptions() as $programName => $programLabel) : ?>
                                                    <option value="<?= esc($programName); ?>" <?= $answer['program'] === $programName ? 'selected' : ''; ?>>
                                                        <?= esc($programLabel); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="admin-form-label">Jawaban Resmi</label>
                                        <textarea
                                            name="answers[<?= esc($answerId); ?>][answer]"
                                            class="form-control admin-form-control"
                                            rows="5"
                                            required><?= esc($answer['answer']); ?></textarea>
                                    </div>
                                </fieldset>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!$isPublished) : ?>
        <div class="document-sticky-actions">
            <div>
                <i class="bi bi-shield-check"></i>
                <span>Simpan koreksi sebelum mempublikasikan jawaban.</span>
            </div>
            <button type="submit" class="btn btn-admin-primary">
                <i class="bi bi-floppy"></i> Simpan Hasil Review
            </button>
        </div>
    <?php endif; ?>
</form>

<div class="admin-card mt-4">
    <div class="admin-card-header">
        <div>
            <span class="lead-card-kicker">Kontrol Publikasi</span>
            <h2>Aktivasi Knowledge</h2>
            <p class="mb-0">Chatbot hanya membaca jawaban yang telah dipublikasikan.</p>
        </div>
    </div>
    <div class="admin-card-body">
        <div class="d-flex flex-column flex-xl-row align-items-xl-center justify-content-between gap-3">
            <div class="document-publish-explanation">
                <?php if ($isPublished) : ?>
                    <i class="bi bi-broadcast"></i>
                    <span>Jawaban dari dokumen ini sedang aktif dan dapat digunakan chatbot.</span>
                <?php else : ?>
                    <i class="bi bi-lock"></i>
                    <span>Jawaban masih berupa draf dan belum terlihat oleh pengunjung.</span>
                <?php endif; ?>
            </div>

            <div class="d-flex flex-wrap gap-2">
                <?php if (!$isPublished) : ?>
                    <form action="<?= base_url('/admin/chatbot/sources/' . $source['id'] . '/reprocess'); ?>" method="post">
                        <?= csrf_field(); ?>
                        <button type="submit" class="btn btn-admin-outline" <?= !$apiStatus['configured'] ? 'disabled' : ''; ?>>
                            <i class="bi bi-arrow-repeat"></i> Proses Ulang
                        </button>
                    </form>

                    <form action="<?= base_url('/admin/chatbot/sources/' . $source['id'] . '/publish'); ?>" method="post" class="document-publish-form">
                        <?= csrf_field(); ?>
                        <label>
                            <input type="checkbox" name="replace_previous" value="1" checked>
                            Arsipkan dokumen lama dengan jenis dan paket yang sama
                        </label>
                        <button type="submit" class="btn btn-admin-primary" data-confirm="Jawaban terpilih akan langsung digunakan chatbot website. Publikasikan sekarang?">
                            <i class="bi bi-broadcast"></i> Publikasikan
                        </button>
                    </form>
                <?php else : ?>
                    <form action="<?= base_url('/admin/chatbot/sources/' . $source['id'] . '/unpublish'); ?>" method="post">
                        <?= csrf_field(); ?>
                        <button type="submit" class="btn btn-admin-outline" data-confirm="Semua jawaban dari dokumen ini akan dinonaktifkan. Batalkan publikasi?">
                            <i class="bi bi-pause-circle"></i> Batalkan Publikasi
                        </button>
                    </form>
                <?php endif; ?>

                <?php if (session()->get('admin_role') === 'super_admin') : ?>
                    <form action="<?= base_url('/admin/chatbot/sources/' . $source['id'] . '/delete'); ?>" method="post">
                        <?= csrf_field(); ?>
                        <button type="submit" class="btn btn-outline-danger" data-confirm="Hapus sumber dokumen ke sampah dan nonaktifkan seluruh jawabannya?">
                            <i class="bi bi-trash3"></i> Hapus
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>

<?= $this->section('script'); ?>
<script>
document.querySelectorAll('.document-answer-check input').forEach((checkbox) => {
    const sync = () => checkbox.closest('.document-answer-item')?.classList.toggle('is-selected', checkbox.checked);
    checkbox.addEventListener('change', sync);
    sync();
});
</script>
<?= $this->endSection(); ?>
