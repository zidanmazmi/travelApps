<section id="paket" class="section-packages wl-package-highlight-section">
    <div class="container">

        <div class="section-heading text-center" data-reveal="up">
            <span class="wl-section-kicker justify-content-center">Lorem Ipsum</span>
            <h2>Lorem ipsum dolor sit amet</h2>
            <div class="heading-line"></div>
            <p class="package-section-intro">
                Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.
            </p>
        </div>

        <?php if (empty($featuredPackages)) : ?>

            <div class="text-center">
                <p>Paket perjalanan belum tersedia.</p>
            </div>

        <?php else : ?>

            <div class="row g-4">

                <?php foreach ($featuredPackages as $index => $package) : ?>

                    <?php
                    $name           = $package['name'] ?? 'Lorem Ipsum';
                    $slug           = $package['slug'] ?? '';
                    $badge          = $package['badge'] ?? 'Lorem';
                    $program        = trim((string) ($package['program'] ?? ''));
                    $programLabel   = $program !== '' ? ucwords($program) : '';
                    $image          = trim((string) ($package['image'] ?? ''));
                    $priceLabel     = $package['priceLabel'] ?? 'Lorem Ipsum';
                    $durationLabel  = $package['durationLabel'] ?? 'Lorem ipsum dolor';
                    $departureLabel = $package['departureLabel'] ?? 'Dolor sit amet';
                    $quotaLabel     = $package['quotaLabel'] ?? 'Consectetur';
                    ?>

                    <div class="col-md-6 col-lg-4" data-reveal="up" data-delay="<?= $index * 90; ?>">
                        <article class="package-card package-highlight-card h-100" data-tilt>

                            <div class="package-image">
                                <?php if ($image !== '') : ?>
                                    <img
                                        src="<?= base_url($image); ?>"
                                        alt="<?= esc($name); ?>"
                                        loading="lazy">
                                <?php else : ?>
                                    <div class="package-image-placeholder">
                                        <i class="bi bi-image"></i>
                                        <span>Lorem Ipsum</span>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($badge)) : ?>
                                    <span class="package-badge">
                                        <?= esc($badge); ?>
                                    </span>
                                <?php endif; ?>
                            </div>

                            <div class="package-body">
                                <?php if ($programLabel !== '') : ?>
                                    <span class="package-program-label">Program <?= esc($programLabel); ?></span>
                                <?php endif; ?>
                                <h3><?= esc($name); ?></h3>

                                <div class="package-highlight-list" aria-label="Ringkasan paket">
                                    <div class="package-highlight-item">
                                        <i class="bi bi-moon-stars"></i>
                                        <span>
                                            <small>Durasi</small>
                                            <strong><?= esc($durationLabel); ?></strong>
                                        </span>
                                    </div>

                                    <div class="package-highlight-item">
                                        <i class="bi bi-calendar2-event"></i>
                                        <span>
                                            <small>Keberangkatan terdekat</small>
                                            <strong><?= esc($departureLabel); ?></strong>
                                        </span>
                                    </div>

                                    <div class="package-highlight-item">
                                        <i class="bi bi-people"></i>
                                        <span>
                                            <small>Ketersediaan</small>
                                            <strong><?= esc($quotaLabel); ?></strong>
                                        </span>
                                    </div>
                                </div>

                                <div class="package-footer package-highlight-footer">
                                    <div class="package-price-box">
                                        <span>Mulai dari</span>
                                        <strong><?= esc($priceLabel); ?></strong>
                                    </div>

                                    <?php if (!empty($slug)) : ?>
                                        <a
                                            href="<?= base_url('/paket/' . $slug); ?>"
                                            class="package-detail-btn"
                                            aria-label="Lihat detail paket <?= esc($name); ?>">
                                            Lihat Detail
                                            <i class="bi bi-arrow-right"></i>
                                        </a>
                                    <?php else : ?>
                                        <button
                                            type="button"
                                            class="package-detail-btn"
                                            disabled>
                                            Lihat Detail
                                            <i class="bi bi-arrow-right"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>

                        </article>
                    </div>

                <?php endforeach; ?>

            </div>

            <div class="text-center mt-5">
                <a href="<?= base_url('/paket'); ?>" class="btn btn-package-outline">
                    Lihat Semua Paket
                    <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>

        <?php endif; ?>

    </div>
</section>
