<?php
$siteName = site_setting('site_name', 'Travel Umroh & Haji');
$brandPrimary = site_setting('brand_primary_color', '#0D6EFD');
$brandSecondary = site_setting('brand_secondary_color', '#6C757D');
$emailLogo = site_asset('email_header_logo');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Status Dokumen Jamaah</title>
</head>
<body style="margin:0; padding:0; background:#FAF6EF; font-family:Arial, sans-serif; color:#1A1A1A;">

    <table width="100%" cellpadding="0" cellspacing="0" style="background:#FAF6EF; padding:30px 0;">
        <tr>
            <td align="center">

                <table width="620" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:16px; overflow:hidden; border:1px solid #E8E2D9;">
                    
                    <tr>
                        <td style="background:<?= esc($brandPrimary); ?>; padding:26px 30px; color:#ffffff;">
                            <img src="<?= esc($emailLogo); ?>" alt="<?= esc($siteName); ?>" style="max-width:180px; max-height:64px; margin-bottom:12px;"><h2 style="margin:0; font-size:24px;"><?= esc($siteName); ?></h2>
                            <p style="margin:8px 0 0; color:<?= esc($brandSecondary); ?>;">
                                Informasi Status Dokumen Jamaah
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:30px;">

                            <p style="margin-top:0;">Assalamu’alaikum Wr. Wb.</p>

                            <p>
                                Yth. <strong><?= esc($document['user_name'] ?? $document['pilgrim_name'] ?? 'Jamaah'); ?></strong>,
                            </p>

                            <p>
                                Kami ingin menginformasikan bahwa dokumen jamaah Anda telah diperiksa oleh admin <?= esc($siteName); ?>.
                            </p>

                            <table width="100%" cellpadding="0" cellspacing="0" style="margin:22px 0; border-collapse:collapse;">
                                <tr>
                                    <td style="padding:12px; background:#FAF6EF; border:1px solid #E8E2D9; width:38%;">No. Pendaftaran</td>
                                    <td style="padding:12px; border:1px solid #E8E2D9;">
                                        <strong><?= esc($document['registration_no'] ?? '-'); ?></strong>
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding:12px; background:#FAF6EF; border:1px solid #E8E2D9;">Paket</td>
                                    <td style="padding:12px; border:1px solid #E8E2D9;">
                                        <?= esc($document['package_name'] ?? '-'); ?>
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding:12px; background:#FAF6EF; border:1px solid #E8E2D9;">Jenis Dokumen</td>
                                    <td style="padding:12px; border:1px solid #E8E2D9;">
                                        <?= esc($document['document_type'] ?? '-'); ?>
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding:12px; background:#FAF6EF; border:1px solid #E8E2D9;">Status</td>
                                    <td style="padding:12px; border:1px solid #E8E2D9;">
                                        <strong style="color:<?= $status === 'Diterima' ? '#0D3B2E' : '#B42318'; ?>;">
                                            <?= esc($status); ?>
                                        </strong>
                                    </td>
                                </tr>
                            </table>

                            <?php if (!empty($note)) : ?>
                                <div style="padding:16px; background:#FAF6EF; border-left:4px solid #C9A04A; margin-bottom:22px;">
                                    <strong>Catatan Admin:</strong><br>
                                    <?= nl2br(esc($note)); ?>
                                </div>
                            <?php endif; ?>

                            <?php if ($status === 'Ditolak') : ?>
                                <p>
                                    Silakan login ke dashboard jamaah dan upload ulang dokumen sesuai catatan admin.
                                </p>
                            <?php else : ?>
                                <p>
                                    Dokumen Anda telah diterima. Silakan pantau status pendaftaran Anda melalui dashboard jamaah.
                                </p>
                            <?php endif; ?>

                            <p style="margin-bottom:0;">
                                Wassalamu’alaikum Wr. Wb.<br>
                                <strong><?= esc($siteName); ?></strong>
                            </p>

                        </td>
                    </tr>

                    <tr>
                        <td style="background:#FAF6EF; padding:18px 30px; text-align:center; color:#777; font-size:12px;">
                            Email ini dikirim otomatis oleh sistem <?= esc($siteName); ?>.
                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>

</body>
</html>