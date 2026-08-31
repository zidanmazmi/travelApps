# Production Deployment

## Prinsip

Satu deployment = satu vendor travel.

Setiap deployment harus mempunyai:

- `.env` sendiri.
- Database sendiri.
- Gemini key sendiri/terisolasi.
- Midtrans credential sesuai vendor/instance.
- SMTP credential sendiri.
- Upload dan knowledge base sendiri.

## Build Production

```bash
composer install --no-dev --optimize-autoloader
```

Set environment:

```ini
CI_ENVIRONMENT = production
app.baseURL = 'https://domain-klien.example/'
```

## Document Root

Web server harus mengarah ke:

```text
<project>/public
```

Jangan expose root project ke public web.

## Database

Fresh deployment:

1. Buat database.
2. Import `database/schema.sql`.
3. Jalankan `php spark migrate`.
4. Isi `SEED_ADMIN_*` sementara.
5. Jalankan `php spark db:seed GenericClientSeeder`.
6. Hapus/ubah password bootstrap setelah login pertama.

Existing deployment:

1. Backup database.
2. Deploy source.
3. `composer install --no-dev --optimize-autoloader`.
4. `php spark migrate`.
5. `php spark cache:clear`.

## Writable Permission

Web server membutuhkan write access ke:

```text
writable/cache
writable/logs
writable/session
writable/uploads
public/uploads
public/assets/uploads/branding
public/assets/uploads/gallery
public/assets/uploads/packages
public/assets/uploads/testimonials
```

Linux umum:

```bash
chmod -R 775 writable
chmod -R 775 public/uploads public/assets/uploads
```

Ownership harus disesuaikan dengan user PHP-FPM/Apache pada server.

## Production Checklist

- [ ] `CI_ENVIRONMENT=production`.
- [ ] HTTPS aktif.
- [ ] CSRF production hardening sudah diuji dan diaktifkan dengan exception webhook yang diperlukan.
- [ ] `app.baseURL` benar.
- [ ] `.env` tidak berada di repository/public folder.
- [ ] Database production terisolasi.
- [ ] Gemini API key production terpasang.
- [ ] Midtrans mode dan credential benar.
- [ ] Callback Midtrans dapat diakses publik via HTTPS.
- [ ] SMTP test berhasil.
- [ ] Site Settings sudah diisi.
- [ ] Branding vendor sudah di-upload.
- [ ] Knowledge base vendor sudah ditinjau dan dipublish.
- [ ] Default/bootstrap admin password sudah diganti.
- [ ] `php spark cache:clear` selesai.
- [ ] `audit-brand` PASS.
- [ ] Tidak ada log/session/upload lokal yang ikut deployment package.

## Handoff

Sebelum serah terima vendor:

```bash
bash audit-brand.sh
```

atau Windows:

```powershell
powershell -ExecutionPolicy Bypass -File .\audit-brand.ps1
```
