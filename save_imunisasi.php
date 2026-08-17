<?php
require_once __DIR__ . '/../config/init.php';
requireLogin();
$balita_id = (int)($_POST['balita_id'] ?? 0);
$jenis = escape($_POST['jenis_imunisasi'] ?? '');
$tgl = escape($_POST['tanggal_imunisasi'] ?? '');
if (!$balita_id || !$jenis || !$tgl) jsonResponse(false, 'Field wajib belum diisi');
$petugas_id = (int)($_POST['petugas_id'] ?? 0) ?: 'NULL';
$sql = "INSERT INTO imunisasi (balita_id,jenis_imunisasi,dosis,tanggal_imunisasi,petugas_id,efek_samping,keterangan)
VALUES ($balita_id,'$jenis','".escape($_POST['dosis']??'')."','$tgl',$petugas_id,'".escape($_POST['efek_samping']??'')."','".escape($_POST['keterangan']??'')."')";
if (query($sql)) jsonResponse(true, 'Data imunisasi berhasil disimpan!');
else jsonResponse(false, 'Gagal menyimpan data');
