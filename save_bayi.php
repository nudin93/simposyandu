<?php
require_once __DIR__ . '/../config/init.php';
requireLogin();
header('Content-Type: application/json; charset=utf-8');
if (function_exists('canInput') && !canInput()) {
  echo json_encode(['success'=>false,'message'=>'Akses ditolak']); exit;
}
$nama = trim($_POST['nama_lengkap'] ?? '');
$jk = $_POST['jenis_kelamin'] ?? 'L';
$tgl = $_POST['tanggal_lahir'] ?? '';
if ($nama === '' || $tgl === '') {
  echo json_encode(['success'=>false,'message'=>'Nama dan tanggal lahir wajib']); exit;
}
$nomor = generateNomorPeserta('BYI', 'bayi');
$id_os = (int)($_POST['id_penduduk_opensid'] ?? 0) ?: 'NULL';
$status_int = in_array($_POST['status_integrasi'] ?? '', ['terhubung','manual','belum']) ? $_POST['status_integrasi'] : 'belum';
$fields = [
  'nomor_peserta' => $nomor,
  'id_penduduk_opensid' => $id_os === 'NULL' ? null : $id_os,
  'status_integrasi' => $status_int,
  'nik_bayi' => trim($_POST['nik_bayi'] ?? '') ?: null,
  'nik_ibu' => trim($_POST['nik_ibu'] ?? '') ?: null,
  'no_kk' => trim($_POST['no_kk'] ?? '') ?: null,
  'nama_lengkap' => $nama,
  'jenis_kelamin' => $jk,
  'tempat_lahir' => trim($_POST['tempat_lahir'] ?? '') ?: null,
  'tanggal_lahir' => $tgl,
  'berat_lahir' => $_POST['berat_lahir'] !== '' ? (float)$_POST['berat_lahir'] : null,
  'panjang_lahir' => $_POST['panjang_lahir'] !== '' ? (float)$_POST['panjang_lahir'] : null,
  'lingkar_kepala_lahir' => $_POST['lingkar_kepala_lahir'] !== '' ? (float)$_POST['lingkar_kepala_lahir'] : null,
  'asi_eksklusif' => $_POST['asi_eksklusif'] ?? 'Ya',
  'nama_ibu' => trim($_POST['nama_ibu'] ?? '') ?: null,
  'nama_ayah' => trim($_POST['nama_ayah'] ?? '') ?: null,
  'dusun' => trim($_POST['dusun'] ?? '') ?: null,
  'rt' => trim($_POST['rt'] ?? '') ?: null,
  'rw' => trim($_POST['rw'] ?? '') ?: null,
  'alamat_lengkap' => trim($_POST['alamat_lengkap'] ?? '') ?: null,
  'catatan' => trim($_POST['catatan'] ?? '') ?: null,
];
$cols = []; $vals = [];
foreach ($fields as $k=>$v) {
  $cols[] = $k;
  if ($v === null) $vals[] = 'NULL';
  else $vals[] = "'" . escape((string)$v) . "'";
}
$sql = "INSERT INTO bayi (".implode(',',$cols).") VALUES (".implode(',',$vals).")";
if (query($sql)) {
  echo json_encode(['success'=>true,'message'=>'Data bayi berhasil disimpan!','id'=>lastInsertId()]);
} else {
  echo json_encode(['success'=>false,'message'=>'Gagal menyimpan. Pastikan tabel bayi sudah dibuat (jalankan migrasi_ilp_kemenkes.sql).']);
}
