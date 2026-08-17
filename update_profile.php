<?php
require_once __DIR__ . '/../config/init.php';
requireLogin();
$id = (int)($_POST['id'] ?? 0);
if ($id != $user['id'] && !isAdmin()) jsonResponse(false, 'Akses ditolak');
$nama = escape($_POST['nama'] ?? '');
$nik = escape($_POST['nik'] ?? '');
$no_hp = escape($_POST['no_hp'] ?? '');
$alamat = escape($_POST['alamat'] ?? '');
$sets = "nama='$nama',nik='$nik',no_hp='$no_hp',alamat='$alamat'";
if (!empty($_FILES['foto']['name'])) {
    $up = uploadFile($_FILES['foto'], 'kader');
    if ($up['success']) { $sets .= ",foto='{$up['filename']}'"; $_SESSION['kader_foto'] = $up['filename']; }
}
$old_pass = $_POST['old_password'] ?? ''; $new_pass = $_POST['new_password'] ?? ''; $conf = $_POST['confirm_password'] ?? '';
if ($old_pass && $new_pass) {
    $me = fetchOne("SELECT password FROM kader WHERE id=$id");
    if (!password_verify($old_pass, $me['password'])) jsonResponse(false, 'Password lama salah');
    if ($new_pass !== $conf) jsonResponse(false, 'Konfirmasi password tidak cocok');
    $hash = password_hash($new_pass, PASSWORD_BCRYPT);
    $sets .= ",password='$hash'";
}
if (query("UPDATE kader SET $sets WHERE id=$id")) { $_SESSION['kader_nama'] = $nama; jsonResponse(true, 'Profil berhasil diperbarui!'); }
else jsonResponse(false, 'Gagal memperbarui profil');
