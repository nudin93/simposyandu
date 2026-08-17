<?php
require_once __DIR__ . '/../config/init.php';
requireLogin();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$nik = escape($_POST['nik'] ?? '');
$no_kk = escape($_POST['no_kk'] ?? '');
$nama = escape($_POST['nama'] ?? '');
$jk = escape($_POST['jenis_kelamin'] ?? 'L');
$tempat = escape($_POST['tempat_lahir'] ?? '');
$tgl = escape($_POST['tanggal_lahir'] ?? '');
$status_kel = escape($_POST['status_dalam_keluarga'] ?? 'Anak');
$status_kawin = escape($_POST['status_perkawinan'] ?? 'Belum Kawin');
$pendidikan = escape($_POST['pendidikan'] ?? '');
$pekerjaan = escape($_POST['pekerjaan'] ?? '');
$hp = escape($_POST['no_hp'] ?? '');

if (!$nik || !$nama || !$no_kk) {
    echo json_encode(['success' => false, 'message' => 'NIK, Nama, dan No. KK wajib diisi']);
    exit;
}
if (strlen($nik) < 10) {
    echo json_encode(['success' => false, 'message' => 'NIK tidak valid']);
    exit;
}

$keluarga = fetchOne("SELECT * FROM keluarga WHERE no_kk='$no_kk'");
if (!$keluarga) {
    echo json_encode(['success' => false, 'message' => 'Keluarga (No. KK) tidak ditemukan']);
    exit;
}

$cek = fetchOne("SELECT id FROM penduduk WHERE nik='$nik'");
if ($cek) {
    // Update existing: pindahkan ke KK ini
    query("UPDATE penduduk SET no_kk='$no_kk', nama='$nama', jenis_kelamin='$jk',
      tempat_lahir='$tempat', tanggal_lahir=".($tgl?"'$tgl'":"NULL").",
      status_dalam_keluarga='$status_kel', status_perkawinan='$status_kawin',
      pendidikan='$pendidikan', pekerjaan='$pekerjaan', no_hp='$hp',
      dusun='".escape($keluarga['dusun']??'')."', rt='".escape($keluarga['rt']??'')."',
      rw='".escape($keluarga['rw']??'')."', alamat='".escape($keluarga['alamat']??'')."',
      status_aktif=1 WHERE nik='$nik'");
    $msg = 'Anggota diperbarui dan dihubungkan ke KK ini';
} else {
    query("INSERT INTO penduduk (nik, no_kk, nama, jenis_kelamin, tempat_lahir, tanggal_lahir,
      status_dalam_keluarga, status_perkawinan, pendidikan, pekerjaan, no_hp,
      dusun, rt, rw, alamat, status_aktif)
      VALUES ('$nik','$no_kk','$nama','$jk','$tempat',".($tgl?"'$tgl'":"NULL").",
      '$status_kel','$status_kawin','$pendidikan','$pekerjaan','$hp',
      '".escape($keluarga['dusun']??'')."','".escape($keluarga['rt']??'')."',
      '".escape($keluarga['rw']??'')."','".escape($keluarga['alamat']??'')."',1)");
    $msg = 'Anggota keluarga berhasil ditambahkan';
}

// Update kepala jika yang ditambah adalah Kepala Keluarga
if ($status_kel === 'Kepala Keluarga') {
    query("UPDATE keluarga SET nama_kepala='$nama', nik_kepala='$nik' WHERE no_kk='$no_kk'");
}

// Update jumlah anggota
query("UPDATE keluarga SET jumlah_anggota=(SELECT COUNT(*) FROM penduduk WHERE no_kk='$no_kk' AND status_aktif=1) WHERE no_kk='$no_kk'");

echo json_encode(['success' => true, 'message' => $msg]);
