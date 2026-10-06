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

/**
 * Hapus artikel — hardened
 * Method: POST (kompatibel dengan doDelete di custom.js)
 */
require_once __DIR__ . '/../config/init.php';
requireLogin();

header('Content-Type: application/json; charset=utf-8');

// Preferensi POST; izinkan GET hanya untuk kompatibilitas lama tapi log
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($method !== 'POST' && $method !== 'GET') {
    jsonResponse(false, 'Metode tidak diizinkan');
}

// CSRF: wajib jika dikirim (form/fetch); untuk doDelete jQuery POST tanpa token —
// validasi ownership tetap proteksi utama. Jika token ada, harus valid.
$csrf = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? '';
if ($csrf !== '' && !verifyCsrf($csrf)) {
    jsonResponse(false, 'Token keamanan tidak valid');
}

$id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
if ($id <= 0) {
    jsonResponse(false, 'Parameter tidak valid');
}

$row = fetchOne("SELECT id, penulis_id, status, gambar FROM artikel WHERE id=$id LIMIT 1");
if (!$row) {
    jsonResponse(false, 'Artikel tidak ditemukan');
}

$userId  = (int)($_SESSION['kader_id'] ?? 0);
$isOwner = ((int)$row['penulis_id'] === $userId);
$isAdm   = isAdmin();

if (!$isOwner && !$isAdm) {
    jsonResponse(false, 'Anda tidak berhak menghapus artikel ini');
}

// Kader tidak boleh hapus artikel yang sudah diterbitkan
if (!$isAdm && $row['status'] === 'diterbitkan') {
    jsonResponse(false, 'Artikel yang sudah diterbitkan hanya dapat dihapus oleh admin');
}

// Hapus file gambar dengan aman (anti path traversal)
if (!empty($row['gambar'])) {
    $filename = basename((string)$row['gambar']);
    if ($filename !== '' && $filename !== '.' && $filename !== '..'
        && preg_match('/^[a-zA-Z0-9._-]+$/', $filename)) {
        $dir = realpath(__DIR__ . '/../uploads/artikel');
        if ($dir !== false) {
            $path = $dir . DIRECTORY_SEPARATOR . $filename;
            $real = realpath($path);
            if ($real !== false && strpos($real, $dir) === 0 && is_file($real)) {
                @unlink($real);
            }
        }
    }
}

if (query("DELETE FROM artikel WHERE id=$id LIMIT 1")) {
    jsonResponse(true, 'Artikel berhasil dihapus');
}
jsonResponse(false, 'Gagal menghapus artikel');
