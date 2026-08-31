# White-Label Travel Umroh & Haji Platform

Aplikasi **CodeIgniter 4.6.3** untuk website dan operasional travel umroh/haji dengan model **single-tenant per instance**: satu deployment dan satu database untuk satu vendor travel.

Branding, kontak, rekening, warna, konten halaman Tentang, kategori program paket, dan persona chatbot dapat dikonfigurasi per instance. Secret seperti Gemini API key, Midtrans key, SMTP credential, dan database credential tetap disimpan di `.env` dan **tidak boleh masuk Git**.

## Fitur Utama

- Website publik: landing page, paket, detail paket, galeri, testimoni, registrasi, status pendaftaran, pembayaran, invoice, dan chatbot.
- Akun jamaah: login/register, dashboard, pendaftaran paket, upload dokumen, pembayaran, invoice.
- Admin panel: dashboard, paket & jadwal keberangkatan, CRM leads, galeri, testimoni, FAQ, chatbot knowledge, Document Knowledge, notifikasi, recycle bin, activity log.
- Super Admin: manajemen admin dan Site Settings/white-label configuration.
- AI: Gemini + hybrid RAG dengan semantic similarity, lexical overlap, program-match bonus, dan source priority.
- Payment: Midtrans.
- Reporting code: PhpSpreadsheet tersedia di source; beberapa route laporan operasional lama saat ini diarahkan kembali ke dashboard.

Dokumentasi lengkap:

- [Fitur](docs/FEATURES.md)
- [Fungsi Modul](docs/FUNCTIONAL.md)
- [Arsitektur Teknis](docs/TECHNICAL.md)
- [Database](docs/DATABASE.md)
- [Instalasi / Running](docs/INSTALLATION.md)
- [Deployment Production](docs/DEPLOYMENT.md)
- [White-Label Guide](docs/WHITE-LABEL-GUIDE.md)
- [Upload ke GitHub](docs/GITHUB.md)
- [Security](SECURITY.md)

## Requirement Minimum

- PHP **8.1+**
- Composer **2.x**
- MySQL 8.x atau MariaDB yang kompatibel dengan `utf8mb4`
- PHP extensions: `intl`, `mbstring`, `mysqli`, `curl`, `openssl`, `fileinfo`, `gd`, `zip`, `dom`, `simplexml`, `xml`, `xmlreader`, `xmlwriter`, `libxml`, `iconv`, `ctype`, `zlib`
- Internet untuk Gemini API dan Midtrans

Cek requirement:

```bash
php scripts/check-requirements.php
```

## Fresh Install Singkat

```bash
git clone <repository-url> travelapps
cd travelapps
composer install
```

Copy environment:

**Windows PowerShell**

```powershell
Copy-Item .env.example .env
```

**Linux/macOS**

```bash
cp .env.example .env
```

Kemudian:

1. Isi database, SMTP, Midtrans, Gemini, dan `SEED_ADMIN_*` di `.env`.
2. Buat database kosong.
3. Import `database/schema.sql`.
4. Jalankan:

```bash
php spark migrate
php spark db:seed GenericClientSeeder
php spark cache:clear
php spark serve
```

Buka:

```text
http://localhost:8080
```

> `database/schema.sql` hanya berisi **struktur**, tanpa data jamaah, paket produksi, credential, atau knowledge base klien lama.

## Laragon

Taruh project, misalnya:

```text
C:\laragon\www\travelapps
```

Lalu:

```powershell
composer install
Copy-Item .env.example .env
php scripts/check-requirements.php
```

Setelah database dibuat dan `database/schema.sql` di-import:

```powershell
php spark migrate
php spark db:seed GenericClientSeeder
php spark cache:clear
php spark serve
```

`composer install` dijalankan **sebelum** aplikasi dipakai karena folder `vendor/` sengaja tidak disimpan di repository.

## Security Gate Sebelum Push/Handoff

```powershell
powershell -ExecutionPolicy Bypass -File .\audit-brand.ps1
```

atau:

```bash
bash audit-brand.sh
```

Pastikan `.env`, `vendor/`, runtime logs/session/cache, dokumen chatbot, dan upload vendor tidak ikut commit.

## Known Issue Sebelum Production

Dua route publik standalone saat ini menunjuk ke controller/view kosong dari codebase asli:

```text
/galeri
/testimoni
```

Section galeri dan testimoni pada landing page tetap berasal dari `Home` dan dapat tampil, tetapi halaman standalone tersebut perlu diimplementasikan atau route-nya diubah sebelum dianggap selesai. Detail ada di `docs/AUDIT.md`.

## Status Database

Source awal project tidak memiliki migration baseline untuk seluruh tabel operasional. Repository GitHub-ready ini karena itu menyertakan `database/schema.sql` sebagai **schema-only baseline**, lalu migration CI4 tetap dijalankan untuk perubahan versi berikutnya.

## License

Project membawa file `LICENSE` dari basis CodeIgniter yang digunakan. Tentukan kebijakan lisensi produk/aplikasi komersial kamu secara terpisah sebelum repository dibuat public.
