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
 * Ambil data suami dari KK OpenSID berdasarkan id_penduduk perempuan.
 * Jika ibu Kepala Keluarga / pisah KK → manual_required = true.
 */
error_reporting(0);
ini_set('display_errors', '0');
require_once __DIR__ . '/../config/init.php';
requireLogin();

header('Content-Type: application/json; charset=utf-8');

$id = (int)($_GET['id_penduduk'] ?? $_POST['id_penduduk'] ?? 0);
if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID tidak valid', 'data' => null]);
    exit;
}

if (!opensid_available()) {
    echo json_encode(['success' => false, 'message' => 'OpenSID tidak terhubung', 'data' => null]);
    exit;
}

$p = getPendudukOpenSIDById($id);
if (!$p) {
    echo json_encode(['success' => false, 'message' => 'Penduduk tidak ditemukan', 'data' => null]);
    exit;
}

$id_kk = (int)($p['id_kk'] ?? 0);
$kk_level = (int)($p['kk_level'] ?? 0);
$suami = $id_kk > 0 ? getSuamiFromOpenSID($id_kk, $kk_level) : [
    'manual_required' => true,
    'alasan' => 'Tidak ada data KK',
    'nama' => '', 'nik' => '', 'no_hp' => '', 'pekerjaan' => '',
];

$manual = !empty($suami['manual_required']);

echo json_encode([
    'success' => true,
    'manual_required' => $manual,
    'alasan' => $suami['alasan'] ?? '',
    'data' => $manual ? null : $suami,
    'penduduk' => [
        'id_penduduk' => $p['id_penduduk'] ?? $id,
        'id_kk' => $id_kk,
        'kk_level' => $kk_level,
        'status_dalam_keluarga' => $p['status_dalam_keluarga'] ?? mapHubungan($kk_level),
        'nama' => $p['nama'] ?? '',
        'nik' => $p['nik'] ?? '',
        'no_kk' => $p['no_kk'] ?? '',
    ],
]);
