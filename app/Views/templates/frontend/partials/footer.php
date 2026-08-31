<?php
$siteName = site_setting('site_name', 'Travel Umroh & Haji');
$siteTagline = site_setting('site_tagline', 'Layanan perjalanan umroh dan haji terpercaya.');
$siteEmail = trim(site_setting('contact_email', site_setting('site_email', '')));
$sitePhone = trim(site_setting('contact_phone', site_setting('site_phone', '')));
$siteWa = trim(site_setting('social_whatsapp', site_setting('site_whatsapp', '')));
$siteAddress = trim(site_setting('contact_address', site_setting('site_address', '')));
$businessHours = trim(site_setting('business_hours', ''));
$instagramUrl = trim(site_setting('social_instagram', site_setting('instagram_url', '')));
$facebookUrl = trim(site_setting('social_facebook', site_setting('facebook_url', '')));
$youtubeUrl = trim(site_setting('youtube_url', ''));
$footerText = trim(site_setting('footer_text', ''));

$cleanWhatsapp = preg_replace('/[^0-9]/', '', $siteWa);
$cleanPhone = preg_replace('/[^0-9+]/', '', $sitePhone);
$mapEmbedUrl = $siteAddress !== '' ? 'https://www.google.com/maps?q=' . rawurlencode($siteAddress) . '&output=embed' : '';
$mapOpenUrl = $siteAddress !== '' ? 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($siteAddress) : '';
?>

<footer class="site-footer wl-footer">
    <div class="wl-footer-glow" aria-hidden="true"></div>
    <div class="container position-relative">
        <div class="footer-main">
            <div class="row g-5">
                <div class="col-lg-4">
                    <div class="footer-brand">
                        <div class="wl-footer-brandline">
                            <div class="footer-logo-wrap">
                                <img src="<?= esc(site_asset('logo')); ?>" alt="Logo <?= esc($siteName); ?>" class="footer-logo">
                            </div>
                            <div>
                                <strong><?= esc($siteName); ?></strong>
                                <span><?= esc($siteTagline); ?></span>
                            </div>
                        </div>

                        <?php if ($footerText !== '') : ?>
                            <p><?= esc($footerText); ?></p>
                        <?php endif; ?>

                        <div class="footer-social">
                            <?php if ($instagramUrl !== '') : ?><a href="<?= esc($instagramUrl); ?>" target="_blank" rel="noopener" aria-label="Instagram"><i class="bi bi-instagram"></i></a><?php endif; ?>
                            <?php if ($facebookUrl !== '') : ?><a href="<?= esc($facebookUrl); ?>" target="_blank" rel="noopener" aria-label="Facebook"><i class="bi bi-facebook"></i></a><?php endif; ?>
                            <?php if ($youtubeUrl !== '') : ?><a href="<?= esc($youtubeUrl); ?>" target="_blank" rel="noopener" aria-label="YouTube"><i class="bi bi-youtube"></i></a><?php endif; ?>
                            <?php if ($cleanWhatsapp !== '') : ?><a href="https://wa.me/<?= esc($cleanWhatsapp); ?>" target="_blank" rel="noopener" aria-label="WhatsApp"><i class="bi bi-whatsapp"></i></a><?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-lg-2">
                    <div class="footer-widget">
                        <h3>Navigasi</h3>
                        <ul>
                            <li><a href="<?= base_url('/paket'); ?>">Paket Umroh</a></li>
                            <li><a href="<?= base_url('/#layanan'); ?>">Layanan</a></li>
                            <li><a href="<?= base_url('/#testimoni'); ?>">Testimoni</a></li>
                            <li><a href="<?= base_url('/#galeri'); ?>">Galeri</a></li>
                        </ul>
                    </div>
                </div>

                <div class="col-6 col-lg-3">
                    <div class="footer-widget">
                        <h3>Layanan</h3>
                        <ul>
                            <li><a href="<?= base_url('/paket'); ?>">Umroh Reguler</a></li>
                            <li><a href="<?= base_url('/paket'); ?>">Umroh Plus</a></li>
                            <li><a href="<?= base_url('/paket'); ?>">Haji</a></li>
                            <li><a href="<?= base_url('/cek-status'); ?>">Cek Status Jamaah</a></li>
                        </ul>
                    </div>
                </div>

                <div class="col-lg-3">
                    <div class="footer-widget">
                        <h3>Kontak</h3>
                        <ul class="footer-contact">
                            <?php if ($siteAddress !== '') : ?><li><i class="bi bi-geo-alt"></i><span><?= nl2br(esc($siteAddress)); ?></span></li><?php endif; ?>
                            <?php if ($sitePhone !== '') : ?>
                                <li><i class="bi bi-telephone"></i><?php if ($cleanPhone !== '') : ?><a href="tel:<?= esc($cleanPhone); ?>"><?= esc($sitePhone); ?></a><?php else : ?><span><?= esc($sitePhone); ?></span><?php endif; ?></li>
                            <?php endif; ?>
                            <?php if ($siteEmail !== '') : ?><li><i class="bi bi-envelope"></i><a href="mailto:<?= esc($siteEmail); ?>"><?= esc($siteEmail); ?></a></li><?php endif; ?>
                            <?php if ($businessHours !== '') : ?><li><i class="bi bi-clock"></i><span><?= esc($businessHours); ?></span></li><?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($siteAddress !== '') : ?>
        <div class="footer-location-panel">
            <div class="row g-0 align-items-stretch">
                <div class="col-lg-7">
                    <div class="footer-map-wrap">
                        <iframe src="<?= esc($mapEmbedUrl); ?>" title="Lokasi kantor <?= esc($siteName); ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="footer-location-copy">
                        <span class="footer-location-eyebrow"><i class="bi bi-geo-alt-fill"></i> Lokasi Kantor</span>
                        <h3>Kunjungi <?= esc($siteName); ?></h3>
                        <p><?= esc($siteAddress); ?></p>
                        <div class="footer-location-meta">
                            <?php if ($businessHours !== '') : ?><div><i class="bi bi-clock"></i><span><?= esc($businessHours); ?></span></div><?php endif; ?>
                            <?php if ($sitePhone !== '') : ?><div><i class="bi bi-whatsapp"></i><span><?= esc($sitePhone); ?></span></div><?php endif; ?>
                        </div>
                        <a href="<?= esc($mapOpenUrl); ?>" target="_blank" rel="noopener" class="footer-map-link"><span>Buka di Google Maps</span><i class="bi bi-arrow-up-right"></i></a>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="footer-bottom">
            <p>© <?= date('Y'); ?> <?= esc($siteName); ?>. Seluruh hak cipta dilindungi.</p>
            <span>Platform layanan perjalanan ibadah.</span>
        </div>
    </div>
</footer>
