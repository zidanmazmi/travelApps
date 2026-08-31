# GitHub-Readiness Audit

Audit dilakukan terhadap ZIP source yang diterima sebelum membuat package GitHub-ready.

## Temuan dan Tindakan

### 1. `.env` ada di ZIP sumber

ZIP sumber membawa `.env` dan terdapat Gemini credential non-kosong. Nilainya tidak disalin ke dokumentasi atau package final.

Tindakan:

- `.env` dihapus dari package GitHub-ready.
- `.env.example` dibuat dengan value generik/kosong.
- `.gitignore` memastikan `.env` tidak ter-commit.
- Credential yang pernah berada di ZIP yang dibagikan harus dianggap terekspos dan sebaiknya di-rotate sebelum penggunaan berikutnya.

### 2. Legacy/dead OpenAI integration

`OpenAIService.php` dan `Config/OpenAI.php` masih ada, tetapi tidak ditemukan caller aktif di source aplikasi.

Tindakan:

- Dihapus dari package GitHub-ready.

### 3. Legacy brand files

Ditemukan file/layout/CSS lama yang sudah tidak dipakai oleh layout aktif dan masih membawa identitas vendor lama.

Tindakan:

- Unused legacy admin layouts dihapus.
- Unused legacy CSS dihapus.
- Legacy logo/favicon lama dihapus.
- Brand audit dibuat sebagai gate.

### 4. Runtime / user data

ZIP sumber membawa logs, session, debugbar, cache, gallery media, dan source document chatbot.

Tindakan:

- Runtime data dibersihkan dari package final.
- Folder tetap dipertahankan melalui marker file.
- `.gitignore` mencegah upload/runtime baru masuk Git.

### 5. Database bootstrap

Migration yang ada tidak membentuk seluruh 27 tabel operasional dari database kosong.

Tindakan:

- Ditambahkan `database/schema.sql` schema-only untuk fresh bootstrap.
- Tidak ada INSERT/data produksi di schema.
- Fresh flow: import schema -> `php spark migrate` -> `GenericClientSeeder`.

### 6. Fallback branding

Asset fallback generik dipisahkan dari runtime upload.

Tindakan:

- Fallback source: `public/assets/frontend/images/default-brand/`.
- Upload vendor tetap ke `public/assets/uploads/branding/` dan di-ignore Git.

## Validation Result

- Legacy brand audit: PASS.
- Secret pattern scan package final: PASS.
- `.env` pada package final: tidak ada.
- Base schema: 27 application tables, 0 INSERT statement.
- PHP syntax: PASS pada source aplikasi/test/scripts yang diperiksa.
- JavaScript syntax: PASS pada asset JS yang diperiksa.

### 7. Active route stub ditemukan

Route `/galeri` dan `/testimoni` aktif di `Routes.php`, tetapi controller/view standalone yang dituju masih kosong dari source awal. Ini tidak saya isi secara asumtif karena desain/behavior halaman standalone belum ditentukan. Section galeri/testimoni pada landing page tetap dikelola melalui `Frontend\Home`.

### 8. CSRF belum aktif secara global

`app/Config/Filters.php` saat ini masih meng-comment filter global `csrf`. Banyak view sudah memiliki `csrf_field()`, tetapi validasi CSRF global belum diaktifkan. Saya tidak mengaktifkannya otomatis pada package GitHub-ready karena endpoint AJAX dan callback Midtrans perlu diuji/di-exclude dengan benar agar flow existing tidak rusak. Ini harus masuk production hardening sebelum go-live.

## Known Limitations

- Composer executable tidak tersedia pada environment audit, sehingga `composer install` tidak dieksekusi di sini. `composer.json` dan `composer.lock` tetap disertakan.
- Business-flow integration test terhadap MySQL, Midtrans, SMTP, dan Gemini harus dilakukan pada environment lokal/deployment setelah `.env` diisi.
- Beberapa controller/view legacy tetap ada karena route operasionalnya saat ini diarahkan ke dashboard; lihat `FEATURES.md` dan `TECHNICAL.md`.
