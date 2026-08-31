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
    <title>Pendaftaran Jamaah Berhasil</title>
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
                            Pendaftaran Jamaah Berhasil
                        </p>
                    </td>
                </tr>

                <tr>
                    <td style="padding:30px;">

                        <p style="margin-top:0;">Assalamu’alaikum Wr. Wb.</p>

                        <p>
                            Yth. <strong><?= esc($registration['user_name'] ?? 'Jamaah'); ?></strong>,
                        </p>

                        <p>
                            Pendaftaran jamaah Anda telah berhasil kami terima. Berikut detail pendaftaran Anda:
                        </p>

                        <table width="100%" cellpadding="0" cellspacing="0" style="margin:22px 0; border-collapse:collapse;">
                            <tr>
                                <td style="padding:12px; background:#FAF6EF; border:1px solid #E8E2D9; width:38%;">No. Pendaftaran</td>
                                <td style="padding:12px; border:1px solid #E8E2D9;">
                                    <strong><?= esc($registration['registration_no'] ?? '-'); ?></strong>
                                </td>
                            </tr>

                            <tr>
                                <td style="padding:12px; background:#FAF6EF; border:1px solid #E8E2D9;">Paket</td>
                                <td style="padding:12px; border:1px solid #E8E2D9;">
                                    <?= esc($registration['package_name'] ?? '-'); ?>
                                </td>
                            </tr>

                            <tr>
                                <td style="padding:12px; background:#FAF6EF; border:1px solid #E8E2D9;">Jadwal Keberangkatan</td>
                                <td style="padding:12px; border:1px solid #E8E2D9;">
                                    <?php if (!empty($registration['departure_date'])) : ?>
                                        <?= date('d M Y', strtotime($registration['departure_date'])); ?>

                                        <?php if (!empty($registration['return_date'])) : ?>
                                            - <?= date('d M Y', strtotime($registration['return_date'])); ?>
                                        <?php endif; ?>
                                    <?php else : ?>
                                        -
                                    <?php endif; ?>
                                </td>
                            </tr>

                            <tr>
                                <td style="padding:12px; background:#FAF6EF; border:1px solid #E8E2D9;">Total Tagihan</td>
                                <td style="padding:12px; border:1px solid #E8E2D9;">
                                    <strong>
                                        Rp <?= number_format((float) ($registration['total_amount'] ?? 0), 0, ',', '.'); ?>
                                    </strong>
                                </td>
                            </tr>

                            <tr>
                                <td style="padding:12px; background:#FAF6EF; border:1px solid #E8E2D9;">Status Pendaftaran</td>
                                <td style="padding:12px; border:1px solid #E8E2D9;">
                                    <?= esc($registration['registration_status'] ?? '-'); ?>
                                </td>
                            </tr>

                            <tr>
                                <td style="padding:12px; background:#FAF6EF; border:1px solid #E8E2D9;">Status Pembayaran</td>
                                <td style="padding:12px; border:1px solid #E8E2D9;">
                                    <?= esc($registration['payment_status'] ?? '-'); ?>
                                </td>
                            </tr>
                        </table>

                        <p>
                            Silakan lanjutkan proses pembayaran dan upload dokumen melalui dashboard jamaah.
                        </p>

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