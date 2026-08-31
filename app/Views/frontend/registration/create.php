<?= $this->extend('templates/frontend/layouts/main'); ?>

<?= $this->section('content'); ?>

<section class="registration-page">
    <div class="container">

        <div class="registration-header text-center">
            <span class="section-label">Pendaftaran Jamaah</span>

            <h1>Form Pendaftaran Paket</h1>

            <p>
                Lengkapi data pendaftaran untuk paket
                <strong><?= esc($package['name']); ?></strong>.
            </p>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-9">

                <div class="registration-card">

                    <?php if (session()->getFlashdata('error')) : ?>
                        <div class="alert alert-danger">
                            <?= session()->getFlashdata('error'); ?>
                        </div>
                    <?php endif; ?>

                    <?php $errors = session()->getFlashdata('errors') ?? []; ?>

                    <div class="selected-package-box">
                        <div>
                            <span>Paket Dipilih</span>
                            <h2><?= esc($package['name']); ?></h2>
                        </div>

                        <strong>
                            Rp <?= number_format($package['price'], 0, ',', '.'); ?>
                        </strong>
                    </div>

                    <form action="<?= base_url('/daftar/simpan'); ?>" method="post">
                        <?= csrf_field(); ?>

                        <input type="hidden" name="package_id" value="<?= esc($package['id']); ?>">
                        <div class="row g-3">

                            <div class="col-md-6">
                                <label class="form-label">Nama Lengkap</label>
                                <input
                                    type="text"
                                    name="full_name"
                                    class="form-control"
                                    value="<?= old('full_name', $user['name']); ?>"
                                    required>

                                <?php if (isset($errors['full_name'])) : ?>
                                    <small class="text-danger"><?= esc($errors['full_name']); ?></small>
                                <?php endif; ?>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">NIK</label>
                                <input
                                    type="text"
                                    name="nik"
                                    class="form-control"
                                    value="<?= old('nik', $user['nik']); ?>"
                                    readonly
                                    required>

                                <?php if (isset($errors['nik'])) : ?>
                                    <small class="text-danger"><?= esc($errors['nik']); ?></small>
                                <?php endif; ?>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input
                                    type="email"
                                    name="email"
                                    class="form-control"
                                    value="<?= old('email', $user['email']); ?>"
                                    readonly
                                    required>

                                <?php if (isset($errors['email'])) : ?>
                                    <small class="text-danger"><?= esc($errors['email']); ?></small>
                                <?php endif; ?>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">No. WhatsApp</label>
                                <input
                                    type="text"
                                    name="phone"
                                    class="form-control"
                                    value="<?= old('phone', $user['phone']); ?>"
                                    required>

                                <?php if (isset($errors['phone'])) : ?>
                                    <small class="text-danger"><?= esc($errors['phone']); ?></small>
                                <?php endif; ?>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Tempat Lahir</label>
                                <input
                                    type="text"
                                    name="birth_place"
                                    class="form-control"
                                    value="<?= old('birth_place'); ?>">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Tanggal Lahir</label>
                                <input
                                    type="date"
                                    name="birth_date"
                                    class="form-control"
                                    value="<?= old('birth_date'); ?>">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Jenis Kelamin</label>
                                <select name="gender" class="form-control">
                                    <option value="">Pilih Jenis Kelamin</option>
                                    <option value="Laki-laki" <?= old('gender') === 'Laki-laki' ? 'selected' : ''; ?>>
                                        Laki-laki
                                    </option>
                                    <option value="Perempuan" <?= old('gender') === 'Perempuan' ? 'selected' : ''; ?>>
                                        Perempuan
                                    </option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">No. Paspor</label>
                                <input
                                    type="text"
                                    name="passport_number"
                                    class="form-control"
                                    value="<?= old('passport_number'); ?>"
                                    placeholder="Opsional">
                            </div>
<div class="mb-3">
                            <label class="form-label">Tanggal Keberangkatan</label>

                            <select name="departure_id" class="form-control" required>
                                <option value="">Pilih Tanggal Keberangkatan</option>

                                <?php if (!empty($departures)) : ?>
                                    <?php foreach ($departures as $departure) : ?>
                                        <?php
                                        $quota = (int) ($departure['quota'] ?? 0);
                                        $booked = (int) ($departure['booked'] ?? 0);
                                        $remaining = $quota > 0 ? max($quota - $booked, 0) : null;
                                        ?>

                                        <option
                                            value="<?= esc($departure['id']); ?>"
                                            <?= old('departure_id') == $departure['id'] ? 'selected' : ''; ?>>
                                            <?= date('d M Y', strtotime($departure['departure_date'])); ?>
                                            <?php if (!empty($departure['return_date'])) : ?>
                                                - <?= date('d M Y', strtotime($departure['return_date'])); ?>
                                            <?php endif; ?>

                                            <?php if ($remaining !== null) : ?>
                                                | Sisa Kuota: <?= esc($remaining); ?>
                                            <?php else : ?>
                                                | Kuota tersedia
                                            <?php endif; ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>

                            <?php $errors = session()->getFlashdata('errors') ?? []; ?>

                            <?php if (isset($errors['departure_id'])) : ?>
                                <small class="text-danger"><?= esc($errors['departure_id']); ?></small>
                            <?php endif; ?>

                            <?php if (empty($departures)) : ?>
                                <small class="text-danger d-block mt-2">
                                    Belum ada jadwal keberangkatan tersedia untuk paket ini. Silakan hubungi admin.
                                </small>
                            <?php endif; ?>
                        </div>
                            <div class="col-12">
                                <label class="form-label">Alamat Lengkap</label>
                                <textarea
                                    name="address"
                                    class="form-control"
                                    rows="4"
                                    required><?= old('address'); ?></textarea>

                                <?php if (isset($errors['address'])) : ?>
                                    <small class="text-danger"><?= esc($errors['address']); ?></small>
                                <?php endif; ?>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Catatan Tambahan</label>
                                <textarea
                                    name="note"
                                    class="form-control"
                                    rows="3"
                                    placeholder="Opsional"><?= old('note'); ?></textarea>
                            </div>

                        </div>

                        <div class="registration-actions">
                            <a href="<?= base_url('/paket/' . $package['slug']); ?>" class="btn btn-outline-secondary">
                                Kembali
                            </a>

                            <button
                                type="submit"
                                class="btn btn-brand-primary"
                                <?= empty($departures) ? 'disabled' : ''; ?>>
                                Kirim Pendaftaran
                            </button>
                        </div>

                    </form>

                </div>

            </div>
        </div>

    </div>
</section>

<?= $this->endSection(); ?>