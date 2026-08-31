# Features

## 1. Website Publik

- Landing page white-label.
- Hero, layanan, paket, galeri, testimoni, FAQ, Tentang, CTA, dan footer.
- Daftar paket aktif dan detail paket berdasarkan slug.
- Informasi program paket yang dapat dikonfigurasi melalui Site Settings.
- Section galeri media pada landing page.
- Section testimoni pada landing page.
- Chatbot website.
- CTA WhatsApp / lead capture.
- Registrasi akun jamaah.
- Verifikasi email.
- Pendaftaran paket.
- Cek status pendaftaran.
- Pembayaran Midtrans.
- Invoice / bukti pembayaran.

## 2. Jamaah

- Login/logout.
- Dashboard jamaah.
- Pendaftaran paket dan jadwal keberangkatan.
- Upload dokumen jamaah.
- Format dokumen: JPG/JPEG/PNG/WebP/PDF.
- Maksimal dokumen jamaah: 3 MB per file sesuai controller saat ini.
- Melihat status pendaftaran/pembayaran.
- Mengakses invoice.

## 3. Admin Panel

### Dashboard

- Ringkasan operasional.
- Akses cepat ke modul aktif.

### Paket & Jadwal

- Tambah/edit/hapus paket.
- Soft delete + trash + restore + force delete.
- Cover paket maksimal 2 MB.
- Kategori/program paket dinamis dari Site Settings.
- Kelola jadwal keberangkatan.

### CRM / Reporting Leads

- Daftar lead.
- Detail lead dan activity.
- Update status lead.
- Bulk delete.
- Trash/restore/permanent delete.
- Export CSV.

### Konten Website

- Section galeri media pada landing page.
- Section testimoni pada landing page.
- FAQ.
- Soft delete/trash pada modul konten terkait.

### Chatbot Website

- Lihat percakapan.
- Lihat pertanyaan yang belum terjawab.
- Resolve / ignore unanswered questions.
- Tambah/edit/toggle/delete knowledge manual.
- Rebuild embedding.

### Document Knowledge

- Upload JPG/JPEG/PNG/WebP/PDF.
- Maksimal 10 file per proses upload.
- Batas file mengikuti `GEMINI_MAX_DOCUMENT_BYTES`, default 50 MB.
- Hubungkan source ke paket tertentu atau informasi umum.
- Tentukan document type, validity period, priority, title, dan admin notes.
- Gemini mengekstrak dokumen menjadi draft knowledge.
- Admin review hasil ekstraksi.
- Publish / unpublish sebelum knowledge dipakai chatbot.
- Reprocess source apabila diperlukan.

### Notifikasi

- Notification Center.
- Read single / read all.
- Bulk action dan delete.

### System

- Activity log.
- Recycle bin.
- Manajemen user admin.

## 4. Super Admin

Super Admin menggunakan admin panel yang sama, dengan akses tambahan:

- Site Settings.
- Manajemen akun admin.
- Konfigurasi branding dan instance.

## 5. White-Label

Dapat dikonfigurasi tanpa mengubah source untuk:

- Site name dan tagline.
- Primary / secondary brand color.
- Logo utama.
- Logo putih.
- Favicon.
- Email header logo.
- Email, telepon, WhatsApp, alamat, jam operasional.
- Facebook, Instagram, YouTube.
- Rekening bank.
- Footer text.
- Section Tentang: kicker, title, description, image, note, 3 poin, CTA.
- Program/kategori paket.
- Chatbot name, welcome message, fallback message.

## 6. Integrasi

- Gemini API.
- Gemini embedding.
- Midtrans.
- SMTP email.
- PhpSpreadsheet.

## 7. Known Public Route Issue

Route `/galeri` dan `/testimoni` ada di `Routes.php`, tetapi `Frontend\GalleryController`, `Frontend\TestimonialController`, serta view standalone terkait masih berupa stub kosong pada source yang diterima. Section galeri/testimoni di landing page tetap dapat berjalan lewat `Frontend\Home`.

## 8. Catatan Fitur Legacy

Route admin berikut pada `Routes.php` saat ini **tidak menjadi modul operasional aktif** dan diarahkan ke `/admin/dashboard`:

- `/admin/registrations`
- `/admin/payments`
- `/admin/documents`
- `/admin/jamaah`
- `/admin/reports`

Controller/view legacy masih ada di source untuk kompatibilitas/riwayat refactor. Jangan mendokumentasikannya sebagai menu aktif sampai route tersebut diaktifkan kembali secara eksplisit.
