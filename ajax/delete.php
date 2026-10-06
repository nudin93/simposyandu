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

header('Content-Type: application/json');

$type = escape($_GET['type'] ?? $_POST['type'] ?? '');
$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);

if (!$type || !$id) {
    jsonResponse(false, 'Parameter tidak valid');
}

$allowed = [
    'balita', 'ibu_hamil', 'lansia',
    'pemeriksaan_balita', 'pemeriksaan_ibu_hamil', 'pemeriksaan_lansia',
    'imunisasi', 'vitamin', 'jadwal', 'kader',
    'penduduk', 'keluarga', 'kb', 'sanitasi', 'phbs', 'kehadiran',
    // ILP
    'bayi', 'pemeriksaan_bayi',
    'remaja', 'pemeriksaan_remaja',
    'anak_tk', 'pemeriksaan_anak_tk',
    'usia_produktif', 'pemeriksaan_dewasa',
    'kegiatan_posyandu', 'kunjungan_rumah', 'skrining_ptm'
];

if (!in_array($type, $allowed)) {
    jsonResponse(false, 'Tipe tidak valid');
}

if ($type === 'kader' && !isAdmin()) {
    jsonResponse(false, 'Akses ditolak');
}

$sessionId = (int)($_SESSION['kader_id'] ?? $_SESSION['user_id'] ?? 0);
if ($type === 'kader' && $id === $sessionId) {
    jsonResponse(false, 'Tidak dapat menghapus akun sendiri');
}

// ---- Special handling ----
// Anak TK: hapus pemeriksaan terkait terlebih dahulu agar tidak meninggalkan
// data pemeriksaan yatim (orphan).
if ($type === 'anak_tk') {
    $anak = fetchOne("SELECT id, nama_lengkap FROM anak_tk WHERE id=$id LIMIT 1");
    if (!$anak) {
        jsonResponse(false, 'Data anak TK tidak ditemukan');
    }

    // Hapus seluruh riwayat pemeriksaan anak ini, lalu data anak TK.
    query("DELETE FROM pemeriksaan_anak_tk WHERE anak_tk_id=$id");
    $result = query("DELETE FROM anak_tk WHERE id=$id");
    if ($result) {
        jsonResponse(true, 'Data anak TK dan riwayat pemeriksaannya berhasil dihapus');
    }
    jsonResponse(false, 'Gagal menghapus data anak TK');
}

if ($type === 'keluarga') {
    $kk = fetchOne("SELECT no_kk FROM keluarga WHERE id=$id");
    if (!$kk) jsonResponse(false, 'Data keluarga tidak ditemukan');
    $no_kk = escape($kk['no_kk']);
    // Hapus data terkait KK (sanitasi, phbs) lalu keluarga
    // Anggota penduduk TIDAK dihapus otomatis (hanya lepas no_kk optional) — hapus hanya record keluarga
    query("DELETE FROM sanitasi WHERE no_kk='$no_kk'");
    query("DELETE FROM phbs WHERE no_kk='$no_kk'");
    // Lepas anggota dari KK (tetap di penduduk) agar tidak orphan confusing
    // Atau hapus anggota? User typically expects delete KK carefully.
    // Soft: set no_kk anggota tetap, hanya hapus record keluarga.
    $result = query("DELETE FROM keluarga WHERE id=$id");
    if ($result) {
        jsonResponse(true, 'Data keluarga berhasil dihapus (sanitasi/PHBS terkait ikut dihapus). Anggota tetap di Data Penduduk.');
    }
    jsonResponse(false, 'Gagal menghapus keluarga');
}

if ($type === 'penduduk') {
    $p = fetchOne("SELECT nik, no_kk FROM penduduk WHERE id=$id");
    if (!$p) jsonResponse(false, 'Data penduduk tidak ditemukan');
    $result = query("DELETE FROM penduduk WHERE id=$id");
    if ($result) {
        // Update jumlah anggota KK jika ada
        if (!empty($p['no_kk'])) {
            $no_kk = escape($p['no_kk']);
            query("UPDATE keluarga SET jumlah_anggota=(SELECT COUNT(*) FROM penduduk WHERE no_kk='$no_kk' AND status_aktif=1) WHERE no_kk='$no_kk'");
        }
        jsonResponse(true, 'Data penduduk berhasil dihapus');
    }
    jsonResponse(false, 'Gagal menghapus penduduk');
}

// Default delete
$result = query("DELETE FROM `$type` WHERE id = $id");
if ($result) {
    jsonResponse(true, 'Data berhasil dihapus');
} else {
    jsonResponse(false, 'Gagal menghapus data');
}
