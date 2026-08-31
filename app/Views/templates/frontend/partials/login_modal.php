<div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header bg-success text-white">
                <div>
                    <small class="text-warning fw-bold text-uppercase">Admin Panel</small>
                    <h5 class="modal-title fw-bold" id="loginModalLabel">Login Admin</h5>
                </div>

                <button 
                    type="button" 
                    class="btn-close btn-close-white" 
                    data-bs-dismiss="modal" 
                    aria-label="Close">
                </button>
            </div>

            <div class="modal-body">

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

                <form action="<?= base_url('/admin/login'); ?>" method="post">
                    <?= csrf_field(); ?>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Email Admin</label>
                        <input 
                            type="email" 
                            name="email" 
                            class="form-control" 
                            value="<?= old('email'); ?>"
                            placeholder="admin@example.com"
                            required
                        >
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Password</label>
                        <input 
                            type="password" 
                            name="password" 
                            class="form-control"
                            placeholder="Masukkan password"
                            required
                        >
                    </div>

                    <button type="submit" class="btn btn-warning w-100 fw-semibold">
                        Masuk Dashboard
                    </button>
                </form>

                <p class="text-muted text-center small mt-3 mb-0">
                    Registrasi admin hanya dapat dilakukan oleh Super Admin melalui dashboard.
                </p>

            </div>

        </div>
    </div>
</div>