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
 * Simpan artikel baru — hardened
 */
require_once __DIR__ . '/../config/init.php';
requireLogin();

// Hanya POST
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    jsonResponse(false, 'Metode tidak diizinkan');
}

if (!canInput()) {
    jsonResponse(false, 'Akses ditolak');
}

// CSRF
$csrf = $_POST['csrf_token'] ?? '';
if (!verifyCsrf($csrf)) {
    jsonResponse(false, 'Token keamanan tidak valid. Muat ulang halaman.');
}

$judul    = trim((string)($_POST['judul'] ?? ''));
$kategori = trim((string)($_POST['kategori'] ?? 'Kesehatan'));
$isi      = trim((string)($_POST['isi_artikel'] ?? ''));
$aksi     = (string)($_POST['aksi'] ?? 'draft');

// Hilangkan null-byte & kontrol karakter berbahaya
$judul = str_replace("\0", '', $judul);
$isi   = str_replace("\0", '', $isi);

// Sanitasi HTML dari editor TinyMCE
$isi = sanitizeArtikelHtml($isi);
$isiPlain = trim(strip_tags($isi));

if (mb_strlen($judul) < 5 || mb_strlen($judul) > 200) {
    jsonResponse(false, 'Judul artikel minimal 5 karakter dan maksimal 200 karakter');
}
if (mb_strlen($isi) > 100000) {
    jsonResponse(false, 'Isi artikel terlalu panjang');
}
if ($judul === '' || $isiPlain === '') {
    jsonResponse(false, 'Judul dan isi artikel wajib diisi');
}

// Whitelist aksi & kategori
if (!in_array($aksi, ['draft', 'kirim'], true)) {
    $aksi = 'draft';
}
$allowed_kat = ['Kesehatan', 'Gizi', 'Imunisasi', 'Posyandu', 'Ibu & Anak', 'Lainnya'];
if (!in_array($kategori, $allowed_kat, true)) {
    $kategori = 'Lainnya';
}

$status = ($aksi === 'kirim') ? 'menunggu' : 'draft';
$penulis_id = (int)($_SESSION['kader_id'] ?? 0);
if ($penulis_id <= 0) {
    jsonResponse(false, 'Sesi tidak valid');
}

// Pastikan penulis masih aktif di DB
$kader = fetchOne("SELECT id, status FROM kader WHERE id=$penulis_id LIMIT 1");
if (!$kader || (int)($kader['status'] ?? 0) !== 1) {
    jsonResponse(false, 'Akun tidak aktif');
}

// Slug aman (huruf, angka, dash saja)
$slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $judul), '-'));
$slug = preg_replace('/-+/', '-', $slug);
if ($slug === '' || strlen($slug) > 200) {
    $slug = 'artikel-' . bin2hex(random_bytes(4));
}
$base = $slug;
$n = 1;
while (numRows("SELECT id FROM artikel WHERE slug='" . escape($slug) . "'") > 0) {
    $slug = $base . '-' . $n;
    $n++;
    if ($n > 100) {
        $slug = $base . '-' . bin2hex(random_bytes(3));
        break;
    }
}

$gambar = '';
if (!empty($_FILES['gambar']['name'])) {
    $gambar = secureUploadArtikelImage($_FILES['gambar']);
    if ($gambar === false) {
        // pesan error sudah di-set di helper via jsonResponse
        exit;
    }
}

$tgl_raw = trim((string)($_POST['tanggal_publish'] ?? ''));
$tanggal_publish_sql = 'NULL';
if ($tgl_raw !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $tgl_raw)) {
    $tanggal_publish_sql = "'" . escape($tgl_raw) . "'";
}

$judul_e  = escape($judul);
$slug_e   = escape($slug);
$isi_e    = escape($isi);
$kat_e    = escape($kategori);
$gambar_e = escape($gambar);
$status_e = escape($status);

$sql = "INSERT INTO artikel (judul, slug, isi_artikel, gambar, kategori, penulis_id, status, tanggal_publish)
        VALUES ('$judul_e', '$slug_e', '$isi_e', '$gambar_e', '$kat_e', $penulis_id, '$status_e', $tanggal_publish_sql)";

if (query($sql)) {
    $msg = $status === 'menunggu'
        ? 'Artikel berhasil dikirim dan menunggu persetujuan admin.'
        : 'Artikel berhasil disimpan sebagai draft.';
    jsonResponse(true, $msg, ['id' => lastInsertId()]);
}
jsonResponse(false, 'Gagal menyimpan artikel');

/**
 * Upload gambar artikel yang aman.
 * - Hanya JPG/JPEG/PNG
 * - Max 2 MB
 * - is_uploaded_file + getimagesize (bukan hanya ekstensi)
 * - Nama file acak, tanpa path asli user
 * @return string|false filename atau false (sudah jsonResponse)
 */
function secureUploadArtikelImage(array $file)
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        jsonResponse(false, 'Gagal upload gambar (kode: ' . (int)($file['error'] ?? 0) . ')');
    }
    if (empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        jsonResponse(false, 'File upload tidak valid');
    }
    if (($file['size'] ?? 0) <= 0 || $file['size'] > 2 * 1024 * 1024) {
        jsonResponse(false, 'Ukuran gambar maksimal 2 MB');
    }

    // Validasi konten gambar nyata (bukan polyglot / PHP shell)
    $imgInfo = @getimagesize($file['tmp_name']);
    if ($imgInfo === false) {
        jsonResponse(false, 'File bukan gambar yang valid');
    }
    $allowedMime = [
        IMAGETYPE_JPEG => 'jpg',
        IMAGETYPE_PNG  => 'png',
    ];
    $type = $imgInfo[2] ?? 0;
    if (!isset($allowedMime[$type])) {
        jsonResponse(false, 'Format gambar harus JPG atau PNG');
    }
    $ext = $allowedMime[$type];

    // Double-check MIME dari finfo jika tersedia
    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        $okMime = ['image/jpeg', 'image/png', 'image/jpg'];
        if (!in_array($mime, $okMime, true)) {
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

    // Pastikan target masih di dalam folder artikel (anti path traversal)
    if (strpos($target, $dirReal) !== 0) {
        jsonResponse(false, 'Path upload tidak valid');
    }

    if (!move_uploaded_file($file['tmp_name'], $target)) {
        jsonResponse(false, 'Gagal menyimpan file gambar');
    }
    @chmod($target, 0644);

    return $filename;
}

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

