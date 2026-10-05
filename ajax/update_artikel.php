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
 * Update artikel / setujui / tolak — hardened
 */
require_once __DIR__ . '/../config/init.php';
requireLogin();

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    jsonResponse(false, 'Metode tidak diizinkan');
}

$csrf = $_POST['csrf_token'] ?? '';
if (!verifyCsrf($csrf)) {
    jsonResponse(false, 'Token keamanan tidak valid. Muat ulang halaman.');
}

$id   = (int)($_POST['id'] ?? 0);
$aksi = (string)($_POST['aksi'] ?? 'draft');

if ($id <= 0) {
    jsonResponse(false, 'Parameter tidak valid');
}

// Whitelist aksi
$allowedAksi = ['draft', 'kirim', 'setujui', 'tolak'];
if (!in_array($aksi, $allowedAksi, true)) {
    jsonResponse(false, 'Aksi tidak valid');
}

// ---- Admin: setujui / tolak ----
if ($aksi === 'setujui' || $aksi === 'tolak') {
    if (!isAdmin()) {
        jsonResponse(false, 'Hanya admin yang dapat menyetujui/menolak artikel');
    }
    $row = fetchOne("SELECT id, status FROM artikel WHERE id=$id LIMIT 1");
    if (!$row) {
        jsonResponse(false, 'Artikel tidak ditemukan');
    }
    if ($row['status'] !== 'menunggu') {
        jsonResponse(false, 'Artikel tidak dalam status menunggu persetujuan');
    }
    if ($aksi === 'setujui') {
        $ok = query("UPDATE artikel SET status='diterbitkan', tanggal_publish=CURDATE() WHERE id=$id AND status='menunggu'");
        if ($ok) {
            jsonResponse(true, 'Artikel berhasil diterbitkan');
        }
        jsonResponse(false, 'Gagal menerbitkan artikel');
    }
    $ok = query("UPDATE artikel SET status='ditolak', tanggal_publish=NULL WHERE id=$id AND status='menunggu'");
    if ($ok) {
        jsonResponse(true, 'Artikel ditolak');
    }
    jsonResponse(false, 'Gagal menolak artikel');
}

// ---- Edit konten ----
if (!canInput()) {
    jsonResponse(false, 'Akses ditolak');
}

$judul    = trim((string)($_POST['judul'] ?? ''));
$kategori = trim((string)($_POST['kategori'] ?? 'Kesehatan'));
$isi      = trim((string)($_POST['isi_artikel'] ?? ''));

$judul = str_replace("\0", '', $judul);
$isi   = str_replace("\0", '', $isi);

$isi = sanitizeArtikelHtml($isi);
$isiPlain = trim(strip_tags($isi));

if (mb_strlen($judul) < 5 || mb_strlen($judul) > 200) {
    jsonResponse(false, 'Judul artikel minimal 5 karakter dan maksimal 200 karakter');
}
if (mb_strlen($isi) > 100000) {
    jsonResponse(false, 'Isi artikel terlalu panjang');
}
if ($judul === '' || $isiPlain === '') {
    jsonResponse(false, 'Data tidak lengkap');
}

$row = fetchOne("SELECT * FROM artikel WHERE id=$id LIMIT 1");
if (!$row) {
    jsonResponse(false, 'Artikel tidak ditemukan');
}

$userId  = (int)($_SESSION['kader_id'] ?? 0);
$isOwner = ((int)$row['penulis_id'] === $userId);
$isAdm   = isAdmin();

if (!$isOwner && !$isAdm) {
    jsonResponse(false, 'Anda tidak berhak mengubah artikel ini');
}

// Kader hanya boleh edit jika belum diterbitkan
if (!$isAdm && !in_array($row['status'], ['draft', 'ditolak', 'menunggu'], true)) {
    jsonResponse(false, 'Artikel yang sudah diterbitkan hanya dapat diubah oleh admin');
}

$allowed_kat = ['Kesehatan', 'Gizi', 'Imunisasi', 'Posyandu', 'Ibu & Anak', 'Lainnya'];
if (!in_array($kategori, $allowed_kat, true)) {
    $kategori = 'Lainnya';
}

// Status baru: hanya draft atau menunggu (kader tidak bisa set diterbitkan sendiri)
$status = $row['status'];
if ($aksi === 'kirim') {
    $status = 'menunggu';
} elseif ($aksi === 'draft') {
    $status = 'draft';
}
// Admin mengedit artikel terbit: pertahankan status diterbitkan kecuali minta draft/kirim
if ($isAdm && $row['status'] === 'diterbitkan' && !in_array($aksi, ['draft', 'kirim'], true)) {
    $status = 'diterbitkan';
}

// Regenerasi slug jika judul berubah
$slug = $row['slug'];
if ($judul !== $row['judul']) {
    $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $judul), '-'));
    $slug = preg_replace('/-+/', '-', $slug);
    if ($slug === '' || strlen($slug) > 200) {
        $slug = 'artikel-' . bin2hex(random_bytes(4));
    }
    $base = $slug;
    $n = 1;
    while (numRows("SELECT id FROM artikel WHERE slug='" . escape($slug) . "' AND id!=$id") > 0) {
        $slug = $base . '-' . $n;
        $n++;
        if ($n > 100) {
            $slug = $base . '-' . bin2hex(random_bytes(3));
            break;
        }
    }
}

$gambar = $row['gambar'];
if (!empty($_FILES['gambar']['name'])) {
    $newFile = secureUploadArtikelImageUpdate($_FILES['gambar']);
    if ($newFile === false) {
        exit; // sudah jsonResponse di helper
    }
    // Hapus gambar lama dengan aman
    safeDeleteArtikelImage($gambar);
    $gambar = $newFile;
}

$tgl_raw = trim((string)($_POST['tanggal_publish'] ?? ''));
$tgl_sql_part = '';
if ($tgl_raw !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $tgl_raw)) {
    $tgl_sql_part = ", tanggal_publish='" . escape($tgl_raw) . "'";
} elseif (array_key_exists('tanggal_publish', $_POST) && $tgl_raw === '') {
    if ($status !== 'diterbitkan') {
        $tgl_sql_part = ", tanggal_publish=NULL";
    }
}

$judul_e  = escape($judul);
$slug_e   = escape($slug);
$isi_e    = escape($isi);
$kat_e    = escape($kategori);
$gambar_e = escape($gambar);
$status_e = escape($status);

$sql = "UPDATE artikel SET
          judul='$judul_e',
          slug='$slug_e',
          isi_artikel='$isi_e',
          gambar='$gambar_e',
          kategori='$kat_e',
          status='$status_e'
          $tgl_sql_part
        WHERE id=$id";

if (query($sql)) {
    $msg = $status === 'menunggu'
        ? 'Artikel diperbarui dan dikirim untuk persetujuan.'
        : 'Artikel berhasil diperbarui.';
    jsonResponse(true, $msg);
}
jsonResponse(false, 'Gagal memperbarui artikel');

/** Bersihkan HTML dari TinyMCE — hanya tag aman */
function sanitizeArtikelHtml($html) {
    $html = (string)$html;
    $html = preg_replace('#<(script|iframe|object|embed|form|input|button|textarea|select|style|link|meta)[^>]*>.*?</\1>#is', '', $html);
    $html = preg_replace('#<(script|iframe|object|embed|form|input|button|textarea|select|style|link|meta)[^>]*/?>#is', '', $html);
    $html = preg_replace('/\son\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html);
    $html = preg_replace('/(javascript|vbscript|data)\s*:/i', '', $html);
    $allowed = '<p><br><b><strong><i><em><u><ul><ol><li><a><img><h1><h2><h3><h4><h5><h6><table><thead><tbody><tr><th><td><blockquote><pre><code><span><div><hr>';
    return trim(strip_tags($html, $allowed));
}

function secureUploadArtikelImageUpdate(array $file)
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        jsonResponse(false, 'Gagal upload gambar');
    }
    if (empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        jsonResponse(false, 'File upload tidak valid');
    }
    if (($file['size'] ?? 0) <= 0 || $file['size'] > 2 * 1024 * 1024) {
        jsonResponse(false, 'Ukuran gambar maksimal 2 MB');
    }
    $imgInfo = @getimagesize($file['tmp_name']);
    if ($imgInfo === false) {
        jsonResponse(false, 'File bukan gambar yang valid');
    }
    $allowedMime = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png'];
    $type = $imgInfo[2] ?? 0;
    if (!isset($allowedMime[$type])) {
        jsonResponse(false, 'Format gambar harus JPG atau PNG');
    }
    $ext = $allowedMime[$type];
    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        if (!in_array($mime, ['image/jpeg', 'image/png', 'image/jpg'], true)) {
            jsonResponse(false, 'Tipe MIME gambar tidak diizinkan');
        }
    }
    $filename = bin2hex(random_bytes(8)) . '_' . time() . '.' . $ext;
    $dir = realpath(__DIR__ . '/../uploads') . DIRECTORY_SEPARATOR . 'artikel';
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
    $dirReal = realpath($dir);
    if ($dirReal === false) {
        jsonResponse(false, 'Folder upload tidak tersedia');
    }
    $target = $dirReal . DIRECTORY_SEPARATOR . $filename;
    if (strpos($target, $dirReal) !== 0) {
        jsonResponse(false, 'Path upload tidak valid');
    }
    if (!move_uploaded_file($file['tmp_name'], $target)) {
        jsonResponse(false, 'Gagal menyimpan file gambar');
    }
    @chmod($target, 0644);
    return $filename;
}

/** Hapus file gambar hanya jika nama aman (basename, tidak ada path) */
function safeDeleteArtikelImage($filename)
{
    if ($filename === null || $filename === '') {
        return;
    }
    // Hanya nama file murni — tolak path traversal
    $filename = basename((string)$filename);
    if ($filename === '' || $filename === '.' || $filename === '..') {
        return;
    }
    if (!preg_match('/^[a-zA-Z0-9._-]+$/', $filename)) {
        return;
    }
    $dir = realpath(__DIR__ . '/../uploads/artikel');
    if ($dir === false) {
        return;
    }
    $path = $dir . DIRECTORY_SEPARATOR . $filename;
    $real = realpath($path);
    if ($real !== false && strpos($real, $dir) === 0 && is_file($real)) {
        @unlink($real);
    }
}
