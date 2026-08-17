<?php
require_once __DIR__.'/../config/init.php'; requireLogin();
header('Content-Type: application/json; charset=utf-8');
$uid=(int)($_POST['usia_produktif_id']??0); $tgl=$_POST['tanggal_pemeriksaan']??'';
if(!$uid||!$tgl){echo json_encode(['success'=>false,'message'=>'Data kurang']);exit;}
$bb=$_POST['berat_badan']!==''?(float)$_POST['berat_badan']:null;
$tb=$_POST['tinggi_badan']!==''?(float)$_POST['tinggi_badan']:null;
$imt=$bb&&$tb?round($bb/(($tb/100)**2),2):null;
$lp=$_POST['lingkar_perut']!==''?(float)$_POST['lingkar_perut']:null;
$gd=$_POST['gula_darah']!==''?(float)$_POST['gula_darah']:null;
$td=$_POST['tekanan_darah']??'';
$r_ht=0;$r_dm=0;$r_ob=0;
if($imt!==null && $imt>=25)$r_ob=1;
if($gd!==null && $gd>=200)$r_dm=1;
if(preg_match('/(\d+)/',$td,$m) && (int)$m[1]>=140)$r_ht=1;
$petugas=currentUser()['id']??'NULL';
$sql=sprintf("INSERT INTO pemeriksaan_dewasa (usia_produktif_id,tanggal_pemeriksaan,berat_badan,tinggi_badan,imt,lingkar_perut,tekanan_darah,gula_darah,kolesterol,faktor_risiko,risiko_hipertensi,risiko_diabetes,risiko_obesitas,penanganan,rujukan,petugas_id)
VALUES (%d,'%s',%s,%s,%s,%s,%s,%s,%s,%s,%d,%d,%d,%s,%s,%s)",
 $uid,escape($tgl),$bb??'NULL',$tb??'NULL',$imt??'NULL',$lp??'NULL',
 $td?"'".escape($td)."'":'NULL',$gd??'NULL',
 $_POST['kolesterol']!==''?(float)$_POST['kolesterol']:'NULL',
 $_POST['faktor_risiko']?"'".escape($_POST['faktor_risiko'])."'":'NULL',
 $r_ht,$r_dm,$r_ob,
 $_POST['penanganan']?"'".escape($_POST['penanganan'])."'":'NULL',
 $_POST['rujukan']?"'".escape($_POST['rujukan'])."'":'NULL',$petugas);
echo json_encode(query($sql)?['success'=>true,'message'=>'Skrining PTM berhasil disimpan!']:['success'=>false,'message'=>'Gagal']);
