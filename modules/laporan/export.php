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

require_once __DIR__ . '/../../config/init.php';
requireLogin();

$type = $_GET['type'] ?? '';
$tgl_dari = db_date($_GET['tgl_dari'] ?? '', date('Y-m-01')) ?: date('Y-m-01');
$tgl_sampai = db_date($_GET['tgl_sampai'] ?? '', date('Y-m-d')) ?: date('Y-m-d');
if ($tgl_sampai < $tgl_dari) { $tmp=$tgl_dari; $tgl_dari=$tgl_sampai; $tgl_sampai=$tmp; }
$dusun = $_GET['dusun'] ?? '';
$cari = trim($_GET['cari'] ?? '');

header('Content-Type: text/csv; charset=utf-8');
$out = fopen('php://output', 'w');
fwrite($out, "\xEF\xBB\xBF"); // BOM UTF-8

if ($type === 'balita') {
    $filename = 'laporan_balita_' . date('Ymd') . '.csv';
    header('Content-Disposition: attachment; filename="'.$filename.'"');
    fputcsv($out, ['No','No. Peserta','Nama Balita','L/P','Usia (bln)','Dusun','Tgl Periksa','BB (kg)','TB (cm)','Status Gizi','Risiko Stunting']);
    $where = ["pb.tanggal_pemeriksaan BETWEEN '".escape($tgl_dari)."' AND '".escape($tgl_sampai)."'"];
    if ($dusun) $where[] = "b.dusun='".escape($dusun)."'";
    if ($cari) $where[] = "(b.nama_lengkap LIKE '%".escape($cari)."%' OR b.nomor_peserta LIKE '%".escape($cari)."%')";
    $status_gizi = $_GET['status_gizi'] ?? '';
    $risiko = $_GET['risiko'] ?? '';
    if ($status_gizi) $where[] = "pb.status_gizi='".escape($status_gizi)."'";
    if ($risiko) $where[] = "pb.risiko_stunting='".escape($risiko)."'";
    $rows = fetchAll("SELECT b.nomor_peserta,b.nama_lengkap,b.jenis_kelamin,b.tanggal_lahir,b.dusun,pb.tanggal_pemeriksaan,pb.berat_badan,pb.tinggi_badan,pb.status_gizi,pb.risiko_stunting,pb.umur_saat_periksa FROM pemeriksaan_balita pb JOIN balita b ON b.id=pb.balita_id WHERE ".implode(' AND ',$where)." ORDER BY pb.tanggal_pemeriksaan DESC");
    foreach ($rows as $i => $r) {
        fputcsv($out, csv_row_guard([$i+1, $r['nomor_peserta'], $r['nama_lengkap'], $r['jenis_kelamin'], $r['umur_saat_periksa']??hitungUmurBulan($r['tanggal_lahir']), $r['dusun']??'', $r['tanggal_pemeriksaan'], $r['berat_badan'], $r['tinggi_badan'], $r['status_gizi'], $r['risiko_stunting']])]);
    }
} elseif ($type === 'ibu_hamil') {
    $filename = 'laporan_ibu_hamil_' . date('Ymd') . '.csv';
    header('Content-Disposition: attachment; filename="'.$filename.'"');
    fputcsv($out, ['No','No. Peserta','Nama','Dusun','Tgl Periksa','Usia Kandungan','BB','TD','Kehamilan Ke','Status Risiko']);
    $where = ["pih.tanggal_pemeriksaan BETWEEN '".escape($tgl_dari)."' AND '".escape($tgl_sampai)."'"];
    if ($dusun) $where[] = "ih.dusun='".escape($dusun)."'";
    if ($cari) $where[] = "(ih.nama LIKE '%".escape($cari)."%' OR ih.nomor_peserta LIKE '%".escape($cari)."%')";
    $rows = fetchAll("SELECT ih.nomor_peserta,ih.nama,ih.dusun,ih.kehamilan_ke,pih.tanggal_pemeriksaan,pih.usia_kandungan,pih.berat_badan,pih.tekanan_darah,pih.status_risiko FROM pemeriksaan_ibu_hamil pih JOIN ibu_hamil ih ON ih.id=pih.ibu_hamil_id WHERE ".implode(' AND ',$where)." ORDER BY pih.tanggal_pemeriksaan DESC");
    foreach ($rows as $i => $r) {
        fputcsv($out, csv_row_guard([$i+1, $r['nomor_peserta'], $r['nama'], $r['dusun']??'', $r['tanggal_pemeriksaan'], $r['usia_kandungan'], $r['berat_badan'], $r['tekanan_darah'], $r['kehamilan_ke'], $r['status_risiko']])]);
    }
} elseif ($type === 'lansia') {
    $filename = 'laporan_lansia_' . date('Ymd') . '.csv';
    header('Content-Disposition: attachment; filename="'.$filename.'"');
    fputcsv($out, ['No','No. Peserta','Nama','L/P','Umur','Tgl Periksa','BB','TB','Tekanan Darah','Gula Darah','Kolesterol']);
    $where = ["pl.tanggal_pemeriksaan BETWEEN '".escape($tgl_dari)."' AND '".escape($tgl_sampai)."'"];
    if ($cari) $where[] = "(l.nama LIKE '%".escape($cari)."%' OR l.nomor_peserta LIKE '%".escape($cari)."%')";
    $rows = fetchAll("SELECT l.nomor_peserta,l.nama,l.jenis_kelamin,l.tanggal_lahir,pl.tanggal_pemeriksaan,pl.berat_badan,pl.tinggi_badan,pl.tekanan_darah,pl.gula_darah,pl.kolesterol FROM pemeriksaan_lansia pl JOIN lansia l ON l.id=pl.lansia_id WHERE ".implode(' AND ',$where)." ORDER BY pl.tanggal_pemeriksaan DESC");
    foreach ($rows as $i => $r) {
        fputcsv($out, csv_row_guard([$i+1, $r['nomor_peserta'], $r['nama'], $r['jenis_kelamin'], hitungUmur($r['tanggal_lahir']), $r['tanggal_pemeriksaan'], $r['berat_badan'], $r['tinggi_badan'], $r['tekanan_darah'], $r['gula_darah'], $r['kolesterol']])]);
    }
} elseif ($type === 'imunisasi') {
    $filename = 'laporan_imunisasi_' . date('Ymd') . '.csv';
    header('Content-Disposition: attachment; filename="'.$filename.'"');
    fputcsv($out, ['No','No. Peserta','Nama Balita','L/P','Dusun','Jenis Imunisasi','Dosis','Tanggal']);
    $where = ["i.tanggal_imunisasi BETWEEN '".escape($tgl_dari)."' AND '".escape($tgl_sampai)."'"];
    $jenis = $_GET['jenis'] ?? '';
    if ($jenis) $where[] = "i.jenis_imunisasi='".escape($jenis)."'";
    if ($cari) $where[] = "(b.nama_lengkap LIKE '%".escape($cari)."%' OR b.nomor_peserta LIKE '%".escape($cari)."%')";
    $rows = fetchAll("SELECT b.nomor_peserta,b.nama_lengkap,b.jenis_kelamin,b.dusun,i.jenis_imunisasi,i.dosis,i.tanggal_imunisasi FROM imunisasi i JOIN balita b ON b.id=i.balita_id WHERE ".implode(' AND ',$where)." ORDER BY i.tanggal_imunisasi DESC");
    foreach ($rows as $i => $r) {
        fputcsv($out, csv_row_guard([$i+1, $r['nomor_peserta'], $r['nama_lengkap'], $r['jenis_kelamin'], $r['dusun']??'', $r['jenis_imunisasi'], $r['dosis'], $r['tanggal_imunisasi']])]);
    }
} elseif ($type === 'stunting') {
    $filename = 'laporan_stunting_' . date('Ymd') . '.csv';
    header('Content-Disposition: attachment; filename="'.$filename.'"');
    fputcsv($out, ['No','No. Peserta','Nama','L/P','Usia (bln)','Dusun','Tgl Periksa','BB','TB','Status Gizi','Risiko']);
    $where = ["pb.tanggal_pemeriksaan BETWEEN '".escape($tgl_dari)."' AND '".escape($tgl_sampai)."'"];
    if ($dusun) $where[] = "b.dusun='".escape($dusun)."'";
    $risiko = $_GET['risiko'] ?? '';
    if ($risiko) $where[] = "pb.risiko_stunting='".escape($risiko)."'";
    else $where[] = "pb.risiko_stunting IN ('Risiko','Stunting')";
    $rows = fetchAll("SELECT b.nomor_peserta,b.nama_lengkap,b.jenis_kelamin,b.tanggal_lahir,b.dusun,pb.tanggal_pemeriksaan,pb.berat_badan,pb.tinggi_badan,pb.status_gizi,pb.risiko_stunting,pb.umur_saat_periksa FROM pemeriksaan_balita pb JOIN balita b ON b.id=pb.balita_id WHERE ".implode(' AND ',$where)." ORDER BY pb.tanggal_pemeriksaan DESC");
    foreach ($rows as $i => $r) {
        fputcsv($out, csv_row_guard([$i+1, $r['nomor_peserta'], $r['nama_lengkap'], $r['jenis_kelamin'], $r['umur_saat_periksa']??hitungUmurBulan($r['tanggal_lahir']), $r['dusun']??'', $r['tanggal_pemeriksaan'], $r['berat_badan'], $r['tinggi_badan'], $r['status_gizi'], $r['risiko_stunting']])]);
    }
} elseif ($type === 'gizi') {
    $filename = 'laporan_gizi_' . date('Ymd') . '.csv';
    header('Content-Disposition: attachment; filename="'.$filename.'"');
    fputcsv($out, ['No','No. Peserta','Nama','L/P','Dusun','Tgl Periksa','Usia (bln)','BB','TB','Status Gizi']);
    $where = ["pb.tanggal_pemeriksaan BETWEEN '".escape($tgl_dari)."' AND '".escape($tgl_sampai)."'"];
    if ($dusun) $where[] = "b.dusun='".escape($dusun)."'";
    $status_gizi = $_GET['status_gizi'] ?? '';
    if ($status_gizi) $where[] = "pb.status_gizi='".escape($status_gizi)."'";
    $rows = fetchAll("SELECT b.nomor_peserta,b.nama_lengkap,b.jenis_kelamin,b.dusun,pb.tanggal_pemeriksaan,pb.berat_badan,pb.tinggi_badan,pb.status_gizi,pb.umur_saat_periksa FROM pemeriksaan_balita pb JOIN balita b ON b.id=pb.balita_id WHERE ".implode(' AND ',$where)." ORDER BY pb.tanggal_pemeriksaan DESC");
    foreach ($rows as $i => $r) {
        fputcsv($out, csv_row_guard([$i+1, $r['nomor_peserta'], $r['nama_lengkap'], $r['jenis_kelamin'], $r['dusun']??'', $r['tanggal_pemeriksaan'], $r['umur_saat_periksa'], $r['berat_badan'], $r['tinggi_badan'], $r['status_gizi']])]);
    }
} elseif ($type === 'kegiatan') {
    $filename = 'laporan_kegiatan_' . date('Ymd') . '.csv';
    header('Content-Disposition: attachment; filename="'.$filename.'"');
    fputcsv($out, ['No','Nama Kegiatan','Tanggal','Jam','Lokasi','Jenis','PJ','Status']);
    $where = ["tanggal_kegiatan BETWEEN '".escape($tgl_dari)."' AND '".escape($tgl_sampai)."'"];
    $jenis = $_GET['jenis'] ?? '';
    $status = $_GET['status'] ?? '';
    if ($jenis) $where[] = "jenis_kegiatan='".escape($jenis)."'";
    if ($status) $where[] = "status='".escape($status)."'";
    $rows = fetchAll("SELECT * FROM jadwal WHERE ".implode(' AND ',$where)." ORDER BY tanggal_kegiatan DESC");
    foreach ($rows as $i => $r) {
        fputcsv($out, csv_row_guard([$i+1, $r['nama_kegiatan'], $r['tanggal_kegiatan'], $r['jam'], $r['lokasi'], $r['jenis_kegiatan'], $r['penanggung_jawab'], $r['status']])]);
    }
} else {
    header('Content-Type: text/plain');
    echo 'Tipe laporan tidak valid';
    exit;
}

fclose($out);
exit;
