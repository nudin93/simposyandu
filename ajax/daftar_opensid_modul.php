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
 * Daftarkan penduduk OpenSID ke modul lokal (anak_tk / remaja / lansia / usia_produktif)
 * POST: type, id_penduduk_opensid, csrf_token
 */
error_reporting(0);
ini_set('display_errors', '0');
require_once __DIR__ . '/../config/init.php';
requireLogin();

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$csrf = $_POST['csrf_token'] ?? '';
if ($csrf !== '' && function_exists('verifyCsrf') && !verifyCsrf($csrf)) {
    echo json_encode(['success' => false, 'message' => 'Token keamanan tidak valid. Silakan refresh halaman.']);
    exit;
}

$type = trim($_POST['type'] ?? '');
$config = [
    'remaja' => [
        'table' => 'remaja',
        'prefix' => 'RMJ',
        'nama_col' => 'nama_lengkap',
        'nik_col' => 'nik',
        'min_tahun' => 10,
        'max_tahun' => 18,
        'label' => 'Remaja',
    ],
    'anak_tk' => [
        'table' => 'anak_tk',
        'prefix' => 'ATK',
        'nama_col' => 'nama_lengkap',
        'nik_col' => 'nik',
        'min_tahun' => 3,
        'max_tahun' => 7,
        'label' => 'Anak TK',
    ],
    'lansia' => [
        'table' => 'lansia',
        'prefix' => 'LNS',
        'nama_col' => 'nama',
        'nik_col' => 'nik',
        'min_tahun' => 60,
        'max_tahun' => 150,
        'label' => 'Lansia',
    ],
    'usia_produktif' => [
        'table' => 'usia_produktif',
        'prefix' => 'USP',
        'nama_col' => 'nama_lengkap',
        'nik_col' => 'nik',
        'min_tahun' => 19,
        'max_tahun' => 59,
        'label' => 'Usia Produktif',
    ],
];

if (!isset($config[$type])) {
    echo json_encode(['success' => false, 'message' => 'Tipe modul tidak valid']);
    exit;
}
$cfg = $config[$type];
$table = $cfg['table'];
$nikCol = $cfg['nik_col'];
$namaCol = $cfg['nama_col'];

$id_opensid = (int)($_POST['id_penduduk_opensid'] ?? 0);
if ($id_opensid <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID penduduk OpenSID tidak valid']);
    exit;
}
if (!opensid_available()) {
    echo json_encode(['success' => false, 'message' => 'OpenSID tidak terhubung']);
    exit;
}

// Sudah terdaftar by OpenSID id?
$ada = fetchOne("SELECT id FROM `$table` WHERE id_penduduk_opensid = $id_opensid LIMIT 1");
if ($ada) {
    echo json_encode([
        'success' => true,
        'message' => $cfg['label'] . ' sudah terdaftar',
        'id' => (int)$ada['id'],
        'data' => ['id' => (int)$ada['id']],
    ]);
    exit;
}

$p = getPendudukOpenSIDById($id_opensid);
if (!$p) {
    echo json_encode(['success' => false, 'message' => 'Data penduduk tidak ditemukan di OpenSID (ID: ' . $id_opensid . ')']);
    exit;
}

$nik = trim((string)($p['nik'] ?? ''));
if ($nik === '' || $nik === '0' || strtolower($nik) === 'null') {
    $nik = '';
}

// Cek duplikat NIK → tautkan
if ($nik !== '') {
    $byNik = fetchOne("SELECT id FROM `$table` WHERE `$nikCol` = '" . escape($nik) . "' LIMIT 1");
    if ($byNik) {
        query("UPDATE `$table` SET id_penduduk_opensid = $id_opensid, status_integrasi = 'terhubung' WHERE id = " . (int)$byNik['id']);
        echo json_encode([
            'success' => true,
            'message' => 'Data lokal ditautkan ke OpenSID',
            'id' => (int)$byNik['id'],
            'data' => ['id' => (int)$byNik['id']],
        ]);
        exit;
    }
}

// Tanggal lahir
$tglRaw = trim((string)($p['tanggal_lahir'] ?? ''));
$tgl = '';
if (preg_match('/^(\d{4})-(\d{2})-(\d{2})/', $tglRaw, $m)) {
    $tgl = $m[1] . '-' . $m[2] . '-' . $m[3];
} elseif (preg_match('/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})$/', $tglRaw, $m)) {
    $tgl = $m[3] . '-' . str_pad($m[2], 2, '0', STR_PAD_LEFT) . '-' . str_pad($m[1], 2, '0', STR_PAD_LEFT);
} elseif ($tglRaw !== '') {
    try { $tgl = (new DateTime($tglRaw))->format('Y-m-d'); } catch (Exception $e) { $tgl = ''; }
}
if ($tgl !== '') {
    $parts = explode('-', $tgl);
    if (count($parts) !== 3 || !checkdate((int)$parts[1], (int)$parts[2], (int)$parts[0])) {
        $tgl = '';
    }
}
if ($tgl === '') {
    echo json_encode(['success' => false, 'message' => 'Tanggal lahir tidak valid/kosong di OpenSID. Perbaiki data penduduk dulu.']);
    exit;
}

try {
    $d1 = new DateTime($tgl);
    $diff = $d1->diff(new DateTime());
    if ($diff->invert) {
        echo json_encode(['success' => false, 'message' => 'Tanggal lahir di masa depan']);
        exit;
    }
    $umur = (int)$diff->y;
    if ($umur < $cfg['min_tahun'] || $umur > $cfg['max_tahun']) {
        echo json_encode(['success' => false, 'message' => "Usia $umur tahun di luar standar {$cfg['label']} ({$cfg['min_tahun']}–{$cfg['max_tahun']} tahun)"]);
        exit;
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Tanggal lahir tidak dapat diproses']);
    exit;
}

$namaRaw = trim((string)($p['nama'] ?? ''));
if ($namaRaw === '') {
    echo json_encode(['success' => false, 'message' => 'Nama tidak tersedia di OpenSID']);
    exit;
}

$jk = $p['jenis_kelamin'] ?? mapSex($p['sex'] ?? null);
if ($jk !== 'L' && $jk !== 'P') {
    $jk = 'L';
}

$nikSql = ($nik !== '') ? ("'" . escape($nik) . "'") : 'NULL';
$alamatParts = array_filter([
    trim((string)($p['alamat'] ?? '')),
    trim((string)($p['dusun'] ?? '')) !== '' ? 'Dusun ' . trim($p['dusun']) : '',
    (trim((string)($p['rt'] ?? '')) !== '' || trim((string)($p['rw'] ?? '')) !== '')
        ? ('RT ' . (trim((string)($p['rt'] ?? '')) ?: '-') . '/RW ' . (trim((string)($p['rw'] ?? '')) ?: '-'))
        : '',
]);
$alamatGabung = trim(implode(', ', $alamatParts));

$newId = 0;
$lastErr = '';

for ($attempt = 0; $attempt < 5; $attempt++) {
    $nomor = function_exists('generateNomorPeserta')
        ? generateNomorPeserta($cfg['prefix'], $table)
        : ($cfg['prefix'] . '-' . date('Y') . '-' . date('His') . $attempt);

    if (fetchOne("SELECT id FROM `$table` WHERE nomor_peserta = '" . escape($nomor) . "' LIMIT 1")) {
        continue;
    }

    // Field sesuai struktur tabel masing-masing (hindari Unknown column)
    if ($type === 'lansia') {
        // Tabel lansia: tanpa dusun/rt/rw — alamat digabung ke alamat_lengkap
        $fields = [
            'nomor_peserta'       => "'" . escape($nomor) . "'",
            'id_penduduk_opensid' => (string)(int)$id_opensid,
            'status_integrasi'    => "'terhubung'",
            'nik'                 => $nikSql,
            'no_kk'               => "'" . escape($p['no_kk'] ?? '') . "'",
            'nama'                => "'" . escape($namaRaw) . "'",
            'jenis_kelamin'       => "'" . escape($jk) . "'",
            'tempat_lahir'        => "'" . escape($p['tempat_lahir'] ?? '') . "'",
            'tanggal_lahir'       => "'" . escape($tgl) . "'",
            'alamat_lengkap'      => "'" . escape($alamatGabung) . "'",
            'no_hp'               => "'" . escape($p['no_hp'] ?? '') . "'",
            'pekerjaan'           => "'" . escape($p['pekerjaan'] ?? '') . "'",
            'pendidikan'          => "'" . escape($p['pendidikan'] ?? '') . "'",
            'status_aktif'        => '1',
        ];
    } elseif ($type === 'anak_tk') {
        $fields = [
            'nomor_peserta'       => "'" . escape($nomor) . "'",
            'id_penduduk_opensid' => (string)(int)$id_opensid,
            'status_integrasi'    => "'terhubung'",
            'nik'                 => $nikSql,
            'no_kk'               => "'" . escape($p['no_kk'] ?? '') . "'",
            'nama_lengkap'        => "'" . escape($namaRaw) . "'",
            'jenis_kelamin'       => "'" . escape($jk) . "'",
            'tanggal_lahir'       => "'" . escape($tgl) . "'",
            'kategori'            => "'Anak TK'",
            'kelas'               => "'TK A'",
            'dusun'               => "'" . escape($p['dusun'] ?? '') . "'",
            'rt'                  => "'" . escape($p['rt'] ?? '') . "'",
            'rw'                  => "'" . escape($p['rw'] ?? '') . "'",
            'alamat_lengkap'      => "'" . escape($p['alamat'] ?? '') . "'",
            'status_aktif'        => '1',
        ];
    } elseif ($type === 'remaja') {
        $kategori = ($umur <= 12) ? 'Anak Sekolah' : 'Remaja';
        $fields = [
            'nomor_peserta'       => "'" . escape($nomor) . "'",
            'id_penduduk_opensid' => (string)(int)$id_opensid,
            'status_integrasi'    => "'terhubung'",
            'nik'                 => $nikSql,
            'no_kk'               => "'" . escape($p['no_kk'] ?? '') . "'",
            'nama_lengkap'        => "'" . escape($namaRaw) . "'",
            'jenis_kelamin'       => "'" . escape($jk) . "'",
            'tanggal_lahir'       => "'" . escape($tgl) . "'",
            'kategori'            => "'" . escape($kategori) . "'",
            'dusun'               => "'" . escape($p['dusun'] ?? '') . "'",
            'rt'                  => "'" . escape($p['rt'] ?? '') . "'",
            'rw'                  => "'" . escape($p['rw'] ?? '') . "'",
            'alamat_lengkap'      => "'" . escape($p['alamat'] ?? '') . "'",
            'status_aktif'        => '1',
        ];
    } else {
        // usia_produktif
        $fields = [
            'nomor_peserta'       => "'" . escape($nomor) . "'",
            'id_penduduk_opensid' => (string)(int)$id_opensid,
            'status_integrasi'    => "'terhubung'",
            'nik'                 => $nikSql,
            'no_kk'               => "'" . escape($p['no_kk'] ?? '') . "'",
            'nama_lengkap'        => "'" . escape($namaRaw) . "'",
            'jenis_kelamin'       => "'" . escape($jk) . "'",
            'tanggal_lahir'       => "'" . escape($tgl) . "'",
            'dusun'               => "'" . escape($p['dusun'] ?? '') . "'",
            'rt'                  => "'" . escape($p['rt'] ?? '') . "'",
            'rw'                  => "'" . escape($p['rw'] ?? '') . "'",
            'alamat_lengkap'      => "'" . escape($p['alamat'] ?? '') . "'",
            'status_aktif'        => '1',
        ];
    }

    $colsOk = [];
    $valsOk = [];
    foreach ($fields as $c => $v) {
        $colsOk[] = "`$c`";
        $valsOk[] = $v;
    }
    $sql = "INSERT INTO `$table` (" . implode(',', $colsOk) . ") VALUES (" . implode(',', $valsOk) . ")";

    if (query($sql)) {
        $newId = (int)lastInsertId();
        if ($newId <= 0) {
            $row = fetchOne("SELECT id FROM `$table` WHERE id_penduduk_opensid = $id_opensid ORDER BY id DESC LIMIT 1");
            $newId = (int)($row['id'] ?? 0);
        }
        if ($newId > 0) {
            echo json_encode([
                'success' => true,
                'message' => $cfg['label'] . ' berhasil didaftarkan ke Posyandu',
                'id' => $newId,
                'data' => ['id' => $newId],
            ]);
            exit;
        }
    }

    global $conn;
    $lastErr = ($conn && isset($conn->error)) ? $conn->error : '';
    if ($lastErr && stripos($lastErr, 'Duplicate') === false) {
        break;
    }
}

$msg = 'Gagal menyimpan data ' . $cfg['label'];
if ($lastErr) {
    if (stripos($lastErr, 'Duplicate') !== false && stripos($lastErr, 'nomor_peserta') !== false) {
        $msg = 'Nomor peserta bentrok. Silakan coba daftar ulang.';
    } elseif (stripos($lastErr, 'Duplicate') !== false) {
        $msg = 'Data sudah ada (duplikat). Refresh halaman.';
    } elseif (stripos($lastErr, 'Unknown column') !== false) {
        $msg = 'Struktur tabel belum sesuai: ' . $lastErr;
    } else {
        $msg = 'Gagal menyimpan: ' . $lastErr;
    }
}
echo json_encode(['success' => false, 'message' => $msg]);
