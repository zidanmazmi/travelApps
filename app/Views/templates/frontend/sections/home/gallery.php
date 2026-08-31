<?php
$photos = array_values(array_filter($galleries ?? [], static fn(array $item): bool => ($item['media_type'] ?? 'image') !== 'video' && !empty($item['image'])));
$videos = array_values(array_filter($galleries ?? [], static fn(array $item): bool => ($item['media_type'] ?? 'image') === 'video' && !empty($item['video'])));
?>

<section id="galeri" class="section-gallery media-gallery-section">
    <div class="container">
        <div class="section-heading text-center" data-reveal="up">
            <span class="media-gallery-eyebrow">Cerita Perjalanan</span>
            <h2>Galeri & Reels Jamaah</h2>
            <div class="heading-line"></div>
            <p class="media-gallery-intro">Lihat momen perjalanan, pelayanan, dan kebersamaan jamaah bersama <?= esc(site_setting('site_name', 'Travel Umroh & Haji')); ?>.</p>
        </div>

        <?php if (empty($photos) && empty($videos)) : ?>
            <div class="text-center"><p>Galeri perjalanan belum tersedia.</p></div>
        <?php else : ?>

            <?php if (!empty($videos)) : ?>
                <div class="reels-section" data-reveal="up">
                    <div class="media-subheading">
                        <div>
                            <span><i class="bi bi-camera-reels"></i> Video Perjalanan</span>
                            <h3>Reels Travel</h3>
                        </div>
                        <div class="reels-nav-group">
                            <button type="button" class="reels-nav" data-reels-prev aria-label="Video sebelumnya"><i class="bi bi-arrow-left"></i></button>
                            <button type="button" class="reels-nav" data-reels-next aria-label="Video berikutnya"><i class="bi bi-arrow-right"></i></button>
                        </div>
                    </div>

                    <div class="travel-reels-track" data-reels-track>
                        <?php foreach ($videos as $index => $video) : ?>
                            <article class="travel-reel-card aspect-<?= esc($video['aspect_ratio'] ?? 'portrait'); ?>" data-reel-card>
                                <video
                                    class="travel-reel-video"
                                    muted
                                    loop
                                    playsinline
                                    preload="metadata"
                                    <?= !empty($video['thumbnail']) ? 'poster="' . esc(base_url($video['thumbnail'])) . '"' : ''; ?>
                                    aria-label="<?= esc($video['title'] ?? 'Video perjalanan jamaah'); ?>">
                                    <source src="<?= esc(base_url($video['video'])); ?>" type="<?= esc($video['mime_type'] ?? 'video/mp4'); ?>">
                                </video>

                                <div class="reel-shade"></div>
                                <div class="reel-topbar">
                                    <span class="reel-brand"><span class="reel-brand-dot"></span> <?= esc(site_setting('site_name', 'Travel Umroh & Haji')); ?></span>
                                    <button type="button" class="reel-control" data-reel-sound aria-label="Aktifkan suara"><i class="bi bi-volume-mute-fill"></i></button>
                                </div>

                                <button type="button" class="reel-play-toggle" data-reel-play aria-label="Putar atau jeda video"><i class="bi bi-play-fill"></i></button>

                                <div class="reel-caption">
                                    <?php if (!empty($video['title'])) : ?><h3><?= esc($video['title']); ?></h3><?php endif; ?>
                                    <?php if (!empty($video['description'])) : ?><p><?= esc($video['description']); ?></p><?php endif; ?>
                                    <?php if (!empty($video['duration_seconds'])) : ?>
                                        <small><i class="bi bi-clock"></i> <?= sprintf('%02d:%02d', intdiv((int) $video['duration_seconds'], 60), ((int) $video['duration_seconds']) % 60); ?></small>
                                    <?php endif; ?>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                    <p class="reels-swipe-hint"><i class="bi bi-hand-index-thumb"></i> Geser untuk melihat video lainnya</p>
                </div>
            <?php endif; ?>

            <?php if (!empty($photos)) : ?>
                <div class="photo-gallery-section <?= !empty($videos) ? 'has-reels-above' : ''; ?>" data-reveal="up">
                    <div class="media-subheading">
                        <div>
                            <span><i class="bi bi-images"></i> Dokumentasi Foto</span>
                            <h3>Momen Kebersamaan Jamaah</h3>
                        </div>
                    </div>

                    <div class="gallery-slider-wrapper">
                        <button type="button" class="gallery-nav gallery-nav-prev" data-gallery-prev aria-label="Geser galeri ke kiri"><i class="bi bi-chevron-left"></i></button>

                        <div class="gallery-slider" data-gallery-slider>
                            <?php foreach ($photos as $photo) : ?>
                                <div class="gallery-slide">
                                    <div class="gallery-card gallery-card-dynamic">
                                        <img src="<?= esc(base_url($photo['image'])); ?>" alt="<?= esc($photo['alt'] ?? $photo['title'] ?? 'Galeri perjalanan ibadah'); ?>" loading="lazy">
                                        <?php if (!empty($photo['title']) || !empty($photo['description'])) : ?>
                                            <div class="gallery-caption">
                                                <?php if (!empty($photo['title'])) : ?><h3><?= esc($photo['title']); ?></h3><?php endif; ?>
                                                <?php if (!empty($photo['description'])) : ?><p><?= esc($photo['description']); ?></p><?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <button type="button" class="gallery-nav gallery-nav-next" data-gallery-next aria-label="Geser galeri ke kanan"><i class="bi bi-chevron-right"></i></button>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>

<script src="<?= base_url('assets/frontend/js/gallery-reels.js'); ?>"></script>
