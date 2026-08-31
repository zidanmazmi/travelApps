<?= $this->extend('templates/frontend/layouts/main'); ?>

<?= $this->section('content'); ?>

<section class="registration-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6">

                <div class="registration-card">

                    <div class="text-center mb-4">
                        <span class="registration-badge">Pendaftaran Jamaah</span>
                        <h1>Verifikasi Email</h1>
                        <p>
                            Masukkan email aktif Anda sebelum melanjutkan proses pendaftaran jamaah.
                        </p>
                    </div>

                    <?php if (session()->getFlashdata('error')) : ?>
                        <div class="alert alert-danger">
                            <?= session()->getFlashdata('error'); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (session()->getFlashdata('success')) : ?>
                        <div class="alert alert-success">
                            <?= session()->getFlashdata('success'); ?>
                        </div>
                    <?php endif; ?>

                    <form action="<?= base_url('/daftar/kirim-verifikasi'); ?>" method="post">
                        <?= csrf_field(); ?>

                        <input type="hidden" name="slug" value="<?= esc($slug); ?>">

                        <div class="mb-3">
                            <label class="form-label">Email Jamaah</label>
                            <input 
                                type="email" 
                                name="email" 
                                class="form-control" 
                                placeholder="nama@email.com"
                                value="<?= old('email'); ?>"
                                required
                            >
                        </div>

                        <button type="submit" class="btn btn-brand-primary w-100">
                            Lanjutkan Pendaftaran
                        </button>
                    </form>

                </div>

            </div>
        </div>
    </div>
</section>

<?= $this->endSection(); ?>