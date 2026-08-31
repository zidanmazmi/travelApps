<?php
$siteEmail = trim(site_setting('contact_email', site_setting('site_email', '')));
$sitePhone = trim(site_setting('contact_phone', site_setting('site_phone', '')));
$siteWa = trim(site_setting('social_whatsapp', site_setting('site_whatsapp', '')));
$siteAddress = trim(site_setting('contact_address', site_setting('site_address', '')));
$cleanWhatsapp = preg_replace('/[^0-9]/', '', $siteWa);
$cleanPhone = preg_replace('/[^0-9+]/', '', $sitePhone);
?>
<div class="contact-info-card">
    <?php if ($siteEmail !== '') : ?>
    <div class="contact-info-item">
        <div class="contact-icon"><i class="bi bi-envelope"></i></div>
        <div><span>Email</span><strong><a href="mailto:<?= esc($siteEmail); ?>"><?= esc($siteEmail); ?></a></strong></div>
    </div>
    <?php endif; ?>

    <?php if ($sitePhone !== '') : ?>
    <div class="contact-info-item">
        <div class="contact-icon"><i class="bi bi-telephone"></i></div>
        <div><span>Telepon</span><strong><a href="tel:<?= esc($cleanPhone); ?>"><?= esc($sitePhone); ?></a></strong></div>
    </div>
    <?php endif; ?>

    <?php if ($cleanWhatsapp !== '') : ?>
    <div class="contact-info-item">
        <div class="contact-icon"><i class="bi bi-whatsapp"></i></div>
        <div><span>WhatsApp</span><strong><a href="https://wa.me/<?= esc($cleanWhatsapp); ?>" target="_blank" rel="noopener"><?= esc($siteWa); ?></a></strong></div>
    </div>
    <?php endif; ?>

    <?php if ($siteAddress !== '') : ?>
    <div class="contact-info-item">
        <div class="contact-icon"><i class="bi bi-geo-alt"></i></div>
        <div><span>Alamat</span><strong><?= esc($siteAddress); ?></strong></div>
    </div>
    <?php endif; ?>

    <?php if ($siteEmail === '' && $sitePhone === '' && $cleanWhatsapp === '' && $siteAddress === '') : ?>
        <p class="mb-0 text-muted">Informasi kontak belum dikonfigurasi.</p>
    <?php endif; ?>
</div>
