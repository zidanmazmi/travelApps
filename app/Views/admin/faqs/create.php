<?= $this->extend('templates/admin/layouts/main'); ?>

<?= $this->section('content_header'); ?>

<div class="admin-page-title">
    <span>Konten Website</span>
    <h1>Tambah FAQ</h1>
    <p>Tambahkan pertanyaan umum untuk membantu calon jamaah memahami layanan.</p>
</div>

<?= $this->endSection(); ?>

<?= $this->section('content'); ?>

<?php $errors = session()->getFlashdata('errors') ?? []; ?>

<div class="mb-3">
    <a href="<?= base_url('/admin/faqs'); ?>" class="btn btn-admin-outline btn-sm">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
</div>

<form action="<?= base_url('/admin/faqs/store'); ?>" method="post">
    <?= csrf_field(); ?>

    <div class="admin-detail-grid">

        <div class="admin-card">
            <div class="admin-card-header">
                <h2>Isi FAQ</h2>
            </div>

            <div class="admin-card-body">

                <div class="mb-3">
                    <label class="admin-form-label">Pertanyaan</label>
                    <input 
                        type="text" 
                        name="question" 
                        class="form-control admin-form-control"
                        value="<?= old('question'); ?>"
                        placeholder="Contoh: Apakah bisa konsultasi terlebih dahulu?">

                    <?php if (isset($errors['question'])) : ?>
                        <small class="text-danger"><?= esc($errors['question']); ?></small>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label class="admin-form-label">Jawaban</label>
                    <textarea 
                        name="answer" 
                        class="form-control admin-form-control"
                        rows="8"
                        placeholder="Tulis jawaban FAQ"><?= old('answer'); ?></textarea>

                    <?php if (isset($errors['answer'])) : ?>
                        <small class="text-danger"><?= esc($errors['answer']); ?></small>
                    <?php endif; ?>
                </div>

            </div>
        </div>

        <div class="admin-card">
            <div class="admin-card-header">
                <h2>Pengaturan</h2>
            </div>

            <div class="admin-card-body">

                <div class="mb-3">
                    <label class="admin-form-label">Urutan Tampil</label>
                    <input 
                        type="number" 
                        name="sort_order" 
                        class="form-control admin-form-control"
                        value="<?= old('sort_order', 0); ?>">
                </div>

                <div class="mb-4">
                    <label class="admin-form-label">Status</label>
                    <select name="status" class="form-control admin-form-control">
                        <option value="active" <?= old('status', 'active') === 'active' ? 'selected' : ''; ?>>
                            Aktif
                        </option>
                        <option value="inactive" <?= old('status') === 'inactive' ? 'selected' : ''; ?>>
                            Nonaktif
                        </option>
                    </select>
                </div>

                <button type="submit" class="btn btn-admin-primary w-100">
                    Simpan FAQ
                </button>

            </div>
        </div>

    </div>
</form>

<?= $this->endSection(); ?>