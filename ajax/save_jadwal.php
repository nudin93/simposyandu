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
$id = (int)($_POST['id'] ?? 0);
$nama = escape($_POST['nama_kegiatan'] ?? '');
$tgl = escape($_POST['tanggal_kegiatan'] ?? '');
$jam = escape($_POST['jam'] ?? '');
if (!$nama || !$tgl || !$jam) jsonResponse(false, 'Field wajib belum diisi');
$lokasi = escape($_POST['lokasi']??''); $pj = escape($_POST['penanggung_jawab']??'');
$jenis = escape($_POST['jenis_kegiatan']??''); $ket = escape($_POST['keterangan']??'');
$status = escape($_POST['status']??'Terjadwal');
if ($id) {
    $sql = "UPDATE jadwal SET nama_kegiatan='$nama',tanggal_kegiatan='$tgl',jam='$jam',lokasi='$lokasi',penanggung_jawab='$pj',jenis_kegiatan='$jenis',keterangan='$ket',status='$status' WHERE id=$id";
    if (query($sql)) jsonResponse(true, 'Jadwal berhasil diperbarui!');
} else {
    $sql = "INSERT INTO jadwal (nama_kegiatan,tanggal_kegiatan,jam,lokasi,penanggung_jawab,jenis_kegiatan,keterangan,status)
    VALUES ('$nama','$tgl','$jam','$lokasi','$pj','$jenis','$ket','$status')";
    if (query($sql)) jsonResponse(true, 'Jadwal berhasil disimpan!');
}
jsonResponse(false, 'Gagal menyimpan jadwal');
