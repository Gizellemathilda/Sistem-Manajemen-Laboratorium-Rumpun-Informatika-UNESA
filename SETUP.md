# 🚀 SimLab — Panduan Menjalankan (SETUP.md)

> Versi terbaru: **+ halaman "Buat Akun Baru" (`/register`)** sudah ditambahkan.
> Halaman lain tidak diubah, semua fitur sesuai `prototype.html` (= simlab-4.html):
> Dashboard, Jadwal Praktikum, Ajukan Peminjaman, Booking Ruang Lab,
> Riwayat Peminjaman, Lapor Kerusakan, Lapor Insiden, Modul, Absensi, Nilai,
> Notifikasi, Pemeliharaan, Manajemen Pengguna.

---

## 0. Prasyarat

| Tool        | Versi minimum | Cek                |
|-------------|---------------|--------------------|
| PHP         | 8.2           | `php -v`           |
| Composer    | 2.x           | `composer -V`      |
| MySQL       | 8.0           | `mysql --version`  |
| Node.js     | 18 LTS / 20+  | `node -v`          |
| npm         | 9+            | `npm -v`           |

---

## 1. Siapkan Database MySQL

```sql
CREATE DATABASE simlab CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Import skema + data demo:

```bash
mysql -u root -p simlab < database.sql
```

`database.sql` sudah berisi seluruh tabel + 5 akun demo
(admin / dosen / aslab / asprak / mahasiswa, password: `password`).

---

## 2. Backend (Laravel 10 + Sanctum)

Folder `backend/` di zip ini hanya berisi **kode khusus SimLab** (Controllers,
Models, Migrations, routes, composer.json). Skeleton Laravel di-generate
otomatis lewat Composer.

```bash
cd backend

# 2.1 Pasang dependency Laravel + Sanctum
composer install            # akan otomatis men-download skeleton via composer.json

# Jika folder skeleton belum lengkap (vendor/, bootstrap/, public/index.php, artisan tidak ada),
# jalankan one-liner berikut untuk membangun skeleton baru lalu menyatukan kode:
#
#   cd ..
#   composer create-project laravel/laravel:^10.0 backend-skeleton
#   cp -r backend/app backend/database backend/routes backend/composer.json \
#         backend-skeleton/
#   composer require laravel/sanctum --working-dir=backend-skeleton
#   rm -rf backend && mv backend-skeleton backend
#   cd backend

# 2.2 Konfigurasi environment
cp .env.example .env
php artisan key:generate

# Edit .env → sesuaikan DB_DATABASE, DB_USERNAME, DB_PASSWORD

# 2.3 Migrasi + seed (skip kalau sudah import database.sql)
php artisan migrate --seed

# 2.4 Sanctum & CORS
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"

# 2.5 Jalankan server
php artisan serve
# → API: http://127.0.0.1:8000
```

Endpoint penting:
- `POST /api/login`
- `POST /api/register`   ← dipakai halaman **Buat Akun Baru**
- `POST /api/logout`
- `GET  /api/me`
- `apiResource` lengkap untuk inventaris, peminjaman, ruangan, jadwal, modul, dst.

---

## 3. Frontend (Vue 3 + Vite + Pinia)

```bash
cd frontend
npm install
cp .env.example .env
# Pastikan VITE_API_BASE_URL=http://127.0.0.1:8000/api
npm run dev
# → buka http://localhost:5173
```

---

## 4. Akses & Halaman Baru

| Halaman              | URL                | Catatan                                    |
|----------------------|--------------------|--------------------------------------------|
| Login                | `/login`           | Sudah ada link **"Buat akun baru"**        |
| **Buat Akun Baru**   | `/register`        | **Halaman baru** sesuai revisi terbaru     |
| Dashboard            | `/`                | Setelah login                              |
| Jadwal Praktikum     | `/jadwal`          |                                            |
| Inventaris           | `/inventaris`      |                                            |
| Ajukan Peminjaman    | `/peminjaman`      | Form ajukan + riwayat                      |
| Booking Ruang Lab    | `/ruangan`         | Aslab/Admin: kelola; lainnya: lihat        |
| Modul Praktikum      | `/modul`           |                                            |
| Lapor Kerusakan      | `/lapor-kerusakan` | Pop-up "Laporan Terkirim"                  |
| Lapor Insiden        | `/lapor-insiden`   | Pop-up "Laporan Terkirim"                  |
| Absensi / Nilai      | `/absensi` `/nilai`|                                            |
| Notifikasi           | `/notifikasi`      | Titik merah hilang otomatis saat dibuka    |
| Manajemen Pengguna   | `/users`           | Khusus Admin                               |

### Akun Demo

| Role       | Email                     | Password  |
|------------|---------------------------|-----------|
| Mahasiswa  | mahasiswa@unesa.ac.id     | password  |
| Asprak     | asprak@unesa.ac.id        | password  |
| Aslab      | aslab@unesa.ac.id         | password  |
| Dosen      | dosen@unesa.ac.id         | password  |
| Admin      | admin@unesa.ac.id         | password  |

---

## 5. Troubleshooting

| Masalah                                         | Solusi                                                          |
|-------------------------------------------------|-----------------------------------------------------------------|
| `CORS error` di browser                         | Cek `SANCTUM_STATEFUL_DOMAINS` & `CORS_ALLOWED_ORIGINS` di `.env` backend |
| `419 CSRF token mismatch`                       | API kita pakai Bearer token, pastikan tidak menambah header CSRF |
| `SQLSTATE[HY000] [2002] Connection refused`     | MySQL belum jalan / port salah                                  |
| Tombol **Daftar** tidak respons                 | Pastikan `php artisan serve` aktif di port 8000                 |
| Halaman kosong setelah `npm run dev`            | Cek console; biasanya `VITE_API_BASE_URL` salah                 |

---

## 6. Yang berubah di revisi ini

Hanya **menambahkan** file/baris berikut — tidak ada halaman lain yang diubah:

```
frontend/src/views/RegisterView.vue        ← baru (halaman Buat Akun Baru)
frontend/src/router/index.js               ← +1 import & +1 route /register
frontend/src/views/LoginView.vue           ← +1 link "Buat akun baru" di bawah form
SETUP.md                                   ← panduan run (file ini)
```

Selesai — selamat mencoba! 🎉
