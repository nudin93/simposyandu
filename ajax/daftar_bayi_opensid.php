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
 * Daftarkan bayi (0–11 bulan) dari OpenSID ke tabel bayi lokal
 */
error_reporting(0);
ini_set('display_errors', '0');
require_once __DIR__ . '/../config/init.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Method not allowed');
}

$csrf = $_POST['csrf_token'] ?? '';
if ($csrf !== '' && function_exists('verifyCsrf') && !verifyCsrf($csrf)) {
    jsonResponse(false, 'Token keamanan tidak valid. Silakan refresh halaman lalu coba lagi.');
}

$id_opensid = (int)($_POST['id_penduduk_opensid'] ?? 0);
if ($id_opensid <= 0) {
    jsonResponse(false, 'ID penduduk OpenSID tidak valid');
}

if (!opensid_available()) {
    jsonResponse(false, 'OpenSID tidak terhubung');
}

// Standar bayi Posyandu: 0–11 bulan
$MIN_BULAN = 0;
$MAX_BULAN = 11;

$ada = fetchOne("SELECT id FROM bayi WHERE id_penduduk_opensid = $id_opensid LIMIT 1");
if ($ada) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Bayi sudah terdaftar di Posyandu',
        'id' => (int)$ada['id'],
        'data' => ['id' => (int)$ada['id']],
    ]);
    exit;
}

$p = getPendudukOpenSIDById($id_opensid);
if (!$p) {
    foreach (getBalitaOpenSID($MAX_BULAN, $MIN_BULAN) as $r) {
        if ((int)($r['id_penduduk'] ?? 0) === $id_opensid) {
            $p = $r;
            break;
        }
    }
}
if (!$p) {
    jsonResponse(false, 'Data penduduk tidak ditemukan di OpenSID');
}

$nik = trim((string)($p['nik'] ?? ''));
if ($nik === '' || $nik === '0') $nik = '';

if ($nik !== '') {
    $byNik = fetchOne("SELECT id FROM bayi WHERE nik_bayi = '" . escape($nik) . "' LIMIT 1");
    if ($byNik) {
        query("UPDATE bayi SET id_penduduk_opensid = $id_opensid, status_integrasi = 'terhubung' WHERE id = " . (int)$byNik['id']);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'Data lokal ditautkan ke OpenSID',
            'id' => (int)$byNik['id'],
            'data' => ['id' => (int)$byNik['id']],
        ]);
        exit;
    }
}

$tglRaw = trim((string)($p['tanggal_lahir'] ?? ''));
$tgl = '';
if ($tglRaw !== '') {
    if (preg_match('/^(\d{4})-(\d{2})-(\d{2})/', $tglRaw, $m)) {
        $tgl = $m[1] . '-' . $m[2] . '-' . $m[3];
    } elseif (preg_match('/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})$/', $tglRaw, $m)) {
        $tgl = $m[3] . '-' . str_pad($m[2], 2, '0', STR_PAD_LEFT) . '-' . str_pad($m[1], 2, '0', STR_PAD_LEFT);
    } else {
        try { $tgl = (new DateTime($tglRaw))->format('Y-m-d'); } catch (Exception $e) { $tgl = ''; }
    }
    if ($tgl !== '') {
        $parts = explode('-', $tgl);
        if (count($parts) !== 3 || !checkdate((int)$parts[1], (int)$parts[2], (int)$parts[0])) $tgl = '';
    }
}
if ($tgl === '') {
    jsonResponse(false, 'Tanggal lahir tidak valid/kosong di OpenSID. Perbaiki data penduduk dulu.');
}

try {
    $d1 = new DateTime($tgl);
    $diff = $d1->diff(new DateTime());
    if ($diff->invert) jsonResponse(false, 'Tanggal lahir di masa depan');
    $bulan = $diff->y * 12 + $diff->m;
    if ($bulan > $MAX_BULAN) {
        jsonResponse(false, "Usia $bulan bulan — bukan sasaran bayi (standar 0–{$MAX_BULAN} bulan). Gunakan menu Balita.");
    }
} catch (Exception $e) {
    jsonResponse(false, 'Tanggal lahir tidak dapat diproses');
}

$namaRaw = trim((string)($p['nama'] ?? ''));
if ($namaRaw === '') jsonResponse(false, 'Nama tidak tersedia di OpenSID');

$jk = $p['jenis_kelamin'] ?? mapSex($p['sex'] ?? null);
if ($jk !== 'L' && $jk !== 'P') $jk = 'L';

$nomor = function_exists('generateNomorPeserta')
    ? generateNomorPeserta('BYI', 'bayi')
    : ('BYI-' . date('Y') . '-' . date('His'));

$nikSql = ($nik !== '') ? ("'" . escape($nik) . "'") : 'NULL';

$fields = [
    'nomor_peserta'       => "'" . escape($nomor) . "'",
    'id_penduduk_opensid' => (string)(int)$id_opensid,
    'status_integrasi'    => "'terhubung'",
    'nik_bayi'            => $nikSql,
    'nik_ibu'             => "'" . escape($p['nik_ibu'] ?? '') . "'",
    'no_kk'               => "'" . escape($p['no_kk'] ?? '') . "'",
    'nama_lengkap'        => "'" . escape($namaRaw) . "'",
    'jenis_kelamin'       => "'" . escape($jk) . "'",
    'tempat_lahir'        => "'" . escape($p['tempat_lahir'] ?? '') . "'",
    'tanggal_lahir'       => "'" . escape($tgl) . "'",
    'nama_ibu'            => "'" . escape($p['nama_ibu'] ?? '') . "'",
    'nama_ayah'           => "'" . escape($p['nama_ayah'] ?? '') . "'",
    'dusun'               => "'" . escape($p['dusun'] ?? '') . "'",
    'rt'                  => "'" . escape($p['rt'] ?? '') . "'",
    'rw'                  => "'" . escape($p['rw'] ?? '') . "'",
    'alamat_lengkap'      => "'" . escape($p['alamat'] ?? '') . "'",
    'status_aktif'        => '1',
    'asi_eksklusif'       => "'Ya'",
];

$cols = implode(',', array_keys($fields));
$vals = implode(',', array_values($fields));
$sql = "INSERT INTO bayi ($cols) VALUES ($vals)";

if (query($sql)) {
    $newId = (int)lastInsertId();
    if ($newId <= 0) {
        $row = fetchOne("SELECT id FROM bayi WHERE id_penduduk_opensid = $id_opensid ORDER BY id DESC LIMIT 1");
        $newId = (int)($row['id'] ?? 0);
    }
    if ($newId > 0) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'Bayi berhasil didaftarkan ke Posyandu',
            'id' => $newId,
            'data' => ['id' => $newId],
        ]);
        exit;
    }
}

global $conn;
$err = ($conn && isset($conn->error)) ? $conn->error : '';
$msg = 'Gagal menyimpan data bayi';
if ($err) {
    if (stripos($err, 'Duplicate') !== false) $msg = 'Data bayi sudah ada (NIK/nomor bentrok). Refresh halaman.';
    else $msg = 'Gagal menyimpan: ' . $err;
}
jsonResponse(false, $msg);
