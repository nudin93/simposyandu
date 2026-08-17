<?php
require_once __DIR__ . '/../config/init.php';
requireLogin();
$ibu_id = (int)($_POST['ibu_hamil_id'] ?? 0);
$tgl = escape($_POST['tanggal_pemeriksaan'] ?? '');
if (!$ibu_id || !$tgl) jsonResponse(false, 'Field wajib belum diisi');
$petugas_id = (int)($_POST['petugas_id'] ?? 0) ?: 'NULL';
$sql = "INSERT INTO pemeriksaan_ibu_hamil (ibu_hamil_id,tanggal_pemeriksaan,petugas_id,usia_kandungan,berat_badan,tekanan_darah,tinggi_fundus,djj,posisi_janin,gerakan_janin,lila,keluhan,riwayat_penyakit,tablet_fe,vitamin,imunisasi_tt,pemeriksaan_lab,catatan_bidan,jadwal_kontrol,risiko_kek,risiko_anemia,risiko_hipertensi,risiko_preeklamsia,status_risiko)
VALUES ($ibu_id,'$tgl',$petugas_id,'".escape($_POST['usia_kandungan']??0)."','".escape($_POST['berat_badan']??0)."','".escape($_POST['tekanan_darah']??'')."','".escape($_POST['tinggi_fundus']??0)."','".escape($_POST['djj']??0)."','".escape($_POST['posisi_janin']??'')."','".escape($_POST['gerakan_janin']??'Aktif')."','".escape($_POST['lila']??0)."','".escape($_POST['keluhan']??'')."','".escape($_POST['riwayat_penyakit']??'')."',".((int)($_POST['tablet_fe']??0)).",'".escape($_POST['vitamin']??'')."','".escape($_POST['imunisasi_tt']??'Tidak')."','".escape($_POST['pemeriksaan_lab']??'')."','".escape($_POST['catatan_bidan']??'')."','".escape($_POST['jadwal_kontrol']??'')."',".((int)($_POST['risiko_kek']??0)).",".((int)($_POST['risiko_anemia']??0)).",".((int)($_POST['risiko_hipertensi']??0)).",0,'".escape($_POST['status_risiko']??'Normal')."')";
if (query($sql)) jsonResponse(true, 'Data ANC berhasil disimpan!');
else jsonResponse(false, 'Gagal menyimpan data ANC');
