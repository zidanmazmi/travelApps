<?= $this->extend('templates/admin/layouts/main'); ?>
<?= $this->section('content_header'); ?>
<div class="admin-page-title d-flex flex-column flex-lg-row align-items-lg-end justify-content-between gap-3">
    <div>
        <span>Data Governance</span>
        <h1>Sampah Data</h1>
        <p>Pusat pemulihan dan penghapusan permanen. Pilih data secara massal atau kosongkan satu kategori Sampah.</p>
    </div>
    <a href="<?= base_url('/admin/dashboard'); ?>" class="btn btn-admin-outline"><i class="bi bi-arrow-left"></i> Dashboard</a>
</div>
<?= $this->endSection(); ?>

<?= $this->section('content'); ?>
<?php if (session()->getFlashdata('success')) : ?><div class="alert alert-success rounded-4"><?= esc(session()->getFlashdata('success')); ?></div><?php endif; ?>
<?php if (session()->getFlashdata('error')) : ?><div class="alert alert-danger rounded-4"><?= esc(session()->getFlashdata('error')); ?></div><?php endif; ?>

<div class="recycle-grid">
    <?php foreach ($items as $item) : ?>
        <a href="<?= base_url($item['url']); ?>" class="recycle-card">
            <div class="recycle-card-icon"><i class="bi <?= esc($item['icon']); ?>"></i></div>
            <div class="recycle-card-copy">
                <span><?= esc($item['label']); ?></span>
                <strong><?= number_format((int) $item['count'], 0, ',', '.'); ?></strong>
                <p><?= esc($item['description']); ?></p>
            </div>
            <i class="bi bi-arrow-up-right recycle-card-arrow"></i>
        </a>
    <?php endforeach; ?>
</div>

<div class="admin-card mt-4">
    <div class="admin-card-body recycle-warning">
        <i class="bi bi-shield-exclamation"></i>
        <div>
            <strong>Penghapusan permanen tidak dapat dibatalkan.</strong>
            <p>Gunakan pilihan massal untuk data tertentu. Tombol “Kosongkan Sampah” akan menghapus seluruh data pada kategori tersebut beserta file terkait jika ada.</p>
        </div>
    </div>
</div>
<?= $this->endSection(); ?>
