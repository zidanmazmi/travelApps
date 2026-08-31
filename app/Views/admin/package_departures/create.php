<?= $this->extend('templates/admin/layouts/main'); ?>

<?= $this->section('content_header'); ?>

<div class="admin-page-title">
    <span>Master Data</span>
    <h1>Tambah Jadwal Keberangkatan</h1>
    <p>Paket: <strong><?= esc($package['name']); ?></strong></p>
</div>

<?= $this->endSection(); ?>

<?= $this->section('content'); ?>

<?php $errors = session()->getFlashdata('errors') ?? []; ?>

<div class="mb-3">
    <a href="<?= base_url('/admin/packages/departures/' . $package['id']); ?>" class="btn btn-admin-outline btn-sm">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
</div>

<form action="<?= base_url('/admin/packages/departures/store'); ?>" method="post">
    <?= csrf_field(); ?>

    <input type="hidden" name="package_id" value="<?= esc($package['id']); ?>">

    <div class="admin-card">
        <div class="admin-card-header">
            <h2>Form Jadwal Keberangkatan</h2>
        </div>

        <div class="admin-card-body">
            <div class="row">

                <div class="col-md-6 mb-3">
                    <label class="admin-form-label">Tanggal Berangkat</label>
                    <input 
                        type="date" 
                        name="departure_date" 
                        class="form-control admin-form-control"
                        value="<?= old('departure_date'); ?>"
                    >

                    <?php if (isset($errors['departure_date'])) : ?>
                        <small class="text-danger"><?= esc($errors['departure_date']); ?></small>
                    <?php endif; ?>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="admin-form-label">Tanggal Pulang</label>
                    <input 
                        type="date" 
                        name="return_date" 
                        class="form-control admin-form-control"
                        value="<?= old('return_date'); ?>"
                    >

                    <?php if (isset($errors['return_date'])) : ?>
                        <small class="text-danger"><?= esc($errors['return_date']); ?></small>
                    <?php endif; ?>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="admin-form-label">Kuota</label>
                    <input 
                        type="number" 
                        name="quota" 
                        class="form-control admin-form-control"
                        value="<?= old('quota', 0); ?>"
                    >

                    <?php if (isset($errors['quota'])) : ?>
                        <small class="text-danger"><?= esc($errors['quota']); ?></small>
                    <?php endif; ?>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="admin-form-label">Terisi</label>
                    <input 
                        type="number" 
                        name="booked" 
                        class="form-control admin-form-control"
                        value="<?= old('booked', 0); ?>"
                    >

                    <?php if (isset($errors['booked'])) : ?>
                        <small class="text-danger"><?= esc($errors['booked']); ?></small>
                    <?php endif; ?>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="admin-form-label">Status</label>
                    <select name="status" class="form-control admin-form-control">
                        <option value="available" <?= old('status') === 'available' ? 'selected' : ''; ?>>
                            Available
                        </option>
                        <option value="full" <?= old('status') === 'full' ? 'selected' : ''; ?>>
                            Full
                        </option>
                        <option value="closed" <?= old('status') === 'closed' ? 'selected' : ''; ?>>
                            Closed
                        </option>
                    </select>

                    <?php if (isset($errors['status'])) : ?>
                        <small class="text-danger"><?= esc($errors['status']); ?></small>
                    <?php endif; ?>
                </div>

            </div>

            <button type="submit" class="btn btn-admin-primary">
                Simpan Jadwal
            </button>
        </div>
    </div>
</form>

<?= $this->endSection(); ?>