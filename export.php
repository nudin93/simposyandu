<?php
require_once __DIR__ . '/../config/init.php';
requireLogin();
$type = $_GET['type'] ?? '';
if ($type === 'balita') {
    $data = fetchAll("SELECT nomor_peserta, nama_lengkap, jenis_kelamin, tanggal_lahir, nama_ibu, nama_ayah, no_hp, desa, status_imunisasi, bpjs_kis FROM balita ORDER BY nama_lengkap");
    $headers = ['No. Peserta','Nama Anak','L/P','Tanggal Lahir','Nama Ibu','Nama Ayah','No HP','Desa','Status Imunisasi','BPJS/KIS'];
    $filename = 'data_balita_' . date('Ymd') . '.csv';
} elseif ($type === 'ibu_hamil') {
    $data = fetchAll("SELECT nomor_peserta, nama, tanggal_lahir, hpht, hpl, kehamilan_ke, desa, no_hp FROM ibu_hamil ORDER BY nama");
    $headers = ['No. Peserta','Nama','Tgl Lahir','HPHT','HPL','Kehamilan Ke','Desa','No HP'];
    $filename = 'data_ibu_hamil_' . date('Ymd') . '.csv';
} else {
    jsonResponse(false, 'Tipe tidak valid');
}
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
$out = fopen('php://output', 'w');
fwrite($out, "\xEF\xBB\xBF");
fputcsv($out, $headers);
foreach ($data as $row) fputcsv($out, array_values($row));
fclose($out);
exit;
