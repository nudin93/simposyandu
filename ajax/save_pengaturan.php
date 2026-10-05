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
if (!isAdmin()) jsonResponse(false, 'Akses ditolak');
// Pastikan kolom reCAPTCHA ada (auto-migrate)
@query("ALTER TABLE pengaturan ADD COLUMN IF NOT EXISTS recaptcha_site_key VARCHAR(255) DEFAULT ''");
@query("ALTER TABLE pengaturan ADD COLUMN IF NOT EXISTS recaptcha_secret_key VARCHAR(255) DEFAULT ''");
// Fallback untuk MySQL lama tanpa IF NOT EXISTS
$cols = fetchAll("SHOW COLUMNS FROM pengaturan");
$col_names = array_map(fn($r) => $r['Field'] ?? array_values($r)[0] ?? '', $cols ?: []);
if (!in_array('recaptcha_site_key', $col_names, true)) {
    @query("ALTER TABLE pengaturan ADD COLUMN recaptcha_site_key VARCHAR(255) DEFAULT ''");
}
if (!in_array('recaptcha_secret_key', $col_names, true)) {
    @query("ALTER TABLE pengaturan ADD COLUMN recaptcha_secret_key VARCHAR(255) DEFAULT ''");
}

$fields = ['nama_aplikasi'=>escape($_POST['nama_aplikasi']??''),'nama_posyandu'=>escape($_POST['nama_posyandu']??''),
  'nama_desa'=>escape($_POST['nama_desa']??''),'kecamatan'=>escape($_POST['kecamatan']??''),
  'kabupaten'=>escape($_POST['kabupaten']??''),'provinsi'=>escape($_POST['provinsi']??''),
  'nomor_kontak'=>escape($_POST['nomor_kontak']??''),'email'=>escape($_POST['email']??''),
  'warna_tema'=>escape($_POST['warna_tema']??'blue'),'dark_mode'=>isset($_POST['dark_mode'])?1:0,
  'recaptcha_site_key'=>escape(trim($_POST['recaptcha_site_key']??'')),
  'recaptcha_secret_key'=>escape(trim($_POST['recaptcha_secret_key']??''))];
$upload_errors = [];
if (!empty($_FILES['logo']['name'])) {
    $up = uploadFile($_FILES['logo'], 'settings');
    if ($up['success']) $fields['logo'] = $up['filename'];
    else $upload_errors[] = 'Logo: ' . ($up['message'] ?? 'gagal upload');
}
if (!empty($_FILES['favicon']['name'])) {
    $up = uploadFile($_FILES['favicon'], 'settings');
    if ($up['success']) $fields['favicon'] = $up['filename'];
    else $upload_errors[] = 'Favicon: ' . ($up['message'] ?? 'gagal upload');
}
$sets = array_map(fn($k,$v)=>"$k='$v'", array_keys($fields), $fields);
if (numRows("SELECT id FROM pengaturan") > 0) {
    query("UPDATE pengaturan SET ".implode(',', $sets)." LIMIT 1");
} else {
    $cols = implode(',', array_keys($fields)); $vals = implode(',', array_map(fn($v)=>"'".$v."'", $fields));
    query("INSERT INTO pengaturan ($cols) VALUES ($vals)");
}
$msg = 'Pengaturan berhasil disimpan!';
if (!empty($upload_errors)) $msg .= ' Namun: ' . implode('; ', $upload_errors);
jsonResponse(true, $msg);
