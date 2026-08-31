# Technical Documentation

## Stack

- PHP 8.1+
- CodeIgniter 4.6.3
- MySQL / MariaDB
- Bootstrap 5 + Bootstrap Icons
- Gemini API
- Midtrans PHP SDK
- PhpSpreadsheet

## Project Layout

```text
app/
  Config/
  Controllers/
    admin/
    frontend/
    SuperAdmin/
  Database/
    Migrations/
    Seeds/
  Helpers/
  Models/
  Services/
  Views/
public/
  assets/
  uploads/
writable/
  cache/
  logs/
  session/
  uploads/chatbot_sources/
system/
tests/
database/schema.sql
```

Project ini membawa folder `system/` CodeIgniter secara lokal. Dependency pihak ketiga tetap di-install ke `vendor/` melalui Composer.

## Authentication / Roles

Role utama:

- `jamaah`
- `admin`
- `super_admin`

Admin routes menggunakan filter `adminAuth`. Filter memastikan user sudah login sebagai admin dan role hanya `admin` atau `super_admin`.

Beberapa operasi seperti Site Settings melakukan pemeriksaan `super_admin` kembali di controller.

## White-Label Settings

Model utama:

```text
app/Models/SiteSettingModel.php
```

Helper:

```text
app/Helpers/setting_helper.php
```

API view/helper:

```php
site_setting($key, $default)
site_asset($key)
```

`site_setting()` menggunakan cache `site_settings_cache` selama 3600 detik. Update settings menghapus cache agar nilai baru langsung terbaca.

Fallback asset generik berada di:

```text
public/assets/frontend/images/default-brand/
```

Asset upload vendor berada di:

```text
public/assets/uploads/branding/
```

dan sengaja di-ignore oleh Git.

## Gemini

Konfigurasi:

```text
app/Config/Gemini.php
```

Service:

```text
app/Services/GeminiService.php
```

Environment default:

```text
GEMINI_CHAT_MODEL=gemini-3.5-flash-lite
GEMINI_DOCUMENT_MODEL=gemini-3.5-flash
GEMINI_EMBEDDING_MODEL=gemini-embedding-001
GEMINI_EMBEDDING_DIMENSIONS=768
```

`GeminiService::isConfigured()` tidak memaksakan prefix/regex API key tertentu; ia memastikan key terisi dan bukan placeholder, lalu validitas credential ditentukan oleh API Gemini.

## Hybrid RAG

Service:

```text
app/Services/ChatbotAIService.php
```

Candidate retrieval saat ini:

```text
maksimal candidate = 500 row
```

Formula score yang dipakai source saat ini:

```text
score = (semantic * 0.62)
      + (lexical  * 0.33)
      + programScore
      + priority
```

Program match dapat memberi:

```text
+0.35
```

Setelah scoring, hasil teratas digunakan sebagai grounding context. Logic scoring ini merupakan business logic inti dan tidak boleh diubah saat melakukan white-labeling biasa.

## Document Knowledge

Controller:

```text
app/Controllers/admin/ChatbotSourceController.php
```

Allowed extensions:

```text
jpg, jpeg, png, webp, pdf
```

Allowed MIME:

```text
image/jpeg
image/png
image/webp
application/pdf
```

Maksimal 10 file per upload. Size maksimum mengikuti `GEMINI_MAX_DOCUMENT_BYTES` dengan default 52,428,800 bytes (50 MiB).

Storage:

```text
writable/uploads/chatbot_sources/
```

Folder ini tidak masuk Git.

## Midtrans

Credential dibaca dari `.env`:

```text
midtrans.serverKey
midtrans.clientKey
midtrans.isProduction
midtrans.isSanitized
midtrans.is3ds
```

Callback:

```text
POST /midtrans/notification
```

Credential tidak boleh disimpan di `site_settings`.

## Database

Project menggunakan 27 tabel aplikasi. Source migration historis tidak mencakup seluruh baseline schema, sehingga `database/schema.sql` digunakan untuk membuat struktur dasar pada fresh install. Setelah import, tetap jalankan migration CI4 untuk menerapkan perubahan versi berikutnya.

## Upload Separation

Runtime data yang tidak boleh masuk repository:

- Dokumen chatbot.
- Gallery uploads.
- Package uploads.
- Testimonial uploads.
- Branding vendor upload.
- Logs.
- Sessions.
- Cache/debugbar.

`.gitignore` pada repository ini sudah disiapkan untuk itu.

## Known Technical Debt

1. Migration baseline penuh belum ditulis sebagai migration CI4 per tabel; fresh install memakai `database/schema.sql` + migrations.
2. Source masih menyimpan beberapa controller/view legacy yang route-nya sudah dialihkan ke dashboard.
3. Automated tests yang ada sebagian besar merupakan test skeleton/framework; coverage business flow belum lengkap.
4. Route standalone `/galeri` dan `/testimoni` masih menunjuk controller/view stub kosong dari codebase asli.
5. Hybrid retrieval melakukan scoring manual di PHP atas maksimal 500 candidate; jika knowledge base tumbuh sangat besar, vector store dapat dipertimbangkan di fase lain.
