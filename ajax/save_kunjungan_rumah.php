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
$tgl=$_POST['tanggal_kunjungan']??''; $nama=trim($_POST['nama_keluarga']??'');
if(!$tgl||!$nama){echo json_encode(['success'=>false,'message'=>'Tanggal & nama keluarga wajib']);exit;}
$kader=currentUser()['id']??'NULL';
$f=function($k){return isset($_POST[$k])&&$_POST[$k]!==''?"'".escape($_POST[$k])."'":'NULL';};
$sql=sprintf("INSERT INTO kunjungan_rumah (tanggal_kunjungan,no_kk,nama_keluarga,alamat,dusun,rt,rw,prioritas,masalah_ditemukan,tindakan,rujukan,status_tindak_lanjut,kader_id,catatan)
VALUES ('%s',%s,'%s',%s,%s,%s,%s,'%s',%s,%s,%s,'%s',%s,%s)",
 escape($tgl),$f('no_kk'),escape($nama),$f('alamat'),$f('dusun'),$f('rt'),$f('rw'),
 escape($_POST['prioritas']??'Lainnya'),$f('masalah_ditemukan'),$f('tindakan'),$f('rujukan'),
 escape($_POST['status_tindak_lanjut']??'Belum'),$kader,$f('catatan'));
echo json_encode(query($sql)?['success'=>true,'message'=>'Kunjungan rumah berhasil disimpan!']:['success'=>false,'message'=>'Gagal. Jalankan migrasi ILP.']);
