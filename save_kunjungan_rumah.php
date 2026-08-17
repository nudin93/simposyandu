<?php
require_once __DIR__.'/../config/init.php'; requireLogin();
header('Content-Type: application/json; charset=utf-8');
$tgl=$_POST['tanggal_kunjungan']??''; $nama=trim($_POST['nama_keluarga']??'');
if(!$tgl||!$nama){echo json_encode(['success'=>false,'message'=>'Tanggal & nama keluarga wajib']);exit;}
$kader=currentUser()['id']??'NULL';
$f=function($k){return isset($_POST[$k])&&$_POST[$k]!==''?"'".escape($_POST[$k])."'":'NULL';};
$sql=sprintf("INSERT INTO kunjungan_rumah (tanggal_kunjungan,no_kk,nama_keluarga,alamat,dusun,rt,rw,prioritas,masalah_ditemukan,tindakan,rujukan,status_tindak_lanjut,kader_id,catatan)
VALUES ('%s',%s,'%s',%s,%s,%s,%s,'%s',%s,%s,%s,'%s',%s,%s)",
 escape($tgl),$f('no_kk'),escape($nama),$f('alamat'),$f('dusun'),$f('rt'),$f('rw'),
 escape($_POST['prioritas']??'Lainnya'),$f('masalah_ditemukan'),$f('tindakan'),$f('rujukan'),
 escape($_POST['status_tindak_lanjut']??'Belum'),$kader,$f('catatan'));
echo json_encode(query($sql)?['success'=>true,'message'=>'Kunjungan rumah berhasil disimpan!']:['success'=>false,'message'=>'Gagal. Jalankan migrasi ILP.']);
