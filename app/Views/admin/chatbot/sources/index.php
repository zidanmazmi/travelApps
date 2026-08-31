<?= $this->extend('templates/admin/layouts/main'); ?>

<?= $this->section('content_header'); ?>
<div class="admin-page-title d-flex flex-column flex-xl-row align-items-xl-end justify-content-between gap-3">
    <div>
        <span>Smart Context Engine 2.0</span>
        <h1>Document Knowledge</h1>
        <p>Ubah itinerary, flyer, dan booklet menjadi jawaban resmi chatbot melalui tahap ekstraksi dan persetujuan admin.</p>
    </div>
    <a href="<?= base_url('/admin/chatbot'); ?>" class="btn btn-admin-outline">
        <i class="bi bi-arrow-left"></i> Kembali ke Chatbot
    </a>
</div>
<?= $this->endSection(); ?>

<?= $this->section('content'); ?>
<?php if (session()->getFlashdata('success')) : ?>
    <div class="alert alert-success rounded-4"><?= esc(session()->getFlashdata('success')); ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')) : ?>
    <div class="alert alert-danger rounded-4"><?= esc(session()->getFlashdata('error')); ?></div>
<?php endif; ?>

<div class="document-ai-status <?= $apiStatus['configured'] ? 'is-ready' : 'is-warning'; ?> mb-4">
    <span class="document-ai-status-icon">
        <i class="bi <?= $apiStatus['configured'] ? 'bi-shield-check' : 'bi-exclamation-triangle'; ?>"></i>
    </span>
    <div>
        <strong><?= $apiStatus['configured'] ? 'Konfigurasi Gemini API ditemukan' : 'Gemini API belum dikonfigurasi'; ?></strong>
        <p>
            <?php if ($apiStatus['configured']) : ?>
                Ekstraksi memakai <?= esc($apiStatus['vision_model']); ?> dan pencarian semantik memakai <?= esc($apiStatus['embedding_model']); ?>.
                Jika kuota gratis habis, chatbot tetap berjalan dengan fallback lokal.
            <?php else : ?>
                Tambahkan <code>GEMINI_API_KEY</code> di file <code>.env</code>. Petunjuk lengkap tersedia dalam <code>PANDUAN-GEMINI-API.md</code>.
            <?php endif; ?>
        </p>
    </div>
    <span class="status-pill <?= $apiStatus['configured'] ? 'status-success' : 'status-warning'; ?>">
        <?= $apiStatus['configured'] ? 'Terkonfigurasi' : 'Perlu setup'; ?>
    </span>
</div>

<div class="admin-stats-grid mb-4">
    <div class="admin-stat-card">
        <div class="stat-icon"><i class="bi bi-files"></i></div>
        <div><span>Total Sumber</span><h3><?= esc($stats['total']); ?></h3></div>
    </div>
    <div class="admin-stat-card">
        <div class="stat-icon accent"><i class="bi bi-eye"></i></div>
        <div><span>Perlu Ditinjau</span><h3><?= esc($stats['review']); ?></h3></div>
    </div>
    <div class="admin-stat-card">
        <div class="stat-icon success"><i class="bi bi-broadcast"></i></div>
        <div><span>Dipublikasikan</span><h3><?= esc($stats['published']); ?></h3></div>
    </div>
    <div class="admin-stat-card">
        <div class="stat-icon danger"><i class="bi bi-exclamation-octagon"></i></div>
        <div><span>Gagal Diproses</span><h3><?= esc($stats['failed']); ?></h3></div>
    </div>
</div>

<div class="admin-card document-upload-card mb-4">
    <div class="admin-card-header">
        <div>
            <span class="lead-card-kicker">Training Data Otomatis</span>
            <h2>Upload Dokumen Baru</h2>
            <p class="mb-0">Mendukung JPG, PNG, WebP, dan PDF. Maksimal <?= esc($apiStatus['max_file_mb']); ?> MB per file dan 10 file sekali upload.</p>
        </div>
        <i class="bi bi-cloud-arrow-up document-upload-heading-icon"></i>
    </div>
    <div class="admin-card-body">
        <form action="<?= base_url('/admin/chatbot/sources/upload'); ?>" method="post" enctype="multipart/form-data">
            <?= csrf_field(); ?>

            <div class="document-upload-dropzone mb-3" data-document-dropzone>
                <i class="bi bi-images"></i>
                <div>
                    <strong>Pilih itinerary, flyer, atau booklet</strong>
                    <span>Anda dapat memilih beberapa gambar/PDF sekaligus.</span>
                </div>
                <input
                    type="file"
                    name="source_files[]"
                    accept=".jpg,.jpeg,.png,.webp,.pdf"
                    multiple
                    required
                    data-document-files
                    <?= !$apiStatus['configured'] ? 'disabled' : ''; ?>>
            </div>

            <div class="document-upload-selection mb-4" data-document-selection hidden>
                <div class="document-upload-selection-head">
                    <div>
                        <strong data-document-selection-title>0 file dipilih</strong>
                        <span data-document-selection-size>0 MB</span>
                    </div>
                    <button type="button" class="btn btn-admin-outline btn-sm" data-document-clear>
                        <i class="bi bi-x-lg"></i> Hapus Semua
                    </button>
                </div>
                <div class="document-upload-preview-grid" data-document-preview></div>
            </div>

            <div class="row g-3">
                <div class="col-lg-4">
                    <label class="admin-form-label">Jenis Dokumen</label>
                    <select name="document_type" class="form-control admin-form-control" required>
                        <?php foreach ($typeOptions as $value => $label) : ?>
                            <option value="<?= esc($value); ?>" <?= old('document_type', 'flyer') === $value ? 'selected' : ''; ?>>
                                <?= esc($label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-lg-4">
                    <label class="admin-form-label">Hubungkan ke Paket</label>
                    <select name="package_id" class="form-control admin-form-control">
                        <option value="">Informasi umum / belum ditentukan</option>
                        <?php foreach ($packages as $package) : ?>
                            <option value="<?= esc($package['id']); ?>" <?= (string) old('package_id') === (string) $package['id'] ? 'selected' : ''; ?>>
                                <?= esc(($package['program'] ? ucfirst($package['program']) . ' — ' : '') . $package['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-lg-4">
                    <label class="admin-form-label">Judul Sumber <small>(opsional)</small></label>
                    <input
                        type="text"
                        name="title"
                        class="form-control admin-form-control"
                        value="<?= esc(old('title')); ?>"
                        maxlength="255"
                        placeholder="Contoh: Flyer Program Reguler Agustus 2026">
                </div>
                <div class="col-md-4">
                    <label class="admin-form-label">Mulai Berlaku</label>
                    <input type="date" name="valid_from" class="form-control admin-form-control" value="<?= esc(old('valid_from')); ?>">
                </div>
                <div class="col-md-4">
                    <label class="admin-form-label">Berlaku Sampai</label>
                    <input type="date" name="valid_until" class="form-control admin-form-control" value="<?= esc(old('valid_until')); ?>">
                </div>
                <div class="col-md-4">
                    <label class="admin-form-label">Prioritas Sumber</label>
                    <input type="number" name="priority" class="form-control admin-form-control" min="1" max="100" value="<?= esc(old('priority', 50)); ?>">
                    <small class="text-muted d-block mt-2">Nilai lebih tinggi didahulukan bila informasi bertentangan.</small>
                </div>
                <div class="col-12">
                    <label class="admin-form-label">Catatan untuk AI <small>(opsional)</small></label>
                    <textarea
                        name="admin_notes"
                        class="form-control admin-form-control"
                        rows="3"
                        maxlength="1000"
                        placeholder="Contoh: Angka harga berlaku per jamaah dan itinerary menggunakan kode bandara."><?= esc(old('admin_notes')); ?></textarea>
                </div>
            </div>

            <div class="document-review-notice mt-4">
                <i class="bi bi-person-check"></i>
                <div>
                    <strong>Tetap melalui persetujuan admin</strong>
                    <span>Hasil AI disimpan sebagai draf. Chatbot belum menggunakannya sampai Anda memeriksa dan menekan Publikasikan.</span>
                </div>
            </div>

            <div class="d-flex justify-content-end mt-4">
                <button type="submit" class="btn btn-admin-primary" <?= !$apiStatus['configured'] ? 'disabled' : ''; ?>>
                    <i class="bi bi-stars"></i> Upload &amp; Ekstrak Otomatis
                </button>
            </div>
        </form>
    </div>
</div>

<div class="admin-card">
    <div class="admin-card-header">
        <div>
            <span class="lead-card-kicker">Sumber Pengetahuan</span>
            <h2>Dokumen Tersimpan</h2>
        </div>
        <span class="status-pill status-muted"><?= esc(count($sources)); ?> dokumen</span>
    </div>
    <div class="table-responsive">
        <table class="admin-table document-source-table">
            <thead>
            <tr>
                <th>Dokumen</th>
                <th>Jenis / Paket</th>
                <th>Status</th>
                <th>Validitas</th>
                <th>Confidence</th>
                <th>Diperbarui</th>
                <th>Aksi</th>
            </tr>
            </thead>
            <tbody>
            <?php if ($sources === []) : ?>
                <tr>
                    <td colspan="7">
                        <div class="admin-empty">
                            <i class="bi bi-file-earmark-richtext"></i>
                            Belum ada document knowledge. Upload sumber pertama dari form di atas.
                        </div>
                    </td>
                </tr>
            <?php else : ?>
                <?php
                $statusLabels = [
                    'uploaded'   => ['Terunggah', 'status-muted'],
                    'processing' => ['Diproses', 'status-warning'],
                    'review'     => ['Perlu ditinjau', 'status-warning'],
                    'published'  => ['Published', 'status-success'],
                    'archived'   => ['Diarsipkan', 'status-muted'],
                    'failed'     => ['Gagal', 'status-danger'],
                ];
                ?>
                <?php foreach ($sources as $source) : ?>
                    <?php $status = $statusLabels[$source['status'] ?? 'uploaded'] ?? ['Unknown', 'status-muted']; ?>
                    <tr>
                        <td>
                            <div class="document-source-name">
                                <span><i class="bi <?= $source['mime_type'] === 'application/pdf' ? 'bi-file-earmark-pdf' : 'bi-file-earmark-image'; ?>"></i></span>
                                <div>
                                    <strong><?= esc($source['title']); ?></strong>
                                    <small><?= esc($source['original_name']); ?> · <?= number_format(((int) $source['file_size']) / 1_048_576, 2, ',', '.'); ?> MB</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <strong><?= esc($typeOptions[$source['document_type']] ?? ucfirst($source['document_type'])); ?></strong><br>
                            <small><?= esc($source['package_name'] ?: 'Informasi umum'); ?></small>
                        </td>
                        <td>
                            <span class="status-pill <?= esc($status[1]); ?>"><?= esc($status[0]); ?></span>
                            <?php if (!empty($source['error_message'])) : ?>
                                <small class="d-block text-danger mt-2"><?= esc(mb_strimwidth($source['error_message'], 0, 80, '...')); ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <small>
                                <?= esc($source['valid_from'] ?: 'Tanpa tanggal mulai'); ?><br>
                                s.d. <?= esc($source['valid_until'] ?: 'Tidak dibatasi'); ?>
                            </small>
                        </td>
                        <td>
                            <?= $source['confidence'] !== null
                                ? esc(number_format(((float) $source['confidence']) * 100, 0)) . '%'
                                : '-'; ?>
                        </td>
                        <td><?= !empty($source['updated_at']) ? date('d M Y H:i', strtotime($source['updated_at'])) : '-'; ?></td>
                        <td>
                            <a href="<?= base_url('/admin/chatbot/sources/' . $source['id']); ?>" class="btn btn-admin-outline btn-sm">
                                <i class="bi bi-eye"></i> Tinjau
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection(); ?>

<?= $this->section('script'); ?>
<script>
(() => {
    const input = document.querySelector('[data-document-files]');
    const dropzone = document.querySelector('[data-document-dropzone]');
    const selection = document.querySelector('[data-document-selection]');
    const preview = document.querySelector('[data-document-preview]');
    const selectionTitle = document.querySelector('[data-document-selection-title]');
    const selectionSize = document.querySelector('[data-document-selection-size]');
    const clearButton = document.querySelector('[data-document-clear]');

    if (!input || !dropzone || !selection || !preview) {
        return;
    }

    let selectedFiles = [];
    const maxFiles = 10;

    const formatSize = (bytes) => {
        if (!Number.isFinite(bytes) || bytes <= 0) {
            return '0 KB';
        }

        if (bytes < 1024 * 1024) {
            return `${(bytes / 1024).toFixed(1)} KB`;
        }

        return `${(bytes / (1024 * 1024)).toFixed(2)} MB`;
    };

    const syncInput = () => {
        const transfer = new DataTransfer();
        selectedFiles.forEach((file) => transfer.items.add(file));
        input.files = transfer.files;
    };

    const render = () => {
        preview.innerHTML = '';

        if (selectedFiles.length === 0) {
            selection.hidden = true;
            dropzone.classList.remove('has-files');
            return;
        }

        selection.hidden = false;
        dropzone.classList.add('has-files');

        const totalBytes = selectedFiles.reduce((total, file) => total + file.size, 0);
        selectionTitle.textContent = `${selectedFiles.length} file dipilih`;
        selectionSize.textContent = `Total ${formatSize(totalBytes)}`;

        selectedFiles.forEach((file, index) => {
            const item = document.createElement('div');
            item.className = 'document-upload-preview-item';

            const media = document.createElement('div');
            media.className = 'document-upload-preview-media';

            if (file.type.startsWith('image/')) {
                const image = document.createElement('img');
                const objectUrl = URL.createObjectURL(file);
                image.src = objectUrl;
                image.alt = file.name;
                image.addEventListener('load', () => URL.revokeObjectURL(objectUrl), { once: true });
                media.appendChild(image);
            } else {
                const icon = document.createElement('i');
                icon.className = 'bi bi-file-earmark-pdf';
                media.appendChild(icon);
            }

            const info = document.createElement('div');
            info.className = 'document-upload-preview-info';

            const name = document.createElement('strong');
            name.textContent = file.name;
            name.title = file.name;

            const meta = document.createElement('span');
            meta.textContent = `${file.type || 'File'} · ${formatSize(file.size)}`;

            info.append(name, meta);

            const remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'document-upload-preview-remove';
            remove.setAttribute('aria-label', `Hapus ${file.name}`);
            remove.innerHTML = '<i class="bi bi-x-lg"></i>';
            remove.addEventListener('click', () => {
                selectedFiles.splice(index, 1);
                syncInput();
                render();
            });

            item.append(media, info, remove);
            preview.appendChild(item);
        });
    };

    input.addEventListener('change', () => {
        selectedFiles = Array.from(input.files || []).slice(0, maxFiles);
        syncInput();
        render();
    });

    clearButton?.addEventListener('click', () => {
        selectedFiles = [];
        syncInput();
        render();
    });

    ['dragenter', 'dragover'].forEach((eventName) => {
        dropzone.addEventListener(eventName, () => dropzone.classList.add('is-dragging'));
    });

    ['dragleave', 'drop'].forEach((eventName) => {
        dropzone.addEventListener(eventName, () => dropzone.classList.remove('is-dragging'));
    });
})();
</script>
<?= $this->endSection(); ?>

