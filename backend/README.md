# SIM-LAB Backend — Setup Guide

## Langkah-langkah menjalankan backend

### 1. Copy file-file ini ke folder `backend-baru`
Ganti/overwrite file yang ada dengan file dari zip ini.

### 2. Copy `.env.example` menjadi `.env`
```bash
cp .env.example .env
```

### 3. Sesuaikan `.env` dengan konfigurasi database kamu
```
DB_DATABASE=simlab
DB_USERNAME=root
DB_PASSWORD=        ← kosongkan jika pakai XAMPP default
```

### 4. Generate app key (jika belum ada)
```bash
php artisan key:generate
```

### 5. Jalankan migrasi + seeder
```bash
php artisan migrate:fresh --seed
```

> **Ini akan menghapus semua data lama dan membuat ulang dari awal dengan data demo.**

### 6. Jalankan server
```bash
php artisan serve
```

---

## Demo Credentials (password: `password`)

| Role      | Email                    |
|-----------|--------------------------|
| Admin     | admin@unesa.ac.id        |
| Dosen     | dosen@unesa.ac.id        |
| Aslab     | aslab@unesa.ac.id        |
| Asprak    | asprak@unesa.ac.id       |
| Mahasiswa | mahasiswa@unesa.ac.id    |

---

## Apa yang diperbaiki

1. **Password seeder** — hash yang benar untuk semua user demo
2. **Email seeder** — sesuai dengan demo credentials di LoginView.vue
3. **User model** — tambah kolom `lab` dan `status` di `$fillable`
4. **Migration users** — tambah kolom `lab`
5. **Tambah BookingRuang** — model, controller, migration, route (`/api/booking-ruang`)
6. **Tambah Laporan** — controller + route (`/api/laporan`) untuk LaporanView
7. **Route notifikasi** — urutan fixed agar `/unread-count` tidak konflik dengan `/{id}/baca`
