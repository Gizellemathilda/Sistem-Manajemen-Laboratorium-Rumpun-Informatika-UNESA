# SimLab — Sistem Informasi Manajemen Laboratorium

Implementasi lengkap dari prototipe `prototype.html` (= `simlab-4.html`) dengan stack:
- **Backend**: Laravel 10.x (PHP 8.2) + Sanctum + MySQL 8.0
- **Frontend**: Vue.js 3 (Composition API) + Vite + Pinia + Vue Router + Axios

## Struktur

```
simlab/
├── prototype.html              ← prototipe HTML referensi (simlab-4)
├── database.sql                ← skema MySQL 8.0 + seed
├── backend/                    ← Laravel 10
│   ├── app/Http/Controllers/Api/
│   │   ├── AuthController.php
│   │   ├── UserController.php
│   │   ├── ProfilController.php
│   │   ├── InventarisController.php
│   │   ├── PeminjamanController.php
│   │   ├── KerusakanController.php
│   │   ├── InsidenController.php
│   │   ├── ModulController.php
│   │   ├── NotifikasiController.php
│   │   ├── MatkulController.php
│   │   ├── JadwalController.php
│   │   ├── RuanganController.php
│   │   ├── AbsensiController.php
│   │   ├── NilaiController.php
│   │   └── PemeliharaanController.php
│   ├── app/Models/
│   ├── database/migrations/
│   ├── database/seeders/
│   └── routes/api.php
└── frontend/                   ← Vue 3
    ├── src/
    │   ├── App.vue             ← shell + sidebar role-based + notif dot
    │   ├── main.js
    │   ├── stores/             ← auth.js, ui.js, data.js (Pinia)
    │   ├── api/index.js        ← axios instance
    │   ├── router/index.js
    │   └── views/
    │       ├── LoginView.vue
    │       ├── DashboardView.vue
    │       ├── ProfilView.vue   ← edit nama real-time + ganti foto
    │       ├── LaporKerusakanView.vue   ← pop-up "Laporan Terkirim"
    │       ├── LaporInsidenView.vue     ← pop-up "Laporan Terkirim"
    │       ├── KerusakanListView.vue
    │       ├── InsidenListView.vue
    │       ├── InventarisView.vue
    │       ├── PeminjamanView.vue
    │       ├── ModulView.vue
    │       ├── NotifikasiView.vue
    │       ├── MatkulView.vue
    │       ├── JadwalView.vue
    │       ├── RuanganView.vue
    │       ├── AbsensiView.vue
    │       ├── NilaiView.vue
    │       ├── PemeliharaanView.vue
    │       └── UserManagementView.vue
    └── vite.config.js
```

## Fitur kunci yang sudah diimplementasi

- **Pop-up laporan terkirim** — `LaporKerusakanView` & `LaporInsidenView` memanggil
  `ui.showSuccess(...)` setelah submit berhasil.
- **Titik merah notifikasi** — komponen `App.vue` menampilkan `<span class="notif-dot">`
  bila `data.unreadCount > 0`. Saat panel notifikasi dibuka, `tandaiSemuaBaca()`
  dipanggil sehingga titik merah otomatis hilang.
- **Edit profil real-time** — `ProfilView` memanggil `auth.updateUserLocal({ nama })`
  pada event `@input`, jadi nama di sidebar berubah saat diketik. Foto profil
  diganti via base64 (preview instan) lalu dikirim ke `POST /profil/foto`.
- **Menu per-role** — `App.vue` punya `MENU_BY_ROLE` mengikuti aturan:
  Aslab tanpa absensi/nilai/modul, Dosen tanpa jadwal (absensi & kerusakan
  read-only), Admin read-only untuk ruangan/matkul/jadwal.
- **Validasi peminjaman dosen** — `nav-badge` menampilkan jumlah peminjaman
  yang `status='menunggu'`.

## Setup Backend

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
# isi DB_* di .env (MySQL 8.0)
php artisan migrate --seed
php artisan serve
```

## Setup Frontend

```bash
cd frontend
npm install
cp .env.example .env       # set VITE_API_URL=http://localhost:8000/api
npm run dev
```

## Akun Demo

| Role       | Email                     | Password  |
|------------|---------------------------|-----------|
| Mahasiswa  | mahasiswa@unesa.ac.id     | password  |
| Asprak     | asprak@unesa.ac.id        | password  |
| Aslab      | aslab@unesa.ac.id         | password  |
| Dosen      | dosen@unesa.ac.id         | password  |
| Admin      | admin@unesa.ac.id         | password  |
