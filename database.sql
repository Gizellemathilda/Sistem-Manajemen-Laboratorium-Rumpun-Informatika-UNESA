-- ════════════════════════════════════════════════════════════════════════════
--  SIM-LAB — Sistem Informasi Manajemen Laboratorium
--  Database Schema (MySQL 8.0)
--  Server: Windows Server 2019 / Windows 10/11
--  Backend: Laravel 10.x + PHP 8.2
--  Frontend: Vue.js 3
-- ════════════════════════════════════════════════════════════════════════════

CREATE DATABASE IF NOT EXISTS `simlab`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
USE `simlab`;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `notifikasi`;
DROP TABLE IF EXISTS `pemeliharaan`;
DROP TABLE IF EXISTS `insiden`;
DROP TABLE IF EXISTS `kerusakan`;
DROP TABLE IF EXISTS `modul`;
DROP TABLE IF EXISTS `nilai`;
DROP TABLE IF EXISTS `absensi`;
DROP TABLE IF EXISTS `booking_ruang`;
DROP TABLE IF EXISTS `peminjaman`;
DROP TABLE IF EXISTS `jadwal`;
DROP TABLE IF EXISTS `mata_kuliah`;
DROP TABLE IF EXISTS `inventaris`;
DROP TABLE IF EXISTS `ruangan`;
DROP TABLE IF EXISTS `users`;
SET FOREIGN_KEY_CHECKS = 1;

-- ────────────────────────────────────────────────────────────────────────────
--  USERS — semua peran (mahasiswa, asprak, aslab, dosen, admin)
-- ────────────────────────────────────────────────────────────────────────────
CREATE TABLE `users` (
  `id`              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `nama`            VARCHAR(150) NOT NULL,
  `email`           VARCHAR(150) NOT NULL UNIQUE,
  `email_verified_at` TIMESTAMP NULL,
  `password`        VARCHAR(255) NOT NULL,
  `role`            ENUM('mahasiswa','asprak','aslab','dosen','admin') NOT NULL,
  `nim_nip`         VARCHAR(30) NULL,
  `lab`             VARCHAR(100) NULL,
  `foto_profil`     VARCHAR(255) NULL,        -- path/URL foto profil
  `status`          ENUM('Aktif','Nonaktif') DEFAULT 'Aktif',
  `remember_token`  VARCHAR(100) NULL,
  `created_at`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_role (`role`),
  INDEX idx_status (`status`)
) ENGINE=InnoDB;

-- ────────────────────────────────────────────────────────────────────────────
--  RUANGAN
-- ────────────────────────────────────────────────────────────────────────────
CREATE TABLE `ruangan` (
  `id`         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `nama`       VARCHAR(100) NOT NULL,
  `gedung`     VARCHAR(100) NOT NULL,
  `kapasitas`  INT NOT NULL DEFAULT 0,
  `status`     ENUM('Tersedia','Terpakai','Tidak Tersedia') DEFAULT 'Tersedia',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ────────────────────────────────────────────────────────────────────────────
--  INVENTARIS ASET
-- ────────────────────────────────────────────────────────────────────────────
CREATE TABLE `inventaris` (
  `id`         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `kode`       VARCHAR(50) NOT NULL UNIQUE,
  `nama`       VARCHAR(150) NOT NULL,
  `lab`        VARCHAR(100) NOT NULL,
  `merk`       VARCHAR(100),
  `tahun`      YEAR,
  `kondisi`    ENUM('Baik','Rusak Ringan','Rusak') DEFAULT 'Baik',
  `status`     ENUM('Tersedia','Dipinjam','Tidak Tersedia') DEFAULT 'Tersedia',
  `ruangan_id` BIGINT UNSIGNED NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_inventaris_ruangan FOREIGN KEY (`ruangan_id`) REFERENCES `ruangan`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ────────────────────────────────────────────────────────────────────────────
--  MATA KULIAH & JADWAL
-- ────────────────────────────────────────────────────────────────────────────
CREATE TABLE `mata_kuliah` (
  `id`         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `kode`       VARCHAR(20) NOT NULL UNIQUE,
  `nama`       VARCHAR(150) NOT NULL,
  `sks`        TINYINT UNSIGNED NOT NULL DEFAULT 3,
  `dosen_id`   BIGINT UNSIGNED NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_mk_dosen FOREIGN KEY (`dosen_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE `jadwal` (
  `id`             BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `mata_kuliah_id` BIGINT UNSIGNED NOT NULL,
  `kelas`          VARCHAR(50) NOT NULL,
  `ruangan_id`     BIGINT UNSIGNED NOT NULL,
  `hari`           ENUM('Senin','Selasa','Rabu','Kamis','Jumat','Sabtu') NOT NULL,
  `jam`            VARCHAR(20) NOT NULL,
  `dosen_id`       BIGINT UNSIGNED NULL,
  `jumlah_mhs`     INT DEFAULT 0,
  `created_at`     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at`     TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_jdw_mk      FOREIGN KEY (`mata_kuliah_id`) REFERENCES `mata_kuliah`(`id`) ON DELETE CASCADE,
  CONSTRAINT fk_jdw_ruangan FOREIGN KEY (`ruangan_id`)     REFERENCES `ruangan`(`id`)     ON DELETE CASCADE,
  CONSTRAINT fk_jdw_dosen   FOREIGN KEY (`dosen_id`)       REFERENCES `users`(`id`)       ON DELETE SET NULL
) ENGINE=InnoDB;

-- ────────────────────────────────────────────────────────────────────────────
--  PEMINJAMAN ASET & BOOKING RUANG
-- ────────────────────────────────────────────────────────────────────────────
CREATE TABLE `peminjaman` (
  `id`             BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `pemohon_id`     BIGINT UNSIGNED NOT NULL,
  `inventaris_id`  BIGINT UNSIGNED NOT NULL,
  `tanggal_pinjam` DATE NOT NULL,
  `tanggal_kembali` DATE NULL,
  `keperluan`      TEXT,
  `validator_id`   BIGINT UNSIGNED NULL,
  `status`         ENUM('Menunggu Validasi','Disetujui','Ditolak','Selesai') DEFAULT 'Menunggu Validasi',
  `created_at`     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at`     TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_pj_pemohon    FOREIGN KEY (`pemohon_id`)    REFERENCES `users`(`id`)      ON DELETE CASCADE,
  CONSTRAINT fk_pj_inventaris FOREIGN KEY (`inventaris_id`) REFERENCES `inventaris`(`id`) ON DELETE CASCADE,
  CONSTRAINT fk_pj_validator  FOREIGN KEY (`validator_id`)  REFERENCES `users`(`id`)      ON DELETE SET NULL,
  INDEX idx_status (`status`)
) ENGINE=InnoDB;

CREATE TABLE `booking_ruang` (
  `id`         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `pemohon_id` BIGINT UNSIGNED NOT NULL,
  `ruangan_id` BIGINT UNSIGNED NOT NULL,
  `tanggal`    DATE NOT NULL,
  `waktu`      VARCHAR(30) NOT NULL,
  `keperluan`  TEXT,
  `status`     ENUM('Menunggu','Disetujui','Ditolak','Selesai') DEFAULT 'Menunggu',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_br_pemohon FOREIGN KEY (`pemohon_id`) REFERENCES `users`(`id`)   ON DELETE CASCADE,
  CONSTRAINT fk_br_ruangan FOREIGN KEY (`ruangan_id`) REFERENCES `ruangan`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ────────────────────────────────────────────────────────────────────────────
--  AKADEMIK: ABSENSI, NILAI, MODUL
-- ────────────────────────────────────────────────────────────────────────────
CREATE TABLE `absensi` (
  `id`             BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `mata_kuliah_id` BIGINT UNSIGNED NOT NULL,
  `kelas`          VARCHAR(50),
  `tanggal`        DATE NOT NULL,
  `hadir`          INT DEFAULT 0,
  `izin`           INT DEFAULT 0,
  `alfa`           INT DEFAULT 0,
  `total`          INT GENERATED ALWAYS AS (`hadir` + `izin` + `alfa`) STORED,
  `dicatat_oleh`   BIGINT UNSIGNED NULL,
  `created_at`     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at`     TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_ab_mk    FOREIGN KEY (`mata_kuliah_id`) REFERENCES `mata_kuliah`(`id`) ON DELETE CASCADE,
  CONSTRAINT fk_ab_user  FOREIGN KEY (`dicatat_oleh`)   REFERENCES `users`(`id`)       ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE `nilai` (
  `id`              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `mahasiswa_id`    BIGINT UNSIGNED NOT NULL,
  `mata_kuliah_id`  BIGINT UNSIGNED NOT NULL,
  `modul1`          DECIMAL(5,2) DEFAULT 0,
  `modul2`          DECIMAL(5,2) DEFAULT 0,
  `modul3`          DECIMAL(5,2) DEFAULT 0,
  `uas`             DECIMAL(5,2) DEFAULT 0,
  `rata_rata`       DECIMAL(5,2) GENERATED ALWAYS AS ((`modul1`+`modul2`+`modul3`+`uas`)/4) STORED,
  `grade`           VARCHAR(3),
  `created_at`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_nl_mhs FOREIGN KEY (`mahasiswa_id`)   REFERENCES `users`(`id`)       ON DELETE CASCADE,
  CONSTRAINT fk_nl_mk  FOREIGN KEY (`mata_kuliah_id`) REFERENCES `mata_kuliah`(`id`) ON DELETE CASCADE,
  UNIQUE KEY uniq_mhs_mk (`mahasiswa_id`,`mata_kuliah_id`)
) ENGINE=InnoDB;

CREATE TABLE `modul` (
  `id`             BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `judul`          VARCHAR(200) NOT NULL,
  `mata_kuliah_id` BIGINT UNSIGNED NOT NULL,
  `dosen_id`       BIGINT UNSIGNED NULL,
  `tanggal_unggah` DATE NOT NULL,
  `nama_file`      VARCHAR(255) NOT NULL,        -- nama file asli (mis. modul.pdf)
  `path_file`      VARCHAR(500) NOT NULL,        -- lokasi penyimpanan
  `mime_type`      VARCHAR(100) NOT NULL,        -- application/pdf, application/msword, dll
  `format`         VARCHAR(10) NOT NULL,         -- PDF, DOCX, PPTX, dll (untuk konsistensi unduh)
  `ukuran_kb`     INT UNSIGNED NOT NULL,
  `created_at`     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at`     TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_md_mk    FOREIGN KEY (`mata_kuliah_id`) REFERENCES `mata_kuliah`(`id`) ON DELETE CASCADE,
  CONSTRAINT fk_md_dosen FOREIGN KEY (`dosen_id`)       REFERENCES `users`(`id`)       ON DELETE SET NULL
) ENGINE=InnoDB;

-- ────────────────────────────────────────────────────────────────────────────
--  KERUSAKAN, INSIDEN, PEMELIHARAAN
-- ────────────────────────────────────────────────────────────────────────────
CREATE TABLE `kerusakan` (
  `id`            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `inventaris_id` BIGINT UNSIGNED NOT NULL,
  `dilaporkan_oleh` BIGINT UNSIGNED NOT NULL,
  `lab`           VARCHAR(100),
  `deskripsi`     TEXT NOT NULL,
  `tanggal_lapor` DATE NOT NULL,
  `status`        ENUM('Menunggu Teknisi','Dalam Perbaikan','Selesai') DEFAULT 'Menunggu Teknisi',
  `ditangani_oleh` BIGINT UNSIGNED NULL,
  `tanggal_selesai` DATE NULL,
  `created_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_kr_inv      FOREIGN KEY (`inventaris_id`)   REFERENCES `inventaris`(`id`) ON DELETE CASCADE,
  CONSTRAINT fk_kr_pelapor  FOREIGN KEY (`dilaporkan_oleh`) REFERENCES `users`(`id`)      ON DELETE CASCADE,
  CONSTRAINT fk_kr_handler  FOREIGN KEY (`ditangani_oleh`)  REFERENCES `users`(`id`)      ON DELETE SET NULL,
  INDEX idx_status (`status`)
) ENGINE=InnoDB;

CREATE TABLE `insiden` (
  `id`              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `judul`           VARCHAR(200) NOT NULL,
  `lab`             VARCHAR(100),
  `deskripsi`       TEXT NOT NULL,
  `tanggal`         DATE NOT NULL,
  `dilaporkan_oleh` BIGINT UNSIGNED NOT NULL,
  `ditangani_oleh`  BIGINT UNSIGNED NULL,
  `status`          ENUM('Dalam Penanganan','Ditangani','Selesai') DEFAULT 'Dalam Penanganan',
  `created_at`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_in_pelapor FOREIGN KEY (`dilaporkan_oleh`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  CONSTRAINT fk_in_handler FOREIGN KEY (`ditangani_oleh`)  REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE `pemeliharaan` (
  `id`         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `aset`       VARCHAR(200) NOT NULL,
  `jenis`      VARCHAR(100) NOT NULL,
  `tanggal`    DATE NOT NULL,
  `teknisi`    VARCHAR(150),
  `status`     ENUM('Dijadwalkan','Sedang Berjalan','Selesai') DEFAULT 'Dijadwalkan',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ────────────────────────────────────────────────────────────────────────────
--  NOTIFIKASI
-- ────────────────────────────────────────────────────────────────────────────
CREATE TABLE `notifikasi` (
  `id`         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id`    BIGINT UNSIGNED NOT NULL,
  `judul`      VARCHAR(200) NOT NULL,
  `deskripsi`  TEXT,
  `dibaca`     BOOLEAN NOT NULL DEFAULT FALSE,
  `dibaca_at`  TIMESTAMP NULL,
  `tipe`       VARCHAR(50) DEFAULT 'info',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_nt_user FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX idx_user_dibaca (`user_id`,`dibaca`)
) ENGINE=InnoDB;

-- ════════════════════════════════════════════════════════════════════════════
--  SEED DATA
-- ════════════════════════════════════════════════════════════════════════════
-- Password hash adalah bcrypt dari "password123"
INSERT INTO `users` (`nama`, `email`, `password`, `role`, `nim_nip`, `lab`, `status`) VALUES
 ('Ata Annisa Azka',     'mahasiswa@unesa.ac.id', '$2y$10$exampleexampleexampleexampleexampleexampleexamp', 'mahasiswa', '24051204139',         'RPL',        'Aktif'),
 ('Sutan Fil''ilmi',     'asprak@unesa.ac.id',    '$2y$10$exampleexampleexampleexampleexampleexampleexamp', 'asprak',    '23051204101',         'Jaringan',   'Aktif'),
 ('Irma Aulia',          'aslab@unesa.ac.id',     '$2y$10$exampleexampleexampleexampleexampleexampleexamp', 'aslab',     '22051204088',         'RPL',        'Aktif'),
 ('Dr. Siti Rahmawati',  'dosen@unesa.ac.id',     '$2y$10$exampleexampleexampleexampleexampleexampleexamp', 'dosen',     '198501012010122001',  'RPL',        'Aktif'),
 ('Ir. Budi Santoso',    'admin@unesa.ac.id',     '$2y$10$exampleexampleexampleexampleexampleexampleexamp', 'admin',     '197003011998031001',  'Semua Lab',  'Aktif'),
 ('Ali Ma''ruf',         'ali@mhs.unesa.ac.id',   '$2y$10$exampleexampleexampleexampleexampleexampleexamp', 'mahasiswa', '24051204145', NULL, 'Aktif'),
 ('Tanisha Savaira',     'tanisha@mhs.unesa.ac.id','$2y$10$exampleexampleexampleexampleexampleexampleexamp','mahasiswa', '24051204158', NULL, 'Aktif');

INSERT INTO `ruangan` (`nama`,`gedung`,`kapasitas`,`status`) VALUES
 ('Lab RPL',                'T4 Lt. 3', 30, 'Tersedia'),
 ('Lab Jaringan Komputer',  'T4 Lt. 2', 30, 'Tersedia'),
 ('Lab AI',                 'T4 Lt. 4', 25, 'Terpakai'),
 ('Lab Multimedia',         'T4 Lt. 2', 32, 'Tersedia');

INSERT INTO `inventaris` (`kode`,`nama`,`lab`,`merk`,`tahun`,`kondisi`,`status`) VALUES
 ('RPL-PC-001','Komputer Workstation','Lab RPL','Lenovo',2023,'Baik','Tersedia'),
 ('RPL-PC-002','Komputer Workstation','Lab RPL','Lenovo',2023,'Rusak','Tidak Tersedia'),
 ('JAR-RT-003','Router Cisco 2900','Lab Jaringan Komputer','Cisco',2022,'Rusak Ringan','Tidak Tersedia'),
 ('AI-GPU-001','Workstation GPU NVIDIA','Lab AI','Asus',2024,'Baik','Tersedia'),
 ('MM-CAM-001','Kamera DSLR Canon','Lab Multimedia','Canon',2023,'Baik','Tersedia');

INSERT INTO `mata_kuliah` (`kode`,`nama`,`sks`,`dosen_id`) VALUES
 ('TI301','Pemrograman Web',3,4),
 ('TI302','Jaringan Komputer',3,4),
 ('TI401','Machine Learning',3,5),
 ('TI303','Desain Multimedia',2,4);

INSERT INTO `jadwal` (`mata_kuliah_id`,`kelas`,`ruangan_id`,`hari`,`jam`,`dosen_id`,`jumlah_mhs`) VALUES
 (2,'TI 2024 A',2,'Senin', '08:00-10:00',4,30),
 (1,'TI 2024 B',1,'Selasa','10:00-12:00',4,35),
 (3,'TI 2023 A',3,'Rabu',  '13:00-15:00',5,25),
 (4,'TI 2024 C',4,'Kamis', '08:00-10:00',4,32);

INSERT INTO `peminjaman` (`pemohon_id`,`inventaris_id`,`tanggal_pinjam`,`tanggal_kembali`,`keperluan`,`status`) VALUES
 (6,1,'2026-04-26','2026-04-27','Praktikum Jaringan','Disetujui'),
 (1,4,'2026-04-27','2026-04-28','Tugas Akhir','Menunggu Validasi');

INSERT INTO `booking_ruang` (`pemohon_id`,`ruangan_id`,`tanggal`,`waktu`,`keperluan`,`status`) VALUES
 (6,1,'2026-04-26','08:00-10:00','Praktikum Jaringan Komputer','Disetujui'),
 (1,3,'2026-04-27','13:00-15:00','Pengerjaan Tugas Akhir','Menunggu');

INSERT INTO `absensi` (`mata_kuliah_id`,`kelas`,`tanggal`,`hadir`,`izin`,`alfa`,`dicatat_oleh`) VALUES
 (1,'TI 2024 B','2026-04-22',33,1,1,2),
 (2,'TI 2024 A','2026-04-21',28,1,1,2);

INSERT INTO `nilai` (`mahasiswa_id`,`mata_kuliah_id`,`modul1`,`modul2`,`modul3`,`uas`,`grade`) VALUES
 (1,1,85,90,88,87,'A'),
 (6,2,92,88,91,90,'A'),
 (7,1,78,82,80,81,'B+');

INSERT INTO `modul` (`judul`,`mata_kuliah_id`,`dosen_id`,`tanggal_unggah`,`nama_file`,`path_file`,`mime_type`,`format`,`ukuran_kb`) VALUES
 ('Modul 1 - Pengenalan HTML & CSS', 1,4,'2026-03-10','modul1_html_css.pdf','/storage/modul/modul1_html_css.pdf','application/pdf','PDF',2400),
 ('Modul 2 - JavaScript Dasar',      1,4,'2026-03-17','modul2_js.pdf',     '/storage/modul/modul2_js.pdf',     'application/pdf','PDF',3100),
 ('Modul 3 - Framework Vue.js',      1,4,'2026-03-24','modul3_vue.pdf',    '/storage/modul/modul3_vue.pdf',    'application/pdf','PDF',4200),
 ('Modul 1 - Routing & Subnetting',  2,5,'2026-03-10','modul1_jaringan.pdf','/storage/modul/modul1_jaringan.pdf','application/pdf','PDF',1800);

INSERT INTO `kerusakan` (`inventaris_id`,`dilaporkan_oleh`,`lab`,`deskripsi`,`tanggal_lapor`,`status`) VALUES
 (2,3,'Lab RPL','Monitor tidak menyala, kemungkinan PSU rusak','2026-04-15','Dalam Perbaikan'),
 (3,2,'Lab Jaringan Komputer','Interface Ethernet tidak terdeteksi','2026-04-20','Menunggu Teknisi');

INSERT INTO `insiden` (`judul`,`lab`,`deskripsi`,`tanggal`,`dilaporkan_oleh`,`status`) VALUES
 ('Kebocoran AC di Lab RPL','Lab RPL','Air menetes dari unit AC ke meja komputer nomor 5','2026-04-22',1,'Ditangani');

INSERT INTO `pemeliharaan` (`aset`,`jenis`,`tanggal`,`teknisi`,`status`) VALUES
 ('Semua Komputer Lab RPL','Pembersihan Rutin','2026-04-28','Tim IT Kampus','Dijadwalkan'),
 ('AC Lab AI','Servis AC','2026-05-05','CV. Sejuk Teknik','Dijadwalkan');

INSERT INTO `notifikasi` (`user_id`,`judul`,`deskripsi`,`dibaca`,`tipe`) VALUES
 (1,'Peminjaman Disetujui','Peminjaman Workstation GPU Anda telah disetujui',FALSE,'success'),
 (1,'Jadwal Praktikum','Praktikum Pemrograman Web besok Selasa 10:00 WIB',FALSE,'info'),
 (1,'Modul Baru','Modul 3 Framework Vue.js telah diunggah',TRUE,'info'),
 (4,'Validasi Peminjaman','Ada permintaan peminjaman menunggu validasi',FALSE,'warning');

-- ════════════════════════════════════════════════════════════════════════════
--  VIEW BANTU (statistik dashboard)
-- ════════════════════════════════════════════════════════════════════════════
CREATE OR REPLACE VIEW `v_dashboard_admin` AS
SELECT
  (SELECT COUNT(*) FROM users)                             AS total_user,
  (SELECT COUNT(*) FROM inventaris)                        AS total_aset,
  (SELECT COUNT(*) FROM kerusakan WHERE status<>'Selesai') AS kerusakan_aktif,
  (SELECT COUNT(*) FROM ruangan)                           AS total_ruangan,
  (SELECT COUNT(*) FROM peminjaman WHERE status='Menunggu Validasi') AS pending_validasi;

-- Done.
