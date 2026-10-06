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

error_reporting(0);
ini_set("display_errors","0");
require_once __DIR__ . '/../config/init.php';
requireLogin();

// CSRF Protection
$csrf = $_POST["csrf_token"] ?? $_GET["csrf_token"] ?? "";
if (!verifyCsrf($csrf)) {
    jsonResponse(false, "Token keamanan tidak valid. Silakan refresh halaman lalu coba lagi.");
}

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$nama = escape($_POST['nama'] ?? '');
$tgl = escape($_POST['tanggal_lahir'] ?? '');
$jk = escape($_POST['jenis_kelamin'] ?? '');

if ($nama === '' || $tgl === '' || $jk === '') {
    echo json_encode(['success' => false, 'message' => 'Field wajib belum diisi (nama, tanggal lahir, jenis kelamin)']);
    exit;
}

$nomor = escape($_POST['nomor_peserta'] ?? '');
if ($nomor === '') {
    $nomor = function_exists('generateNomorPeserta') ? generateNomorPeserta('LNS', 'lansia') : ('LNS-' . date('Y') . '-' . rand(100,999));
    $nomor = escape($nomor);
}

$fields = array(
    'nomor_peserta' => $nomor,
    'nik' => escape($_POST['nik'] ?? ''),
    'nama' => $nama,
    'jenis_kelamin' => $jk,
    'tempat_lahir' => escape($_POST['tempat_lahir'] ?? ''),
    'tanggal_lahir' => $tgl,
    'alamat_lengkap' => escape($_POST['alamat_lengkap'] ?? ''),
    'no_hp' => escape($_POST['no_hp'] ?? ''),
    'pekerjaan' => escape($_POST['pekerjaan'] ?? ''),
    'status_perkawinan' => escape($_POST['status_perkawinan'] ?? 'Menikah'),
    'pendidikan' => escape($_POST['pendidikan'] ?? ''),
    'riwayat_penyakit' => escape($_POST['riwayat_penyakit'] ?? ''),
    'bpjs_kis' => escape($_POST['bpjs_kis'] ?? ''),
    'status_aktif' => isset($_POST['status_aktif']) ? 1 : 0
);

// Cek nomor peserta duplikat
$cek = fetchOne("SELECT id FROM lansia WHERE nomor_peserta='$nomor'");
if ($cek) {
    // Generate nomor baru
    if (function_exists('generateNomorPeserta')) {
        $nomor = escape(generateNomorPeserta('LNS', 'lansia'));
        $fields['nomor_peserta'] = $nomor;
    }
}

$cols = array();
$vals = array();
foreach ($fields as $k => $v) {
    $cols[] = "`$k`";
    $vals[] = "'" . $v . "'";
}
$sql = "INSERT INTO lansia (" . implode(',', $cols) . ") VALUES (" . implode(',', $vals) . ")";
$result = query($sql);

if ($result) {
    echo json_encode(['success' => true, 'message' => 'Data lansia berhasil disimpan!']);
} else {
    global $conn;
    $err = $conn ? $conn->error : 'unknown';
    echo json_encode(['success' => false, 'message' => 'Gagal menyimpan: ' . $err]);
}
exit;
