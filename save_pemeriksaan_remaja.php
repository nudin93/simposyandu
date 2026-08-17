<?php
require_once __DIR__.'/../config/init.php'; requireLogin();
header('Content-Type: application/json; charset=utf-8');
$rid=(int)($_POST['remaja_id']??0); $tgl=$_POST['tanggal_pemeriksaan']??'';
if(!$rid||!$tgl){echo json_encode(['success'=>false,'message'=>'Data kurang']);exit;}
$bb=$_POST['berat_badan']!==''?(float)$_POST['berat_badan']:null;
$tb=$_POST['tinggi_badan']!==''?(float)$_POST['tinggi_badan']:null;
$imt=$bb&&$tb?round($bb/(($tb/100)**2),2):null;
$hb=$_POST['hb']!==''?(float)$_POST['hb']:null;
$anemia=function_exists('statusAnemia')?statusAnemia($hb,'P'):0;
$sg=function_exists('statusIMTDewasa')?statusIMTDewasa($imt):'Normal';
$petugas=currentUser()['id']??'NULL';
$sql=sprintf("INSERT INTO pemeriksaan_remaja (remaja_id,tanggal_pemeriksaan,berat_badan,tinggi_badan,imt,status_gizi,hb,status_anemia,tekanan_darah,edukasi_kesehatan,keluhan,petugas_id)
VALUES (%d,'%s',%s,%s,%s,'%s',%s,%d,%s,%s,%s,%s)",
  $rid,escape($tgl),$bb??'NULL',$tb??'NULL',$imt??'NULL',escape($sg),$hb??'NULL',$anemia,
  $_POST['tekanan_darah']?"'".escape($_POST['tekanan_darah'])."'":'NULL',
  $_POST['edukasi_kesehatan']?"'".escape($_POST['edukasi_kesehatan'])."'":'NULL',
  $_POST['keluhan']?"'".escape($_POST['keluhan'])."'":'NULL',$petugas);
echo json_encode(query($sql)?['success'=>true,'message'=>'Data berhasil disimpan!']:['success'=>false,'message'=>'Gagal simpan']);
