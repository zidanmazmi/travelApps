# Installation & Running Guide

## Urutan yang Benar

Untuk repository tanpa `vendor/`, urutan normal adalah:

```text
1. clone / extract source
2. composer install
3. copy .env.example -> .env
4. isi environment
5. buat database
6. import database/schema.sql (fresh install saja)
7. php spark migrate
8. php spark db:seed GenericClientSeeder (fresh install saja)
9. php spark cache:clear
10. php spark serve
```

Jadi **Composer terlebih dahulu**, baru menjalankan aplikasi secara lengkap.

## 1. Requirement

### PHP

Minimum:

```text
PHP 8.1
```

Recommended untuk development saat ini:

```text
PHP 8.2 / 8.3
```

Required extensions berdasarkan `composer.json` dan dependency yang dipakai:

```text
intl
mbstring
mysqli
curl
openssl
json
fileinfo
gd
zip
dom
simplexml
xml
xmlreader
xmlwriter
libxml
iconv
ctype
zlib
```

Cek otomatis:

```bash
php scripts/check-requirements.php
```

### Tool

- Composer 2.x
- MySQL/MariaDB
- Git (jika clone dari GitHub)
- Apache/Nginx atau `php spark serve`

## 2. Clone

```bash
git clone <repository-url> travelapps
cd travelapps
```

Jika memakai ZIP, extract hingga `spark`, `composer.json`, `app/`, `public/`, dan `system/` berada langsung di root project.

## 3. Install Dependency

```bash
composer install
```

Untuk development, gunakan command di atas agar `require-dev` ikut terpasang.

## 4. Environment

Windows:

```powershell
Copy-Item .env.example .env
```

Linux/macOS:

```bash
cp .env.example .env
```

Edit `.env`.

Minimal untuk aplikasi bisa boot dengan database:

```ini
CI_ENVIRONMENT = development
app.baseURL = 'http://localhost:8080/'

database.default.hostname = localhost
database.default.database = travel_white_label
database.default.username = root
database.default.password =
database.default.DBDriver = MySQLi
database.default.port = 3306
```

## 5. Fresh Database

Buat database kemudian import:

```text
database/schema.sql
```

Jangan import schema ini pada existing production database.

## 6. Migration

```bash
php spark migrate
```

Cek status:

```bash
php spark migrate:status
```

## 7. Bootstrap Super Admin

Isi di `.env`:

```ini
SEED_ADMIN_NAME = 'Super Admin'
SEED_ADMIN_EMAIL = 'admin@example.com'
SEED_ADMIN_PASSWORD = 'GantiDenganPasswordKuat123!'
```

Lalu:

```bash
php spark db:seed GenericClientSeeder
```

Segera ganti password bootstrap setelah login pertama.

## 8. Cache

```bash
php spark cache:clear
```

## 9. Run Development

```bash
php spark serve
```

Default:

```text
http://localhost:8080
```

## 10. Laragon

Contoh:

```text
C:\laragon\www\travelapps
```

Command:

```powershell
cd C:\laragon\www\travelapps
composer install
Copy-Item .env.example .env
php scripts/check-requirements.php
```

Buat/import database, lalu:

```powershell
php spark migrate
php spark db:seed GenericClientSeeder
php spark cache:clear
php spark serve
```

Jika memakai Apache/Auto Virtual Host Laragon, kamu dapat mengakses domain `.test` dan tidak wajib memakai `php spark serve`; pastikan document root mengarah ke folder `public/` untuk production-like setup.

## 11. Gemini

Isi:

```ini
CHATBOT_AI_ENABLED = true
GEMINI_API_KEY = '...'
```

Jangan commit key.

Document Knowledge menerima JPG/JPEG/PNG/WebP/PDF, maksimal 10 file per request dengan batas default 50 MiB/file.

## 12. Midtrans

Isi:

```ini
midtrans.serverKey = '...'
midtrans.clientKey = '...'
midtrans.isProduction = false
midtrans.isSanitized = true
midtrans.is3ds = true
```

Untuk development gunakan credential sandbox.

## 13. SMTP

Isi konfigurasi SMTP sesuai provider. Untuk Gmail gunakan App Password/OAuth sesuai kebijakan akun; jangan commit credential.

## Existing Database

Jika source ini dipasang di instance yang database-nya sudah ada:

```bash
composer install
php spark migrate
php spark cache:clear
```

**Jangan** import `database/schema.sql` dan **jangan** menjalankan GenericClientSeeder secara sembarangan pada database produksi.
