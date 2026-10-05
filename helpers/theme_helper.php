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
 * Helper tema halaman publik
 * Tema disimpan di: themes/public/{nama_tema}/
 * Ganti tema cukup copy folder tema baru + set di Pengaturan
 */

function getPublicThemeName() {
    $p = getPengaturan();
    $p = is_array($p) ? $p : [];
    $name = trim($p['tema_publik'] ?? '');
    if ($name === '') {
        $name = is_dir(__DIR__ . '/../themes/public/rangkiang') ? 'rangkiang' : 'default';
    }
    // sanitasi nama folder
    $name = preg_replace('/[^a-zA-Z0-9_\-]/', '', $name);
    $base = __DIR__ . '/../themes/public/' . $name;
    if (!is_dir($base)) {
        $name = is_dir(__DIR__ . '/../themes/public/rangkiang') ? 'rangkiang' : 'default';
    }
    return $name;
}

function themePath($file = '') {
    $name = getPublicThemeName();
    $base = __DIR__ . '/../themes/public/' . $name;
    return $file === '' ? $base : $base . '/' . ltrim($file, '/');
}

function themeUrl($file = '') {
    $name = getPublicThemeName();
    return APP_URL . '/themes/public/' . $name . ($file ? '/' . ltrim($file, '/') : '');
}

function renderThemePartial($name, $data = []) {
    extract($data, EXTR_SKIP);
    $file = themePath('partials/' . $name . '.php');
    if (file_exists($file)) {
        include $file;
    }
}

function renderThemePage($page, $data = []) {
    extract($data, EXTR_SKIP);
    $file = themePath('pages/' . $page . '.php');
    if (!file_exists($file)) {
        $file = themePath('pages/home.php');
    }
    include $file;
}

function getPublicLogoUrl($pengaturan) {
    $pengaturan = is_array($pengaturan) ? $pengaturan : [];
    $logo_file = trim($pengaturan['logo'] ?? '');
    if ($logo_file !== '' && file_exists(__DIR__ . '/../uploads/settings/' . $logo_file)) {
        return APP_URL . '/uploads/settings/' . rawurlencode($logo_file);
    }
    if (file_exists(__DIR__ . '/../assets/img/logo.png')) {
        return APP_URL . '/assets/img/logo.png';
    }
    return '';
}
