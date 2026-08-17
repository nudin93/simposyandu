<?php
require_once __DIR__ . '/../config/init.php';
requireLogin();
if (!isAdmin()) jsonResponse(false, 'Akses ditolak');
$fields = ['nama_aplikasi'=>escape($_POST['nama_aplikasi']??''),'nama_posyandu'=>escape($_POST['nama_posyandu']??''),
  'nama_desa'=>escape($_POST['nama_desa']??''),'kecamatan'=>escape($_POST['kecamatan']??''),
  'kabupaten'=>escape($_POST['kabupaten']??''),'provinsi'=>escape($_POST['provinsi']??''),
  'nomor_kontak'=>escape($_POST['nomor_kontak']??''),'email'=>escape($_POST['email']??''),
  'warna_tema'=>escape($_POST['warna_tema']??'blue'),'dark_mode'=>isset($_POST['dark_mode'])?1:0];
$upload_errors = [];
if (!empty($_FILES['logo']['name'])) {
    $up = uploadFile($_FILES['logo'], 'settings');
    if ($up['success']) $fields['logo'] = $up['filename'];
    else $upload_errors[] = 'Logo: ' . ($up['message'] ?? 'gagal upload');
}
if (!empty($_FILES['favicon']['name'])) {
    $up = uploadFile($_FILES['favicon'], 'settings');
    if ($up['success']) $fields['favicon'] = $up['filename'];
    else $upload_errors[] = 'Favicon: ' . ($up['message'] ?? 'gagal upload');
}
$sets = array_map(fn($k,$v)=>"$k='$v'", array_keys($fields), $fields);
if (numRows("SELECT id FROM pengaturan") > 0) {
    query("UPDATE pengaturan SET ".implode(',', $sets)." LIMIT 1");
} else {
    $cols = implode(',', array_keys($fields)); $vals = implode(',', array_map(fn($v)=>"'".$v."'", $fields));
    query("INSERT INTO pengaturan ($cols) VALUES ($vals)");
}
$msg = 'Pengaturan berhasil disimpan!';
if (!empty($upload_errors)) $msg .= ' Namun: ' . implode('; ', $upload_errors);
jsonResponse(true, $msg);
