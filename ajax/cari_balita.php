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
 * Pencarian balita teroptimasi
 * - Prefix match (ketik "A" → Ahmad, Ani)
 * - Query minimal, dedupe cepat
 * - OpenSID + lokal, usia 0–59 bulan
 */
error_reporting(0);
ini_set('display_errors', '0');
require_once __DIR__ . '/../config/init.php';
requireLogin();
header('Content-Type: application/json; charset=utf-8');

$q     = trim($_GET['q'] ?? $_GET['term'] ?? '');
$id    = (int)($_GET['id'] ?? 0);
$limit = min(60, max(8, (int)($_GET['limit'] ?? 40)));

// ---------- Detail by ID ----------
if ($id > 0) {
    $b = fetchOne("SELECT id, nama_lengkap, nomor_peserta, nik_anak, jenis_kelamin, tanggal_lahir,
        nama_ibu, nama_ayah, id_penduduk_opensid,
        TIMESTAMPDIFF(MONTH, tanggal_lahir, CURDATE()) AS umur_bulan
        FROM balita WHERE id={$id} LIMIT 1");
    if ($b) {
        $b['umur']   = function_exists('hitungUmur') ? hitungUmur($b['tanggal_lahir']) : ($b['umur_bulan'] . ' bln');
        $b['text']   = $b['nama_lengkap'];
        $b['sumber'] = 'lokal';
        echo json_encode(['success' => true, 'data' => $b], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode(['success' => false, 'data' => null]);
    }
    exit;
}

// ---------- Helpers ----------
function umurTeks($ub) {
    $ub = (int)$ub;
    return $ub < 12 ? ($ub . ' bln') : (intdiv($ub, 12) . ' th ' . ($ub % 12) . ' bln');
}

/** Skor: 0=awalan nama, 1=awalan kata, 2=mengandung, 3=lain */
function skorCocok($nama, $q) {
    if ($q === '') return 1;
    $n = mb_strtolower(trim((string)$nama));
    $q = mb_strtolower(trim((string)$q));
    if ($n === '' || $q === '') return 9;
    if (mb_strpos($n, $q) === 0) return 0;
    // awalan kata (spasi + query)
    if (mb_strpos($n, ' ' . $q) !== false) return 1;
    if (mb_strpos($n, $q) !== false) return 2;
    return 9;
}

function barisLokal(array $b, $q) {
    $ub  = (int)($b['umur_bulan'] ?? 0);
    $nik = trim($b['nik_anak'] ?? '');
    $oid = (int)($b['id_penduduk_opensid'] ?? 0);
    $nama = $b['nama_lengkap'] ?? '';
    $label = $nama;
    if (!empty($b['nomor_peserta'])) $label .= ' · ' . $b['nomor_peserta'];
    $sub = umurTeks($ub) . ' · Posyandu';
    if (!empty($b['nama_ibu'])) $sub .= ' · Ibu: ' . $b['nama_ibu'];
    return [
        'id'            => (int)$b['id'],
        'id_lokal'      => (int)$b['id'],
        'id_opensid'    => $oid,
        'text'          => $label,
        'nama_lengkap'  => $nama,
        'nomor_peserta' => $b['nomor_peserta'] ?? '',
        'nik_anak'      => $nik,
        'jenis_kelamin' => $b['jenis_kelamin'] ?? '',
        'tanggal_lahir' => $b['tanggal_lahir'] ?? '',
        'umur_bulan'    => $ub,
        'umur'          => umurTeks($ub),
        'nama_ibu'      => $b['nama_ibu'] ?? '',
        'sub'           => $sub,
        'sumber'        => 'lokal',
        'terdaftar'     => true,
        '_skor'         => skorCocok($nama, $q),
    ];
}

function barisOpenSID(array $p, $id_lokal, $q) {
    $oid  = (int)($p['id_penduduk'] ?? $p['id'] ?? 0);
    $nik  = trim($p['nik'] ?? '');
    $ub   = (int)($p['umur_bulan'] ?? 0);
    $nama = $p['nama'] ?? $p['nama_lengkap'] ?? '';
    $label = $nama;
    if ($nik !== '') $label .= ' · ' . $nik;
    $sub = umurTeks($ub) . ' · OpenSID';
    if (!empty($p['nama_ibu'])) $sub .= ' · Ibu: ' . $p['nama_ibu'];
    if ($id_lokal) $sub .= ' · sudah di Posyandu';
    return [
        'id'            => $id_lokal > 0 ? $id_lokal : ('os:' . $oid),
        'id_lokal'      => $id_lokal,
        'id_opensid'    => $oid,
        'text'          => $label,
        'nama_lengkap'  => $nama,
        'nomor_peserta' => '',
        'nik_anak'      => $nik,
        'jenis_kelamin' => $p['jenis_kelamin'] ?? '',
        'tanggal_lahir' => $p['tanggal_lahir'] ?? '',
        'umur_bulan'    => $ub,
        'umur'          => umurTeks($ub),
        'nama_ibu'      => $p['nama_ibu'] ?? '',
        'nama_ayah'     => $p['nama_ayah'] ?? '',
        'sub'           => $sub,
        'sumber'        => 'opensid',
        'terdaftar'     => $id_lokal > 0,
        '_skor'         => skorCocok($nama, $q),
    ];
}

$data = [];
$seen_nik = [];
$seen_oid = [];
$seen_lid = [];

// ---------- 1) Query lokal (1 query, prefix-friendly) ----------
$where = "(status_aktif=1 OR status_aktif IS NULL)
    AND tanggal_lahir IS NOT NULL
    AND TIMESTAMPDIFF(MONTH, tanggal_lahir, CURDATE()) BETWEEN 0 AND 59";

if ($q !== '') {
    $s = escape($q);
    // Prefix di nama / kata / nik / ibu — memanfaatkan index jika ada di nama_lengkap
    $where .= " AND (
        nama_lengkap LIKE '{$s}%'
        OR nama_lengkap LIKE '% {$s}%'
        OR IFNULL(nik_anak,'') LIKE '{$s}%'
        OR IFNULL(nomor_peserta,'') LIKE '{$s}%'
        OR IFNULL(nama_ibu,'') LIKE '{$s}%'
        OR IFNULL(nama_ibu,'') LIKE '% {$s}%'
    )";
}

$orderSql = $q !== ''
    ? "CASE
         WHEN nama_lengkap LIKE '" . escape($q) . "%' THEN 0
         WHEN nama_lengkap LIKE '% " . escape($q) . "%' THEN 1
         ELSE 2
       END ASC, nama_lengkap ASC"
    : "nama_lengkap ASC";

$lokal = fetchAll("SELECT id, nama_lengkap, nomor_peserta, nik_anak, jenis_kelamin, tanggal_lahir,
    nama_ibu, nama_ayah, id_penduduk_opensid,
    TIMESTAMPDIFF(MONTH, tanggal_lahir, CURDATE()) AS umur_bulan
    FROM balita WHERE {$where}
    ORDER BY {$orderSql}
    LIMIT {$limit}") ?: [];

foreach ($lokal as $b) {
    $row = barisLokal($b, $q);
    $data[] = $row;
    $seen_lid[(int)$b['id']] = true;
    $nik = $row['nik_anak'];
    $oid = $row['id_opensid'];
    if ($nik !== '') $seen_nik[$nik] = true;
    if ($oid > 0) $seen_oid[$oid] = true;
}

// ---------- 2) OpenSID (query terarah, tanpa full-scan berulang) ----------
if (opensid_available() && count($data) < $limit) {
    $sisa = $limit - count($data);
    $osRows = [];

    if ($q === '') {
        // Tanpa kata kunci: ambil sebagian balita OpenSID saja
        $osRows = array_slice(getBalitaOpenSID(59, 0), 0, $sisa);
    } else {
        // a) Cari via helper OpenSID (LIKE di DB OpenSID)
        $found = cariPendudukOpenSID($q, '', min(50, $sisa + 20));
        foreach ($found as $p) {
            $tgl = $p['tanggal_lahir'] ?? '';
            if ($tgl === '') continue;
            try {
                $d1 = new DateTime($tgl);
                $ub = $d1->diff(new DateTime());
                $bulan = $ub->y * 12 + $ub->m;
            } catch (Exception $e) {
                continue;
            }
            if ($bulan < 0 || $bulan > 59) continue;
            // Terima awalan nama/kata atau awalan NIK
            $sk = skorCocok($p['nama'] ?? '', $q);
            $nikOk = ($p['nik'] ?? '') !== '' && mb_stripos($p['nik'], $q) === 0;
            if ($sk > 2 && !$nikOk) continue;
            $p['umur_bulan'] = $bulan;
            $osRows[] = $p;
        }

        // b) Jika masih sedikit dan query pendek (1–2 huruf), filter prefix dari daftar balita OpenSID
        //    Hanya sekali, dibatasi, menghindari double full-scan besar
        if (count($osRows) < max(5, $sisa) && mb_strlen($q) <= 2) {
            $all = getBalitaOpenSID(59, 0);
            $ql = mb_strtolower($q);
            foreach ($all as $p) {
                $nama = mb_strtolower($p['nama'] ?? '');
                if ($nama === '') continue;
                if (mb_strpos($nama, $ql) === 0 || mb_strpos($nama, ' ' . $ql) !== false) {
                    $osRows[] = $p;
                }
                if (count($osRows) >= $sisa + 15) break;
            }
        }
    }

    // Map id_opensid / nik → id lokal dalam 1–2 query batch (bukan per-baris)
    $oids = [];
    $niks = [];
    foreach ($osRows as $p) {
        $oid = (int)($p['id_penduduk'] ?? $p['id'] ?? 0);
        $nik = trim($p['nik'] ?? '');
        if ($oid > 0 && !isset($seen_oid[$oid])) $oids[$oid] = true;
        if ($nik !== '' && !isset($seen_nik[$nik])) $niks[$nik] = true;
    }
    $mapOid = [];
    $mapNik = [];
    if ($oids) {
        $in = implode(',', array_map('intval', array_keys($oids)));
        foreach (fetchAll("SELECT id, id_penduduk_opensid FROM balita WHERE id_penduduk_opensid IN ({$in})") ?: [] as $r) {
            $mapOid[(int)$r['id_penduduk_opensid']] = (int)$r['id'];
        }
    }
    if ($niks) {
        $parts = [];
        foreach (array_keys($niks) as $n) {
            $parts[] = "'" . escape($n) . "'";
        }
        $in = implode(',', $parts);
        foreach (fetchAll("SELECT id, nik_anak FROM balita WHERE nik_anak IN ({$in})") ?: [] as $r) {
            $mapNik[trim($r['nik_anak'])] = (int)$r['id'];
        }
    }

    foreach ($osRows as $p) {
        if (count($data) >= $limit) break;
        $oid = (int)($p['id_penduduk'] ?? $p['id'] ?? 0);
        $nik = trim($p['nik'] ?? '');
        if ($oid > 0 && isset($seen_oid[$oid])) continue;
        if ($nik !== '' && isset($seen_nik[$nik])) continue;

        $id_lokal = 0;
        if ($oid > 0 && isset($mapOid[$oid])) $id_lokal = $mapOid[$oid];
        if (!$id_lokal && $nik !== '' && isset($mapNik[$nik])) $id_lokal = $mapNik[$nik];
        if ($id_lokal && isset($seen_lid[$id_lokal])) continue;

        $row = barisOpenSID($p, $id_lokal, $q);
        $data[] = $row;
        if ($id_lokal) $seen_lid[$id_lokal] = true;
        if ($nik !== '') $seen_nik[$nik] = true;
        if ($oid > 0) $seen_oid[$oid] = true;
    }
}

// ---------- Sort & trim ----------
usort($data, function ($a, $b) {
    $sa = $a['_skor'] ?? 5;
    $sb = $b['_skor'] ?? 5;
    if ($sa !== $sb) return $sa <=> $sb;
    return strcasecmp($a['nama_lengkap'] ?? '', $b['nama_lengkap'] ?? '');
});

foreach ($data as &$r) {
    unset($r['_skor']);
}
unset($r);

$data = array_slice($data, 0, $limit);

echo json_encode([
    'success' => true,
    'results' => $data,
    'data'    => $data,
    'total'   => count($data),
    'query'   => $q,
    'opensid' => opensid_available(),
], JSON_UNESCAPED_UNICODE);
