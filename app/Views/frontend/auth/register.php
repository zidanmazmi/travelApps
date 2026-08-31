<?= $this->extend('templates/frontend/layouts/main'); ?>

<?= $this->section('content'); ?>

<section class="auth-page">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-7">

                <div class="auth-card">

                    <div class="text-center mb-4">
                        <div class="auth-brand-mark">
                            <img src="<?= site_asset('logo'); ?>" alt="<?= esc(site_setting('site_name', 'Travel Umroh & Haji')); ?>">
                        </div>
                        <h1>Buat Akun Jamaah</h1>
                        <p>Gunakan NIK dan email aktif. NIK hanya bisa digunakan untuk satu akun.</p>
                    </div>

                    <?php if (session()->getFlashdata('error')) : ?>
                        <div class="alert alert-danger">
                            <?= session()->getFlashdata('error'); ?>
                        </div>
                    <?php endif; ?>

                    <?php $errors = session()->getFlashdata('errors') ?? []; ?>

                    <form action="<?= base_url('/register'); ?>" method="post">
                        <?= csrf_field(); ?>

                        <div class="row g-3">

                            <div class="col-md-6">
                                <label class="form-label">Nama Lengkap</label>
                                <input 
                                    type="text" 
                                    name="name" 
                                    class="form-control" 
                                    value="<?= old('name'); ?>"
                                    required
                                >

                                <?php if (isset($errors['name'])) : ?>
                                    <small class="text-danger"><?= esc($errors['name']); ?></small>
                                <?php endif; ?>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">NIK</label>
                                <input 
                                    type="text" 
                                    name="nik" 
                                    class="form-control" 
                                    value="<?= old('nik'); ?>"
                                    maxlength="16"
                                    required
                                >

                                <?php if (isset($errors['nik'])) : ?>
                                    <small class="text-danger"><?= esc($errors['nik']); ?></small>
                                <?php endif; ?>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Email Aktif</label>
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

                            <div class="col-md-6">
                                <label class="form-label">No. WhatsApp</label>
                                <input 
                                    type="text" 
                                    name="phone" 
                                    class="form-control" 
                                    value="<?= old('phone'); ?>"
                                    required
                                >

                                <?php if (isset($errors['phone'])) : ?>
                                    <small class="text-danger"><?= esc($errors['phone']); ?></small>
                                <?php endif; ?>
                            </div>

                            <div class="col-md-6">
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

                            <div class="col-md-6">
                                <label class="form-label">Konfirmasi Password</label>
                                <input 
                                    type="password" 
                                    name="password_confirmation" 
                                    class="form-control" 
                                    required
                                >

                                <?php if (isset($errors['password_confirmation'])) : ?>
                                    <small class="text-danger"><?= esc($errors['password_confirmation']); ?></small>
                                <?php endif; ?>
                            </div>

                        </div>

                        <button type="submit" class="btn btn-brand-primary w-100 mt-4">
                            Register
                        </button>
                    </form>

                    <div class="auth-links justify-content-center">
                        <a href="<?= base_url('/login'); ?>">Sudah punya akun? Login</a>
                    </div>

                </div>

            </div>
        </div>
    </div>
</section>

<?= $this->endSection(); ?>