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
header('Content-Type: application/json; charset=utf-8');
$bayi_id = (int)($_POST['bayi_id'] ?? 0);
$tgl = $_POST['tanggal_pemeriksaan'] ?? '';
if (!$bayi_id || !$tgl) { echo json_encode(['success'=>false,'message'=>'Data tidak lengkap']); exit; }
$bb = $_POST['berat_badan'] !== '' ? (float)$_POST['berat_badan'] : null;
$pb = $_POST['panjang_badan'] !== '' ? (float)$_POST['panjang_badan'] : null;
$umur = (int)($_POST['umur_bulan'] ?? 0);
$ant = function_exists('hitungAntropometri') ? hitungAntropometri($umur, $bb ?: 0, $pb ?: 0) : ['status_gizi'=>'Normal','risiko_stunting'=>'Tidak','bb_u'=>null,'tb_u'=>null];
$petugas = currentUser()['id'] ?? 'NULL';
$sql = sprintf(
  "INSERT INTO pemeriksaan_bayi (bayi_id,tanggal_pemeriksaan,umur_bulan,berat_badan,panjang_badan,lingkar_kepala,lila,asi_eksklusif,imunisasi_diberikan,vitamin_diberikan,keluhan,catatan,status_gizi,bb_u,pb_u,risiko_stunting,petugas_id)
   VALUES (%d,'%s',%d,%s,%s,%s,%s,'%s',%s,%s,%s,%s,'%s',%s,%s,'%s',%s)",
  $bayi_id, escape($tgl), $umur,
  $bb !== null ? $bb : 'NULL', $pb !== null ? $pb : 'NULL',
  $_POST['lingkar_kepala'] !== '' ? (float)$_POST['lingkar_kepala'] : 'NULL',
  $_POST['lila'] !== '' ? (float)$_POST['lila'] : 'NULL',
  escape($_POST['asi_eksklusif'] ?? 'Ya'),
  $_POST['imunisasi_diberikan'] ? "'".escape($_POST['imunisasi_diberikan'])."'" : 'NULL',
  $_POST['vitamin_diberikan'] ? "'".escape($_POST['vitamin_diberikan'])."'" : 'NULL',
  $_POST['keluhan'] ? "'".escape($_POST['keluhan'])."'" : 'NULL',
  $_POST['catatan'] ? "'".escape($_POST['catatan'])."'" : 'NULL',
  escape($ant['status_gizi'] ?? 'Normal'),
  $ant['bb_u'] ? "'".escape($ant['bb_u'])."'" : 'NULL',
  $ant['tb_u'] ? "'".escape($ant['tb_u'])."'" : 'NULL',
  escape($ant['risiko_stunting'] ?? 'Tidak'),
  $petugas
);
if (query($sql)) echo json_encode(['success'=>true,'message'=>'Pemeriksaan bayi berhasil disimpan!']);
else echo json_encode(['success'=>false,'message'=>'Gagal simpan. Cek migrasi tabel.']);
