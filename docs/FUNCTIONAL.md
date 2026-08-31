# Functional Documentation

## Tujuan Aplikasi

Platform ini menggabungkan website marketing travel, alur jamaah, pengelolaan paket, CRM leads, pembayaran, dokumen, serta chatbot AI dalam satu instance CodeIgniter 4 yang dapat di-brand ulang untuk vendor travel berbeda.

Arsitektur bisnisnya adalah **single-tenant per instance**. Setiap vendor mempunyai database, `.env`, API key, branding, dan knowledge base sendiri.

## Public Website

### Home

Menampilkan landing page dan mengambil data paket, galeri, testimoni, serta Site Settings untuk membangun tampilan per vendor.

### Paket

Menampilkan paket aktif. Detail paket diakses melalui slug. Paket berisi nama, program, deskripsi, airline, hotel Makkah/Madinah, fasilitas, harga, durasi, cover image, dan status.

### Lead Capture

Calon jamaah yang tertarik dapat diarahkan ke WhatsApp dan dicatat sebagai lead untuk follow-up admin.

### Authentication

Satu flow login dipakai untuk membedakan jamaah dan user admin. Role admin yang valid adalah `admin` dan `super_admin`.

### Registrasi Jamaah

Jamaah memilih paket/jadwal, melakukan pendaftaran, kemudian melanjutkan ke dokumen dan pembayaran sesuai flow aplikasi.

### Status

Memungkinkan pengecekan status pendaftaran melalui route publik `/cek-status`.

### Payment

Menampilkan pembayaran dan berkomunikasi dengan Midtrans. Endpoint callback tersedia pada `/midtrans/notification`.

### Invoice

Menyediakan tampilan invoice/bukti pembayaran berdasarkan identifier registrasi/order yang digunakan aplikasi.

## Admin

### Dashboard

Menjadi pusat navigasi operasional dan ringkasan data.

### Paket & Departure

Mengelola paket serta jadwal keberangkatan. Delete bersifat soft delete untuk beberapa modul sehingga data dapat dipulihkan dari trash.

### CRM Leads

Mencatat calon jamaah, aktivitas follow-up, status lead, export CSV, trash, restore, dan permanent delete.

### Website Content

Admin dapat mengelola galeri, testimoni, dan FAQ yang tampil di frontend.

### Notification Center

Menyediakan notifikasi operasional untuk admin.

### Admin User Management

Super Admin dapat membuat, mengubah, mengaktifkan/nonaktifkan, mengganti password, menghapus, restore, atau permanent delete akun admin.

### Site Settings

Hanya Super Admin yang dapat membuka dan menyimpan pengaturan website. Controller melakukan validasi upload asset dan menyimpan konfigurasi key-value melalui `SiteSettingModel`.

Asset upload branding disimpan di:

```text
public/assets/uploads/branding/
```

Nilai setting dibaca terpusat melalui:

```php
site_setting('site_name')
site_asset('logo')
```

## Chatbot

### Chatbot Manual Knowledge

Admin dapat menambahkan question/keywords/answer manual, mengaktifkan/nonaktifkan knowledge, menyelesaikan unanswered question, dan rebuild embedding.

### Document Knowledge

Flow:

```text
Upload dokumen
    -> validasi file
    -> simpan source ke writable/uploads/chatbot_sources
    -> Gemini extraction
    -> draft/review
    -> admin review/edit
    -> publish
    -> knowledge aktif untuk retrieval
```

Source dapat dikaitkan dengan paket dan memiliki validity period serta priority agar informasi yang sudah kedaluwarsa tidak diprioritaskan.

### Retrieval

`ChatbotAIService` memuat maksimal 500 knowledge candidate, lalu menghitung hybrid score. Detail formula ada di `TECHNICAL.md`.

## Email

Template email yang tersedia:

- Registration success.
- Payment success.
- Document status.

Template mengambil site name, warna brand, dan email logo dari white-label settings.

## Reporting

Source `ReportController` dan dependency PhpSpreadsheet tersedia. Namun route `/admin/reports` pada konfigurasi sekarang diarahkan kembali ke dashboard sehingga reporting XLSX lama bukan menu aktif pada build ini.
