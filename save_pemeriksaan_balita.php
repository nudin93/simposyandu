<?php
require_once __DIR__ . '/../config/init.php';
requireLogin();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonResponse(false, 'Method not allowed');
$balita_id = (int)($_POST['balita_id'] ?? 0);
$tgl = escape($_POST['tanggal_pemeriksaan'] ?? '');
$bb = (float)($_POST['berat_badan'] ?? 0);
$tb = (float)($_POST['tinggi_badan'] ?? 0);
if (!$balita_id || !$tgl || !$bb || !$tb) jsonResponse(false, 'Field wajib belum diisi');
$umur = (int)($_POST['umur_saat_periksa'] ?? 0);
$imt = (float)($_POST['imt'] ?? 0);
$status_gizi = escape($_POST['status_gizi'] ?? 'Normal');
$risiko_stunting = escape($_POST['risiko_stunting'] ?? 'Tidak');
$petugas_id = (int)($_POST['petugas_id'] ?? 0) ?: 'NULL';
$sql = "INSERT INTO pemeriksaan_balita (balita_id,tanggal_pemeriksaan,petugas_id,berat_badan,tinggi_badan,lingkar_kepala,lingkar_lengan,suhu_tubuh,denyut_nadi,nafsu_makan,status_asi,imunisasi_diberikan,vitamin_diberikan,keluhan,penanganan,catatan_kader,jadwal_kontrol,umur_saat_periksa,imt,status_gizi,risiko_stunting)
VALUES ($balita_id,'$tgl',$petugas_id,$bb,$tb,'".escape($_POST['lingkar_kepala']??0)."','".escape($_POST['lingkar_lengan']??0)."','".escape($_POST['suhu_tubuh']??0)."',".(int)($_POST['denyut_nadi']??0).",'".escape($_POST['nafsu_makan']??'Baik')."','".escape($_POST['status_asi']??'Ya')."','".escape($_POST['imunisasi_diberikan']??'')."','".escape($_POST['vitamin_diberikan']??'')."','".escape($_POST['keluhan']??'')."','".escape($_POST['penanganan']??'')."','".escape($_POST['catatan_kader']??'')."','".escape($_POST['jadwal_kontrol']??'')."',$umur,$imt,'$status_gizi','$risiko_stunting')";
if (query($sql)) jsonResponse(true, 'Pemeriksaan berhasil disimpan!');
else jsonResponse(false, 'Gagal menyimpan pemeriksaan');
