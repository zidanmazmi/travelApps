<?php

if (!function_exists('sendVerificationEmail')) {
    function sendVerificationEmail(string $toEmail, string $code): bool
    {
        $email = \Config\Services::email();

        $config = [
            'protocol'    => env('email.protocol', 'smtp'),
            'SMTPHost'    => env('email.SMTPHost'),
            'SMTPUser'    => env('email.SMTPUser'),
            'SMTPPass'    => env('email.SMTPPass'),
            'SMTPPort'    => (int) env('email.SMTPPort', 587),
            'SMTPCrypto'  => env('email.SMTPCrypto', 'tls'),
            'SMTPTimeout' => (int) env('email.SMTPTimeout', 30),
            'mailType'    => env('email.mailType', 'html'),
            'charset'     => env('email.charset', 'UTF-8'),
            'wordWrap'    => true,
        ];

        $email->initialize($config);

        $siteName = site_setting('site_name', 'Travel Umroh & Haji');
        $brandPrimary = site_setting('brand_primary_color', '#0D6EFD');

        $email->setFrom(
            env('email.fromEmail'),
            env('email.fromName', $siteName)
        );

        $email->setTo($toEmail);
        $email->setSubject('Kode Verifikasi Email - ' . $siteName);

        $message = '
            <div style="font-family: Arial, sans-serif; line-height: 1.6;">
                <h2>Verifikasi Email Jamaah</h2>
                <p>Assalamu’alaikum,</p>
                <p>Terima kasih telah melakukan registrasi akun jamaah di ' . esc($siteName) . '.</p>
                <p>Kode verifikasi Anda adalah:</p>
                <h1 style="letter-spacing: 4px; color: ' . esc($brandPrimary) . ';">' . esc($code) . '</h1>
                <p>Kode ini berlaku selama 15 menit. Jangan berikan kode ini kepada siapa pun.</p>
                <p>Hormat kami,<br>' . esc($siteName) . '</p>
            </div>
        ';

        $email->setMessage($message);

        if (!$email->send()) {
            log_message('error', $email->printDebugger(['headers']));
            return false;
        }

        return true;
    }
}