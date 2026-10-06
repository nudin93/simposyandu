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
 * Bootstrap halaman publik (tanpa login)
 */
require_once __DIR__ . '/../config/init.php';

$pengaturan = getPengaturan();
$pengaturan = is_array($pengaturan) ? $pengaturan : [];

$app_name      = $pengaturan['nama_aplikasi'] ?? 'SIMPOSYANDU';
$nama_desa     = $pengaturan['nama_desa'] ?? 'Desa';
$nama_posyandu = $pengaturan['nama_posyandu'] ?? 'Posyandu';
$kecamatan     = $pengaturan['kecamatan'] ?? '';
$kabupaten     = $pengaturan['kabupaten'] ?? '';
$provinsi      = $pengaturan['provinsi'] ?? '';
$kontak        = $pengaturan['nomor_kontak'] ?? '';
$email         = $pengaturan['email'] ?? '';

$logo_file = trim($pengaturan['logo'] ?? '');
$logo_url = '';
if ($logo_file !== '' && is_file(__DIR__ . '/../uploads/settings/' . $logo_file)) {
    $logo_url = APP_URL . '/uploads/settings/' . rawurlencode($logo_file);
}
$favicon_file = trim($pengaturan['favicon'] ?? '');
$favicon_url = APP_URL . '/assets/img/favicon.ico';
if ($favicon_file !== '' && is_file(__DIR__ . '/../uploads/settings/' . $favicon_file)) {
    $favicon_url = APP_URL . '/uploads/settings/' . rawurlencode($favicon_file);
}

/** Hitung statistik publik (jumlah saja, tanpa data pribadi) */
function public_stats()
{
    $s = [
        'bayi' => 0, 'balita' => 0, 'bumil' => 0, 'remaja' => 0,
        'usia_produktif' => 0, 'lansia' => 0, 'kegiatan' => 0,
        'sanitasi' => 0, 'jamban_sendiri' => 0, 'tanpa_jamban' => 0,
        'skrining_ptm' => 0, 'jadwal_aktif' => 0,
    ];
    $q = function ($sql) {
        try {
            $r = fetchOne($sql);
            return (int)($r['c'] ?? 0);
        } catch (Throwable $e) {
            return 0;
        }
    };
    $s['bayi'] = $q("SELECT COUNT(*) AS c FROM bayi WHERE status_aktif=1 OR status_aktif IS NULL");
    $s['balita'] = $q("SELECT COUNT(*) AS c FROM balita WHERE status_aktif=1 OR status_aktif IS NULL");
    $s['bumil'] = $q("SELECT COUNT(*) AS c FROM ibu_hamil WHERE status_aktif=1 OR status_aktif IS NULL");
    $s['remaja'] = $q("SELECT COUNT(*) AS c FROM remaja WHERE status_aktif=1 OR status_aktif IS NULL");
    $s['usia_produktif'] = $q("SELECT COUNT(*) AS c FROM usia_produktif WHERE status_aktif=1 OR status_aktif IS NULL");
    $s['lansia'] = $q("SELECT COUNT(*) AS c FROM lansia WHERE status_aktif=1 OR status_aktif IS NULL");
    $s['kegiatan'] = $q("SELECT COUNT(*) AS c FROM kegiatan_posyandu");
    $s['sanitasi'] = $q("SELECT COUNT(*) AS c FROM sanitasi");
    $s['jamban_sendiri'] = $q("SELECT COUNT(*) AS c FROM sanitasi WHERE kepemilikan_jamban='Jamban Sendiri'");
    $s['tanpa_jamban'] = $q("SELECT COUNT(*) AS c FROM sanitasi WHERE kepemilikan_jamban='Tidak Memiliki'");
    $s['skrining_ptm'] = $q("SELECT COUNT(DISTINCT usia_produktif_id) AS c FROM pemeriksaan_dewasa");
    $s['jadwal_aktif'] = $q("SELECT COUNT(*) AS c FROM jadwal WHERE status='Terjadwal' AND tanggal_kegiatan >= CURDATE()");
    $s['sasaran'] = $s['bayi'] + $s['balita'] + $s['bumil'] + $s['remaja'] + $s['usia_produktif'] + $s['lansia'];
    return $s;
}

function public_nav_active($page)
{
    $cur = basename($_SERVER['PHP_SELF'] ?? '');
    return $cur === $page ? 'active' : '';
}
