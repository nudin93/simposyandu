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
/** Kelola Kelas Anak TK: TK A -> TK B -> Lulus. POST: id, aksi=naik|set|lulus, kelas, csrf_token */
error_reporting(0);
ini_set('display_errors', '0');
require_once __DIR__ . '/../config/init.php';
requireLogin();
$csrf = $_POST["csrf_token"] ?? $_GET["csrf_token"] ?? "";
if (!verifyCsrf($csrf)) { jsonResponse(false, "Token keamanan tidak valid. Silakan refresh halaman lalu coba lagi."); }
header('Content-Type: application/json; charset=utf-8');
$id = (int)($_POST['id'] ?? 0);
$aksi = trim($_POST['aksi'] ?? '');
$ids = [];
if (isset($_POST['ids']) && is_array($_POST['ids'])) {
    foreach ($_POST['ids'] as $v) { $v = (int)$v; if ($v > 0) $ids[] = $v; }
    $ids = array_values(array_unique($ids));
    if (count($ids) > 200) $ids = array_slice($ids, 0, 200);
}
if (function_exists('canInput') && !canInput()) {
    echo json_encode(['success'=>false,'message'=>'Akses ditolak']); exit;
}
if (in_array($aksi, ['naik_bulk','lulus_bulk','hapus_bulk'], true)) {
    if (empty($ids)) { echo json_encode(['success'=>false,'message'=>'Tidak ada data dipilih']); exit; }
    $in = implode(',', $ids);
    $rows = fetchAll("SELECT id, nama_lengkap, kelas, status_aktif FROM anak_tk WHERE id IN ($in)");
    $ok = 0; $skip = 0;
    foreach ($rows as $r) {
        $rid = (int)$r['id'];
        $rk = trim((string)($r['kelas'] ?? ''));
        $rl = ((int)($r['status_aktif'] ?? 1)) === 0;
        if ($aksi === 'naik_bulk') {
            if (!$rl && $rk === 'TK A') { if (query("UPDATE anak_tk SET kelas='TK B', status_aktif=1 WHERE id=$rid")) $ok++; else $skip++; }
            else $skip++;
        } elseif ($aksi === 'lulus_bulk') {
            if (!$rl && $rk === 'TK B') { if (query("UPDATE anak_tk SET status_aktif=0 WHERE id=$rid")) $ok++; else $skip++; }
            else $skip++;
        } else {
            query("DELETE FROM pemeriksaan_anak_tk WHERE anak_tk_id=$rid");
            if (query("DELETE FROM anak_tk WHERE id=$rid")) $ok++; else $skip++;
        }
    }
    $label = $aksi === 'naik_bulk' ? 'naik ke TK B' : ($aksi === 'lulus_bulk' ? 'diluluskan' : 'dihapus');
    $msg = $ok > 0 ? ($ok.' anak berhasil '.$label.($skip>0 ? ', '.$skip.' dilewati' : '')) : 'Tidak ada data yang diproses';
    echo json_encode(['success'=>$ok>0,'message'=>$msg,'ok'=>$ok,'skip'=>$skip]); exit;
}
if ($id <= 0 || !in_array($aksi, ['naik','set','lulus'], true)) {
    echo json_encode(['success'=>false,'message'=>'Permintaan tidak valid']); exit;
}
$row = fetchOne("SELECT id, nama_lengkap, kelas, status_aktif FROM anak_tk WHERE id=$id LIMIT 1");
if (!$row) { echo json_encode(['success'=>false,'message'=>'Data anak tidak ditemukan']); exit; }
$kls = trim((string)($row['kelas'] ?? ''));
if ($aksi === 'naik') {
    if ($kls === 'TK A') {
        $ok = query("UPDATE anak_tk SET kelas='TK B', status_aktif=1 WHERE id=$id");
        echo json_encode($ok?['success'=>true,'message'=>$row['nama_lengkap'].' naik ke TK B']:['success'=>false,'message'=>'Gagal menyimpan']); exit;
    }
    echo json_encode(['success'=>false,'message'=>'Hanya TK A yang bisa naik ke TK B']); exit;
}
if ($aksi === 'lulus') {
    if ($kls !== 'TK B') { echo json_encode(['success'=>false,'message'=>'Hanya TK B yang bisa diluluskan']); exit; }
    $ok = query("UPDATE anak_tk SET status_aktif=0 WHERE id=$id");
    echo json_encode($ok?['success'=>true,'message'=>$row['nama_lengkap'].' telah LULUS']:['success'=>false,'message'=>'Gagal menyimpan']); exit;
}
$setKelas = trim($_POST['kelas'] ?? '');
if ($setKelas !== 'TK A' && $setKelas !== 'TK B') { echo json_encode(['success'=>false,'message'=>'Kelas tidak valid']); exit; }
$ok = query("UPDATE anak_tk SET kelas='".escape($setKelas)."', status_aktif=1 WHERE id=$id");
echo json_encode($ok?['success'=>true,'message'=>$row['nama_lengkap'].' masuk '.$setKelas]:['success'=>false,'message'=>'Gagal menyimpan']);
