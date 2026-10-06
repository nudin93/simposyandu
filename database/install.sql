-- SIMPOSYANDU - File instalasi tunggal
-- Cara pakai: import file ini SEKALI di phpMyAdmin (tidak perlu file lain).
-- Isi: struktur database + migrasi + 1 baris pengaturan contoh (tanpa NIK/data warga).
-- Dibuat: 2026-10-06
-- ============================================================

-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Waktu pembuatan: 18 Agu 2026 pada 17.16
-- Versi server: 10.6.27-MariaDB-cll-lve-log
-- Versi PHP: 8.4.24

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Basis data: `simposyandu`
--

DELIMITER $$
--
-- Prosedur
--
$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Struktur dari tabel `artikel`
--

CREATE TABLE `artikel` (
  `id` int(11) NOT NULL,
  `judul` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `isi_artikel` longtext NOT NULL,
  `gambar` varchar(255) DEFAULT '',
  `kategori` varchar(100) DEFAULT 'Kesehatan',
  `penulis_id` int(11) NOT NULL,
  `status` enum('draft','menunggu','diterbitkan','ditolak') DEFAULT 'draft',
  `tanggal_publish` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `balita`
--

CREATE TABLE `balita` (
  `id` int(11) NOT NULL,
  `id_penduduk_opensid` int(11) DEFAULT NULL COMMENT 'FK ke tweb_penduduk.id OpenSID',
  `status_integrasi` enum('belum','terhubung','manual') NOT NULL DEFAULT 'belum' COMMENT 'Status tautan ke OpenSID',
  `catatan_integrasi` text DEFAULT NULL,
  `nomor_peserta` varchar(20) NOT NULL,
  `nik_anak` varchar(16) DEFAULT NULL,
  `no_kk` varchar(16) DEFAULT NULL,
  `nama_lengkap` varchar(150) NOT NULL,
  `jenis_kelamin` enum('L','P') NOT NULL,
  `tempat_lahir` varchar(100) DEFAULT NULL,
  `tanggal_lahir` date NOT NULL,
  `anak_ke` int(11) DEFAULT 1,
  `status_anak` enum('kandung','angkat','tiri') DEFAULT 'kandung',
  `golongan_darah` enum('A','B','AB','O','Tidak Tahu') DEFAULT 'Tidak Tahu',
  `berat_lahir` decimal(5,2) DEFAULT NULL,
  `tinggi_lahir` decimal(5,2) DEFAULT NULL,
  `nama_ayah` varchar(150) DEFAULT NULL,
  `nik_ayah` varchar(16) DEFAULT NULL,
  `pekerjaan_ayah` varchar(100) DEFAULT NULL,
  `nama_ibu` varchar(150) DEFAULT NULL,
  `nik_ibu` varchar(16) DEFAULT NULL,
  `pekerjaan_ibu` varchar(100) DEFAULT NULL,
  `no_hp` varchar(15) DEFAULT NULL,
  `dusun` varchar(100) DEFAULT NULL,
  `rt` varchar(5) DEFAULT NULL,
  `rw` varchar(5) DEFAULT NULL,
  `desa` varchar(100) DEFAULT NULL,
  `kecamatan` varchar(100) DEFAULT NULL,
  `kabupaten` varchar(100) DEFAULT NULL,
  `provinsi` varchar(100) DEFAULT NULL,
  `alamat_lengkap` text DEFAULT NULL,
  `status_asi` enum('ASI Eksklusif','Susu Formula','Keduanya') DEFAULT 'ASI Eksklusif',
  `riwayat_alergi` text DEFAULT NULL,
  `riwayat_penyakit` text DEFAULT NULL,
  `status_imunisasi` enum('Lengkap','Belum Lengkap','Dalam Proses') DEFAULT 'Dalam Proses',
  `bpjs_kis` varchar(30) DEFAULT NULL,
  `status_aktif` tinyint(1) DEFAULT 1,
  `foto_anak` varchar(255) DEFAULT '',
  `foto_kk` varchar(255) DEFAULT '',
  `foto_bpjs` varchar(255) DEFAULT '',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `balita`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `bayi`
--

CREATE TABLE `bayi` (
  `id` int(11) NOT NULL,
  `nomor_peserta` varchar(20) NOT NULL,
  `id_penduduk_opensid` int(11) DEFAULT NULL,
  `status_integrasi` enum('belum','terhubung','manual') NOT NULL DEFAULT 'belum',
  `nik_bayi` varchar(16) DEFAULT NULL,
  `nik_ibu` varchar(16) DEFAULT NULL,
  `no_kk` varchar(16) DEFAULT NULL,
  `nama_lengkap` varchar(150) NOT NULL,
  `jenis_kelamin` enum('L','P') NOT NULL,
  `tempat_lahir` varchar(100) DEFAULT NULL,
  `tanggal_lahir` date NOT NULL,
  `berat_lahir` decimal(5,2) DEFAULT NULL,
  `panjang_lahir` decimal(5,2) DEFAULT NULL,
  `lingkar_kepala_lahir` decimal(5,2) DEFAULT NULL,
  `asi_eksklusif` enum('Ya','Tidak','Sebagian') DEFAULT 'Ya',
  `nama_ibu` varchar(150) DEFAULT NULL,
  `nama_ayah` varchar(150) DEFAULT NULL,
  `dusun` varchar(100) DEFAULT NULL,
  `rt` varchar(5) DEFAULT NULL,
  `rw` varchar(5) DEFAULT NULL,
  `alamat_lengkap` text DEFAULT NULL,
  `status_aktif` tinyint(1) DEFAULT 1,
  `catatan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `ibu_hamil`
--

CREATE TABLE `ibu_hamil` (
  `id` int(11) NOT NULL,
  `id_penduduk_opensid` int(11) DEFAULT NULL COMMENT 'FK ke tweb_penduduk.id OpenSID',
  `status_integrasi` enum('belum','terhubung','manual') NOT NULL DEFAULT 'belum',
  `nomor_peserta` varchar(20) NOT NULL,
  `nik` varchar(16) DEFAULT NULL,
  `no_kk` varchar(16) DEFAULT NULL,
  `nama` varchar(150) NOT NULL,
  `tempat_lahir` varchar(100) DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `golongan_darah` enum('A','B','AB','O','Tidak Tahu') DEFAULT 'Tidak Tahu',
  `pendidikan` varchar(100) DEFAULT NULL,
  `pekerjaan` varchar(100) DEFAULT NULL,
  `no_hp` varchar(15) DEFAULT NULL,
  `nama_suami` varchar(150) DEFAULT NULL,
  `nik_suami` varchar(16) DEFAULT NULL,
  `pekerjaan_suami` varchar(100) DEFAULT NULL,
  `no_hp_suami` varchar(15) DEFAULT NULL,
  `kehamilan_ke` int(11) DEFAULT 1,
  `hpht` date DEFAULT NULL,
  `hpl` date DEFAULT NULL,
  `usia_kehamilan` int(11) DEFAULT NULL,
  `risiko_kehamilan` enum('Normal','Risiko Rendah','Risiko Tinggi') DEFAULT 'Normal',
  `berat_awal` decimal(5,2) DEFAULT NULL,
  `tinggi_badan` decimal(5,2) DEFAULT NULL,
  `lila` decimal(5,2) DEFAULT NULL,
  `tekanan_darah` varchar(20) DEFAULT NULL,
  `hb` decimal(4,1) DEFAULT NULL,
  `status_kek` tinyint(1) DEFAULT 0,
  `status_anemia` tinyint(1) DEFAULT 0,
  `tablet_tambah_darah` int(11) DEFAULT 0,
  `imunisasi_tt` enum('Tidak','TT1','TT2','TT3','TT4','TT5') DEFAULT 'Tidak',
  `konseling` text DEFAULT NULL,
  `rujukan` text DEFAULT NULL,
  `riwayat_penyakit` text DEFAULT NULL,
  `riwayat_persalinan` text DEFAULT NULL,
  `bpjs_kis` varchar(30) DEFAULT NULL,
  `dusun` varchar(100) DEFAULT NULL,
  `rt` varchar(5) DEFAULT NULL,
  `rw` varchar(5) DEFAULT NULL,
  `desa` varchar(100) DEFAULT NULL,
  `kecamatan` varchar(100) DEFAULT NULL,
  `kabupaten` varchar(100) DEFAULT NULL,
  `provinsi` varchar(100) DEFAULT NULL,
  `alamat_lengkap` text DEFAULT NULL,
  `foto_ibu` varchar(255) DEFAULT '',
  `foto_kia` varchar(255) DEFAULT '',
  `foto_ktp` varchar(255) DEFAULT '',
  `foto_bpjs` varchar(255) DEFAULT '',
  `status_aktif` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `ibu_hamil`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `imunisasi`
--

CREATE TABLE `imunisasi` (
  `id` int(11) NOT NULL,
  `balita_id` int(11) NOT NULL,
  `jenis_imunisasi` enum('BCG','Polio 1','Polio 2','Polio 3','Polio 4','DPT-HB-Hib 1','DPT-HB-Hib 2','DPT-HB-Hib 3','Campak','MR','Hepatitis B0','Hepatitis B1','Hepatitis B2','Hepatitis B3','PCV','Rotavirus','Lainnya') NOT NULL,
  `dosis` varchar(50) DEFAULT NULL,
  `tanggal_imunisasi` date NOT NULL,
  `petugas_id` int(11) DEFAULT NULL,
  `efek_samping` text DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `jadwal`
--

CREATE TABLE `jadwal` (
  `id` int(11) NOT NULL,
  `nama_kegiatan` varchar(200) NOT NULL,
  `tanggal_kegiatan` date NOT NULL,
  `jam` time NOT NULL,
  `lokasi` varchar(200) DEFAULT NULL,
  `penanggung_jawab` varchar(150) DEFAULT NULL,
  `jenis_kegiatan` enum('Penimbangan Balita','Pemeriksaan Ibu Hamil','Pemeriksaan Lansia','Imunisasi','Penyuluhan','Lainnya') DEFAULT 'Penimbangan Balita',
  `keterangan` text DEFAULT NULL,
  `status` enum('Terjadwal','Berlangsung','Selesai','Dibatalkan') DEFAULT 'Terjadwal',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `jadwal`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `kader`
--

CREATE TABLE `kader` (
  `id` int(11) NOT NULL,
  `nama` varchar(150) NOT NULL,
  `nik` varchar(16) DEFAULT NULL,
  `jenis_kelamin` enum('L','P') DEFAULT 'P',
  `alamat` text DEFAULT NULL,
  `no_hp` varchar(15) DEFAULT NULL,
  `jabatan` varchar(100) DEFAULT 'Kader',
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `foto` varchar(255) DEFAULT '',
  `role` enum('admin','kader','bidan','kepala_desa') DEFAULT 'kader',
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `kader`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `kb`
--

CREATE TABLE `kb` (
  `id` int(11) NOT NULL,
  `id_penduduk_opensid` int(11) DEFAULT NULL,
  `status_integrasi` enum('belum','terhubung','manual') NOT NULL DEFAULT 'belum',
  `penduduk_id` int(11) DEFAULT NULL,
  `nik` varchar(16) DEFAULT NULL,
  `no_kk` varchar(16) DEFAULT NULL,
  `nama` varchar(150) NOT NULL,
  `jenis_kelamin` enum('L','P') DEFAULT 'P',
  `tanggal_lahir` date DEFAULT NULL,
  `jenis_kontrasepsi` enum('Pil','Suntik','IUD','Implan','Kondom','MOW','MOP','Lainnya') NOT NULL,
  `tanggal_pelayanan` date NOT NULL,
  `tanggal_mulai` date DEFAULT NULL,
  `tanggal_kontrol` date DEFAULT NULL,
  `petugas_id` int(11) DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `status` enum('Aktif','Selesai','Ganti Metode') DEFAULT 'Aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `kb`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `kegiatan_posyandu`
--

CREATE TABLE `kegiatan_posyandu` (
  `id` int(11) NOT NULL,
  `tanggal_kegiatan` date NOT NULL,
  `nama_posyandu` varchar(150) NOT NULL,
  `lokasi` varchar(200) DEFAULT NULL,
  `kader_bertugas` text DEFAULT NULL,
  `jumlah_sasaran` int(11) DEFAULT 0,
  `jumlah_hadir` int(11) DEFAULT 0,
  `langkah1_pendaftaran` text DEFAULT NULL,
  `langkah2_pengukuran` text DEFAULT NULL,
  `langkah3_pencatatan` text DEFAULT NULL,
  `langkah4_pelayanan` text DEFAULT NULL,
  `langkah5_penyuluhan` text DEFAULT NULL,
  `materi_penyuluhan` text DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `status` enum('Terjadwal','Berlangsung','Selesai','Dibatalkan') DEFAULT 'Terjadwal',
  `petugas_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `kehadiran`
--

CREATE TABLE `kehadiran` (
  `id` int(11) NOT NULL,
  `jadwal_id` int(11) DEFAULT NULL,
  `jenis_peserta` enum('Balita','Ibu Hamil','Lansia','KB','Kader') NOT NULL,
  `referensi_id` int(11) DEFAULT NULL,
  `nama_peserta` varchar(150) DEFAULT NULL,
  `no_kk` varchar(16) DEFAULT NULL,
  `nik` varchar(16) DEFAULT NULL,
  `tanggal` date NOT NULL,
  `status` enum('Hadir','Tidak Hadir','Izin') DEFAULT 'Hadir',
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `keluarga`
--

CREATE TABLE `keluarga` (
  `id` int(11) NOT NULL,
  `no_kk` varchar(16) NOT NULL,
  `nik_kepala` varchar(16) DEFAULT NULL,
  `nama_kepala` varchar(150) NOT NULL,
  `alamat` text DEFAULT NULL,
  `dusun` varchar(100) DEFAULT NULL,
  `rt` varchar(5) DEFAULT NULL,
  `rw` varchar(5) DEFAULT NULL,
  `desa` varchar(100) DEFAULT NULL,
  `kecamatan` varchar(100) DEFAULT NULL,
  `kabupaten` varchar(100) DEFAULT NULL,
  `jumlah_anggota` int(11) DEFAULT 1,
  `status_ekonomi` enum('Mampu','Menengah','Kurang Mampu') DEFAULT 'Menengah',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `kunjungan_rumah`
--

CREATE TABLE `kunjungan_rumah` (
  `id` int(11) NOT NULL,
  `tanggal_kunjungan` date NOT NULL,
  `no_kk` varchar(16) DEFAULT NULL,
  `nama_keluarga` varchar(150) NOT NULL,
  `alamat` text DEFAULT NULL,
  `dusun` varchar(100) DEFAULT NULL,
  `rt` varchar(5) DEFAULT NULL,
  `rw` varchar(5) DEFAULT NULL,
  `prioritas` enum('Balita Stunting','Ibu Hamil Risiko Tinggi','Lansia Sakit','Tidak Hadir Posyandu','Lainnya') DEFAULT 'Lainnya',
  `referensi_modul` varchar(50) DEFAULT NULL,
  `referensi_id` int(11) DEFAULT NULL,
  `masalah_ditemukan` text DEFAULT NULL,
  `tindakan` text DEFAULT NULL,
  `rujukan` text DEFAULT NULL,
  `status_tindak_lanjut` enum('Belum','Proses','Selesai') DEFAULT 'Belum',
  `kader_id` int(11) DEFAULT NULL,
  `catatan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `kunjungan_rumah`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `lansia`
--

CREATE TABLE `lansia` (
  `id` int(11) NOT NULL,
  `id_penduduk_opensid` int(11) DEFAULT NULL COMMENT 'FK ke tweb_penduduk.id OpenSID',
  `status_integrasi` enum('belum','terhubung','manual') NOT NULL DEFAULT 'belum',
  `nomor_peserta` varchar(20) NOT NULL,
  `nik` varchar(16) DEFAULT NULL,
  `no_kk` varchar(16) DEFAULT NULL,
  `nama` varchar(150) NOT NULL,
  `jenis_kelamin` enum('L','P') NOT NULL,
  `tempat_lahir` varchar(100) DEFAULT NULL,
  `tanggal_lahir` date NOT NULL,
  `alamat_lengkap` text DEFAULT NULL,
  `no_hp` varchar(15) DEFAULT NULL,
  `pekerjaan` varchar(100) DEFAULT NULL,
  `status_perkawinan` enum('Menikah','Janda','Duda','Belum Menikah') DEFAULT 'Menikah',
  `pendidikan` varchar(100) DEFAULT NULL,
  `riwayat_penyakit` text DEFAULT NULL,
  `status_kesehatan` enum('Sehat','Sakit Ringan','Sakit Berat','Rutin Kontrol') DEFAULT 'Sehat',
  `kebutuhan_kunjungan_rumah` tinyint(1) DEFAULT 0,
  `bpjs_kis` varchar(30) DEFAULT NULL,
  `status_aktif` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `lansia`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `log_integrasi_opensid`
--

CREATE TABLE `log_integrasi_opensid` (
  `id` int(11) NOT NULL,
  `modul` enum('balita','ibu_hamil','lansia','kb') NOT NULL,
  `referensi_id` int(11) NOT NULL,
  `id_penduduk_opensid` int(11) DEFAULT NULL,
  `nik` varchar(16) DEFAULT NULL,
  `aksi` enum('hubungkan','lepaskan','sinkron') NOT NULL,
  `keterangan` text DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `log_integrasi_opensid`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `pemeriksaan_balita`
--

CREATE TABLE `pemeriksaan_balita` (
  `id` int(11) NOT NULL,
  `balita_id` int(11) NOT NULL,
  `tanggal_pemeriksaan` date NOT NULL,
  `petugas_id` int(11) DEFAULT NULL,
  `berat_badan` decimal(5,2) DEFAULT NULL,
  `tinggi_badan` decimal(5,2) DEFAULT NULL,
  `panjang_badan` decimal(5,2) DEFAULT NULL,
  `lingkar_kepala` decimal(5,2) DEFAULT NULL,
  `lingkar_lengan` decimal(5,2) DEFAULT NULL,
  `suhu_tubuh` decimal(4,1) DEFAULT NULL,
  `denyut_nadi` int(11) DEFAULT NULL,
  `nafsu_makan` enum('Baik','Kurang','Buruk') DEFAULT 'Baik',
  `status_asi` enum('Ya','Tidak') DEFAULT 'Ya',
  `imunisasi_diberikan` text DEFAULT NULL,
  `vitamin_diberikan` text DEFAULT NULL,
  `keluhan` text DEFAULT NULL,
  `penanganan` text DEFAULT NULL,
  `catatan_kader` text DEFAULT NULL,
  `jadwal_kontrol` date DEFAULT NULL,
  `umur_saat_periksa` int(11) DEFAULT NULL,
  `imt` decimal(5,2) DEFAULT NULL,
  `status_gizi` enum('Normal','Kurang','Buruk','Lebih','Obesitas') DEFAULT 'Normal',
  `bb_u` enum('Sangat Kurang','Kurang','Normal','Lebih') DEFAULT NULL,
  `tb_u` enum('Sangat Pendek','Pendek','Normal','Tinggi') DEFAULT NULL,
  `bb_tb` enum('Gizi Buruk','Gizi Kurang','Gizi Baik','Berisiko Gizi Lebih','Gizi Lebih','Obesitas') DEFAULT NULL,
  `zscore_bb_u` decimal(5,2) DEFAULT NULL,
  `zscore_tb_u` decimal(5,2) DEFAULT NULL,
  `zscore_bb_tb` decimal(5,2) DEFAULT NULL,
  `risiko_stunting` enum('Tidak','Risiko','Stunting') DEFAULT 'Tidak',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `pemeriksaan_bayi`
--

CREATE TABLE `pemeriksaan_bayi` (
  `id` int(11) NOT NULL,
  `bayi_id` int(11) NOT NULL,
  `tanggal_pemeriksaan` date NOT NULL,
  `umur_bulan` int(11) DEFAULT NULL,
  `berat_badan` decimal(5,2) DEFAULT NULL,
  `panjang_badan` decimal(5,2) DEFAULT NULL,
  `lingkar_kepala` decimal(5,2) DEFAULT NULL,
  `lila` decimal(5,2) DEFAULT NULL,
  `asi_eksklusif` enum('Ya','Tidak','Sebagian') DEFAULT 'Ya',
  `imunisasi_diberikan` text DEFAULT NULL,
  `vitamin_diberikan` text DEFAULT NULL,
  `keluhan` text DEFAULT NULL,
  `penanganan` text DEFAULT NULL,
  `status_gizi` enum('Normal','Kurang','Buruk','Lebih') DEFAULT 'Normal',
  `bb_u` enum('Sangat Kurang','Kurang','Normal','Lebih') DEFAULT NULL,
  `pb_u` enum('Sangat Pendek','Pendek','Normal','Tinggi') DEFAULT NULL,
  `risiko_stunting` enum('Tidak','Risiko','Stunting') DEFAULT 'Tidak',
  `catatan` text DEFAULT NULL,
  `petugas_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `pemeriksaan_dewasa`
--

CREATE TABLE `pemeriksaan_dewasa` (
  `id` int(11) NOT NULL,
  `usia_produktif_id` int(11) NOT NULL,
  `tanggal_pemeriksaan` date NOT NULL,
  `berat_badan` decimal(5,2) DEFAULT NULL,
  `tinggi_badan` decimal(5,2) DEFAULT NULL,
  `imt` decimal(5,2) DEFAULT NULL,
  `lingkar_perut` decimal(5,2) DEFAULT NULL,
  `tekanan_darah` varchar(20) DEFAULT NULL,
  `gula_darah` decimal(6,2) DEFAULT NULL,
  `kolesterol` decimal(6,2) DEFAULT NULL,
  `faktor_risiko` text DEFAULT NULL,
  `risiko_hipertensi` tinyint(1) DEFAULT 0,
  `risiko_diabetes` tinyint(1) DEFAULT 0,
  `risiko_obesitas` tinyint(1) DEFAULT 0,
  `keluhan` text DEFAULT NULL,
  `penanganan` text DEFAULT NULL,
  `rujukan` text DEFAULT NULL,
  `catatan` text DEFAULT NULL,
  `petugas_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `pemeriksaan_ibu_hamil`
--

CREATE TABLE `pemeriksaan_ibu_hamil` (
  `id` int(11) NOT NULL,
  `ibu_hamil_id` int(11) NOT NULL,
  `tanggal_pemeriksaan` date NOT NULL,
  `usia_kandungan` int(11) DEFAULT NULL,
  `berat_badan` decimal(5,2) DEFAULT NULL,
  `tekanan_darah` varchar(20) DEFAULT NULL,
  `tinggi_fundus` decimal(5,2) DEFAULT NULL,
  `djj` int(11) DEFAULT NULL,
  `posisi_janin` varchar(100) DEFAULT NULL,
  `gerakan_janin` enum('Aktif','Kurang','Tidak Ada') DEFAULT 'Aktif',
  `lila` decimal(5,2) DEFAULT NULL,
  `hb` decimal(4,1) DEFAULT NULL,
  `keluhan` text DEFAULT NULL,
  `riwayat_penyakit` text DEFAULT NULL,
  `tablet_fe` tinyint(1) DEFAULT 0,
  `tablet_tambah_darah` int(11) DEFAULT 0,
  `vitamin` text DEFAULT NULL,
  `imunisasi_tt` enum('Tidak','TT1','TT2','TT3','TT4','TT5') DEFAULT 'Tidak',
  `pemeriksaan_lab` text DEFAULT NULL,
  `catatan_bidan` text DEFAULT NULL,
  `konseling` text DEFAULT NULL,
  `rujukan` text DEFAULT NULL,
  `jadwal_kontrol` date DEFAULT NULL,
  `risiko_kek` tinyint(1) DEFAULT 0,
  `status_kek` tinyint(1) DEFAULT 0,
  `risiko_anemia` tinyint(1) DEFAULT 0,
  `status_anemia` tinyint(1) DEFAULT 0,
  `risiko_hipertensi` tinyint(1) DEFAULT 0,
  `risiko_preeklamsia` tinyint(1) DEFAULT 0,
  `status_risiko` enum('Normal','Risiko Ringan','Risiko Tinggi') DEFAULT 'Normal',
  `petugas_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `pemeriksaan_ibu_hamil`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `pemeriksaan_lansia`
--

CREATE TABLE `pemeriksaan_lansia` (
  `id` int(11) NOT NULL,
  `lansia_id` int(11) NOT NULL,
  `tanggal_pemeriksaan` date NOT NULL,
  `berat_badan` decimal(5,2) DEFAULT NULL,
  `tinggi_badan` decimal(5,2) DEFAULT NULL,
  `tekanan_darah` varchar(20) DEFAULT NULL,
  `gula_darah` decimal(6,2) DEFAULT NULL,
  `kolesterol` decimal(6,2) DEFAULT NULL,
  `asam_urat` decimal(5,2) DEFAULT NULL,
  `suhu_tubuh` decimal(4,1) DEFAULT NULL,
  `denyut_nadi` int(11) DEFAULT NULL,
  `keluhan` text DEFAULT NULL,
  `obat_diberikan` text DEFAULT NULL,
  `vitamin` text DEFAULT NULL,
  `catatan_petugas` text DEFAULT NULL,
  `jadwal_kontrol` date DEFAULT NULL,
  `risiko_hipertensi` tinyint(1) DEFAULT 0,
  `risiko_diabetes` tinyint(1) DEFAULT 0,
  `risiko_kolesterol` tinyint(1) DEFAULT 0,
  `petugas_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `pemeriksaan_remaja`
--

CREATE TABLE `pemeriksaan_remaja` (
  `id` int(11) NOT NULL,
  `remaja_id` int(11) NOT NULL,
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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `penduduk`
--

CREATE TABLE `penduduk` (
  `id` int(11) NOT NULL,
  `nik` varchar(16) NOT NULL,
  `no_kk` varchar(16) DEFAULT NULL,
  `nama` varchar(150) NOT NULL,
  `jenis_kelamin` enum('L','P') NOT NULL,
  `tempat_lahir` varchar(100) DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `agama` varchar(50) DEFAULT 'Islam',
  `status_perkawinan` enum('Belum Kawin','Kawin','Cerai Hidup','Cerai Mati') DEFAULT 'Belum Kawin',
  `pekerjaan` varchar(100) DEFAULT NULL,
  `pendidikan` varchar(100) DEFAULT NULL,
  `no_hp` varchar(15) DEFAULT NULL,
  `dusun` varchar(100) DEFAULT NULL,
  `rt` varchar(5) DEFAULT NULL,
  `rw` varchar(5) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `status_dalam_keluarga` enum('Kepala Keluarga','Istri','Anak','Menantu','Cucu','Orang Tua','Mertua','Famili Lain','Pembantu','Lainnya') DEFAULT 'Anak',
  `status_aktif` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `pengaturan`
--

CREATE TABLE `pengaturan` (
  `id` int(11) NOT NULL,
  `nama_aplikasi` varchar(100) DEFAULT 'SIMPOSYANDU',
  `nama_posyandu` varchar(150) DEFAULT 'Posyandu Anggrek',
  `nama_desa` varchar(100) DEFAULT '',
  `kecamatan` varchar(100) DEFAULT '',
  `kabupaten` varchar(100) DEFAULT '',
  `provinsi` varchar(100) DEFAULT '',
  `logo` varchar(255) DEFAULT '',
  `favicon` varchar(255) DEFAULT '',
  `nomor_kontak` varchar(20) DEFAULT '',
  `email` varchar(100) DEFAULT '',
  `warna_tema` varchar(20) DEFAULT 'blue',
  `dark_mode` tinyint(1) DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `pengaturan`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `phbs`
--

CREATE TABLE `phbs` (
  `id` int(11) NOT NULL,
  `no_kk` varchar(16) NOT NULL,
  `nama_kepala` varchar(150) DEFAULT NULL,
  `dusun` varchar(100) DEFAULT NULL,
  `rt` varchar(5) DEFAULT NULL,
  `rw` varchar(5) DEFAULT NULL,
  `cuci_tangan` enum('Ya','Tidak','Tidak Diketahui') DEFAULT 'Tidak Diketahui',
  `air_bersih` enum('Ya','Tidak','Tidak Diketahui') DEFAULT 'Tidak Diketahui',
  `menggunakan_jamban` enum('Ya','Tidak','Tidak Diketahui') DEFAULT 'Tidak Diketahui',
  `tidak_bab_sembarangan` enum('Ya','Tidak','Tidak Diketahui') DEFAULT 'Tidak Diketahui',
  `pengelolaan_sampah` enum('Ya','Tidak','Tidak Diketahui') DEFAULT 'Tidak Diketahui',
  `pengelolaan_limbah` enum('Ya','Tidak','Tidak Diketahui') DEFAULT 'Tidak Diketahui',
  `bebas_asap_rokok` enum('Ya','Tidak','Tidak Diketahui') DEFAULT 'Tidak Diketahui',
  `kebersihan_lingkungan` enum('Ya','Tidak','Tidak Diketahui') DEFAULT 'Tidak Diketahui',
  `skor_phbs` int(11) DEFAULT 0,
  `tanggal_pemantauan` date DEFAULT NULL,
  `petugas_id` int(11) DEFAULT NULL,
  `catatan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `remaja`
--

CREATE TABLE `remaja` (
  `id` int(11) NOT NULL,
  `nomor_peserta` varchar(20) NOT NULL,
  `id_penduduk_opensid` int(11) DEFAULT NULL,
  `status_integrasi` enum('belum','terhubung','manual') NOT NULL DEFAULT 'belum',
  `nik` varchar(16) DEFAULT NULL,
  `no_kk` varchar(16) DEFAULT NULL,
  `nama_lengkap` varchar(150) NOT NULL,
  `jenis_kelamin` enum('L','P') NOT NULL,
  `tanggal_lahir` date NOT NULL,
  `kategori` enum('Anak Sekolah','Remaja') DEFAULT 'Remaja',
  `sekolah` varchar(150) DEFAULT NULL,
  `kelas` varchar(50) DEFAULT NULL,
  `dusun` varchar(100) DEFAULT NULL,
  `rt` varchar(5) DEFAULT NULL,
  `rw` varchar(5) DEFAULT NULL,
  `alamat_lengkap` text DEFAULT NULL,
  `status_aktif` tinyint(1) DEFAULT 1,
  `catatan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `remaja`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `sanitasi`
--

CREATE TABLE `sanitasi` (
  `id` int(11) NOT NULL,
  `no_kk` varchar(16) NOT NULL,
  `nama_kepala` varchar(150) DEFAULT NULL,
  `nik_kepala` varchar(16) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `dusun` varchar(100) DEFAULT NULL,
  `rt` varchar(5) DEFAULT NULL,
  `rw` varchar(5) DEFAULT NULL,
  `kepemilikan_jamban` enum('Jamban Sendiri','Jamban Bersama','Jamban Umum','Tidak Memiliki') DEFAULT 'Tidak Memiliki',
  `jenis_jamban` enum('Leher Angsa','Cemplung','Plengsengan','Lainnya','Tidak Ada') DEFAULT 'Tidak Ada',
  `kondisi_jamban` enum('Baik','Rusak Ringan','Rusak Berat','Tidak Layak','Tidak Ada') DEFAULT 'Tidak Ada',
  `pembuangan_tinja` enum('Septic Tank','IPAL','Sungai','Kebun/Tanah Terbuka','Lainnya','Tidak Diketahui') DEFAULT 'Tidak Diketahui',
  `sumber_air` enum('PDAM','Sumur Gali','Sumur Bor','Mata Air','Sungai','Air Hujan','Air Isi Ulang','Lainnya') DEFAULT 'Sumur Gali',
  `kondisi_air` enum('Layak','Perlu Perlindungan','Tidak Layak','Tidak Diketahui') DEFAULT 'Tidak Diketahui',
  `pengelolaan_sampah` enum('Diangkut Petugas','Dibakar','Ditimbun','Bank Sampah','Dibuang ke Sungai','Dibuang Sembarangan','Lainnya') DEFAULT 'Dibakar',
  `tempat_sampah_tersedia` tinyint(1) DEFAULT 0,
  `tempat_sampah_tertutup` tinyint(1) DEFAULT 0,
  `pemilahan_sampah` tinyint(1) DEFAULT 0,
  `saluran_limbah` enum('Tersedia Tertutup','Tersedia Terbuka','Septic/IPAL','Dialirkan ke Tanah','Dialirkan ke Sungai','Tidak Tersedia') DEFAULT 'Tidak Tersedia',
  `lantai` enum('Keramik','Semen','Tanah','Kayu','Lainnya') DEFAULT 'Semen',
  `dinding` enum('Tembok','Kayu','Bambu','Lainnya') DEFAULT 'Tembok',
  `atap` enum('Genteng','Seng','Asbes','Rumbia','Lainnya') DEFAULT 'Genteng',
  `ventilasi` enum('Baik','Kurang','Tidak Ada') DEFAULT 'Baik',
  `pencahayaan` enum('Baik','Kurang','Tidak Ada') DEFAULT 'Baik',
  `kepadatan_hunian` enum('Sesuai','Padat','Sangat Padat') DEFAULT 'Sesuai',
  `kebersihan_rumah` enum('Bersih','Cukup','Kurang') DEFAULT 'Cukup',
  `tanggal_pemantauan` date DEFAULT NULL,
  `petugas_id` int(11) DEFAULT NULL,
  `catatan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `skrining_ptm`
--

CREATE TABLE `skrining_ptm` (
  `id` int(11) NOT NULL,
  `penduduk_nik` varchar(16) DEFAULT NULL,
  `no_kk` varchar(16) DEFAULT NULL,
  `nama` varchar(150) NOT NULL,
  `jenis_kelamin` enum('L','P') DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `tanggal_skrining` date NOT NULL,
  `berat_badan` decimal(5,2) DEFAULT NULL,
  `tinggi_badan` decimal(5,2) DEFAULT NULL,
  `imt` decimal(5,2) DEFAULT NULL,
  `lingkar_perut` decimal(5,2) DEFAULT NULL,
  `tekanan_darah` varchar(20) DEFAULT NULL,
  `gula_darah` decimal(6,2) DEFAULT NULL,
  `merokok` enum('Ya','Tidak','Pernah') DEFAULT 'Tidak',
  `aktivitas_fisik` enum('Cukup','Kurang','Tidak Ada') DEFAULT 'Cukup',
  `konsumsi_gula` enum('Berlebih','Cukup','Kurang') DEFAULT 'Cukup',
  `konsumsi_garam` enum('Berlebih','Cukup','Kurang') DEFAULT 'Cukup',
  `konsumsi_lemak` enum('Berlebih','Cukup','Kurang') DEFAULT 'Cukup',
  `riwayat_keluarga_ptm` text DEFAULT NULL,
  `hasil_skrining` enum('Normal','Risiko','Tinggi') DEFAULT 'Normal',
  `tindak_lanjut` text DEFAULT NULL,
  `petugas_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `usia_produktif`
--

CREATE TABLE `usia_produktif` (
  `id` int(11) NOT NULL,
  `nomor_peserta` varchar(20) NOT NULL,
  `id_penduduk_opensid` int(11) DEFAULT NULL,
  `status_integrasi` enum('belum','terhubung','manual') NOT NULL DEFAULT 'belum',
  `nik` varchar(16) DEFAULT NULL,
  `no_kk` varchar(16) DEFAULT NULL,
  `nama_lengkap` varchar(150) NOT NULL,
  `jenis_kelamin` enum('L','P') NOT NULL,
  `tanggal_lahir` date NOT NULL,
  `dusun` varchar(100) DEFAULT NULL,
  `rt` varchar(5) DEFAULT NULL,
  `rw` varchar(5) DEFAULT NULL,
  `alamat_lengkap` text DEFAULT NULL,
  `status_aktif` tinyint(1) DEFAULT 1,
  `catatan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `usia_produktif`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `vitamin`
--

CREATE TABLE `vitamin` (
  `id` int(11) NOT NULL,
  `balita_id` int(11) DEFAULT NULL,
  `ibu_hamil_id` int(11) DEFAULT NULL,
  `lansia_id` int(11) DEFAULT NULL,
  `jenis_vitamin` enum('Vitamin A (Merah)','Vitamin A (Biru)','Vitamin D','Vitamin C','Zinc','Tablet Fe','Asam Folat','Multivitamin','Lainnya') NOT NULL,
  `dosis` varchar(100) DEFAULT NULL,
  `tanggal_pemberian` date NOT NULL,
  `petugas_id` int(11) DEFAULT NULL,
  `catatan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Indeks untuk tabel yang dibuang
--

--
-- Indeks untuk tabel `artikel`
--
ALTER TABLE `artikel`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_artikel_slug` (`slug`),
  ADD KEY `idx_artikel_status` (`status`),
  ADD KEY `idx_artikel_penulis` (`penulis_id`),
  ADD KEY `idx_artikel_publish` (`tanggal_publish`);

--
-- Indeks untuk tabel `balita`
--
ALTER TABLE `balita`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nomor_peserta` (`nomor_peserta`),
  ADD UNIQUE KEY `nik_anak` (`nik_anak`),
  ADD KEY `idx_id_penduduk_opensid` (`id_penduduk_opensid`),
  ADD KEY `idx_status_integrasi` (`status_integrasi`),
  ADD KEY `idx_nik_anak` (`nik_anak`),
  ADD KEY `idx_no_kk` (`no_kk`);

--
-- Indeks untuk tabel `bayi`
--
ALTER TABLE `bayi`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nomor_peserta` (`nomor_peserta`),
  ADD KEY `idx_bayi_nik` (`nik_bayi`),
  ADD KEY `idx_bayi_nik_ibu` (`nik_ibu`),
  ADD KEY `idx_bayi_no_kk` (`no_kk`),
  ADD KEY `idx_bayi_opensid` (`id_penduduk_opensid`);

--
-- Indeks untuk tabel `ibu_hamil`
--
ALTER TABLE `ibu_hamil`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nomor_peserta` (`nomor_peserta`),
  ADD UNIQUE KEY `nik` (`nik`),
  ADD KEY `idx_id_penduduk_opensid` (`id_penduduk_opensid`),
  ADD KEY `idx_nik` (`nik`),
  ADD KEY `idx_ibu_no_kk` (`no_kk`),
  ADD KEY `idx_ibu_id_opensid` (`id_penduduk_opensid`);

--
-- Indeks untuk tabel `imunisasi`
--
ALTER TABLE `imunisasi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `balita_id` (`balita_id`),
  ADD KEY `petugas_id` (`petugas_id`);

--
-- Indeks untuk tabel `jadwal`
--
ALTER TABLE `jadwal`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `kader`
--
ALTER TABLE `kader`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `nik` (`nik`);

--
-- Indeks untuk tabel `kb`
--
ALTER TABLE `kb`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_kb_nik` (`nik`),
  ADD KEY `idx_kb_no_kk` (`no_kk`);

--
-- Indeks untuk tabel `kegiatan_posyandu`
--
ALTER TABLE `kegiatan_posyandu`
  ADD PRIMARY KEY (`id`),
  ADD KEY `petugas_id` (`petugas_id`),
  ADD KEY `idx_kegiatan_tanggal` (`tanggal_kegiatan`);

--
-- Indeks untuk tabel `kehadiran`
--
ALTER TABLE `kehadiran`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `keluarga`
--
ALTER TABLE `keluarga`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `no_kk` (`no_kk`);

--
-- Indeks untuk tabel `kunjungan_rumah`
--
ALTER TABLE `kunjungan_rumah`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kader_id` (`kader_id`),
  ADD KEY `idx_kr_tanggal` (`tanggal_kunjungan`),
  ADD KEY `idx_kr_no_kk` (`no_kk`),
  ADD KEY `idx_kr_prioritas` (`prioritas`);

--
-- Indeks untuk tabel `lansia`
--
ALTER TABLE `lansia`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nomor_peserta` (`nomor_peserta`),
  ADD UNIQUE KEY `nik` (`nik`),
  ADD KEY `idx_id_penduduk_opensid` (`id_penduduk_opensid`),
  ADD KEY `idx_nik` (`nik`),
  ADD KEY `idx_lansia_no_kk` (`no_kk`),
  ADD KEY `idx_lansia_opensid` (`id_penduduk_opensid`);

--
-- Indeks untuk tabel `log_integrasi_opensid`
--
ALTER TABLE `log_integrasi_opensid`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_modul_ref` (`modul`,`referensi_id`),
  ADD KEY `idx_id_penduduk` (`id_penduduk_opensid`);

--
-- Indeks untuk tabel `pemeriksaan_balita`
--
ALTER TABLE `pemeriksaan_balita`
  ADD PRIMARY KEY (`id`),
  ADD KEY `balita_id` (`balita_id`),
  ADD KEY `petugas_id` (`petugas_id`);

--
-- Indeks untuk tabel `pemeriksaan_bayi`
--
ALTER TABLE `pemeriksaan_bayi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bayi_id` (`bayi_id`),
  ADD KEY `petugas_id` (`petugas_id`),
  ADD KEY `idx_pbayi_tanggal` (`tanggal_pemeriksaan`);

--
-- Indeks untuk tabel `pemeriksaan_dewasa`
--
ALTER TABLE `pemeriksaan_dewasa`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usia_produktif_id` (`usia_produktif_id`),
  ADD KEY `petugas_id` (`petugas_id`),
  ADD KEY `idx_pdewasa_tanggal` (`tanggal_pemeriksaan`);

--
-- Indeks untuk tabel `pemeriksaan_ibu_hamil`
--
ALTER TABLE `pemeriksaan_ibu_hamil`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ibu_hamil_id` (`ibu_hamil_id`),
  ADD KEY `petugas_id` (`petugas_id`);

--
-- Indeks untuk tabel `pemeriksaan_lansia`
--
ALTER TABLE `pemeriksaan_lansia`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lansia_id` (`lansia_id`),
  ADD KEY `petugas_id` (`petugas_id`);

--
-- Indeks untuk tabel `pemeriksaan_remaja`
--
ALTER TABLE `pemeriksaan_remaja`
  ADD PRIMARY KEY (`id`),
  ADD KEY `remaja_id` (`remaja_id`),
  ADD KEY `petugas_id` (`petugas_id`),
  ADD KEY `idx_premaja_tanggal` (`tanggal_pemeriksaan`);

--
-- Indeks untuk tabel `penduduk`
--
ALTER TABLE `penduduk`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nik` (`nik`),
  ADD KEY `idx_no_kk` (`no_kk`),
  ADD KEY `idx_nik` (`nik`);

--
-- Indeks untuk tabel `pengaturan`
--
ALTER TABLE `pengaturan`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `phbs`
--
ALTER TABLE `phbs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_no_kk_phbs` (`no_kk`);

--
-- Indeks untuk tabel `remaja`
--
ALTER TABLE `remaja`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nomor_peserta` (`nomor_peserta`),
  ADD KEY `idx_remaja_nik` (`nik`),
  ADD KEY `idx_remaja_no_kk` (`no_kk`),
  ADD KEY `idx_remaja_opensid` (`id_penduduk_opensid`);

--
-- Indeks untuk tabel `sanitasi`
--
ALTER TABLE `sanitasi`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_no_kk` (`no_kk`);

--
-- Indeks untuk tabel `skrining_ptm`
--
ALTER TABLE `skrining_ptm`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ptm_nik` (`penduduk_nik`),
  ADD KEY `idx_ptm_tanggal` (`tanggal_skrining`);

--
-- Indeks untuk tabel `usia_produktif`
--
ALTER TABLE `usia_produktif`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nomor_peserta` (`nomor_peserta`),
  ADD KEY `idx_up_nik` (`nik`),
  ADD KEY `idx_up_no_kk` (`no_kk`),
  ADD KEY `idx_up_opensid` (`id_penduduk_opensid`);

--
-- Indeks untuk tabel `vitamin`
--
ALTER TABLE `vitamin`
  ADD PRIMARY KEY (`id`),
  ADD KEY `balita_id` (`balita_id`),
  ADD KEY `petugas_id` (`petugas_id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `artikel`
--
ALTER TABLE `artikel`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `balita`
--
ALTER TABLE `balita`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `bayi`
--
ALTER TABLE `bayi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `ibu_hamil`
--
ALTER TABLE `ibu_hamil`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `imunisasi`
--
ALTER TABLE `imunisasi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `jadwal`
--
ALTER TABLE `jadwal`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `kader`
--
ALTER TABLE `kader`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `kb`
--
ALTER TABLE `kb`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `kegiatan_posyandu`
--
ALTER TABLE `kegiatan_posyandu`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `kehadiran`
--
ALTER TABLE `kehadiran`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `keluarga`
--
ALTER TABLE `keluarga`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `kunjungan_rumah`
--
ALTER TABLE `kunjungan_rumah`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `lansia`
--
ALTER TABLE `lansia`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `log_integrasi_opensid`
--
ALTER TABLE `log_integrasi_opensid`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `pemeriksaan_balita`
--
ALTER TABLE `pemeriksaan_balita`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `pemeriksaan_bayi`
--
ALTER TABLE `pemeriksaan_bayi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `pemeriksaan_dewasa`
--
ALTER TABLE `pemeriksaan_dewasa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `pemeriksaan_ibu_hamil`
--
ALTER TABLE `pemeriksaan_ibu_hamil`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `pemeriksaan_lansia`
--
ALTER TABLE `pemeriksaan_lansia`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `pemeriksaan_remaja`
--
ALTER TABLE `pemeriksaan_remaja`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `penduduk`
--
ALTER TABLE `penduduk`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `pengaturan`
--
ALTER TABLE `pengaturan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `phbs`
--
ALTER TABLE `phbs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `remaja`
--
ALTER TABLE `remaja`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `sanitasi`
--
ALTER TABLE `sanitasi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `skrining_ptm`
--
ALTER TABLE `skrining_ptm`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `usia_produktif`
--
ALTER TABLE `usia_produktif`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `vitamin`
--
ALTER TABLE `vitamin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `artikel`
--
ALTER TABLE `artikel`
  ADD CONSTRAINT `fk_artikel_penulis` FOREIGN KEY (`penulis_id`) REFERENCES `kader` (`id`) ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `imunisasi`
--
ALTER TABLE `imunisasi`
  ADD CONSTRAINT `imunisasi_ibfk_1` FOREIGN KEY (`balita_id`) REFERENCES `balita` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `imunisasi_ibfk_2` FOREIGN KEY (`petugas_id`) REFERENCES `kader` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `kegiatan_posyandu`
--
ALTER TABLE `kegiatan_posyandu`
  ADD CONSTRAINT `kegiatan_posyandu_ibfk_1` FOREIGN KEY (`petugas_id`) REFERENCES `kader` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `kunjungan_rumah`
--
ALTER TABLE `kunjungan_rumah`
  ADD CONSTRAINT `kunjungan_rumah_ibfk_1` FOREIGN KEY (`kader_id`) REFERENCES `kader` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `pemeriksaan_balita`
--
ALTER TABLE `pemeriksaan_balita`
  ADD CONSTRAINT `pemeriksaan_balita_ibfk_1` FOREIGN KEY (`balita_id`) REFERENCES `balita` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pemeriksaan_balita_ibfk_2` FOREIGN KEY (`petugas_id`) REFERENCES `kader` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `pemeriksaan_bayi`
--
ALTER TABLE `pemeriksaan_bayi`
  ADD CONSTRAINT `pemeriksaan_bayi_ibfk_1` FOREIGN KEY (`bayi_id`) REFERENCES `bayi` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pemeriksaan_bayi_ibfk_2` FOREIGN KEY (`petugas_id`) REFERENCES `kader` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `pemeriksaan_dewasa`
--
ALTER TABLE `pemeriksaan_dewasa`
  ADD CONSTRAINT `pemeriksaan_dewasa_ibfk_1` FOREIGN KEY (`usia_produktif_id`) REFERENCES `usia_produktif` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pemeriksaan_dewasa_ibfk_2` FOREIGN KEY (`petugas_id`) REFERENCES `kader` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `pemeriksaan_ibu_hamil`
--
ALTER TABLE `pemeriksaan_ibu_hamil`
  ADD CONSTRAINT `pemeriksaan_ibu_hamil_ibfk_1` FOREIGN KEY (`ibu_hamil_id`) REFERENCES `ibu_hamil` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pemeriksaan_ibu_hamil_ibfk_2` FOREIGN KEY (`petugas_id`) REFERENCES `kader` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `pemeriksaan_lansia`
--
ALTER TABLE `pemeriksaan_lansia`
  ADD CONSTRAINT `pemeriksaan_lansia_ibfk_1` FOREIGN KEY (`lansia_id`) REFERENCES `lansia` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pemeriksaan_lansia_ibfk_2` FOREIGN KEY (`petugas_id`) REFERENCES `kader` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `pemeriksaan_remaja`
--
ALTER TABLE `pemeriksaan_remaja`
  ADD CONSTRAINT `pemeriksaan_remaja_ibfk_1` FOREIGN KEY (`remaja_id`) REFERENCES `remaja` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pemeriksaan_remaja_ibfk_2` FOREIGN KEY (`petugas_id`) REFERENCES `kader` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `vitamin`
--
ALTER TABLE `vitamin`
  ADD CONSTRAINT `vitamin_ibfk_1` FOREIGN KEY (`balita_id`) REFERENCES `balita` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `vitamin_ibfk_2` FOREIGN KEY (`petugas_id`) REFERENCES `kader` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- --------------------------------------------------------
-- SEED GENERIK (aman untuk publik, tanpa NIK/data warga)
-- --------------------------------------------------------
INSERT INTO `pengaturan` (`id`, `nama_aplikasi`, `nama_posyandu`, `nama_desa`, `kecamatan`, `kabupaten`, `provinsi`, `logo`, `favicon`, `nomor_kontak`, `email`, `warna_tema`, `dark_mode`, `updated_at`) VALUES
(1, 'SIMPOSYANDU', 'Posyandu Contoh', 'Desa Contoh', 'Kecamatan Contoh', 'Kabupaten Contoh', 'Provinsi Contoh', '', '', '', '', 'green', 0, NOW());


-- ============================================================
-- MIGRASI 001: tabel menu dinamis
-- ============================================================
-- Tabel menu dinamis untuk SIMPOSYANDU (tambahan, tidak mengganggu tabel lama)
-- Jalankan sekali di database yang sama

CREATE TABLE IF NOT EXISTS `menus` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) DEFAULT 0,
  `title` varchar(100) NOT NULL,
  `url` varchar(255) DEFAULT '#',
  `icon` varchar(50) DEFAULT 'fas fa-circle',
  `order_num` int(11) DEFAULT 0,
  `is_header` tinyint(1) DEFAULT 0 COMMENT '1 = nav-header',
  `is_active` tinyint(1) DEFAULT 1,
  `role` varchar(50) DEFAULT NULL COMMENT 'null = semua role',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`),
  KEY `order_num` (`order_num`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed menu default (sesuai sidebar lama)
INSERT INTO `menus` (`parent_id`, `title`, `url`, `icon`, `order_num`, `is_header`, `is_active`) VALUES
(0, 'UTAMA', '#', '', 10, 1, 1),
(0, 'Beranda', 'dashboard.php', 'fas fa-home', 11, 0, 1),
(0, 'Statistik', 'modules/statistik/index.php', 'fas fa-chart-pie', 12, 0, 1),
(0, 'DATA WARGA', '#', '', 20, 1, 1),
(0, 'Kependudukan', '#', 'fas fa-users', 21, 0, 1),
(5, 'Penduduk', 'modules/penduduk/index.php', 'far fa-circle', 211, 0, 1),
(5, 'Keluarga', 'modules/keluarga/index.php', 'far fa-circle', 212, 0, 1),
(0, 'POSYANDU', '#', '', 30, 1, 1),
(0, 'Ibu Hamil', '#', 'fas fa-female', 31, 0, 1),
(9, 'Data Ibu Hamil', 'modules/ibu_hamil/index.php', 'far fa-circle', 311, 0, 1),
(9, 'Pemeriksaan', 'modules/pemeriksaan_ibu_hamil/index.php', 'far fa-circle', 312, 0, 1),
(0, 'Bayi', '#', 'fas fa-baby', 32, 0, 1),
(12, 'Data Bayi', 'modules/bayi/index.php', 'far fa-circle', 321, 0, 1),
(12, 'Pemeriksaan', 'modules/pemeriksaan_bayi/index.php', 'far fa-circle', 322, 0, 1),
(0, 'Balita', '#', 'fas fa-child', 33, 0, 1),
(15, 'Data Balita', 'modules/balita/index.php', 'far fa-circle', 331, 0, 1),
(15, 'Pemeriksaan', 'modules/pemeriksaan_balita/index.php', 'far fa-circle', 332, 0, 1),
(15, 'Imunisasi', 'modules/imunisasi/index.php', 'far fa-circle', 333, 0, 1),
(15, 'Vitamin', 'modules/vitamin/index.php', 'far fa-circle', 334, 0, 1),
(0, 'Remaja', '#', 'fas fa-user-graduate', 34, 0, 1),
(20, 'Data Remaja', 'modules/remaja/index.php', 'far fa-circle', 341, 0, 1),
(20, 'Pemeriksaan', 'modules/pemeriksaan_remaja/index.php', 'far fa-circle', 342, 0, 1),
(0, 'Lansia', '#', 'fas fa-user-friends', 35, 0, 1),
(23, 'Data Lansia', 'modules/lansia/index.php', 'far fa-circle', 351, 0, 1),
(23, 'Pemeriksaan', 'modules/pemeriksaan_lansia/index.php', 'far fa-circle', 352, 0, 1),
(0, 'KEGIATAN', '#', '', 40, 1, 1),
(0, 'Kegiatan Posyandu', 'modules/kegiatan_posyandu/index.php', 'fas fa-calendar-check', 41, 0, 1),
(0, 'Jadwal', 'modules/jadwal/index.php', 'fas fa-calendar-alt', 42, 0, 1),
(0, 'Kunjungan Rumah', 'modules/kunjungan_rumah/index.php', 'fas fa-home', 43, 0, 1),
(0, 'PENGATURAN', '#', '', 90, 1, 1),
(0, 'Kader', 'modules/kader/index.php', 'fas fa-user-nurse', 91, 0, 1),
(0, 'Pengaturan', 'modules/pengaturan/index.php', 'fas fa-cog', 92, 0, 1),
(0, 'Artikel', 'modules/artikel/index.php', 'fas fa-newspaper', 93, 0, 1);


-- ============================================================
-- MIGRASI 002: kolom reCAPTCHA di pengaturan
-- ============================================================
-- Tambah kolom Google reCAPTCHA di tabel pengaturan
-- Bisa dijalankan manual di phpMyAdmin jika auto-migrate gagal

ALTER TABLE `pengaturan`
  ADD COLUMN IF NOT EXISTS `recaptcha_site_key` VARCHAR(255) DEFAULT '' AFTER `dark_mode`,
  ADD COLUMN IF NOT EXISTS `recaptcha_secret_key` VARCHAR(255) DEFAULT '' AFTER `recaptcha_site_key`;

-- Untuk MySQL/MariaDB lama yang tidak support IF NOT EXISTS:
-- ALTER TABLE `pengaturan` ADD COLUMN `recaptcha_site_key` VARCHAR(255) DEFAULT '';
-- ALTER TABLE `pengaturan` ADD COLUMN `recaptcha_secret_key` VARCHAR(255) DEFAULT '';


-- ============================================================
-- MIGRASI 003: hak akses menu per kader
-- ============================================================
-- Hak akses menu per kader/user
-- Jalankan sekali di database

CREATE TABLE IF NOT EXISTS `kader_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kader_id` int(11) NOT NULL,
  `menu_key` varchar(80) NOT NULL COMMENT 'slug unik menu, mis: balita, laporan',
  `can_view` tinyint(1) NOT NULL DEFAULT 0,
  `can_edit` tinyint(1) NOT NULL DEFAULT 0,
  `can_delete` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `kader_menu` (`kader_id`,`menu_key`),
  KEY `kader_id` (`kader_id`),
  KEY `menu_key` (`menu_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Catatan: Admin selalu punya semua hak (bypass di kode).
-- Untuk user lama, jalankan query di bawah jika ingin beri akses default (opsional):
-- INSERT IGNORE INTO kader_permissions (kader_id, menu_key, can_view, can_edit, can_delete)
-- SELECT id, 'dashboard', 1, 0, 0 FROM kader WHERE role != 'admin';


-- ============================================================
-- MIGRASI 004: tabel anak TK + pemeriksaan
-- ============================================================
-- POSYANDU DIGITAL DESA | Versi 1.0.0 | Zainudin Larau
-- Tabel Anak TK (4-6 tahun) + Pemeriksaan Anak TK
-- Migrasi 004: Tabel Anak TK (4-6 tahun) + Pemeriksaan Anak TK
-- Kloning struktur tabel remaja & pemeriksaan_remaja. Tampilan & inputan sama.
CREATE TABLE IF NOT EXISTS `anak_tk` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nomor_peserta` varchar(20) NOT NULL,
  `id_penduduk_opensid` int(11) DEFAULT NULL,
  `status_integrasi` enum('belum','terhubung','manual') NOT NULL DEFAULT 'belum',
  `nik` varchar(16) DEFAULT NULL,
  `no_kk` varchar(16) DEFAULT NULL,
  `nama_lengkap` varchar(150) NOT NULL,
  `jenis_kelamin` enum('L','P') NOT NULL,
  `tanggal_lahir` date NOT NULL,
  `kategori` enum('Anak TK','TK A','TK B') DEFAULT 'Anak TK',
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
  KEY `idx_tk_lahir` (`tanggal_lahir`),
  KEY `idx_tk_opensid` (`id_penduduk_opensid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `pemeriksaan_anak_tk` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `anak_tk_id` int(11) NOT NULL,
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
  KEY `idx_ptk_anak` (`anak_tk_id`),
  KEY `idx_ptk_tgl` (`tanggal_pemeriksaan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

