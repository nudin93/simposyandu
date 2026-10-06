<?php
/**
 * POSYANDU DIGITAL DESA
 * Versi: 1.0.0
 * Dikembangkan oleh: Zainudin Larau
 *
 * Tujuan:
 * Aplikasi digital untuk membantu pendataan,
 * pelayanan, pemantauan, dan pelaporan kegiatan Posyandu Desa.
 */
/**
 * ==========================================================
 * SIMPOSYANDU - Sistem Informasi Posyandu Terintegrasi
 * ==========================================================
 *
 * Pengembang  : Zainudin Larau
 * Tahun       : 2026
 *
 * Deskripsi:
 * Aplikasi ini dikembangkan untuk membantu pengelolaan
 * data Posyandu secara digital, meliputi data balita,
 * ibu hamil, pemeriksaan kesehatan, pertumbuhan anak,
 * statistik, dan pelayanan masyarakat.
 *
 * Ketentuan:
 * Aplikasi ini merupakan karya pengembang dan
 * TIDAK DIPERJUALBELIKAN.
 *
 * Setiap penggunaan, penggandaan, perubahan kode,
 * atau pendistribusian aplikasi wajib mendapatkan
 * izin dari pemilik dan pengembang.
 *
 * Hak Cipta:
 * © 2026 Zainudin Larau
 * Semua Hak Dilindungi.
 *
 * ==========================================================
 */

/**
 * SIMPOSYANDU - Credentials Example
 * 
 * Cara pakai:
 * 1. Salin file ini menjadi credentials.php
 * 2. Isi nilai di bawah dengan data database asli
 * 3. JANGAN pernah commit credentials.php ke Git atau bagikan ke publik
 */

// ========== DATABASE SIMPOsyandu (aplikasi utama) ==========
define('DB_HOST', 'localhost');
define('DB_USER', 'username_database_anda');
define('DB_PASS', 'password_database_anda');
define('DB_NAME', 'nama_database_simposyandu');

// ========== DATABASE OpenSID (sumber data penduduk - read only) ==========
define('OPENSID_DB_HOST', 'localhost');
define('OPENSID_DB_USER', 'username_opensid');
define('OPENSID_DB_PASS', 'password_opensid');
define('OPENSID_DB_NAME', 'nama_database_opensid');

// ========== Google reCAPTCHA v2 (opsional, untuk halaman login) ==========
// Daftar di: https://www.google.com/recaptcha/admin
// Pilih reCAPTCHA v2 → "I'm not a robot" Checkbox
// Domain: domain-anda.com
define('RECAPTCHA_SITE_KEY', '');   // Site Key (publik)
define('RECAPTCHA_SECRET_KEY', ''); // Secret Key (rahasia)
