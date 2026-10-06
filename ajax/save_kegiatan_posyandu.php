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

require_once __DIR__.'/../config/init.php'; requireLogin();

// CSRF Protection
$csrf = $_POST["csrf_token"] ?? $_GET["csrf_token"] ?? "";
if (!verifyCsrf($csrf)) {
    jsonResponse(false, "Token keamanan tidak valid. Silakan refresh halaman lalu coba lagi.");
}
header('Content-Type: application/json; charset=utf-8');
$tgl=$_POST['tanggal_kegiatan']??''; $nama=trim($_POST['nama_posyandu']??'');
if(!$tgl||!$nama){echo json_encode(['success'=>false,'message'=>'Tanggal & nama posyandu wajib']);exit;}
$petugas=currentUser()['id']??'NULL';
$f=function($k){return isset($_POST[$k])&&$_POST[$k]!==''?"'".escape($_POST[$k])."'":'NULL';};
$sql=sprintf("INSERT INTO kegiatan_posyandu (tanggal_kegiatan,nama_posyandu,lokasi,kader_bertugas,jumlah_sasaran,jumlah_hadir,langkah1_pendaftaran,langkah2_pengukuran,langkah3_pencatatan,langkah4_pelayanan,langkah5_penyuluhan,materi_penyuluhan,keterangan,status,petugas_id)
VALUES ('%s','%s',%s,%s,%d,%d,%s,%s,%s,%s,%s,%s,%s,'%s',%s)",
 escape($tgl),escape($nama),$f('lokasi'),$f('kader_bertugas'),
 (int)($_POST['jumlah_sasaran']??0),(int)($_POST['jumlah_hadir']??0),
 $f('langkah1_pendaftaran'),$f('langkah2_pengukuran'),$f('langkah3_pencatatan'),$f('langkah4_pelayanan'),$f('langkah5_penyuluhan'),
 $f('materi_penyuluhan'),$f('keterangan'),escape($_POST['status']??'Selesai'),$petugas);
echo json_encode(query($sql)?['success'=>true,'message'=>'Kegiatan Posyandu berhasil disimpan!']:['success'=>false,'message'=>'Gagal. Jalankan migrasi ILP.']);
