<?= $this->extend('templates/frontend/layouts/main'); ?>

<?= $this->section('content'); ?>

<section class="auth-page">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-5">

                <div class="auth-card">

                    <div class="text-center mb-4">
                        <div class="auth-brand-mark">
                            <img src="<?= site_asset('logo'); ?>" alt="<?= esc(site_setting('site_name', 'Travel Umroh & Haji')); ?>">
                        </div>
                        <span class="section-label">Verifikasi Email</span>
                        <h1>Masukkan Kode</h1>
                        <p>
                            Kode verifikasi dikirim untuk email:
                            <strong><?= esc($email); ?></strong>
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

                    <form action="<?= base_url('/verifikasi-email'); ?>" method="post">
                        <?= csrf_field(); ?>

                        <div class="mb-3">
                            <label class="form-label">Kode Verifikasi</label>
                            <input 
                                type="text" 
                                name="verification_code" 
                                class="form-control text-center"
                                maxlength="6"
                                required
                            >
                        </div>

                        <button type="submit" class="btn btn-brand-primary w-100">
                            Verifikasi Email
                        </button>
                    </form>

                    <div class="auth-links justify-content-center">
                        <a href="<?= base_url('/login'); ?>">Kembali ke Login</a>
                    </div>

                </div>

            </div>
        </div>
    </div>
</section>

<?= $this->endSection(); ?>