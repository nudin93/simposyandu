<?php
error_reporting(0);
ini_set("display_errors","0");
require_once __DIR__ . '/../config/init.php';
requireLogin();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonResponse(false, 'Method not allowed');
$nomor = escape($_POST['nomor_peserta'] ?? '');
$nama = escape($_POST['nama'] ?? '');
$hpht = escape($_POST['hpht'] ?? '');
if (!$nama) jsonResponse(false, 'Nama wajib diisi');
$hpl = $hpht ? hitungHPL($hpht) : '';
$fields = ['nomor_peserta'=>$nomor,'nik'=>escape($_POST['nik']??''),'nama'=>$nama,
  'tempat_lahir'=>escape($_POST['tempat_lahir']??''),'tanggal_lahir'=>escape($_POST['tanggal_lahir']??''),
  'golongan_darah'=>escape($_POST['golongan_darah']??''),'pendidikan'=>escape($_POST['pendidikan']??''),
  'pekerjaan'=>escape($_POST['pekerjaan']??''),'no_hp'=>escape($_POST['no_hp']??''),
  'nama_suami'=>escape($_POST['nama_suami']??''),'nik_suami'=>escape($_POST['nik_suami']??''),
  'pekerjaan_suami'=>escape($_POST['pekerjaan_suami']??''),'no_hp_suami'=>escape($_POST['no_hp_suami']??''),
  'kehamilan_ke'=>(int)($_POST['kehamilan_ke']??1),'hpht'=>$hpht,'hpl'=>$hpl,
  'berat_awal'=>(float)($_POST['berat_awal']??0),'tinggi_badan'=>(float)($_POST['tinggi_badan']??0),
  'lila'=>(float)($_POST['lila']??0),'riwayat_penyakit'=>escape($_POST['riwayat_penyakit']??''),
  'riwayat_persalinan'=>escape($_POST['riwayat_persalinan']??''),'bpjs_kis'=>escape($_POST['bpjs_kis']??''),
  'dusun'=>escape($_POST['dusun']??''),'rt'=>escape($_POST['rt']??''),'rw'=>escape($_POST['rw']??''),
  'desa'=>escape($_POST['desa']??''),'kecamatan'=>escape($_POST['kecamatan']??''),
  'kabupaten'=>escape($_POST['kabupaten']??''),'provinsi'=>escape($_POST['provinsi']??''),
  'alamat_lengkap'=>escape($_POST['alamat_lengkap']??''),'status_aktif'=>1];
foreach (['foto_ibu'=>'ibu_hamil','foto_kia'=>'ibu_hamil','foto_ktp'=>'ibu_hamil','foto_bpjs'=>'ibu_hamil'] as $f=>$d) {
    if (!empty($_FILES[$f]['name'])) { $up = uploadFile($_FILES[$f],$d); if ($up['success']) $fields[$f]=$up['filename']; }
}
$cols = implode(',', array_keys($fields)); $vals_arr = array();
foreach ($fields as $v) { $vals_arr[] = "'" . $v . "'"; }
$vals = implode(',', $vals_arr);
if (query("INSERT INTO ibu_hamil ($cols) VALUES ($vals)")) { jsonResponse(true, 'Data ibu hamil berhasil disimpan!'); }
else jsonResponse(false, 'Gagal menyimpan data');
