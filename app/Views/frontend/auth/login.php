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
                        <h1>Masuk Akun Jamaah</h1>
                        <p>Masuk untuk melanjutkan pendaftaran paket umrah atau haji.</p>
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

                    <form action="<?= base_url('/login'); ?>" method="post">
                        <?= csrf_field(); ?>

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input 
                                type="email" 
                                name="email" 
                                class="form-control" 
                                value="<?= old('email'); ?>"
                                required
                            >

                            <?php if (isset($errors['email'])) : ?>
                                <small class="text-danger"><?= esc($errors['email']); ?></small>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input 
                                type="password" 
                                name="password" 
                                class="form-control" 
                                required
                            >

                            <?php if (isset($errors['password'])) : ?>
                                <small class="text-danger"><?= esc($errors['password']); ?></small>
                            <?php endif; ?>
                        </div>

                        <button type="submit" class="btn btn-brand-primary w-100">
                            Login
                        </button>
                    </form>

                    <div class="auth-links">
                        <a href="<?= base_url('/register'); ?>">Belum punya akun? Register</a>
                        <a href="<?= base_url('/lupa-password'); ?>">Lupa password?</a>
                    </div>

                </div>

            </div>
        </div>
    </div>
</section>

<?= $this->endSection(); ?>