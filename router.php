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
 * Router sederhana (MVC ringan)
 * Pemakaian: router.php?c=balita&m=index
 * atau: router.php?c=balita&m=tambah
 */
require_once __DIR__ . '/config/init.php';

$controller = strtolower(preg_replace('/[^a-z0-9_]/i', '', $_GET['c'] ?? 'dashboard'));
$method     = strtolower(preg_replace('/[^a-z0-9_]/i', '', $_GET['m'] ?? 'index'));

$map = [
    'balita'     => 'Balita',
    'ibu_hamil'  => 'Ibu_hamil',
    'bayi'       => 'Bayi',
    'lansia'     => 'Lansia',
    'dashboard'  => null, // pakai dashboard.php
];

if ($controller === 'dashboard' || empty($map[$controller])) {
    header('Location: ' . APP_URL . '/dashboard.php');
    exit;
}

$classFile = __DIR__ . '/controllers/' . $map[$controller] . '.php';
if (!file_exists($classFile)) {
    // Fallback ke modul lama
    $mod = __DIR__ . '/modules/' . $controller . '/index.php';
    if (file_exists($mod)) {
        require $mod;
        exit;
    }
    http_response_code(404);
    echo 'Controller tidak ditemukan';
    exit;
}

require_once $classFile;
$className = $map[$controller];
if (!class_exists($className)) {
    http_response_code(500);
    echo 'Class controller error';
    exit;
}

$obj = new $className();
if (!method_exists($obj, $method)) {
    $method = 'index';
}
$obj->$method();
