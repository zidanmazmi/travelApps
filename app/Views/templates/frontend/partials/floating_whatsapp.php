<?php
$siteName = site_setting('site_name', 'Travel Umroh & Haji');
$siteWa   = site_setting('social_whatsapp', site_setting('site_whatsapp', ''));

$cleanWhatsapp = preg_replace('/[^0-9]/', '', $siteWa);

if (str_starts_with($cleanWhatsapp, '0')) {
    $cleanWhatsapp = '62' . substr($cleanWhatsapp, 1);
}

$waMessage = urlencode(
    'Assalamualaikum, saya ingin konsultasi mengenai paket umrah/haji di ' . $siteName . '.'
);
?>

<?php if (!empty($cleanWhatsapp)) : ?>
    <a
        href="https://wa.me/<?= esc($cleanWhatsapp); ?>?text=<?= esc($waMessage); ?>"
        target="_blank"
        rel="noopener"
        class="floating-whatsapp"
        aria-label="Konsultasi WhatsApp <?= esc($siteName); ?>">

        <div class="floating-whatsapp-icon">
            <i class="bi bi-whatsapp"></i>
        </div>

        <div class="floating-whatsapp-text">
            <span>Butuh Bantuan?</span>
            <strong>Konsultasi WhatsApp</strong>
        </div>
    </a>
<?php endif; ?>