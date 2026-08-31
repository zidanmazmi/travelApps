<?= $this->extend('templates/frontend/layouts/main'); ?>

<?= $this->section('content'); ?>

<section class="registration-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">

                <div class="registration-card">

                    <div class="text-center mb-4">
                        <span class="registration-badge">Data Jamaah</span>
                        <h1>Form Pendaftaran</h1>
                        <p>
                            Lengkapi data jamaah untuk paket: <strong><?= esc($slug); ?></strong>
                        </p>
                    </div>

                    <form action="<?= base_url('/daftar/simpan'); ?>" method="post">
                        <?= csrf_field(); ?>

                        <input type="hidden" name="slug" value="<?= esc($slug); ?>">
                        <input type="hidden" name="email" value="<?= esc($email); ?>">

                        <div class="row g-3">

                            <div class="col-md-6">
                                <label class="form-label">Nama Lengkap</label>
                                <input type="text" name="full_name" class="form-control" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">NIK</label>
                                <input type="text" name="nik" class="form-control" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">No. WhatsApp</label>
                                <input type="text" name="phone" class="form-control" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Tanggal Lahir</label>
                                <input type="date" name="birth_date" class="form-control" required>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Alamat</label>
                                <textarea name="address" class="form-control" rows="3" required></textarea>
                            </div>

                        </div>

                        <button type="submit" class="btn btn-brand-primary mt-4">
                            Simpan Pendaftaran
                        </button>

                    </form>

                </div>

            </div>
        </div>
    </div>
</section>

<?= $this->endSection(); ?>