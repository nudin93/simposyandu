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
 * SIMPosyandu - Koneksi Database
 * Database aplikasi: SIMPosyandu (pelayanan kesehatan)
 * Database OpenSID: sumber data penduduk (read-only)
 *
 * KEAMANAN: Kredensial dipisah ke file credentials.php
 * 1. Salin credentials.example.php menjadi credentials.php
 * 2. Isi data database asli di credentials.php
 * 3. JANGAN commit / bagikan credentials.php ke publik
 */

// ========== LOAD CREDENTIALS (aman) ==========
$credFile = __DIR__ . '/credentials.php';
if (file_exists($credFile)) {
    require_once $credFile;
} else {
    // Fallback generik untuk instalasi baru (isi asli ada di credentials.php, tidak ikut Git).
    // Disarankan membuat config/credentials.php dari credentials.example.php
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'simposyandu');

    define('OPENSID_DB_HOST', 'localhost');
    define('OPENSID_DB_USER', 'root');
    define('OPENSID_DB_PASS', '');
    define('OPENSID_DB_NAME', 'opensid');
}

// Pastikan konstanta sudah terdefinisi
if (!defined('DB_HOST'))     define('DB_HOST', 'localhost');
if (!defined('DB_USER'))     define('DB_USER', 'root');
if (!defined('DB_PASS'))     define('DB_PASS', '');
if (!defined('DB_NAME'))     define('DB_NAME', 'simposyandu');
if (!defined('OPENSID_DB_HOST')) define('OPENSID_DB_HOST', 'localhost');
if (!defined('OPENSID_DB_USER')) define('OPENSID_DB_USER', 'root');
if (!defined('OPENSID_DB_PASS')) define('OPENSID_DB_PASS', '');
if (!defined('OPENSID_DB_NAME')) define('OPENSID_DB_NAME', 'opensid');

// APP_URL - otomatis dari domain + path aplikasi (subfolder aman)
$_proto = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$_host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$_script = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
// naik dari /modules/xxx atau /ajax ke root app
while ($_script !== '/' && $_script !== '' && (
    preg_match('#/(modules|ajax|includes|config|assets|uploads)(/|$)#', $_script)
    || preg_match('#/(balita|ibu_hamil|lansia|penduduk|keluarga|sanitasi|phbs|kb|laporan|pengaturan|kader|imunisasi|vitamin|jadwal|pemeriksaan_balita|pemeriksaan_ibu_hamil|pemeriksaan_lansia|kartu|backup|bayi|pemeriksaan_bayi|remaja|pemeriksaan_remaja|usia_produktif|pemeriksaan_dewasa|kegiatan_posyandu|kunjungan_rumah|statistik|artikel|analitik)(/|$)#', $_script)
)) {
    $_script = str_replace('\\', '/', dirname($_script));
}
if ($_script === '/' || $_script === '\\' || $_script === '.') $_script = '';
define('APP_URL', rtrim($_proto . '://' . $_host . $_script, '/'));

// Koneksi database SIMPosyandu
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("<div style='background:#f8d7da;color:#721c24;padding:20px;font-family:sans-serif;'>
    <h3>Koneksi Database SIMPosyandu Gagal!</h3>
    <p>" . htmlspecialchars($conn->connect_error) . "</p>
    <p>Pastikan MySQL berjalan dan database <b>" . DB_NAME . "</b> sudah dibuat.</p>
    </div>");
}
$conn->set_charset("utf8mb4");

// Koneksi database OpenSID (read-only untuk data penduduk)
$opensid_conn = null;
$opensid_error = null;
try {
    $opensid_conn = @new mysqli(OPENSID_DB_HOST, OPENSID_DB_USER, OPENSID_DB_PASS, OPENSID_DB_NAME);
    if ($opensid_conn->connect_error) {
        $opensid_error = $opensid_conn->connect_error;
        $opensid_conn = null;
    } else {
        $opensid_conn->set_charset("utf8mb4");
    }
} catch (Exception $e) {
    $opensid_error = $e->getMessage();
    $opensid_conn = null;
}

function db() {
    global $conn;
    return $conn;
}

function opensid_db() {
    global $opensid_conn;
    return $opensid_conn;
}

function opensid_available() {
    global $opensid_conn;
    return $opensid_conn !== null && !$opensid_conn->connect_error;
}

function escape($str) {
    global $conn;
    return $conn->real_escape_string((string)$str);
}

function opensid_escape($str) {
    global $opensid_conn;
    if (!$opensid_conn) return addslashes((string)$str);
    return $opensid_conn->real_escape_string((string)$str);
}

function query($sql) {
    global $conn;
    $result = $conn->query($sql);
    if (!$result) {
        error_log("DB Error: " . $conn->error . " | SQL: " . $sql);
    }
    return $result;
}

function opensid_query($sql) {
    global $opensid_conn;
    if (!$opensid_conn) return false;
    $result = $opensid_conn->query($sql);
    if (!$result) {
        error_log("OpenSID DB Error: " . $opensid_conn->error . " | SQL: " . $sql);
    }
    return $result;
}

function fetchAll($sql) {
    $result = query($sql);
    $rows = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
    }
    return $rows;
}

function opensid_fetchAll($sql) {
    $result = opensid_query($sql);
    $rows = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
    }
    return $rows;
}

function fetchOne($sql) {
    $result = query($sql);
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    return null;
}

function opensid_fetchOne($sql) {
    $result = opensid_query($sql);
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    return null;
}


// ========== SECURITY HELPERS (patch v1.0.1 anti-injection) ==========
function db_escape_like($s) {
    global $conn;
    $s = (string)$s;
    $s = str_replace(['\\\\', '%', '_'], ['\\\\\\\\', '\\\\%', '\\\\_'], $s);
    return $conn ? $conn->real_escape_string($s) : addslashes($s);
}
function db_int($v, $def = 0) {
    if ($v === null || $v === '') return $def;
    if (is_int($v)) return $v;
    if (is_string($v) && preg_match('/^-?\\d+$/', trim($v))) return (int)$v;
    if (is_numeric($v)) return (int)$v;
    return $def;
}
function db_date($v, $def = null) {
    $v = trim((string)$v);
    if (preg_match('/^\\d{4}-\\d{2}-\\d{2}$/', $v)) {
        $p = explode('-', $v);
        if (checkdate((int)$p[1], (int)$p[2], (int)$p[0])) return $v;
    }
    return $def;
}
function db_enum($v, $allowed, $def = '') {
    return in_array($v, $allowed, true) ? $v : $def;
}
function csv_guard($v) {
    if ($v === null) return '';
    $s = (string)$v;
    if ($s !== '' && preg_match('/^[\\s]*[=\\+\\-@\\t\\r\\n\|%]/', $s)) return "'" . $s;
    return $s;
}
function csv_row_guard($row) {
    return array_map('csv_guard', array_values($row));
}
function safe_csv_filename($base, $prefix = 'export') {
    $base = preg_replace('/[^a-z0-9_-]/i', '', (string)$base);
    if ($base === '') $base = $prefix;
    return $base . '_' . date('Ymd') . '.csv';
}

function lastInsertId() {
    global $conn;
    return $conn->insert_id;
}

function numRows($sql) {
    $result = query($sql);
    return $result ? $result->num_rows : 0;
}

/**
 * Mapping sex OpenSID (1=L, 2=P) ke L/P
 */
function mapSex($sex) {
    if ($sex == 1 || $sex === '1' || strtoupper($sex) === 'L') return 'L';
    if ($sex == 2 || $sex === '2' || strtoupper($sex) === 'P') return 'P';
    return '';
}

/**
 * Mapping kk_level OpenSID ke status dalam keluarga
 */
function mapHubungan($kk_level) {
    $map = [
        1 => 'Kepala Keluarga',
        2 => 'Suami',
        3 => 'Istri',
        4 => 'Anak',
        5 => 'Menantu',
        6 => 'Cucu',
        7 => 'Orang Tua',
        8 => 'Mertua',
        9 => 'Famili Lain',
        10 => 'Pembantu',
        11 => 'Lainnya'
    ];
    return $map[(int)$kk_level] ?? 'Lainnya';
}

/**
 * Mapping status kawin OpenSID
 */
function mapKawin($status) {
    $map = [
        1 => 'BELUM KAWIN',
        2 => 'KAWIN',
        3 => 'CERAI HIDUP',
        4 => 'CERAI MATI'
    ];
    return $map[(int)$status] ?? '-';
}

/**
 * Cari penduduk hidup dari OpenSID berdasarkan nama / NIK / No KK
 */
function cariPendudukOpenSID($q, $jk = '', $limit = 25) {
    if (!opensid_available() || strlen(trim($q)) < 1) return [];

    $s = opensid_escape(trim($q));
    $where = "p.status_dasar = 1 AND (
        p.nama LIKE '%$s%' OR p.nik LIKE '%$s%' OR IFNULL(k.no_kk,'') LIKE '%$s%'
    )";
    if ($jk === 'L') $where .= " AND p.sex = 1";
    if ($jk === 'P') $where .= " AND p.sex = 2";

    $sql = "
        SELECT
            p.id AS id_penduduk,
            p.nik,
            k.no_kk,
            p.nama,
            p.sex,
            p.tempatlahir AS tempat_lahir,
            p.tanggallahir AS tanggal_lahir,
            TIMESTAMPDIFF(YEAR, p.tanggallahir, CURDATE()) AS umur,
            p.kk_level,
            p.nama_ayah,
            p.ayah_nik AS nik_ayah,
            p.nama_ibu,
            p.ibu_nik AS nik_ibu,
            p.telepon AS no_hp,
            p.alamat_sekarang AS alamat,
            p.id_kk,
            p.id_cluster,
            w.dusun,
            w.rt,
            w.rw,
            pk.nama AS pekerjaan,
            pd.nama AS pendidikan
        FROM tweb_penduduk p
        LEFT JOIN tweb_keluarga k ON k.id = p.id_kk
        LEFT JOIN tweb_wil_clusterdesa w ON w.id = p.id_cluster
        LEFT JOIN tweb_penduduk_pekerjaan pk ON pk.id = p.pekerjaan_id
        LEFT JOIN tweb_penduduk_pendidikan_kk pd ON pd.id = p.pendidikan_kk_id
        WHERE $where
        ORDER BY
            CASE WHEN p.nama LIKE '$s%' THEN 0 ELSE 1 END,
            p.nama
        LIMIT " . (int)$limit;

    $rows = opensid_fetchAll($sql);
    $data = [];
    foreach ($rows as $r) {
        $data[] = [
            'id' => $r['id_penduduk'],
            'id_penduduk' => $r['id_penduduk'],
            'nik' => $r['nik'] ?? '',
            'no_kk' => $r['no_kk'] ?? '',
            'nama' => $r['nama'] ?? '',
            'jenis_kelamin' => mapSex($r['sex']),
            'tempat_lahir' => $r['tempat_lahir'] ?? '',
            'tanggal_lahir' => $r['tanggal_lahir'] ?? '',
            'umur' => $r['umur'] ?? null,
            'status_dalam_keluarga' => mapHubungan($r['kk_level']),
            'nama_ayah' => $r['nama_ayah'] ?? '',
            'nik_ayah' => $r['nik_ayah'] ?? '',
            'nama_ibu' => $r['nama_ibu'] ?? '',
            'nik_ibu' => $r['nik_ibu'] ?? '',
            'no_hp' => $r['no_hp'] ?? '',
            'alamat' => $r['alamat'] ?? '',
            'dusun' => $r['dusun'] ?? '',
            'rt' => $r['rt'] ?? '',
            'rw' => $r['rw'] ?? '',
            'pekerjaan' => $r['pekerjaan'] ?? '',
            'pendidikan' => $r['pendidikan'] ?? '',
            'sumber' => 'opensid'
        ];
    }
    return $data;
}

/**
 * Ambil detail penduduk OpenSID by ID
 */

/**
 * Ambil daftar balita dari OpenSID berdasarkan usia (bulan).
 * Aturan: 0–59 bulan (di bawah 5 tahun) = sasaran Posyandu balita.
 * status_dasar = 1 (hidup/aktif).
 */
function getBalitaOpenSID($max_bulan = 59, $min_bulan = 0) {
    if (!opensid_available()) return [];
    $max_bulan = (int)$max_bulan;
    $min_bulan = (int)$min_bulan;
    $sql = "
        SELECT
            p.id AS id_penduduk,
            p.nik,
            k.no_kk,
            p.nama,
            p.sex,
            p.tempatlahir AS tempat_lahir,
            p.tanggallahir AS tanggal_lahir,
            TIMESTAMPDIFF(MONTH, p.tanggallahir, CURDATE()) AS umur_bulan,
            TIMESTAMPDIFF(YEAR, p.tanggallahir, CURDATE()) AS umur_tahun,
            p.nama_ayah,
            p.ayah_nik AS nik_ayah,
            p.nama_ibu,
            p.ibu_nik AS nik_ibu,
            p.telepon AS no_hp,
            p.alamat_sekarang AS alamat,
            w.dusun, w.rt, w.rw
        FROM tweb_penduduk p
        LEFT JOIN tweb_keluarga k ON k.id = p.id_kk
        LEFT JOIN tweb_wil_clusterdesa w ON w.id = p.id_cluster
        WHERE p.status_dasar = 1
          AND p.tanggallahir IS NOT NULL
          AND p.tanggallahir > '1900-01-01'
          AND TIMESTAMPDIFF(MONTH, p.tanggallahir, CURDATE()) BETWEEN $min_bulan AND $max_bulan
        ORDER BY p.tanggallahir DESC, p.nama ASC
    ";
    $rows = opensid_fetchAll($sql);
    $data = [];
    foreach ($rows as $r) {
        $data[] = [
            'id_penduduk'   => (int)$r['id_penduduk'],
            'nik'           => $r['nik'] ?? '',
            'no_kk'         => $r['no_kk'] ?? '',
            'nama'          => $r['nama'] ?? '',
            'jenis_kelamin' => mapSex($r['sex'] ?? null),
            'tempat_lahir'  => $r['tempat_lahir'] ?? '',
            'tanggal_lahir' => $r['tanggal_lahir'] ?? '',
            'umur_bulan'    => (int)($r['umur_bulan'] ?? 0),
            'umur_tahun'    => (int)($r['umur_tahun'] ?? 0),
            'nama_ayah'     => $r['nama_ayah'] ?? '',
            'nik_ayah'      => $r['nik_ayah'] ?? '',
            'nama_ibu'      => $r['nama_ibu'] ?? '',
            'nik_ibu'       => $r['nik_ibu'] ?? '',
            'no_hp'         => $r['no_hp'] ?? '',
            'alamat'        => $r['alamat'] ?? '',
            'dusun'         => $r['dusun'] ?? '',
            'rt'            => $r['rt'] ?? '',
            'rw'            => $r['rw'] ?? '',
            'sumber'        => 'opensid',
        ];
    }
    return $data;
}

/**
 * Ambil daftar perempuan usia subur (WUS) dari OpenSID.
 * Default: usia 15–49 tahun, jenis kelamin perempuan (sex=2), status hidup.
 * Digunakan sebagai kandidat sasaran Ibu Hamil Posyandu.
 */
function getPerempuanOpenSID($min_tahun = 15, $max_tahun = 49) {
    if (!opensid_available()) return [];
    $min_tahun = (int)$min_tahun;
    $max_tahun = (int)$max_tahun;
    $sql = "
        SELECT
            p.id AS id_penduduk,
            p.nik,
            k.no_kk,
            p.nama,
            p.sex,
            p.tempatlahir AS tempat_lahir,
            p.tanggallahir AS tanggal_lahir,
            TIMESTAMPDIFF(YEAR, p.tanggallahir, CURDATE()) AS umur_tahun,
            p.nama_ayah,
            p.ayah_nik AS nik_ayah,
            p.nama_ibu,
            p.ibu_nik AS nik_ibu,
            p.telepon AS no_hp,
            p.alamat_sekarang AS alamat,
            w.dusun, w.rt, w.rw,
            pk.nama AS pekerjaan,
            pd.nama AS pendidikan
        FROM tweb_penduduk p
        LEFT JOIN tweb_keluarga k ON k.id = p.id_kk
        LEFT JOIN tweb_wil_clusterdesa w ON w.id = p.id_cluster
        LEFT JOIN tweb_penduduk_pekerjaan pk ON pk.id = p.pekerjaan_id
        LEFT JOIN tweb_penduduk_pendidikan_kk pd ON pd.id = p.pendidikan_kk_id
        WHERE p.status_dasar = 1
          AND p.sex = 2
          AND p.tanggallahir IS NOT NULL
          AND p.tanggallahir > '1900-01-01'
          AND TIMESTAMPDIFF(YEAR, p.tanggallahir, CURDATE()) BETWEEN $min_tahun AND $max_tahun
        ORDER BY p.nama ASC
    ";
    $rows = opensid_fetchAll($sql);
    $data = [];
    foreach ($rows as $r) {
        $data[] = [
            'id_penduduk'   => (int)$r['id_penduduk'],
            'nik'           => $r['nik'] ?? '',
            'no_kk'         => $r['no_kk'] ?? '',
            'nama'          => $r['nama'] ?? '',
            'jenis_kelamin' => 'P',
            'tempat_lahir'  => $r['tempat_lahir'] ?? '',
            'tanggal_lahir' => $r['tanggal_lahir'] ?? '',
            'umur_tahun'    => (int)($r['umur_tahun'] ?? 0),
            'nama_ayah'     => $r['nama_ayah'] ?? '',
            'nik_ayah'      => $r['nik_ayah'] ?? '',
            'nama_ibu'      => $r['nama_ibu'] ?? '',
            'nik_ibu'       => $r['nik_ibu'] ?? '',
            'no_hp'         => $r['no_hp'] ?? '',
            'alamat'        => $r['alamat'] ?? '',
            'dusun'         => $r['dusun'] ?? '',
            'rt'            => $r['rt'] ?? '',
            'rw'            => $r['rw'] ?? '',
            'pekerjaan'     => $r['pekerjaan'] ?? '',
            'pendidikan'    => $r['pendidikan'] ?? '',
            'sumber'        => 'opensid',
        ];
    }
    return $data;
}

/**
 * Ambil penduduk OpenSID berdasarkan rentang usia (tahun).
 * Digunakan untuk remaja, usia produktif, lansia, dll.
 */
function getPendudukByUsiaOpenSID($min_tahun = 0, $max_tahun = 150) {
    if (!opensid_available()) return [];
    $min_tahun = (int)$min_tahun;
    $max_tahun = (int)$max_tahun;
    $sql = "
        SELECT
            p.id AS id_penduduk,
            p.nik,
            k.no_kk,
            p.nama,
            p.sex,
            p.tempatlahir AS tempat_lahir,
            p.tanggallahir AS tanggal_lahir,
            TIMESTAMPDIFF(YEAR, p.tanggallahir, CURDATE()) AS umur_tahun,
            p.nama_ayah,
            p.ayah_nik AS nik_ayah,
            p.nama_ibu,
            p.ibu_nik AS nik_ibu,
            p.telepon AS no_hp,
            p.alamat_sekarang AS alamat,
            w.dusun, w.rt, w.rw,
            pk.nama AS pekerjaan,
            pd.nama AS pendidikan
        FROM tweb_penduduk p
        LEFT JOIN tweb_keluarga k ON k.id = p.id_kk
        LEFT JOIN tweb_wil_clusterdesa w ON w.id = p.id_cluster
        LEFT JOIN tweb_penduduk_pekerjaan pk ON pk.id = p.pekerjaan_id
        LEFT JOIN tweb_penduduk_pendidikan_kk pd ON pd.id = p.pendidikan_kk_id
        WHERE p.status_dasar = 1
          AND p.tanggallahir IS NOT NULL
          AND p.tanggallahir > '1900-01-01'
          AND TIMESTAMPDIFF(YEAR, p.tanggallahir, CURDATE()) BETWEEN $min_tahun AND $max_tahun
        ORDER BY p.nama ASC
    ";
    $rows = opensid_fetchAll($sql);
    $data = [];
    foreach ($rows as $r) {
        $data[] = [
            'id_penduduk'   => (int)$r['id_penduduk'],
            'nik'           => $r['nik'] ?? '',
            'no_kk'         => $r['no_kk'] ?? '',
            'nama'          => $r['nama'] ?? '',
            'jenis_kelamin' => mapSex($r['sex'] ?? null),
            'tempat_lahir'  => $r['tempat_lahir'] ?? '',
            'tanggal_lahir' => $r['tanggal_lahir'] ?? '',
            'umur_tahun'    => (int)($r['umur_tahun'] ?? 0),
            'nama_ayah'     => $r['nama_ayah'] ?? '',
            'nik_ayah'      => $r['nik_ayah'] ?? '',
            'nama_ibu'      => $r['nama_ibu'] ?? '',
            'nik_ibu'       => $r['nik_ibu'] ?? '',
            'no_hp'         => $r['no_hp'] ?? '',
            'alamat'        => $r['alamat'] ?? '',
            'dusun'         => $r['dusun'] ?? '',
            'rt'            => $r['rt'] ?? '',
            'rw'            => $r['rw'] ?? '',
            'pekerjaan'     => $r['pekerjaan'] ?? '',
            'pendidikan'    => $r['pendidikan'] ?? '',
            'sumber'        => 'opensid',
        ];
    }
    return $data;
}

/**
 * Format umur balita: "X th Y bln" atau "Y bulan"
 */
function formatUmurBalita($tanggal_lahir, $umur_bulan = null) {
    if ($umur_bulan === null && $tanggal_lahir) {
        try {
            $d1 = new DateTime($tanggal_lahir);
            $d2 = new DateTime();
            $diff = $d1->diff($d2);
            $umur_bulan = $diff->y * 12 + $diff->m;
        } catch (Exception $e) {
            return '-';
        }
    }
    $umur_bulan = (int)$umur_bulan;
    if ($umur_bulan < 0) return '-';
    if ($umur_bulan < 12) return $umur_bulan . ' bulan';
    $th = intdiv($umur_bulan, 12);
    $bl = $umur_bulan % 12;
    if ($bl === 0) return $th . ' tahun';
    return $th . ' th ' . $bl . ' bln';
}

function getPendudukOpenSIDById($id) {
    if (!opensid_available() || !$id) return null;
    $id = (int)$id;
    $sql = "
        SELECT
            p.id AS id_penduduk,
            p.nik,
            k.no_kk,
            p.nama,
            p.sex,
            p.tempatlahir AS tempat_lahir,
            p.tanggallahir AS tanggal_lahir,
            TIMESTAMPDIFF(YEAR, p.tanggallahir, CURDATE()) AS umur,
            p.kk_level,
            p.nama_ayah,
            p.ayah_nik AS nik_ayah,
            p.nama_ibu,
            p.ibu_nik AS nik_ibu,
            p.telepon AS no_hp,
            p.alamat_sekarang AS alamat,
            p.id_kk,
            p.id_cluster,
            w.dusun,
            w.rt,
            w.rw,
            pk.nama AS pekerjaan,
            pd.nama AS pendidikan
        FROM tweb_penduduk p
        LEFT JOIN tweb_keluarga k ON k.id = p.id_kk
        LEFT JOIN tweb_wil_clusterdesa w ON w.id = p.id_cluster
        LEFT JOIN tweb_penduduk_pekerjaan pk ON pk.id = p.pekerjaan_id
        LEFT JOIN tweb_penduduk_pendidikan_kk pd ON pd.id = p.pendidikan_kk_id
        WHERE p.id = $id AND p.status_dasar = 1
        LIMIT 1";
    $r = opensid_fetchOne($sql);
    if (!$r) return null;
    return [
        'id' => $r['id_penduduk'],
        'id_penduduk' => $r['id_penduduk'],
        'id_kk' => (int)($r['id_kk'] ?? 0),
        'nik' => $r['nik'] ?? '',
        'no_kk' => $r['no_kk'] ?? '',
        'nama' => $r['nama'] ?? '',
        'jenis_kelamin' => mapSex($r['sex']),
        'tempat_lahir' => $r['tempat_lahir'] ?? '',
        'tanggal_lahir' => $r['tanggal_lahir'] ?? '',
        'umur' => $r['umur'] ?? null,
        'status_dalam_keluarga' => mapHubungan($r['kk_level']),
        'kk_level' => (int)($r['kk_level'] ?? 0),
        'nama_ayah' => $r['nama_ayah'] ?? '',
        'nik_ayah' => $r['nik_ayah'] ?? '',
        'nama_ibu' => $r['nama_ibu'] ?? '',
        'nik_ibu' => $r['nik_ibu'] ?? '',
        'no_hp' => $r['no_hp'] ?? '',
        'alamat' => $r['alamat'] ?? '',
        'dusun' => $r['dusun'] ?? '',
        'rt' => $r['rt'] ?? '',
        'rw' => $r['rw'] ?? '',
        'pekerjaan' => $r['pekerjaan'] ?? '',
        'pendidikan' => $r['pendidikan'] ?? '',
        'sumber' => 'opensid'
    ];
}

/**
 * Ambil data suami dari KK OpenSID.
 *
 * Aturan:
 * - Jika ibu adalah Kepala Keluarga (kk_level=1) → biasanya pisah KK dengan suami
 *   → jangan isi otomatis, biarkan manual ($force_manual = true).
 * - Jika ibu adalah Istri (kk_level=3) → cari Kepala Keluarga laki-laki (kk_level=1) di KK sama.
 * - Selain itu → cari status Suami (kk_level=2) di KK sama.
 * - Tidak ada fallback "laki-laki sembarang" agar tidak salah isi.
 *
 * @param int $id_kk
 * @param int|null $kk_level_ibu  kk_level perempuan yang didaftarkan
 * @return array|null  data suami + flag 'manual_required'
 */
function getSuamiFromOpenSID($id_kk, $kk_level_ibu = null) {
    $result_empty = [
        'id_penduduk'     => 0,
        'nik'             => '',
        'nama'            => '',
        'no_hp'           => '',
        'pekerjaan'       => '',
        'manual_required' => true,
        'alasan'          => '',
    ];

    if (!opensid_available() || !(int)$id_kk) {
        $result_empty['alasan'] = 'KK tidak ditemukan di OpenSID';
        return $result_empty;
    }

    $kk_id = (int)$id_kk;
    $level_ibu = $kk_level_ibu !== null ? (int)$kk_level_ibu : null;

    // Ibu sebagai Kepala Keluarga → suami biasanya di KK lain (pisah KK)
    if ($level_ibu === 1) {
        $result_empty['alasan'] = 'Ibu adalah Kepala Keluarga (kemungkinan pisah KK). Isi data suami secara manual.';
        return $result_empty;
    }

    // Prioritas pencarian suami di KK yang sama
    // 1) Status Suami (kk_level=2)
    // 2) Kepala Keluarga laki-laki (kk_level=1) — jika ibu berstatus Istri
    $sql = "
        SELECT
            p.id AS id_penduduk,
            p.nik,
            p.nama,
            p.telepon AS no_hp,
            p.kk_level,
            pk.nama AS pekerjaan
        FROM tweb_penduduk p
        LEFT JOIN tweb_penduduk_pekerjaan pk ON pk.id = p.pekerjaan_id
        WHERE p.id_kk = $kk_id
          AND p.sex = 1
          AND p.status_dasar = 1
          AND p.kk_level IN (1, 2)
        ORDER BY
          CASE
            WHEN p.kk_level = 2 THEN 0
            WHEN p.kk_level = 1 THEN 1
            ELSE 2
          END
        LIMIT 1
    ";
    $r = opensid_fetchOne($sql);

    // Jika ibu bukan istri dan yang ketemu hanya Kepala Keluarga, jangan paksa (bisa ayah/mertua)
    if ($r && (int)($r['kk_level'] ?? 0) === 1 && $level_ibu !== null && $level_ibu !== 3) {
        $result_empty['alasan'] = 'Status ibu bukan Istri di KK. Isi data suami secara manual.';
        return $result_empty;
    }

    if (!$r) {
        $result_empty['alasan'] = 'Suami tidak ditemukan di KK yang sama (kemungkinan pisah KK). Isi manual.';
        return $result_empty;
    }

    return [
        'id_penduduk'     => (int)($r['id_penduduk'] ?? 0),
        'nik'             => $r['nik'] ?? '',
        'nama'            => $r['nama'] ?? '',
        'no_hp'           => $r['no_hp'] ?? '',
        'pekerjaan'       => $r['pekerjaan'] ?? '',
        'manual_required' => false,
        'alasan'          => '',
    ];
}

/**
 * Ambil detail penduduk OpenSID by NIK
 */
function getPendudukOpenSIDByNik($nik) {
    if (!opensid_available() || !$nik) return null;
    $nik = opensid_escape(trim($nik));
    $sql = "
        SELECT
            p.id AS id_penduduk,
            p.nik,
            k.no_kk,
            p.nama,
            p.sex,
            p.tempatlahir AS tempat_lahir,
            p.tanggallahir AS tanggal_lahir,
            TIMESTAMPDIFF(YEAR, p.tanggallahir, CURDATE()) AS umur,
            p.kk_level,
            p.nama_ayah,
            p.ayah_nik AS nik_ayah,
            p.nama_ibu,
            p.ibu_nik AS nik_ibu,
            p.telepon AS no_hp,
            p.alamat_sekarang AS alamat,
            p.id_kk,
            w.dusun,
            w.rt,
            w.rw
        FROM tweb_penduduk p
        LEFT JOIN tweb_keluarga k ON k.id = p.id_kk
        LEFT JOIN tweb_wil_clusterdesa w ON w.id = p.id_cluster
        WHERE p.nik = '$nik' AND p.status_dasar = 1
        LIMIT 1";
    $r = opensid_fetchOne($sql);
    if (!$r) return null;
    return [
        'id' => $r['id_penduduk'],
        'id_penduduk' => $r['id_penduduk'],
        'nik' => $r['nik'] ?? '',
        'no_kk' => $r['no_kk'] ?? '',
        'nama' => $r['nama'] ?? '',
        'jenis_kelamin' => mapSex($r['sex']),
        'tempat_lahir' => $r['tempat_lahir'] ?? '',
        'tanggal_lahir' => $r['tanggal_lahir'] ?? '',
        'umur' => $r['umur'] ?? null,
        'status_dalam_keluarga' => mapHubungan($r['kk_level']),
        'nama_ayah' => $r['nama_ayah'] ?? '',
        'nik_ayah' => $r['nik_ayah'] ?? '',
        'nama_ibu' => $r['nama_ibu'] ?? '',
        'nik_ibu' => $r['nik_ibu'] ?? '',
        'no_hp' => $r['no_hp'] ?? '',
        'alamat' => $r['alamat'] ?? '',
        'dusun' => $r['dusun'] ?? '',
        'rt' => $r['rt'] ?? '',
        'rw' => $r['rw'] ?? '',
        'sumber' => 'opensid'
    ];
}

/**
 * Ambil anggota keluarga berdasarkan no_kk dari OpenSID
 */
function getAnggotaKeluargaOpenSID($no_kk) {
    if (!opensid_available() || !$no_kk) return [];
    $no_kk = opensid_escape(trim($no_kk));
    $sql = "
        SELECT
            p.id AS id_penduduk,
            p.nik,
            k.no_kk,
            p.nama,
            p.sex,
            p.tempatlahir AS tempat_lahir,
            p.tanggallahir AS tanggal_lahir,
            TIMESTAMPDIFF(YEAR, p.tanggallahir, CURDATE()) AS umur,
            p.kk_level,
            p.status_dasar,
            p.nama_ayah,
            p.nama_ibu,
            w.dusun,
            w.rt,
            w.rw
        FROM tweb_penduduk p
        INNER JOIN tweb_keluarga k ON k.id = p.id_kk
        LEFT JOIN tweb_wil_clusterdesa w ON w.id = p.id_cluster
        WHERE k.no_kk = '$no_kk' AND p.status_dasar = 1
        ORDER BY
            CASE WHEN p.kk_level = 1 THEN 0 ELSE 1 END,
            CASE WHEN p.kk_level IS NULL OR p.kk_level = 0 THEN 2 ELSE 0 END,
            p.kk_level ASC,
            p.tanggallahir ASC";
    $rows = opensid_fetchAll($sql);
    $data = [];
    foreach ($rows as $r) {
        $data[] = [
            'id' => $r['id_penduduk'],
            'id_penduduk' => $r['id_penduduk'],
            'nik' => $r['nik'] ?? '',
            'no_kk' => $r['no_kk'] ?? '',
            'nama' => $r['nama'] ?? '',
            'jenis_kelamin' => mapSex($r['sex']),
            'tempat_lahir' => $r['tempat_lahir'] ?? '',
            'tanggal_lahir' => $r['tanggal_lahir'] ?? '',
            'umur' => $r['umur'] ?? null,
            'status_dalam_keluarga' => mapHubungan($r['kk_level']),
            'nama_ayah' => $r['nama_ayah'] ?? '',
            'nama_ibu' => $r['nama_ibu'] ?? '',
            'dusun' => $r['dusun'] ?? '',
            'rt' => $r['rt'] ?? '',
            'rw' => $r['rw'] ?? '',
            'sumber' => 'opensid'
        ];
    }
    return $data;
}

/**
 * Resolve nama & NIK kepala keluarga untuk satu baris keluarga OpenSID.
 * Menangani data tidak konsisten: kk_level kosong, nik_kepala = id ATAU nik, status_dasar.
 */
function resolveKepalaKeluargaOpenSID($k) {
    if (!opensid_available() || empty($k['id'])) {
        return ['nama_kepala' => '', 'nik_kepala_str' => ''];
    }
    $kid = (int)$k['id'];
    $nk = opensid_escape(trim((string)($k['nik_kepala'] ?? '')));

    // 1) kk_level = 1, hidup
    $r = opensid_fetchOne("SELECT nama, nik FROM tweb_penduduk
        WHERE id_kk = $kid AND kk_level = 1 AND status_dasar = 1
        ORDER BY id ASC LIMIT 1");
    if ($r && !empty($r['nama'])) {
        return ['nama_kepala' => $r['nama'], 'nik_kepala_str' => $r['nik'] ?? ''];
    }

    // 2) nik_kepala sebagai ID penduduk
    if ($nk !== '' && ctype_digit($nk)) {
        $r = opensid_fetchOne("SELECT nama, nik FROM tweb_penduduk
            WHERE id = " . (int)$nk . " LIMIT 1");
        if ($r && !empty($r['nama'])) {
            return ['nama_kepala' => $r['nama'], 'nik_kepala_str' => $r['nik'] ?? ''];
        }
    }

    // 3) nik_kepala sebagai NIK (string 16 digit)
    if ($nk !== '') {
        $r = opensid_fetchOne("SELECT nama, nik FROM tweb_penduduk
            WHERE nik = '$nk' LIMIT 1");
        if ($r && !empty($r['nama'])) {
            return ['nama_kepala' => $r['nama'], 'nik_kepala_str' => $r['nik'] ?? ''];
        }
    }

    // 4) kk_level = 1 tanpa filter status (bisa pindah/meninggal tapi masih tercatat kepala)
    $r = opensid_fetchOne("SELECT nama, nik FROM tweb_penduduk
        WHERE id_kk = $kid AND kk_level = 1
        ORDER BY status_dasar ASC, id ASC LIMIT 1");
    if ($r && !empty($r['nama'])) {
        return ['nama_kepala' => $r['nama'], 'nik_kepala_str' => $r['nik'] ?? ''];
    }

    // 5) Anggota hidup pertama di KK
    $r = opensid_fetchOne("SELECT nama, nik FROM tweb_penduduk
        WHERE id_kk = $kid AND status_dasar = 1
        ORDER BY CASE WHEN kk_level IS NULL OR kk_level = 0 THEN 99 ELSE kk_level END ASC, id ASC
        LIMIT 1");
    if ($r && !empty($r['nama'])) {
        return ['nama_kepala' => $r['nama'], 'nik_kepala_str' => $r['nik'] ?? ''];
    }

    // 6) Anggota manapun di KK
    $r = opensid_fetchOne("SELECT nama, nik FROM tweb_penduduk
        WHERE id_kk = $kid
        ORDER BY CASE WHEN kk_level IS NULL OR kk_level = 0 THEN 99 ELSE kk_level END ASC, id ASC
        LIMIT 1");
    if ($r && !empty($r['nama'])) {
        return ['nama_kepala' => $r['nama'], 'nik_kepala_str' => $r['nik'] ?? ''];
    }

    return ['nama_kepala' => '', 'nik_kepala_str' => ''];
}

/**
 * Lengkapi nama_kepala pada hasil query keluarga
 */
function enrichKeluargaRows($rows) {
    $out = [];
    foreach ($rows as $k) {
        if (empty($k['nama_kepala'])) {
            $resolved = resolveKepalaKeluargaOpenSID($k);
            $k['nama_kepala'] = $resolved['nama_kepala'];
            $k['nik_kepala_str'] = $resolved['nik_kepala_str'] ?: ($k['nik_kepala_str'] ?? '');
        }
        if (empty($k['nama_kepala'])) {
            $k['nama_kepala'] = '(Belum ada data kepala)';
        }
        $out[] = $k;
    }
    return $out;
}

/**
 * Cari keluarga dari OpenSID
 */
function cariKeluargaOpenSID($q, $limit = 25) {
    if (!opensid_available() || strlen(trim($q)) < 1) return [];
    $s = opensid_escape(trim($q));
    $sql = "
        SELECT
            k.id,
            k.no_kk,
            k.nik_kepala,
            COALESCE(
                (SELECT p1.nama FROM tweb_penduduk p1
                 WHERE p1.id_kk = k.id AND p1.kk_level = 1 AND p1.status_dasar = 1
                 ORDER BY p1.id ASC LIMIT 1),
                (SELECT p2.nama FROM tweb_penduduk p2
                 WHERE p2.id = CAST(k.nik_kepala AS UNSIGNED) AND k.nik_kepala REGEXP '^[0-9]+$'
                 LIMIT 1),
                (SELECT p2b.nama FROM tweb_penduduk p2b
                 WHERE p2b.nik = k.nik_kepala LIMIT 1),
                (SELECT p3.nama FROM tweb_penduduk p3
                 WHERE p3.id_kk = k.id AND p3.kk_level = 1
                 ORDER BY p3.id ASC LIMIT 1),
                (SELECT p4.nama FROM tweb_penduduk p4
                 WHERE p4.id_kk = k.id AND p4.status_dasar = 1
                 ORDER BY CASE WHEN p4.kk_level IS NULL OR p4.kk_level = 0 THEN 99 ELSE p4.kk_level END ASC, p4.id ASC
                 LIMIT 1)
            ) AS nama_kepala,
            COALESCE(
                (SELECT p1.nik FROM tweb_penduduk p1
                 WHERE p1.id_kk = k.id AND p1.kk_level = 1 AND p1.status_dasar = 1
                 ORDER BY p1.id ASC LIMIT 1),
                (SELECT p2.nik FROM tweb_penduduk p2
                 WHERE p2.id = CAST(k.nik_kepala AS UNSIGNED) AND k.nik_kepala REGEXP '^[0-9]+$'
                 LIMIT 1),
                (SELECT p2b.nik FROM tweb_penduduk p2b
                 WHERE p2b.nik = k.nik_kepala LIMIT 1),
                (SELECT p3.nik FROM tweb_penduduk p3
                 WHERE p3.id_kk = k.id AND p3.kk_level = 1
                 ORDER BY p3.id ASC LIMIT 1)
            ) AS nik_kepala_str,
            k.alamat,
            w.dusun,
            w.rt,
            w.rw,
            (SELECT COUNT(*) FROM tweb_penduduk px WHERE px.id_kk = k.id AND px.status_dasar = 1) AS jml
        FROM tweb_keluarga k
        LEFT JOIN tweb_wil_clusterdesa w ON w.id = k.id_cluster
        WHERE k.no_kk LIKE '%$s%'
           OR IFNULL(k.alamat,'') LIKE '%$s%'
           OR IFNULL(w.dusun,'') LIKE '%$s%'
           OR EXISTS (
                SELECT 1 FROM tweb_penduduk px
                WHERE px.id_kk = k.id
                  AND (px.nama LIKE '%$s%' OR px.nik LIKE '%$s%')
           )
        ORDER BY nama_kepala ASC
        LIMIT " . (int)$limit;
    return enrichKeluargaRows(opensid_fetchAll($sql));
}

/**
 * Daftar keluarga OpenSID (tanpa filter pencarian)
 */
function listKeluargaOpenSID($limit = 100, $offset = 0) {
    if (!opensid_available()) return [];
    $limit = (int)$limit;
    $offset = (int)$offset;
    $sql = "
        SELECT
            k.id,
            k.no_kk,
            k.nik_kepala,
            COALESCE(
                (SELECT p1.nama FROM tweb_penduduk p1
                 WHERE p1.id_kk = k.id AND p1.kk_level = 1 AND p1.status_dasar = 1
                 ORDER BY p1.id ASC LIMIT 1),
                (SELECT p2.nama FROM tweb_penduduk p2
                 WHERE p2.id = CAST(k.nik_kepala AS UNSIGNED) AND k.nik_kepala REGEXP '^[0-9]+$'
                 LIMIT 1),
                (SELECT p2b.nama FROM tweb_penduduk p2b
                 WHERE p2b.nik = k.nik_kepala LIMIT 1),
                (SELECT p3.nama FROM tweb_penduduk p3
                 WHERE p3.id_kk = k.id AND p3.kk_level = 1
                 ORDER BY p3.id ASC LIMIT 1),
                (SELECT p4.nama FROM tweb_penduduk p4
                 WHERE p4.id_kk = k.id AND p4.status_dasar = 1
                 ORDER BY CASE WHEN p4.kk_level IS NULL OR p4.kk_level = 0 THEN 99 ELSE p4.kk_level END ASC, p4.id ASC
                 LIMIT 1),
                (SELECT p5.nama FROM tweb_penduduk p5
                 WHERE p5.id_kk = k.id
                 ORDER BY CASE WHEN p5.kk_level IS NULL OR p5.kk_level = 0 THEN 99 ELSE p5.kk_level END ASC, p5.id ASC
                 LIMIT 1)
            ) AS nama_kepala,
            COALESCE(
                (SELECT p1.nik FROM tweb_penduduk p1
                 WHERE p1.id_kk = k.id AND p1.kk_level = 1 AND p1.status_dasar = 1
                 ORDER BY p1.id ASC LIMIT 1),
                (SELECT p2.nik FROM tweb_penduduk p2
                 WHERE p2.id = CAST(k.nik_kepala AS UNSIGNED) AND k.nik_kepala REGEXP '^[0-9]+$'
                 LIMIT 1),
                (SELECT p2b.nik FROM tweb_penduduk p2b
                 WHERE p2b.nik = k.nik_kepala LIMIT 1),
                (SELECT p3.nik FROM tweb_penduduk p3
                 WHERE p3.id_kk = k.id AND p3.kk_level = 1
                 ORDER BY p3.id ASC LIMIT 1)
            ) AS nik_kepala_str,
            k.alamat,
            w.dusun,
            w.rt,
            w.rw,
            (SELECT COUNT(*) FROM tweb_penduduk px WHERE px.id_kk = k.id AND px.status_dasar = 1) AS jml
        FROM tweb_keluarga k
        LEFT JOIN tweb_wil_clusterdesa w ON w.id = k.id_cluster
        ORDER BY nama_kepala ASC
        LIMIT $limit OFFSET $offset";
    return enrichKeluargaRows(opensid_fetchAll($sql));
}

function getPengaturan() {
    return fetchOne("SELECT * FROM pengaturan LIMIT 1");
}

function generateNomorPeserta($prefix, $table) {
    $year = date('Y');
    $prefix = preg_replace('/[^A-Z0-9]/i', '', $prefix);
    $table = preg_replace('/[^a-z0-9_]/i', '', $table);
    // Ambil nomor urut maksimum (bukan COUNT) agar tidak bentrok jika ada data terhapus/tidak berurutan
    $result = fetchOne("SELECT nomor_peserta FROM `$table`
        WHERE nomor_peserta LIKE '" . escape($prefix) . "-$year-%'
        ORDER BY id DESC LIMIT 1");
    $no = 1;
    if ($result && !empty($result['nomor_peserta'])) {
        if (preg_match('/-(\d+)$/', $result['nomor_peserta'], $m)) {
            $no = (int)$m[1] + 1;
        }
    } else {
        // Fallback: hitung semua lalu +1
        $c = fetchOne("SELECT COUNT(*) AS total FROM `$table` WHERE nomor_peserta LIKE '" . escape($prefix) . "-$year-%'");
        $no = (int)($c['total'] ?? 0) + 1;
    }
    // Pastikan belum dipakai
    $tries = 0;
    do {
        $nomor = $prefix . '-' . $year . '-' . str_pad((string)$no, 3, '0', STR_PAD_LEFT);
        $exists = fetchOne("SELECT id FROM `$table` WHERE nomor_peserta = '" . escape($nomor) . "' LIMIT 1");
        if (!$exists) return $nomor;
        $no++;
        $tries++;
    } while ($tries < 50);
    return $prefix . '-' . $year . '-' . date('His') . rand(10, 99);
}

function hitungUmur($tanggal_lahir) {
    if (!$tanggal_lahir) return '';
    $lahir = new DateTime($tanggal_lahir);
    $sekarang = new DateTime();
    $diff = $sekarang->diff($lahir);
    if ($diff->y > 0) return $diff->y . ' Tahun ' . $diff->m . ' Bulan';
    if ($diff->m > 0) return $diff->m . ' Bulan ' . $diff->d . ' Hari';
    return $diff->d . ' Hari';
}

function hitungUmurBulan($tanggal_lahir) {
    if (!$tanggal_lahir) return 0;
    $lahir = new DateTime($tanggal_lahir);
    $sekarang = new DateTime();
    $diff = $sekarang->diff($lahir);
    return ($diff->y * 12) + $diff->m;
}

function hitungHPL($hpht) {
    if (!$hpht) return '';
    $hpht_date = new DateTime($hpht);
    $hpht_date->modify('+280 days');
    return $hpht_date->format('Y-m-d');
}

function hitungUsiaKandungan($hpht, $tgl_periksa = null) {
    if (!$hpht) return 0;
    try {
        $hpht_date = new DateTime($hpht);
        $target = $tgl_periksa ? new DateTime($tgl_periksa) : new DateTime();
        if ($target < $hpht_date) return 0;
        $diff = $hpht_date->diff($target);
        return (int) floor($diff->days / 7);
    } catch (Exception $e) {
        return 0;
    }
}

/**
 * Perhitungan antropometri sederhana berbasis median WHO (perkiraan).
 * Untuk produksi idealnya gunakan library z-score WHO lengkap.
 * Return array: bb_u, tb_u, bb_tb, status_gizi, risiko_stunting, imt
 */
function hitungAntropometri($umur_bulan, $bb, $tb, $jenis_kelamin = 'L') {
    $result = [
        'bb_u' => null, 'tb_u' => null, 'bb_tb' => null,
        'status_gizi' => 'Normal', 'risiko_stunting' => 'Tidak', 'imt' => null
    ];
    if (!$bb || !$tb || $umur_bulan === null || $umur_bulan < 0) return $result;

    $imt = $tb > 0 ? round($bb / (($tb/100) * ($tb/100)), 2) : null;
    $result['imt'] = $imt;

    // Median TB/U & BB/U perkiraan (campuran L/P, cukup untuk skrining Posyandu)
    $median_tb = [
        0=>49.9,1=>54.7,2=>58.4,3=>61.4,4=>63.9,5=>65.9,6=>67.6,
        9=>72.0,12=>75.7,15=>79.1,18=>82.3,21=>85.1,24=>87.1,
        30=>91.9,36=>96.1,42=>99.9,48=>103.3,54=>106.7,60=>109.9
    ];
    $median_bb = [
        0=>3.3,1=>4.5,2=>5.6,3=>6.4,4=>7.0,5=>7.5,6=>7.9,
        9=>8.9,12=>9.6,15=>10.3,18=>10.9,21=>11.5,24=>12.2,
        30=>13.3,36=>14.3,42=>15.3,48=>16.3,54=>17.3,60=>18.3
    ];

    $getMedian = function($map, $umur) {
        $keys = array_keys($map);
        sort($keys);
        $prev = $keys[0];
        foreach ($keys as $k) {
            if ($umur <= $k) {
                if ($k == $prev) return $map[$k];
                $ratio = ($umur - $prev) / max(1, ($k - $prev));
                return $map[$prev] + $ratio * ($map[$k] - $map[$prev]);
            }
            $prev = $k;
        }
        return $map[$prev];
    };

    $med_tb = $getMedian($median_tb, $umur_bulan);
    $med_bb = $getMedian($median_bb, $umur_bulan);

    // TB/U (stunting) — ambang kasar ±2SD ≈ 7-8%
    $pct_tb = $med_tb > 0 ? ($tb / $med_tb) * 100 : 100;
    if ($pct_tb < 85) $result['tb_u'] = 'Sangat Pendek';
    elseif ($pct_tb < 90) $result['tb_u'] = 'Pendek';
    elseif ($pct_tb > 120) $result['tb_u'] = 'Tinggi';
    else $result['tb_u'] = 'Normal';

    if ($result['tb_u'] === 'Sangat Pendek' || $result['tb_u'] === 'Pendek') {
        $result['risiko_stunting'] = ($result['tb_u'] === 'Sangat Pendek') ? 'Stunting' : 'Risiko';
    }

    // BB/U
    $pct_bb = $med_bb > 0 ? ($bb / $med_bb) * 100 : 100;
    if ($pct_bb < 70) $result['bb_u'] = 'Sangat Kurang';
    elseif ($pct_bb < 80) $result['bb_u'] = 'Kurang';
    elseif ($pct_bb > 120) $result['bb_u'] = 'Lebih';
    else $result['bb_u'] = 'Normal';

    // BB/TB (wasting / overweight) via IMT kasar untuk anak
    if ($imt !== null) {
        if ($imt < 12) $result['bb_tb'] = 'Gizi Buruk';
        elseif ($imt < 14) $result['bb_tb'] = 'Gizi Kurang';
        elseif ($imt < 17) $result['bb_tb'] = 'Gizi Baik';
        elseif ($imt < 18.5) $result['bb_tb'] = 'Berisiko Gizi Lebih';
        elseif ($imt < 25) $result['bb_tb'] = 'Gizi Lebih';
        else $result['bb_tb'] = 'Obesitas';
    }

    // Status gizi ringkas
    if (in_array($result['bb_tb'], ['Gizi Buruk','Gizi Kurang'], true) || $result['bb_u'] === 'Sangat Kurang') {
        $result['status_gizi'] = 'Buruk';
    } elseif ($result['bb_u'] === 'Kurang' || $result['bb_tb'] === 'Gizi Kurang') {
        $result['status_gizi'] = 'Kurang';
    } elseif (in_array($result['bb_tb'], ['Gizi Lebih','Obesitas'], true) || $result['bb_u'] === 'Lebih') {
        $result['status_gizi'] = 'Lebih';
    } else {
        $result['status_gizi'] = 'Normal';
    }

    return $result;
}

function statusGiziBalita($umur_bulan, $bb, $tb) {
    $r = hitungAntropometri($umur_bulan, $bb, $tb);
    return $r['status_gizi'];
}

function risikoStunting($umur_bulan, $tb) {
    $r = hitungAntropometri($umur_bulan, 10, $tb); // bb dummy
    return $r['risiko_stunting'];
}

function hitungIMT($bb, $tb) {
    if (!$bb || !$tb || $tb <= 0) return null;
    return round($bb / (($tb/100) * ($tb/100)), 2);
}

function statusIMTDewasa($imt) {
    if ($imt === null) return 'Tidak Diketahui';
    if ($imt < 18.5) return 'Kurus';
    if ($imt < 25) return 'Normal';
    if ($imt < 27) return 'Gemuk';
    return 'Obesitas';
}

function statusAnemia($hb, $jenis_kelamin = 'P', $hamil = false) {
    if ($hb === null || $hb === '') return 0;
    $hb = (float)$hb;
    if ($hamil) return $hb < 11 ? 1 : 0;
    if ($jenis_kelamin === 'L') return $hb < 13 ? 1 : 0;
    return $hb < 12 ? 1 : 0;
}

function jsonResponse($success, $message, $data = null) {
    header('Content-Type: application/json');
    echo json_encode(['success' => $success, 'message' => $message, 'data' => $data]);
    exit;
}

function formatTanggal($date) {
    if (!$date) return '-';
    $bulan = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
              'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $d = explode('-', $date);
    if (count($d) < 3) return $date;
    return $d[2] . ' ' . $bulan[(int)$d[1]] . ' ' . $d[0];
}

function uploadFile($file, $folder) {
    $allowed = ['jpg','jpeg','png','pdf','ico','svg','gif','webp'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed)) return ['success' => false, 'message' => 'Format file tidak didukung'];
    if ($file['size'] > 5 * 1024 * 1024) return ['success' => false, 'message' => 'File terlalu besar (max 5MB)'];
    $filename = uniqid() . '_' . time() . '.' . $ext;
    $dir = __DIR__ . '/../uploads/' . $folder;
    if (!is_dir($dir)) { @mkdir($dir, 0755, true); }
    $target = $dir . '/' . $filename;
    if (move_uploaded_file($file['tmp_name'], $target)) {
        return ['success' => true, 'filename' => $filename];
    }
    return ['success' => false, 'message' => 'Gagal upload file'];
}
