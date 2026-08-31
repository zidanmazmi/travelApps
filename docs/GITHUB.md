# GitHub Guide

## Sebelum Push

Repository GitHub **tidak boleh** berisi:

- `.env`
- `vendor/`
- API key
- password database/SMTP
- Midtrans credential
- Gemini credential
- session/cache/log/debugbar
- dokumen knowledge vendor
- upload jamaah/vendor
- dump database produksi

Repository GitHub-ready ini sudah memiliki `.gitignore` untuk kategori tersebut.

## 1. Cek Safety

Windows:

```powershell
powershell -ExecutionPolicy Bypass -File .\audit-brand.ps1
php scripts/check-requirements.php
```

Linux/macOS:

```bash
bash audit-brand.sh
php scripts/check-requirements.php
```

Pastikan `.env` tidak ada di package GitHub-ready.

## 2. Init Git

Dari root project:

```bash
git init
git branch -M main
```

## 3. Cek File yang Akan Masuk

```bash
git status
```

Kemudian:

```bash
git add .
git status
```

Pastikan tidak terlihat:

```text
.env
vendor/
writable/logs/*
writable/session/*
writable/uploads/chatbot_sources/<file-user>
```

Jika aman:

```bash
git commit -m "Initial white-label travel platform"
```

## 4. Buat Repository GitHub

Di GitHub buat repository kosong. Jangan centang auto-generate README jika source lokal sudah mempunyai README.

Contoh nama:

```text
travel-white-label-platform
```

Untuk source komersial/private, gunakan **Private Repository** kecuali memang ingin source dibuka ke publik.

## 5. Connect Remote

HTTPS:

```bash
git remote add origin https://github.com/USERNAME/travel-white-label-platform.git
git push -u origin main
```

atau SSH:

```bash
git remote add origin git@github.com:USERNAME/travel-white-label-platform.git
git push -u origin main
```

## 6. Clone Ulang untuk Test

Jangan hanya mengandalkan project Laragon lama. Test repository dari clone baru:

```bash
git clone <repository-url> travelapps-test
cd travelapps-test
composer install
```

Copy `.env.example`, setup database, import schema, migrate, seed, dan run sesuai `INSTALLATION.md`.

Jika fresh clone berhasil, repository sudah jauh lebih aman untuk handoff/deployment ulang.

## File Media Besar

Build saat ini memiliki video hero sekitar 44 MB. Nilai ini masih di bawah batas file GitHub 100 MB, tetapi binary media besar membuat clone/pull lebih berat. Jika nanti video bertambah atau melebihi batas, pindahkan media besar ke Git LFS/CDN/object storage.
