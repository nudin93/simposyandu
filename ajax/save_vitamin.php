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

error_reporting(0);
ini_set('display_errors', '0');
require_once __DIR__ . '/../config/init.php';
requireLogin();

// CSRF Protection
$csrf = $_POST["csrf_token"] ?? $_GET["csrf_token"] ?? "";
if (!verifyCsrf($csrf)) {
    jsonResponse(false, "Token keamanan tidak valid. Silakan refresh halaman lalu coba lagi.");
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Method not allowed');
}

$balita_id = (int)($_POST['balita_id'] ?? 0);
$jenis = trim($_POST['jenis_vitamin'] ?? '');
$tgl = trim($_POST['tanggal_pemberian'] ?? '');
$dosis = trim($_POST['dosis'] ?? '');
$catatan = trim($_POST['catatan'] ?? '');
$petugas_id = (int)($_POST['petugas_id'] ?? 0);

if ($balita_id <= 0) {
    jsonResponse(false, 'Pilih balita terlebih dahulu (cari nama lalu klik hasilnya)');
}
if ($jenis === '') {
    jsonResponse(false, 'Jenis vitamin wajib diisi');
}
if ($tgl === '') {
    jsonResponse(false, 'Tanggal pemberian wajib diisi');
}

$cek = fetchOne("SELECT id, nama_lengkap FROM balita WHERE id=$balita_id LIMIT 1");
if (!$cek) {
    jsonResponse(false, 'Data balita tidak ditemukan. Daftarkan dulu dari OpenSID.');
}

$petugas_sql = $petugas_id > 0 ? $petugas_id : 'NULL';
$sql = "INSERT INTO vitamin (balita_id, jenis_vitamin, dosis, tanggal_pemberian, petugas_id, catatan)
VALUES (
  $balita_id,
  '" . escape($jenis) . "',
  '" . escape($dosis) . "',
  '" . escape($tgl) . "',
  $petugas_sql,
  '" . escape($catatan) . "'
)";

if (query($sql)) {
    $newId = function_exists('lastInsertId') ? lastInsertId() : 0;
    jsonResponse(true, 'Data vitamin berhasil disimpan!', [
        'id' => (int)$newId,
        'balita_id' => $balita_id,
        'nama' => $cek['nama_lengkap'] ?? '',
    ]);
}
jsonResponse(false, 'Gagal menyimpan data vitamin');
