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
$nama = preg_replace('/[^a-z0-9_]/', '_', strtolower($_POST['nama_backup'] ?? 'backup_' . date('Y-m-d_His')));
$filename = $nama . '.sql';
$backup_dir = __DIR__ . '/../uploads/backup/';
if (!is_dir($backup_dir)) mkdir($backup_dir, 0755, true);
$output = "-- SIMPOSYANDU Database Backup\n-- Date: " . date('Y-m-d H:i:s') . "\n-- Database: " . DB_NAME . "\n\n";
$output .= "SET FOREIGN_KEY_CHECKS=0;\n\n";
$tables = fetchAll("SHOW TABLES");
foreach ($tables as $table) {
    $tname = array_values($table)[0];
    $create = fetchOne("SHOW CREATE TABLE `$tname`");
    $output .= "DROP TABLE IF EXISTS `$tname`;\n";
    $output .= array_values($create)[1] . ";\n\n";
    $rows = fetchAll("SELECT * FROM `$tname`");
    foreach ($rows as $row) {
        $vals = array_map(fn($v) => $v === null ? 'NULL' : "'".addslashes($v)."'", $row);
        $output .= "INSERT INTO `$tname` VALUES (" . implode(',', $vals) . ");\n";
    }
    $output .= "\n";
}
$output .= "SET FOREIGN_KEY_CHECKS=1;\n";
file_put_contents($backup_dir . $filename, $output);
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . strlen($output));
echo $output;
exit;
