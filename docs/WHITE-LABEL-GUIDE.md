# White-Label Guide

## Konsep

Repository ini bukan multi-tenant SaaS. Setiap vendor menggunakan instance terpisah.

```text
Vendor A -> source template + .env A + DB A + uploads A
Vendor B -> source template + .env B + DB B + uploads B
```

Tidak boleh menyalin database atau `.env` antar klien.

## Site Settings

Login sebagai Super Admin lalu buka:

```text
/admin/settings
```

### Identitas

- Site Name
- Site Tagline
- Primary Color
- Secondary Color

### Asset

- Site Logo
- White Logo
- Favicon
- Email Header Logo

### Kontak

- Email
- Phone
- WhatsApp
- Address
- Business Hours

### Social

- Instagram
- Facebook
- YouTube

### Rekening

- Bank Name
- Account Number
- Account Name

### Section Tentang

- Kicker/label
- Title
- Description
- Image
- Note label/title
- Point 1 + description
- Point 2 + description
- Point 3 + description
- CTA text
- CTA note

### Paket

`package_program_options` menentukan pilihan program. Format:

```text
reguler:Reguler
plus:Plus
ramadhan:Ramadhan
haji-khusus:Haji Khusus
```

Vendor dapat mengganti daftar ini tanpa mengubah `PackageModel`.

### Chatbot

- Chatbot enabled
- Chatbot name
- Welcome message
- Fallback message

## Secret yang Tidak Masuk Site Settings

Tetap di `.env`:

- Database credential.
- Gemini API key.
- Midtrans server/client key.
- SMTP credential.
- Encryption key.

## Knowledge Base Per Vendor

Setelah branding selesai:

1. Buka `/admin/chatbot/sources`.
2. Upload flyer/booklet/itinerary vendor.
3. Review hasil extraction Gemini.
4. Koreksi jika diperlukan.
5. Publish.
6. Test pertanyaan dari frontend.

Jangan membawa source document/knowledge dari vendor lain.

## Runtime Upload

Upload vendor tidak masuk Git. Folder runtime sudah di-ignore.

Fallback logo generik untuk fresh clone berada di:

```text
public/assets/frontend/images/default-brand/
```

Ketika admin upload branding, setting akan menunjuk ke asset di:

```text
public/assets/uploads/branding/
```

## New Client Checklist

- [ ] Clone repository template.
- [ ] Buat `.env` baru.
- [ ] Buat database baru.
- [ ] Import schema + migrate.
- [ ] Seed Super Admin.
- [ ] Ganti password.
- [ ] Isi Site Settings.
- [ ] Upload logo/favicon.
- [ ] Isi rekening/contact/social.
- [ ] Setup Gemini.
- [ ] Setup Midtrans.
- [ ] Setup SMTP.
- [ ] Upload knowledge vendor.
- [ ] Test public/admin/jamaah flow.
- [ ] Run brand audit.
