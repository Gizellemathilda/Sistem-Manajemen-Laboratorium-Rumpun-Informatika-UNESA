<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Ruangan;
use App\Models\Inventaris;
use App\Models\Matkul;
use App\Models\Jadwal;
use App\Models\Notifikasi;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Users — email sesuai dengan demo credentials di LoginView.vue ──
        $users = [
            ['nama' => 'Admin SimLab',       'email' => 'admin@unesa.ac.id',     'role' => 'admin',     'nim_nip' => '197003011998031001'],
            ['nama' => 'Dr. Siti Rahmawati', 'email' => 'dosen@unesa.ac.id',     'role' => 'dosen',     'nim_nip' => '198501012010122001'],
            ['nama' => 'Irma Aulia',         'email' => 'aslab@unesa.ac.id',     'role' => 'aslab',     'nim_nip' => '22051204088'],
            ['nama' => "Sutan Fil'ilmi",     'email' => 'asprak@unesa.ac.id',    'role' => 'asprak',    'nim_nip' => '23051204101'],
            ['nama' => 'Ata Annisa Azka',    'email' => 'mahasiswa@unesa.ac.id', 'role' => 'mahasiswa', 'nim_nip' => '24051204139'],
        ];

        foreach ($users as $u) {
            User::updateOrCreate(
                ['email' => $u['email']],
                array_merge($u, [
                    'password' => Hash::make('password'),
                    'status'   => 'Aktif',
                ])
            );
        }

        // ── Ruangan ──
        $ruangans = [
            ['nama' => 'Lab RPL',               'lokasi' => 'T4 Lt. 3', 'kapasitas' => 30, 'status' => 'Tersedia'],
            ['nama' => 'Lab Jaringan Komputer',  'lokasi' => 'T4 Lt. 2', 'kapasitas' => 30, 'status' => 'Tersedia'],
            ['nama' => 'Lab AI',                 'lokasi' => 'T4 Lt. 4', 'kapasitas' => 25, 'status' => 'Terpakai'],
            ['nama' => 'Lab Multimedia',         'lokasi' => 'T4 Lt. 2', 'kapasitas' => 32, 'status' => 'Tersedia'],
        ];
        foreach ($ruangans as $r) {
            Ruangan::firstOrCreate(['nama' => $r['nama']], $r);
        }

        // ── Inventaris ──
        $items = [
            ['nama' => 'Komputer Workstation', 'kategori' => 'Komputer', 'jumlah' => 10, 'kondisi' => 'Baik',        'ruangan_id' => 1, 'status' => 'Tersedia'],
            ['nama' => 'Router Cisco 2900',    'kategori' => 'Jaringan', 'jumlah' => 2,  'kondisi' => 'Rusak Ringan','ruangan_id' => 2, 'status' => 'Tidak Tersedia'],
            ['nama' => 'Workstation GPU',      'kategori' => 'Komputer', 'jumlah' => 5,  'kondisi' => 'Baik',        'ruangan_id' => 3, 'status' => 'Tersedia'],
            ['nama' => 'Kamera DSLR Canon',    'kategori' => 'Kamera',   'jumlah' => 3,  'kondisi' => 'Baik',        'ruangan_id' => 4, 'status' => 'Tersedia'],
        ];
        foreach ($items as $i) {
            Inventaris::firstOrCreate(['nama' => $i['nama']], $i);
        }

        // ── Mata Kuliah ──
        $matkuls = [
            ['kode' => 'TI301', 'nama' => 'Pemrograman Web',     'sks' => 3, 'dosen' => 'Dr. Siti Rahmawati'],
            ['kode' => 'TI302', 'nama' => 'Jaringan Komputer',   'sks' => 3, 'dosen' => 'Dr. Siti Rahmawati'],
            ['kode' => 'TI401', 'nama' => 'Machine Learning',    'sks' => 3, 'dosen' => 'Dr. Siti Rahmawati'],
            ['kode' => 'TI303', 'nama' => 'Desain Multimedia',   'sks' => 2, 'dosen' => 'Dr. Siti Rahmawati'],
        ];
        foreach ($matkuls as $m) {
            Matkul::firstOrCreate(['kode' => $m['kode']], $m);
        }

        // ── Jadwal ──
        $jadwals = [
            ['hari' => 'Senin',  'jam' => '08:00-10:00', 'matkul' => 'Jaringan Komputer', 'ruang' => 'Lab Jaringan Komputer', 'dosen' => 'Dr. Siti Rahmawati'],
            ['hari' => 'Selasa', 'jam' => '10:00-12:00', 'matkul' => 'Pemrograman Web',   'ruang' => 'Lab RPL',               'dosen' => 'Dr. Siti Rahmawati'],
            ['hari' => 'Rabu',   'jam' => '13:00-15:00', 'matkul' => 'Machine Learning',  'ruang' => 'Lab AI',                'dosen' => 'Dr. Siti Rahmawati'],
            ['hari' => 'Kamis',  'jam' => '08:00-10:00', 'matkul' => 'Desain Multimedia', 'ruang' => 'Lab Multimedia',        'dosen' => 'Dr. Siti Rahmawati'],
        ];
        foreach ($jadwals as $j) {
            Jadwal::firstOrCreate(['hari' => $j['hari'], 'matkul' => $j['matkul']], $j);
        }

        // ── Notifikasi untuk mahasiswa ──
        $mhs = User::where('email', 'mahasiswa@unesa.ac.id')->first();
        if ($mhs && Notifikasi::where('user_id', $mhs->id)->count() === 0) {
            Notifikasi::insert([
                ['user_id' => $mhs->id, 'judul' => 'Selamat datang di SIM-LAB', 'pesan' => 'Akun Anda telah aktif.', 'tipe' => 'info',    'dibaca' => false, 'created_at' => now(), 'updated_at' => now()],
                ['user_id' => $mhs->id, 'judul' => 'Jadwal Praktikum',          'pesan' => 'Praktikum Pemrograman Web besok Selasa 10:00.', 'tipe' => 'info', 'dibaca' => false, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }
    }
}
