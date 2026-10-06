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
 * SIMPOSYANDU - Daftarkan anak dari OpenSID ke tabel balita
 * ==========================================================
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

// Sudah terdaftar by OpenSID id?
$ada = fetchOne("SELECT id FROM balita WHERE id_penduduk_opensid = $id_opensid LIMIT 1");
if ($ada) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Anak sudah terdaftar di Posyandu',
        'id' => (int)$ada['id'],
        'data' => ['id' => (int)$ada['id']],
    ]);
    exit;
}

$p = getPendudukOpenSIDById($id_opensid);
if (!$p) {
    // Fallback: cari di daftar balita OpenSID
    $rows = getBalitaOpenSID(59, 0);
    foreach ($rows as $r) {
        if ((int)($r['id_penduduk'] ?? 0) === $id_opensid) {
            $p = $r;
            break;
        }
    }
}
if (!$p) {
    jsonResponse(false, 'Data penduduk tidak ditemukan di OpenSID (ID: ' . $id_opensid . ')');
}

// Normalisasi NIK: kosong → NULL (hindari bentrok UNIQUE nik_anak = '')
$nik = trim((string)($p['nik'] ?? ''));
if ($nik === '' || $nik === '0' || strtolower($nik) === 'null') {
    $nik = '';
}

// Cek duplikat NIK (hanya jika NIK ada)
if ($nik !== '') {
    $byNik = fetchOne("SELECT id FROM balita WHERE nik_anak = '" . escape($nik) . "' LIMIT 1");
    if ($byNik) {
        query("UPDATE balita SET id_penduduk_opensid = $id_opensid, status_integrasi = 'terhubung' WHERE id = " . (int)$byNik['id']);
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

// Normalisasi tanggal lahir → YYYY-MM-DD
$tglRaw = trim((string)($p['tanggal_lahir'] ?? $p['tanggallahir'] ?? ''));
$tgl = '';
if ($tglRaw !== '') {
    // Sudah Y-m-d
    if (preg_match('/^(\d{4})-(\d{2})-(\d{2})/', $tglRaw, $m)) {
        $tgl = $m[1] . '-' . $m[2] . '-' . $m[3];
    } elseif (preg_match('/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})$/', $tglRaw, $m)) {
        $tgl = $m[3] . '-' . str_pad($m[2], 2, '0', STR_PAD_LEFT) . '-' . str_pad($m[1], 2, '0', STR_PAD_LEFT);
    } elseif (preg_match('/^(\d{4})(\d{2})(\d{2})$/', $tglRaw, $m)) {
        $tgl = $m[1] . '-' . $m[2] . '-' . $m[3];
    } else {
        try {
            $dt = new DateTime($tglRaw);
            $tgl = $dt->format('Y-m-d');
        } catch (Exception $e) {
            $tgl = '';
        }
    }
    // Validasi tanggal benar-benar valid
    if ($tgl !== '') {
        $parts = explode('-', $tgl);
        if (count($parts) !== 3 || !checkdate((int)$parts[1], (int)$parts[2], (int)$parts[0])) {
            $tgl = '';
        }
        // Tanggal tidak masuk akal
        if ($tgl !== '' && ($parts[0] < 1990 || $parts[0] > (int)date('Y'))) {
            // masih boleh jika tahun masuk akal untuk balita
        }
    }
}

if ($tgl === '') {
    jsonResponse(false, 'Tanggal lahir anak tidak valid/kosong di OpenSID. Perbaiki data penduduk dulu, lalu daftar ulang.');
}

// Validasi usia 0–59 bulan
try {
    $d1 = new DateTime($tgl);
    $diff = $d1->diff(new DateTime());
    $bulan = $diff->y * 12 + $diff->m;
    if ($diff->invert) {
        jsonResponse(false, 'Tanggal lahir di masa depan — perbaiki data di OpenSID');
    }
    if ($bulan > 59) {
        jsonResponse(false, "Usia anak $bulan bulan (lebih dari 59 bulan) — bukan sasaran balita Posyandu");
    }
} catch (Exception $e) {
    jsonResponse(false, 'Tanggal lahir tidak dapat diproses');
}

$namaRaw = trim((string)($p['nama'] ?? $p['nama_lengkap'] ?? ''));
if ($namaRaw === '') {
    jsonResponse(false, 'Nama anak tidak tersedia di OpenSID');
}

$jk = $p['jenis_kelamin'] ?? mapSex($p['sex'] ?? null);
if ($jk !== 'L' && $jk !== 'P') {
    $jk = 'L';
}

$nomor = function_exists('generateNomorPeserta')
    ? generateNomorPeserta('BLT', 'balita')
    : ('BLT-' . date('Y') . '-' . date('His'));

// Pastikan nomor unik (jika bentrok race condition)
$tries = 0;
while ($tries < 5 && fetchOne("SELECT id FROM balita WHERE nomor_peserta = '" . escape($nomor) . "' LIMIT 1")) {
    $tries++;
    $nomor = 'BLT-' . date('Y') . '-' . str_pad((string)(rand(1, 999) + $tries * 10), 3, '0', STR_PAD_LEFT) . date('s');
}

$nama = escape($namaRaw);

// NIK: NULL jika kosong agar UNIQUE tidak bentrok antar anak tanpa NIK
$nikSql = ($nik !== '') ? ("'" . escape($nik) . "'") : 'NULL';

$fields = [
    'nomor_peserta'         => "'" . escape($nomor) . "'",
    'id_penduduk_opensid'   => (string)(int)$id_opensid,
    'status_integrasi'      => "'terhubung'",
    'nik_anak'              => $nikSql,
    'no_kk'                 => "'" . escape($p['no_kk'] ?? '') . "'",
    'nama_lengkap'          => "'" . $nama . "'",
    'jenis_kelamin'         => "'" . escape($jk) . "'",
    'tempat_lahir'          => "'" . escape($p['tempat_lahir'] ?? '') . "'",
    'tanggal_lahir'         => "'" . escape($tgl) . "'",
    'nama_ayah'             => "'" . escape($p['nama_ayah'] ?? '') . "'",
    'nik_ayah'              => "'" . escape($p['nik_ayah'] ?? '') . "'",
    'nama_ibu'              => "'" . escape($p['nama_ibu'] ?? '') . "'",
    'nik_ibu'               => "'" . escape($p['nik_ibu'] ?? '') . "'",
    'no_hp'                 => "'" . escape($p['no_hp'] ?? '') . "'",
    'dusun'                 => "'" . escape($p['dusun'] ?? '') . "'",
    'rt'                    => "'" . escape($p['rt'] ?? '') . "'",
    'rw'                    => "'" . escape($p['rw'] ?? '') . "'",
    'alamat_lengkap'        => "'" . escape($p['alamat'] ?? '') . "'",
    'status_aktif'          => '1',
];

$cols = implode(',', array_keys($fields));
$vals = implode(',', array_values($fields));
$sql = "INSERT INTO balita ($cols) VALUES ($vals)";

if (query($sql)) {
    $newId = (int)lastInsertId();
    if ($newId <= 0) {
        // Fallback: ambil by opensid id
        $row = fetchOne("SELECT id FROM balita WHERE id_penduduk_opensid = $id_opensid ORDER BY id DESC LIMIT 1");
        $newId = (int)($row['id'] ?? 0);
    }
    if ($newId > 0) {
        @query("INSERT INTO log_integrasi_opensid (modul, referensi_id, id_penduduk_opensid, nik, aksi, keterangan, user_id)
            VALUES ('balita', $newId, $id_opensid, " . $nikSql . ", 'hubungkan', 'Daftar dari OpenSID', " . (int)($_SESSION['kader_id'] ?? 0) . ")");
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'Berhasil didaftarkan ke Posyandu',
            'id' => $newId,
            'data' => ['id' => $newId],
        ]);
        exit;
    }
}

// Ambil error MySQL untuk pesan yang lebih jelas
global $conn;
$err = ($conn && isset($conn->error)) ? $conn->error : '';
$msg = 'Gagal menyimpan data balita';
if ($err) {
    if (stripos($err, 'Duplicate') !== false && stripos($err, 'nik') !== false) {
        $msg = 'NIK anak sudah terdaftar di Posyandu. Refresh halaman lalu coba lagi.';
    } elseif (stripos($err, 'Duplicate') !== false && stripos($err, 'nomor_peserta') !== false) {
        $msg = 'Nomor peserta bentrok. Silakan coba daftar ulang.';
    } elseif (stripos($err, 'tanggal_lahir') !== false || stripos($err, 'Incorrect date') !== false) {
        $msg = 'Tanggal lahir tidak valid di OpenSID. Perbaiki data penduduk terlebih dahulu.';
    } else {
        $msg = 'Gagal menyimpan: ' . $err;
    }
}
jsonResponse(false, $msg);
