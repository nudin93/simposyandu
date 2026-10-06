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
if (!$id) jsonResponse(false, 'ID tidak valid');
$nama = escape($_POST['nama_lengkap'] ?? '');
if (!$nama) jsonResponse(false, 'Nama wajib diisi');
$sets = "nama_lengkap='$nama',nik_anak='".escape($_POST['nik_anak']??'')."',jenis_kelamin='".escape($_POST['jenis_kelamin']??'')."',tanggal_lahir='".escape($_POST['tanggal_lahir']??'')."',nama_ibu='".escape($_POST['nama_ibu']??'')."',nama_ayah='".escape($_POST['nama_ayah']??'')."',no_hp='".escape($_POST['no_hp']??'')."',desa='".escape($_POST['desa']??'')."',rt='".escape($_POST['rt']??'')."',rw='".escape($_POST['rw']??'')."',status_imunisasi='".escape($_POST['status_imunisasi']??'')."',riwayat_alergi='".escape($_POST['riwayat_alergi']??'')."',riwayat_penyakit='".escape($_POST['riwayat_penyakit']??'')."',bpjs_kis='".escape($_POST['bpjs_kis']??'')."',status_aktif=".((int)isset($_POST['status_aktif']));
if (!empty($_FILES['foto_anak']['name'])) { $up=uploadFile($_FILES['foto_anak'],'balita'); if($up['success']) $sets.=",foto_anak='{$up['filename']}'"; }
if (query("UPDATE balita SET $sets WHERE id=$id")) jsonResponse(true, 'Data balita berhasil diperbarui!');
else jsonResponse(false, 'Gagal memperbarui data');
