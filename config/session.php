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

session_start();

function isLoggedIn() {
    return isset($_SESSION['kader_id']) && !empty($_SESSION['kader_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . APP_URL . '/index.php');
        exit;
    }
}

function getSession($key) {
    return $_SESSION[$key] ?? null;
}

function setSession($key, $value) {
    $_SESSION[$key] = $value;
}

function currentUser() {
    if (!isLoggedIn()) return null;
    return [
        'id' => $_SESSION['kader_id'],
        'nama' => $_SESSION['kader_nama'],
        'username' => $_SESSION['kader_username'],
        'role' => $_SESSION['kader_role'],
        'foto' => $_SESSION['kader_foto'] ?? '',
    ];
}

function isAdmin() {
    return ($_SESSION['kader_role'] ?? '') === 'admin';
}

function isBidan() {
    $role = $_SESSION['kader_role'] ?? '';
    return $role === 'admin' || $role === 'bidan';
}

/** Kepala Desa: hanya dashboard & laporan */
function isKepalaDesa() {
    return ($_SESSION['kader_role'] ?? '') === 'kepala_desa';
}

/** Boleh input pelayanan (admin, bidan, kader) */
function canInput() {
    $role = $_SESSION['kader_role'] ?? '';
    return in_array($role, ['admin', 'bidan', 'kader'], true);
}

/** Boleh verifikasi data (admin, bidan) */
function canVerify() {
    return isBidan();
}

/** Boleh lihat laporan (semua role login) */
function canViewLaporan() {
    return isLoggedIn();
}

/** Boleh akses pengaturan & kader (admin saja) */
function canManage() {
    return isAdmin();
}

function csrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrf($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
