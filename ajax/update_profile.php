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

$user = currentUser();

// CSRF Protection
$csrf = $_POST["csrf_token"] ?? $_GET["csrf_token"] ?? "";
if (!verifyCsrf($csrf)) {
    jsonResponse(false, "Token keamanan tidak valid. Silakan refresh halaman lalu coba lagi.");
}
$id = (int)($_POST['id'] ?? 0);
if ($id != ($user['id'] ?? 0) && !isAdmin()) jsonResponse(false, 'Akses ditolak');
$nama = escape($_POST['nama'] ?? '');
$nik = escape($_POST['nik'] ?? '');
$no_hp = escape($_POST['no_hp'] ?? '');
$alamat = escape($_POST['alamat'] ?? '');
$sets = "nama='$nama',nik='$nik',no_hp='$no_hp',alamat='$alamat'";
if (!empty($_FILES['foto']['name'])) {
    $up = uploadFile($_FILES['foto'], 'kader');
    if ($up['success']) { $sets .= ",foto='{$up['filename']}'"; $_SESSION['kader_foto'] = $up['filename']; }
}
$old_pass = $_POST['old_password'] ?? ''; $new_pass = $_POST['new_password'] ?? ''; $conf = $_POST['confirm_password'] ?? '';
if ($old_pass && $new_pass) {
    $me = fetchOne("SELECT password FROM kader WHERE id=$id");
    if (!password_verify($old_pass, $me['password'])) jsonResponse(false, 'Password lama salah');
    if ($new_pass !== $conf) jsonResponse(false, 'Konfirmasi password tidak cocok');
    $hash = password_hash($new_pass, PASSWORD_BCRYPT);
    $sets .= ",password='$hash'";
}
if (query("UPDATE kader SET $sets WHERE id=$id")) { $_SESSION['kader_nama'] = $nama; jsonResponse(true, 'Profil berhasil diperbarui!'); }
else jsonResponse(false, 'Gagal memperbarui profil');
