# Security

## Secrets

Jangan commit:

- `.env`
- Gemini API keys
- Midtrans keys
- SMTP passwords
- database passwords
- encryption keys

Jika sebuah API key pernah masuk ZIP/chat/repository yang dibagikan, anggap key tersebut sudah terekspos dan lakukan **revoke/rotate**.

## Client Isolation

Setiap vendor harus menggunakan database, `.env`, upload, dan knowledge base terpisah.

## CSRF

Pada source saat ini filter global `csrf` belum aktif. Sebelum production, aktifkan CSRF setelah seluruh form/AJAX diuji dan definisikan exception yang diperlukan untuk webhook/callback eksternal seperti Midtrans. Jangan mengaktifkan secara blind tanpa regression test.

## Production

- Gunakan HTTPS.
- Document root harus `public/`.
- Jangan expose project root.
- Gunakan `CI_ENVIRONMENT=production`.
- Backup database sebelum migration.
- Ganti bootstrap Super Admin password segera.
- Batasi permission folder writable/upload hanya sesuai kebutuhan web server.

## Responsible Disclosure

Jika repository ini digunakan secara internal/private, tentukan kontak security internal sebelum diserahkan ke klien.
