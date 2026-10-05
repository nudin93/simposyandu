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

require_once __DIR__ . '/../config/init.php';
requireLogin();

// CSRF Protection
$csrf = $_POST["csrf_token"] ?? $_GET["csrf_token"] ?? "";
if (!verifyCsrf($csrf)) {
    jsonResponse(false, "Token keamanan tidak valid. Silakan refresh halaman lalu coba lagi.");
}
if (!isAdmin()) jsonResponse(false, 'Akses ditolak');
if (empty($_FILES['sql_file']['name'])) jsonResponse(false, 'File SQL tidak dipilih');
$tmp = $_FILES['sql_file']['tmp_name'];
$ext = strtolower(pathinfo($_FILES['sql_file']['name'], PATHINFO_EXTENSION));
if ($ext !== 'sql') jsonResponse(false, 'Hanya file .sql yang diizinkan');
$sql_content = file_get_contents($tmp);
if (!$sql_content) jsonResponse(false, 'File SQL kosong atau tidak dapat dibaca');
global $conn;
$conn->multi_query($sql_content);
do { if ($res = $conn->store_result()) $res->free(); } while ($conn->more_results() && $conn->next_result());
if ($conn->errno) jsonResponse(false, 'Restore gagal: ' . $conn->error);
jsonResponse(true, 'Database berhasil di-restore!');
