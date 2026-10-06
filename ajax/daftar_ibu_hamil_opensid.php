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
 * Daftarkan perempuan dari OpenSID ke tabel ibu_hamil.
 * Wajib: HPHT (untuk usia kehamilan & HPL).
 * Data suami diisi otomatis dari anggota KK OpenSID.
 *
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
if (!verifyCsrf($csrf)) {
    jsonResponse(false, 'Token keamanan tidak valid. Silakan refresh halaman lalu coba lagi.');
}

$id_opensid = (int)($_POST['id_penduduk_opensid'] ?? 0);
if ($id_opensid <= 0) {
    jsonResponse(false, 'ID penduduk OpenSID tidak valid');
}

// Data kehamilan wajib
$hpht = trim($_POST['hpht'] ?? '');
$kehamilan_ke = (int)($_POST['kehamilan_ke'] ?? 1);
if ($kehamilan_ke < 1) $kehamilan_ke = 1;

if ($hpht === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $hpht)) {
    jsonResponse(false, 'HPHT (Hari Pertama Haid Terakhir) wajib diisi dengan format tanggal yang valid');
}

try {
    $dHpht = new DateTime($hpht);
    $today = new DateTime('today');
    if ($dHpht > $today) {
        jsonResponse(false, 'HPHT tidak boleh di masa depan');
    }
    $minggu = (int)floor(($today->getTimestamp() - $dHpht->getTimestamp()) / (7 * 86400));
    if ($minggu > 45) {
        jsonResponse(false, "Usia kehamilan terlalu lama ($minggu minggu). Periksa kembali tanggal HPHT.");
    }
} catch (Exception $e) {
    jsonResponse(false, 'Tanggal HPHT tidak valid');
}

if (!opensid_available()) {
    jsonResponse(false, 'OpenSID tidak terhubung');
}

$ada = fetchOne("SELECT id FROM ibu_hamil WHERE id_penduduk_opensid = $id_opensid LIMIT 1");
if ($ada) {
    $hpl = hitungHPL($hpht);
    $usia = hitungUsiaKandungan($hpht, date('Y-m-d'));
    query("UPDATE ibu_hamil SET hpht='" . escape($hpht) . "', hpl='" . escape($hpl) . "',
        usia_kehamilan=" . (int)$usia . ", kehamilan_ke=$kehamilan_ke
        WHERE id=" . (int)$ada['id']);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Ibu sudah terdaftar — data kehamilan diperbarui',
        'id' => (int)$ada['id'],
        'data' => ['id' => (int)$ada['id'], 'usia_kehamilan' => (int)$usia, 'hpl' => $hpl],
    ]);
    exit;
}

$p = getPendudukOpenSIDById($id_opensid);
if (!$p) {
    jsonResponse(false, 'Data penduduk tidak ditemukan di OpenSID');
}

$jk = $p['jenis_kelamin'] ?? mapSex($p['sex'] ?? null);
if ($jk !== 'P') {
    jsonResponse(false, 'Hanya penduduk perempuan yang dapat didaftarkan sebagai ibu hamil');
}

$nik = trim($p['nik'] ?? '');
if ($nik !== '') {
    $byNik = fetchOne("SELECT id FROM ibu_hamil WHERE nik = '" . escape($nik) . "' LIMIT 1");
    if ($byNik) {
        $hpl = hitungHPL($hpht);
        $usia = hitungUsiaKandungan($hpht, date('Y-m-d'));
        query("UPDATE ibu_hamil SET
            id_penduduk_opensid = $id_opensid,
            status_integrasi = 'terhubung',
            hpht='" . escape($hpht) . "',
            hpl='" . escape($hpl) . "',
            usia_kehamilan=" . (int)$usia . ",
            kehamilan_ke=$kehamilan_ke
            WHERE id = " . (int)$byNik['id']);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'Data lokal ditautkan ke OpenSID & kehamilan disimpan',
            'id' => (int)$byNik['id'],
            'data' => ['id' => (int)$byNik['id'], 'usia_kehamilan' => (int)$usia, 'hpl' => $hpl],
        ]);
        exit;
    }
}

$tgl = $p['tanggal_lahir'] ?? '';
if ($tgl) {
    try {
        $umur = (int)(new DateTime($tgl))->diff(new DateTime())->y;
        if ($umur < 12 || $umur > 55) {
            jsonResponse(false, "Usia $umur tahun di luar rentang sasaran ibu hamil");
        }
    } catch (Exception $e) {}
}

// Data suami: otomatis hanya jika ibu bukan Kepala Keluarga & suami ada di KK sama.
// Jika ibu Kepala Keluarga / pisah KK → isi manual dari form.
$nama_suami = '';
$nik_suami = '';
$pekerjaan_suami = '';
$no_hp_suami = '';
$suami_manual = false;
$id_kk = (int)($p['id_kk'] ?? 0);
$kk_level_ibu = (int)($p['kk_level'] ?? 0);
if ($id_kk > 0 && function_exists('getSuamiFromOpenSID')) {
    $suami = getSuamiFromOpenSID($id_kk, $kk_level_ibu);
    if ($suami) {
        $suami_manual = !empty($suami['manual_required']);
        if (!$suami_manual) {
            $nama_suami = $suami['nama'] ?? '';
            $nik_suami = $suami['nik'] ?? '';
            $pekerjaan_suami = $suami['pekerjaan'] ?? '';
            $no_hp_suami = $suami['no_hp'] ?? '';
        }
    }
}
// Input manual selalu diutamakan jika diisi user
if (isset($_POST['nama_suami']) && trim((string)$_POST['nama_suami']) !== '') {
    $nama_suami = trim($_POST['nama_suami']);
}
if (isset($_POST['nik_suami']) && trim((string)$_POST['nik_suami']) !== '') {
    $nik_suami = trim($_POST['nik_suami']);
}
if (isset($_POST['pekerjaan_suami']) && trim((string)$_POST['pekerjaan_suami']) !== '') {
    $pekerjaan_suami = trim($_POST['pekerjaan_suami']);
}
if (isset($_POST['no_hp_suami']) && trim((string)$_POST['no_hp_suami']) !== '') {
    $no_hp_suami = trim($_POST['no_hp_suami']);
}

$hpl = hitungHPL($hpht);
$usia_kehamilan = hitungUsiaKandungan($hpht, date('Y-m-d'));

$nomor = function_exists('generateNomorPeserta') ? generateNomorPeserta('IBH', 'ibu_hamil') : ('IBH' . date('ymdHis'));
$nama = escape($p['nama'] ?? '');
if (!$nama) {
    jsonResponse(false, 'Nama tidak tersedia');
}

$fields = [
    'nomor_peserta'       => $nomor,
    'id_penduduk_opensid' => $id_opensid,
    'status_integrasi'    => 'terhubung',
    'nik'                 => escape($nik),
    'no_kk'               => escape($p['no_kk'] ?? ''),
    'nama'                => $nama,
    'tempat_lahir'        => escape($p['tempat_lahir'] ?? ''),
    'tanggal_lahir'       => escape($tgl),
    'pekerjaan'           => escape($p['pekerjaan'] ?? ''),
    'pendidikan'          => escape($p['pendidikan'] ?? ''),
    'no_hp'               => escape($p['no_hp'] ?? ''),
    'nama_suami'          => escape($nama_suami),
    'nik_suami'           => escape($nik_suami),
    'pekerjaan_suami'     => escape($pekerjaan_suami),
    'no_hp_suami'         => escape($no_hp_suami),
    'kehamilan_ke'        => $kehamilan_ke,
    'hpht'                => escape($hpht),
    'hpl'                 => escape($hpl),
    'usia_kehamilan'      => (int)$usia_kehamilan,
    'dusun'               => escape($p['dusun'] ?? ''),
    'rt'                  => escape($p['rt'] ?? ''),
    'rw'                  => escape($p['rw'] ?? ''),
    'alamat_lengkap'      => escape($p['alamat'] ?? ''),
    'status_aktif'        => 1,
];

$cols = implode(',', array_keys($fields));
$vals = [];
foreach ($fields as $v) {
    $vals[] = ($v === 'NULL' || $v === null) ? 'NULL' : "'" . $v . "'";
}
$sql = "INSERT INTO ibu_hamil ($cols) VALUES (" . implode(',', $vals) . ")";

if (query($sql)) {
    $newId = lastInsertId();
    @query("INSERT INTO log_integrasi_opensid (modul, referensi_id, id_penduduk_opensid, nik, aksi, keterangan, user_id)
        VALUES ('ibu_hamil', $newId, $id_opensid, '" . escape($nik) . "', 'hubungkan', 'Daftar dari OpenSID + HPHT', " . (int)($_SESSION['kader_id'] ?? 0) . ")");
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Berhasil didaftarkan. Usia kehamilan: ' . (int)$usia_kehamilan . ' minggu. HPL: ' . $hpl,
        'id' => (int)$newId,
        'data' => [
            'id' => (int)$newId,
            'usia_kehamilan' => (int)$usia_kehamilan,
            'hpl' => $hpl,
            'nama_suami' => $nama_suami,
            'suami_manual' => !empty($suami_manual) && $nama_suami === '',
        ],
    ]);
    exit;
}
jsonResponse(false, 'Gagal menyimpan data');
