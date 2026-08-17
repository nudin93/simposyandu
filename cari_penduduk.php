<?php
/**
 * Pencarian penduduk / keluarga dari OpenSID (sumber data utama)
 * Fallback: tabel balita lokal (untuk bayi tanpa NIK)
 */
error_reporting(0);
ini_set('display_errors', '0');

require_once __DIR__ . '/../config/init.php';
requireLogin();

header('Content-Type: application/json; charset=utf-8');

$q = trim($_GET['q'] ?? '');
$jk = $_GET['jk'] ?? '';
$mode = $_GET['mode'] ?? 'penduduk';
$anggota = $_GET['anggota'] ?? ''; // no_kk untuk ambil anggota keluarga

if (strlen($q) < 1 && empty($anggota)) {
    echo json_encode(['success' => true, 'data' => [], 'sumber' => opensid_available() ? 'opensid' : 'lokal']);
    exit;
}

// ========== AMBIL ANGGOTA KELUARGA ==========
if (!empty($anggota)) {
    $data = getAnggotaKeluargaOpenSID($anggota);
    echo json_encode([
        'success' => true,
        'data' => $data,
        'sumber' => 'opensid',
        'message' => count($data) . ' anggota keluarga ditemukan'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// ========== MODE KELUARGA ==========
if ($mode === 'keluarga') {
    $data = [];
    if (opensid_available()) {
        $data = cariKeluargaOpenSID($q, 25);
    }
    echo json_encode([
        'success' => true,
        'data' => $data,
        'sumber' => opensid_available() ? 'opensid' : 'lokal'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// ========== MODE PENDUDUK (dari OpenSID) ==========
$data = [];
$seen = [];

if (opensid_available()) {
    $rows = cariPendudukOpenSID($q, $jk, 25);
    foreach ($rows as $r) {
        $key = $r['nik'] ?: ('id-' . $r['id_penduduk']);
        if (isset($seen[$key])) continue;
        $seen[$key] = 1;
        $data[] = $r;
    }
}

// Fallback: balita lokal (bayi tanpa NIK / belum terintegrasi)
if (count($data) < 25) {
    $s = escape($q);
    $jkF = ($jk === 'L' || $jk === 'P') ? " AND jenis_kelamin='$jk'" : '';
    $limit = 25 - count($data);
    $rows = fetchAll("SELECT id, IFNULL(nik_anak,'') as nik, IFNULL(no_kk,'') as no_kk,
        nama_lengkap as nama, jenis_kelamin,
        IFNULL(tempat_lahir,'') as tempat_lahir, tanggal_lahir,
        TIMESTAMPDIFF(YEAR, tanggal_lahir, CURDATE()) as umur,
        IFNULL(no_hp,'') as no_hp,
        IFNULL(dusun,'') as dusun, IFNULL(rt,'') as rt, IFNULL(rw,'') as rw,
        IFNULL(alamat_lengkap,'') as alamat,
        IFNULL(nama_ayah,'') as nama_ayah, IFNULL(nik_ayah,'') as nik_ayah,
        IFNULL(nama_ibu,'') as nama_ibu, IFNULL(nik_ibu,'') as nik_ibu,
        IFNULL(id_penduduk_opensid, 0) as id_penduduk,
        IFNULL(status_integrasi, 'belum') as status_integrasi
        FROM balita
        WHERE status_aktif = 1
          AND (nama_lengkap LIKE '%$s%' OR IFNULL(nik_anak,'') LIKE '%$s%' OR IFNULL(no_kk,'') LIKE '%$s%')
          $jkF
        LIMIT $limit");
    foreach ($rows as $r) {
        $key = $r['nik'] ?: ('balita-' . $r['id']);
        if (isset($seen[$key])) continue;
        $seen[$key] = 1;
        $r['status_dalam_keluarga'] = 'Anak';
        $r['status_perkawinan'] = '';
        $r['pendidikan'] = '';
        $r['pekerjaan'] = '';
        $r['sumber'] = 'balita_lokal';
        $data[] = $r;
    }
}

echo json_encode([
    'success' => true,
    'data' => array_slice($data, 0, 25),
    'sumber' => opensid_available() ? 'opensid' : 'lokal',
    'opensid_ok' => opensid_available()
], JSON_UNESCAPED_UNICODE);
exit;
