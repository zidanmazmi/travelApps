<?php
$siteName = site_setting('site_name', 'Travel Umroh & Haji');
$siteTagline = site_setting('site_tagline', 'Layanan perjalanan umroh dan haji terpercaya.');
$brandPrimary = trim(site_setting('brand_primary_color'));
$brandSecondary = trim(site_setting('brand_secondary_color'));
$resolvedMetaDescription = $metaDescription ?? ($siteName . ' - ' . $siteTagline);
?>
<meta charset="UTF-8">
<title><?= esc($title ?? ($siteName . ' - Travel Umroh dan Haji')); ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php if ($brandPrimary !== '') : ?>
<meta name="theme-color" content="<?= esc($brandPrimary); ?>">
<?php endif; ?>
<meta name="description" content="<?= esc($resolvedMetaDescription); ?>">

<link rel="icon" href="<?= esc(site_asset('favicon')); ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link
    href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=Manrope:wght@400;500;600;700;800&display=swap"
    rel="stylesheet"
>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="<?= base_url('assets/frontend/css/frontend.css'); ?>">
<link rel="stylesheet" href="<?= base_url('assets/frontend/css/brand.css'); ?>?v=5.0.0">
<link rel="stylesheet" href="<?= base_url('assets/frontend/css/chatbot.css'); ?>?v=3.0.0">

<?php if ($brandPrimary !== '' || $brandSecondary !== '') : ?>
<style>
:root {
<?php if ($brandPrimary !== '') : ?>
    --brand-primary: <?= esc($brandPrimary); ?>;
    --brand-primary-dark: color-mix(in srgb, var(--brand-primary) 72%, black);
<?php endif; ?>
<?php if ($brandSecondary !== '') : ?>
    --brand-secondary: <?= esc($brandSecondary); ?>;
    --brand-secondary-light: color-mix(in srgb, var(--brand-secondary) 62%, white);
    --brand-secondary-dark: color-mix(in srgb, var(--brand-secondary) 72%, black);
<?php endif; ?>
    --brand-gradient: linear-gradient(135deg, var(--brand-primary) 0%, var(--brand-secondary) 100%);
}
</style>
<?php endif; ?>
