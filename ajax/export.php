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
$type = db_enum($_GET['type'] ?? '', ['balita','ibu_hamil'], '');
if ($type === 'balita') {
    $data = fetchAll("SELECT nomor_peserta, nama_lengkap, jenis_kelamin, tanggal_lahir, nama_ibu, nama_ayah, no_hp, desa, status_imunisasi, bpjs_kis FROM balita ORDER BY nama_lengkap");
    $headers = ['No. Peserta','Nama Anak','L/P','Tanggal Lahir','Nama Ibu','Nama Ayah','No HP','Desa','Status Imunisasi','BPJS/KIS'];
    $filename = 'data_balita_' . date('Ymd') . '.csv';
} elseif ($type === 'ibu_hamil') {
    $data = fetchAll("SELECT nomor_peserta, nama, tanggal_lahir, hpht, hpl, kehamilan_ke, desa, no_hp FROM ibu_hamil ORDER BY nama");
    $headers = ['No. Peserta','Nama','Tgl Lahir','HPHT','HPL','Kehamilan Ke','Desa','No HP'];
    $filename = 'data_ibu_hamil_' . date('Ymd') . '.csv';
} else {
    jsonResponse(false, 'Tipe tidak valid');
}
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
$out = fopen('php://output', 'w');
fwrite($out, "\xEF\xBB\xBF");
fputcsv($out, csv_row_guard($headers));
foreach ($data as $row) fputcsv($out, csv_row_guard($row));
fclose($out);
exit;
