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
 * Simpan pemeriksaan anak TK
 */
error_reporting(0);
ini_set('display_errors', '0');
require_once __DIR__ . '/../config/init.php';
requireLogin();

$csrf = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? '';
if (!verifyCsrf($csrf)) {
    jsonResponse(false, 'Token keamanan tidak valid. Silakan refresh halaman lalu coba lagi.');
}

header('Content-Type: application/json; charset=utf-8');

$rid = (int)($_POST['anak_tk_id'] ?? $_POST['id'] ?? 0);
$tgl = trim($_POST['tanggal_pemeriksaan'] ?? '');
if ($rid <= 0 || $tgl === '') {
    echo json_encode(['success' => false, 'message' => 'Pilih anak TK dan isi tanggal pemeriksaan']);
    exit;
}

$anak = fetchOne("SELECT id, jenis_kelamin FROM anak_tk WHERE id=$rid LIMIT 1");
if (!$anak) {
    echo json_encode(['success' => false, 'message' => 'Data anak TK tidak ditemukan. Daftarkan dulu dari menu Data Anak TK.']);
    exit;
}

$bb = (isset($_POST['berat_badan']) && $_POST['berat_badan'] !== '') ? (float)$_POST['berat_badan'] : null;
$tb = (isset($_POST['tinggi_badan']) && $_POST['tinggi_badan'] !== '') ? (float)$_POST['tinggi_badan'] : null;
$hb = (isset($_POST['hb']) && $_POST['hb'] !== '') ? (float)$_POST['hb'] : null;
$imt = ($bb && $tb) ? round($bb / (($tb / 100) ** 2), 2) : null;

$sg = 'Normal';
if ($imt !== null && function_exists('statusIMTDewasa')) {
    $sg = statusIMTDewasa($imt);
}
$allowedGizi = ['Kurus', 'Normal', 'Gemuk', 'Obesitas'];
if (!in_array($sg, $allowedGizi, true)) {
    $sg = 'Normal';
}

$jk = $anak['jenis_kelamin'] ?? 'P';
$anemia = function_exists('statusAnemia') ? (int)statusAnemia($hb, $jk) : 0;

$user = currentUser();
$petugas = ($user && !empty($user['id'])) ? (int)$user['id'] : 'NULL';

$sqlNull = function ($v) {
    if ($v === null || $v === '') return 'NULL';
    if (is_numeric($v)) return (string)$v;
    return "'" . escape((string)$v) . "'";
};

$td = trim($_POST['tekanan_darah'] ?? '');
$edukasi = trim($_POST['edukasi_kesehatan'] ?? '');
$keluhan = trim($_POST['keluhan'] ?? '');
$penanganan = trim($_POST['penanganan'] ?? '');

$sql = "INSERT INTO pemeriksaan_anak_tk
    (anak_tk_id, tanggal_pemeriksaan, berat_badan, tinggi_badan, imt, status_gizi, hb, status_anemia, tekanan_darah, edukasi_kesehatan, keluhan, penanganan, petugas_id)
    VALUES (
        $rid,
        '" . escape($tgl) . "',
        " . $sqlNull($bb) . ",
        " . $sqlNull($tb) . ",
        " . $sqlNull($imt) . ",
        '" . escape($sg) . "',
        " . $sqlNull($hb) . ",
        $anemia,
        " . $sqlNull($td !== '' ? $td : null) . ",
        " . $sqlNull($edukasi !== '' ? $edukasi : null) . ",
        " . $sqlNull($keluhan !== '' ? $keluhan : null) . ",
        " . $sqlNull($penanganan !== '' ? $penanganan : null) . ",
        $petugas
    )";

if (query($sql)) {
    echo json_encode(['success' => true, 'message' => 'Pemeriksaan anak TK berhasil disimpan!']);
    exit;
}

global $conn;
$err = ($conn && isset($conn->error)) ? $conn->error : '';
$msg = 'Gagal menyimpan pemeriksaan';
if ($err) {
    if (stripos($err, 'Unknown table') !== false || stripos($err, "doesn't exist") !== false) {
        $msg = 'Tabel pemeriksaan_anak_tk belum ada. Jalankan migrasi database.';
    } elseif (stripos($err, 'Data truncated') !== false || stripos($err, 'enum') !== false) {
        $msg = 'Nilai status gizi tidak valid. Isi BB dan TB, lalu simpan ulang.';
    } else {
        $msg = 'Gagal menyimpan: ' . $err;
    }
}
echo json_encode(['success' => false, 'message' => $msg]);
