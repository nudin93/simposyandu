<?php
require_once __DIR__.'/../config/init.php'; requireLogin();
header('Content-Type: application/json; charset=utf-8');
$tgl=$_POST['tanggal_kegiatan']??''; $nama=trim($_POST['nama_posyandu']??'');
if(!$tgl||!$nama){echo json_encode(['success'=>false,'message'=>'Tanggal & nama posyandu wajib']);exit;}
$petugas=currentUser()['id']??'NULL';
$f=function($k){return isset($_POST[$k])&&$_POST[$k]!==''?"'".escape($_POST[$k])."'":'NULL';};
$sql=sprintf("INSERT INTO kegiatan_posyandu (tanggal_kegiatan,nama_posyandu,lokasi,kader_bertugas,jumlah_sasaran,jumlah_hadir,langkah1_pendaftaran,langkah2_pengukuran,langkah3_pencatatan,langkah4_pelayanan,langkah5_penyuluhan,materi_penyuluhan,keterangan,status,petugas_id)
VALUES ('%s','%s',%s,%s,%d,%d,%s,%s,%s,%s,%s,%s,%s,'%s',%s)",
 escape($tgl),escape($nama),$f('lokasi'),$f('kader_bertugas'),
 (int)($_POST['jumlah_sasaran']??0),(int)($_POST['jumlah_hadir']??0),
 $f('langkah1_pendaftaran'),$f('langkah2_pengukuran'),$f('langkah3_pencatatan'),$f('langkah4_pelayanan'),$f('langkah5_penyuluhan'),
 $f('materi_penyuluhan'),$f('keterangan'),escape($_POST['status']??'Selesai'),$petugas);
echo json_encode(query($sql)?['success'=>true,'message'=>'Kegiatan Posyandu berhasil disimpan!']:['success'=>false,'message'=>'Gagal. Jalankan migrasi ILP.']);
