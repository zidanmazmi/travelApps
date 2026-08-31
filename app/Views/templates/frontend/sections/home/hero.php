<section class="site-hero wl-hero" data-parallax-root>
    <img
        src="<?= base_url('assets/frontend/images/mekah-hero-poster.jpg'); ?>"
        alt="Pemandangan Masjidil Haram di Mekah"
        class="wl-hero-fallback"
        aria-hidden="true">

    <video
        class="wl-hero-video"
        autoplay
        muted
        loop
        playsinline
        preload="metadata"
        poster="<?= base_url('assets/frontend/images/mekah-hero-poster.jpg'); ?>"
        aria-hidden="true">
        <source src="<?= base_url('assets/frontend/videos/mekah-hero.mp4'); ?>" type="video/mp4">
    </video>

    <div class="hero-overlay wl-hero-overlay"></div>
    <div class="wl-hero-noise" aria-hidden="true"></div>
    <div class="wl-morph wl-morph-one" aria-hidden="true"></div>
    <div class="wl-morph wl-morph-two" aria-hidden="true"></div>

    <div class="hero-content-wrap wl-hero-content-wrap">
        <div class="container">
            <div class="wl-hero-centered">
                <div class="wl-hero-copy wl-hero-copy--center" data-reveal="up">
                    <span class="wl-eyebrow justify-content-center">
                        <i class="bi bi-stars"></i>
                        <?= esc(site_setting('site_name', 'Travel Umroh & Haji')); ?>
                    </span>

                    <h1 class="hero-title wl-hero-title">
                        Perjalanan ibadah yang
                        <span>tenang, terarah, dan berkesan.</span>
                    </h1>

                    <p class="hero-subtitle wl-hero-subtitle">
                        <?= esc(site_setting('site_name', 'Travel Umroh & Haji')); ?> mendampingi perjalanan umroh dan haji Anda mulai dari pemilihan paket,
                        konsultasi, persiapan dokumen, hingga keberangkatan.
                    </p>

                    <div class="wl-hero-actions justify-content-center">
                        <a href="#paket" class="btn btn-brand-primary">
                            Lihat Paket
                            <i class="bi bi-arrow-up-right"></i>
                        </a>

                        <a href="#kontak" class="btn btn-brand-secondary">
                            Konsultasi Sekarang
                        </a>
                    </div>

                    <div class="wl-trust-row justify-content-center">
                        <span><i class="bi bi-shield-check"></i> Pendampingan jamaah</span>
                        <span><i class="bi bi-calendar2-check"></i> Jadwal transparan</span>
                        <span><i class="bi bi-headset"></i> Responsif</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <a href="#layanan" class="wl-scroll-cue" aria-label="Lihat bagian berikutnya">
        <span></span>
        Jelajahi
    </a>
</section>
