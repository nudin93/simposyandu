<?php
require_once __DIR__ . '/../config/init.php';
requireLogin();
$balita_id = (int)($_POST['balita_id'] ?? 0) ?: 'NULL';
$jenis = escape($_POST['jenis_vitamin'] ?? '');
$tgl = escape($_POST['tanggal_pemberian'] ?? '');
if (!$jenis || !$tgl) jsonResponse(false, 'Field wajib belum diisi');
$petugas_id = (int)($_POST['petugas_id'] ?? 0) ?: 'NULL';
$sql = "INSERT INTO vitamin (balita_id,jenis_vitamin,dosis,tanggal_pemberian,petugas_id,catatan)
VALUES ($balita_id,'$jenis','".escape($_POST['dosis']??'')."','$tgl',$petugas_id,'".escape($_POST['catatan']??'')."')";
if (query($sql)) jsonResponse(true, 'Data vitamin berhasil disimpan!');
else jsonResponse(false, 'Gagal menyimpan data');
