<?= $this->extend('templates/frontend/layouts/main'); ?>

<?= $this->section('content'); ?>

<section class="registration-page">
    <div class="container">

        <div class="row justify-content-center">
            <div class="col-lg-7">

                <div class="registration-form-card">

                    <div class="registration-header text-center">
                        <span class="section-label">Cek Status</span>

                        <h1>Cek Status Pendaftaran</h1>

                        <p>
                            Masukkan nomor pendaftaran untuk melihat status pendaftaran dan pembayaran jamaah.
                        </p>
                    </div>

                    <?php if (session()->getFlashdata('error')) : ?>
                        <div class="alert alert-danger">
                            <?= session()->getFlashdata('error'); ?>
                        </div>
                    <?php endif; ?>

                    <form action="<?= base_url('/cek-status'); ?>" method="post">
                        <?= csrf_field(); ?>

                        <div class="mb-3">
                            <label class="form-label">Nomor Pendaftaran</label>
                            <input 
                                type="text" 
                                name="registration_no" 
                                class="form-control"
                                value="<?= old('registration_no'); ?>"
                                placeholder="Contoh: HQM-2026-ABC123"
                                required
                            >
                        </div>

                        <button type="submit" class="btn btn-brand-primary w-100">
                            Cek Status
                        </button>
                    </form>

                    <div class="text-center mt-3">
                        <a href="<?= base_url('/'); ?>" class="text-decoration-none">
                            Kembali ke Beranda
                        </a>
                    </div>

                </div>

            </div>
        </div>

    </div>
</section>

<?= $this->endSection(); ?>