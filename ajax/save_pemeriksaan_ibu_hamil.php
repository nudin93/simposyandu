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

$ibu_id = (int)($_POST['ibu_hamil_id'] ?? 0);
$tgl = escape($_POST['tanggal_pemeriksaan'] ?? '');
if (!$ibu_id || !$tgl) jsonResponse(false, 'Field wajib belum diisi');

$petugas_id = (int)($_POST['petugas_id'] ?? 0);
$petugas_sql = $petugas_id ? $petugas_id : 'NULL';

// Hitung usia kandungan otomatis dari HPHT ibu + tanggal pemeriksaan
$ibu = fetchOne("SELECT hpht FROM ibu_hamil WHERE id=$ibu_id");
$hpht = $ibu['hpht'] ?? '';
$usia_auto = hitungUsiaKandungan($hpht, $tgl);
// Jika form mengirim nilai, tetap prioritaskan hasil hitung otomatis agar konsisten
$usia_kandungan = $usia_auto > 0 ? $usia_auto : (int)($_POST['usia_kandungan'] ?? 0);

$sql = "INSERT INTO pemeriksaan_ibu_hamil (
  ibu_hamil_id, tanggal_pemeriksaan, petugas_id, usia_kandungan, berat_badan, tekanan_darah,
  tinggi_fundus, djj, posisi_janin, gerakan_janin, lila, keluhan, riwayat_penyakit,
  tablet_fe, vitamin, imunisasi_tt, pemeriksaan_lab, catatan_bidan, jadwal_kontrol,
  risiko_kek, risiko_anemia, risiko_hipertensi, risiko_preeklamsia, status_risiko
) VALUES (
  $ibu_id, '$tgl', $petugas_sql, $usia_kandungan,
  '".escape($_POST['berat_badan']??0)."',
  '".escape($_POST['tekanan_darah']??'')."',
  '".escape($_POST['tinggi_fundus']??0)."',
  '".escape($_POST['djj']??0)."',
  '".escape($_POST['posisi_janin']??'')."',
  '".escape($_POST['gerakan_janin']??'Aktif')."',
  '".escape($_POST['lila']??0)."',
  '".escape($_POST['keluhan']??'')."',
  '".escape($_POST['riwayat_penyakit']??'')."',
  ".((int)($_POST['tablet_fe']??0)).",
  '".escape($_POST['vitamin']??'')."',
  '".escape($_POST['imunisasi_tt']??'Tidak')."',
  '".escape($_POST['pemeriksaan_lab']??'')."',
  '".escape($_POST['catatan_bidan']??'')."',
  '".escape($_POST['jadwal_kontrol']??'')."',
  ".((int)($_POST['risiko_kek']??0)).",
  ".((int)($_POST['risiko_anemia']??0)).",
  ".((int)($_POST['risiko_hipertensi']??0)).",
  0,
  '".escape($_POST['status_risiko']??'Normal')."'
)";

if (query($sql)) {
    // Update usia_kehamilan di master ibu_hamil agar selalu sinkron
    if ($usia_kandungan > 0) {
        query("UPDATE ibu_hamil SET usia_kehamilan=$usia_kandungan WHERE id=$ibu_id");
    }
    jsonResponse(true, 'Data ANC berhasil disimpan! Usia kehamilan: '.$usia_kandungan.' minggu');
} else {
    jsonResponse(false, 'Gagal menyimpan data ANC');
}
