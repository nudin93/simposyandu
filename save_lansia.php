<?php
error_reporting(0);
ini_set("display_errors","0");
require_once __DIR__ . '/../config/init.php';
requireLogin();

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
