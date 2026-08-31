<?php
$aboutSiteName = site_setting('site_name', 'Lorem Ipsum');
$aboutKicker = site_setting('about_kicker', 'Lorem Ipsum');
$aboutTitle = site_setting('about_title', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.');
$aboutDescription = site_setting('about_description', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.');
$aboutImage = trim(site_setting('about_image', ''));
$aboutNoteLabel = site_setting('about_note_label', 'Lorem Ipsum');
$aboutNoteTitle = site_setting('about_note_title', 'Lorem ipsum dolor sit amet');
$aboutCtaText = site_setting('about_cta_text', 'Lorem Ipsum');
$aboutCtaNote = site_setting('about_cta_note', 'Lorem ipsum dolor sit amet');

$aboutPoints = [
    [
        'icon' => 'bi-signpost-split',
        'title' => site_setting('about_point_1_title', 'Lorem Ipsum'),
        'description' => site_setting('about_point_1_description', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.'),
    ],
    [
        'icon' => 'bi-calendar2-check',
        'title' => site_setting('about_point_2_title', 'Dolor Sit Amet'),
        'description' => site_setting('about_point_2_description', 'Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.'),
    ],
    [
        'icon' => 'bi-people',
        'title' => site_setting('about_point_3_title', 'Consectetur'),
        'description' => site_setting('about_point_3_description', 'Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris.'),
    ],
];

$aboutWords = preg_split('/\s+/u', trim($aboutSiteName)) ?: [];
$aboutInitials = '';
foreach (array_slice($aboutWords, 0, 2) as $word) {
    $aboutInitials .= mb_strtoupper(mb_substr($word, 0, 1));
}
?>

<section id="tentang" class="section-about wl-section wl-about">
    <div class="container">
        <div class="row align-items-center g-5">

            <div class="col-lg-6 col-xl-6">
                <div class="about-image-wrap wl-about-visual" data-reveal="left" data-tilt>
                    <div class="about-image">
                        <?php if ($aboutImage !== '') : ?>
                            <img
                                src="<?= base_url(ltrim($aboutImage, '/')); ?>"
                                alt="<?= esc($aboutKicker); ?>"
                                loading="lazy">
                        <?php else : ?>
                            <div class="wl-about-placeholder" aria-label="Lorem Ipsum">
                                <i class="bi bi-image"></i>
                                <span>Lorem Ipsum</span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="wl-about-image-note">
                        <span><i class="bi bi-heart-pulse"></i></span>
                        <div>
                            <small><?= esc($aboutNoteLabel); ?></small>
                            <strong><?= esc($aboutNoteTitle); ?></strong>
                        </div>
                    </div>

                    <div class="about-badge wl-about-badge" aria-label="<?= esc($aboutSiteName); ?>">
                        <strong><?= esc($aboutInitials !== '' ? $aboutInitials : 'LI'); ?></strong>
                        <span><?= esc($aboutSiteName); ?></span>
                    </div>

                    <div class="wl-about-line" aria-hidden="true"></div>
                </div>
            </div>

            <div class="col-lg-6 col-xl-6">
                <div class="about-content" data-reveal="right">
                    <span class="about-label wl-section-kicker"><?= esc($aboutKicker); ?></span>

                    <h2><?= esc($aboutTitle); ?></h2>

                    <p class="wl-about-intro">
                        <?= esc($aboutDescription); ?>
                    </p>

                    <div class="wl-about-points">
                        <?php foreach ($aboutPoints as $point) : ?>
                            <article class="wl-about-point">
                                <span class="wl-about-point-icon"><i class="bi <?= esc($point['icon']); ?>"></i></span>
                                <div>
                                    <strong><?= esc($point['title']); ?></strong>
                                    <p><?= esc($point['description']); ?></p>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>

                    <div class="wl-about-actions">
                        <a href="#kontak" class="btn btn-brand-primary">
                            <?= esc($aboutCtaText); ?>
                            <i class="bi bi-arrow-right"></i>
                        </a>
                        <span><i class="bi bi-chat-dots"></i> <?= esc($aboutCtaNote); ?></span>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>
