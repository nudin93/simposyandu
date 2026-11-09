-- ============================================================
-- MIGRASI: tabel anak SD (6-12 tahun) + pemeriksaan anak SD
-- SIMPOSYANDU | tiruan struktur anak TK / pemeriksaan_anak_tk
-- Cara pakai: import file ini ke database simposyandu
-- (phpMyAdmin > pilih database > Import), atau:
--   mysql -u root -p simposyandu < migrasi_anak_sd.sql
-- Aman dijalankan ulang (IF NOT EXISTS).
-- ============================================================

CREATE TABLE IF NOT EXISTS `anak_sd` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nomor_peserta` varchar(20) NOT NULL,
  `id_penduduk_opensid` int(11) DEFAULT NULL,
  `status_integrasi` enum('belum','terhubung','manual') NOT NULL DEFAULT 'belum',
  `nik` varchar(16) DEFAULT NULL,
  `no_kk` varchar(16) DEFAULT NULL,
  `nama_lengkap` varchar(150) NOT NULL,
  `jenis_kelamin` enum('L','P') NOT NULL,
  `tanggal_lahir` date NOT NULL,
  `kategori` enum('Anak SD') DEFAULT 'Anak SD',
  `sekolah` varchar(150) DEFAULT NULL,
  `kelas` varchar(50) DEFAULT NULL,
  `dusun` varchar(100) DEFAULT NULL,
  `rt` varchar(5) DEFAULT NULL,
  `rw` varchar(5) DEFAULT NULL,
  `alamat_lengkap` text DEFAULT NULL,
  `status_aktif` tinyint(1) DEFAULT 1,
  `catatan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `nomor_peserta` (`nomor_peserta`),
  KEY `idx_sd_lahir` (`tanggal_lahir`),
  KEY `idx_sd_opensid` (`id_penduduk_opensid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `pemeriksaan_anak_sd` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `anak_sd_id` int(11) NOT NULL,
  `tanggal_pemeriksaan` date NOT NULL,
  `berat_badan` decimal(5,2) DEFAULT NULL,
  `tinggi_badan` decimal(5,2) DEFAULT NULL,
  `imt` decimal(5,2) DEFAULT NULL,
  `status_gizi` enum('Kurus','Normal','Gemuk','Obesitas') DEFAULT 'Normal',
  `hb` decimal(4,1) DEFAULT NULL,
  `status_anemia` tinyint(1) DEFAULT 0,
  `tekanan_darah` varchar(20) DEFAULT NULL,
  `edukasi_kesehatan` text DEFAULT NULL,
  `keluhan` text DEFAULT NULL,
  `penanganan` text DEFAULT NULL,
  `catatan` text DEFAULT NULL,
  `petugas_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_psd_anak` (`anak_sd_id`),
  KEY `idx_psd_tgl` (`tanggal_pemeriksaan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
