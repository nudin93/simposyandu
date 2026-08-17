<?php
require_once __DIR__.'/../config/init.php'; requireLogin();
header('Content-Type: application/json; charset=utf-8');
$nama=trim($_POST['nama_lengkap']??''); $tgl=$_POST['tanggal_lahir']??'';
if(!$nama||!$tgl){echo json_encode(['success'=>false,'message'=>'Wajib diisi']);exit;}
$nomor=generateNomorPeserta('USP','usia_produktif');
$id_os=(int)($_POST['id_penduduk_opensid']??0);
$sql=sprintf("INSERT INTO usia_produktif (nomor_peserta,id_penduduk_opensid,status_integrasi,nik,no_kk,nama_lengkap,jenis_kelamin,tanggal_lahir,dusun,rt,rw,alamat_lengkap)
VALUES ('%s',%s,'%s',%s,%s,'%s','%s','%s',%s,%s,%s,%s)",
 escape($nomor),$id_os?:'NULL',escape($_POST['status_integrasi']??'belum'),
 $_POST['nik']?"'".escape($_POST['nik'])."'":'NULL',$_POST['no_kk']?"'".escape($_POST['no_kk'])."'":'NULL',
 escape($nama),escape($_POST['jenis_kelamin']??'L'),escape($tgl),
 $_POST['dusun']?"'".escape($_POST['dusun'])."'":'NULL',$_POST['rt']?"'".escape($_POST['rt'])."'":'NULL',
 $_POST['rw']?"'".escape($_POST['rw'])."'":'NULL',$_POST['alamat_lengkap']?"'".escape($_POST['alamat_lengkap'])."'":'NULL');
echo json_encode(query($sql)?['success'=>true,'message'=>'Data usia produktif berhasil disimpan!']:['success'=>false,'message'=>'Gagal. Cek migrasi.']);
