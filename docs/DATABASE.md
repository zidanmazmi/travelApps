# Database

## Base Schema

Fresh install menggunakan:

```text
database/schema.sql
```

File ini hanya berisi struktur tabel dan index/foreign key. Tidak ada data jamaah, paket produksi, knowledge base, credential, atau akun admin dari instance sebelumnya.

Setelah schema di-import:

```bash
php spark migrate
```

Kemudian seed setting generik + Super Admin:

```bash
php spark db:seed GenericClientSeeder
```

Seeder membutuhkan:

```text
SEED_ADMIN_NAME
SEED_ADMIN_EMAIL
SEED_ADMIN_PASSWORD
```

Password bootstrap minimal 12 karakter.

## 27 Tabel Aplikasi

### Authentication & User

- `users`
- `email_verifications`

### Package / Booking

- `packages`
- `package_departures`
- `package_facilities`
- `package_itineraries`
- `registrations`
- `pilgrims`
- `pilgrim_documents`
- `payments`

### CRM

- `travel_leads`
- `travel_lead_activities`

### Chatbot

- `chatbot_sessions`
- `chatbot_messages`
- `chatbot_unanswered`
- `chatbot_knowledge`
- `chatbot_sources`
- `chatbot_source_answers`

### Website Content

- `site_settings`
- `settings`
- `content_pages`
- `galleries`
- `testimonials`
- `faqs`
- `contact_messages`

### System

- `notifications`
- `admin_activity_logs`

CodeIgniter akan mengelola tabel migration internalnya sendiri ketika `php spark migrate` dijalankan.

## Fresh Database - phpMyAdmin

1. Buat database, contoh `travel_white_label`.
2. Pilih database tersebut.
3. Buka tab **Import**.
4. Pilih `database/schema.sql`.
5. Jalankan import.
6. Kembali ke terminal dan jalankan `php spark migrate`.
7. Isi `SEED_ADMIN_*` di `.env`.
8. Jalankan `php spark db:seed GenericClientSeeder`.

## Fresh Database - MySQL CLI

```bash
mysql -u root -p -e "CREATE DATABASE travel_white_label CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p travel_white_label < database/schema.sql
php spark migrate
php spark db:seed GenericClientSeeder
```

Jika user MySQL tanpa password di local development, hilangkan `-p`.

## Existing Instance

Jangan import `database/schema.sql` ke database produksi yang sudah berjalan.

Untuk existing instance:

```bash
php spark migrate
php spark cache:clear
```

Backup database sebelum migration.
