<?php
require_once __DIR__ . '/../config/init.php';
requireLogin();
if (!isAdmin()) jsonResponse(false, 'Akses ditolak');
$nama = escape($_POST['nama'] ?? '');
$username = escape($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
if (!$nama || !$username || !$password) jsonResponse(false, 'Field wajib belum diisi');
if (numRows("SELECT id FROM kader WHERE username='$username'") > 0) jsonResponse(false, 'Username sudah digunakan');
$hash = password_hash($password, PASSWORD_BCRYPT);
$foto = '';
if (!empty($_FILES['foto']['name'])) { $up = uploadFile($_FILES['foto'],'kader'); if ($up['success']) $foto = $up['filename']; }
$sql = "INSERT INTO kader (nama,nik,jenis_kelamin,no_hp,jabatan,alamat,username,password,role,foto,status)
VALUES ('$nama','".escape($_POST['nik']??'')."','".escape($_POST['jenis_kelamin']??'P')."','".escape($_POST['no_hp']??'')."','".escape($_POST['jabatan']??'Kader')."','".escape($_POST['alamat']??'')."','$username','$hash','".escape($_POST['role']??'kader')."','$foto',1)";
if (query($sql)) jsonResponse(true, 'Data kader berhasil disimpan!');
else jsonResponse(false, 'Gagal menyimpan data');
