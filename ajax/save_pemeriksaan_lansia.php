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
$lansia_id = (int)($_POST['lansia_id'] ?? 0);
$tgl = escape($_POST['tanggal_pemeriksaan'] ?? '');
if (!$lansia_id || !$tgl) jsonResponse(false, 'Field wajib belum diisi');
$petugas_id = (int)($_POST['petugas_id'] ?? 0) ?: 'NULL';
$sql = "INSERT INTO pemeriksaan_lansia (lansia_id,tanggal_pemeriksaan,petugas_id,berat_badan,tinggi_badan,tekanan_darah,gula_darah,kolesterol,asam_urat,suhu_tubuh,denyut_nadi,keluhan,obat_diberikan,vitamin,catatan_petugas,jadwal_kontrol,risiko_hipertensi,risiko_diabetes,risiko_kolesterol)
VALUES ($lansia_id,'$tgl',$petugas_id,'".escape($_POST['berat_badan']??0)."','".escape($_POST['tinggi_badan']??0)."','".escape($_POST['tekanan_darah']??'')."','".escape($_POST['gula_darah']??0)."','".escape($_POST['kolesterol']??0)."','".escape($_POST['asam_urat']??0)."','".escape($_POST['suhu_tubuh']??0)."',".((int)($_POST['denyut_nadi']??0)).",'".escape($_POST['keluhan']??'')."','".escape($_POST['obat_diberikan']??'')."','".escape($_POST['vitamin']??'')."','".escape($_POST['catatan_petugas']??'')."','".escape($_POST['jadwal_kontrol']??'')."',".((int)($_POST['risiko_hipertensi']??0)).",".((int)($_POST['risiko_diabetes']??0)).",".((int)($_POST['risiko_kolesterol']??0)).")";
if (query($sql)) jsonResponse(true, 'Pemeriksaan lansia berhasil disimpan!');
else jsonResponse(false, 'Gagal menyimpan data');
