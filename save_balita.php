<?php
error_reporting(0);
ini_set("display_errors","0");
require_once __DIR__ . '/../config/init.php';
requireLogin();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonResponse(false, 'Method not allowed');
$nomor = escape($_POST['nomor_peserta'] ?? '');
$nama = escape($_POST['nama_lengkap'] ?? '');
$jk = escape($_POST['jenis_kelamin'] ?? '');
$tgl = escape($_POST['tanggal_lahir'] ?? '');
if (!$nama || !$jk || !$tgl) jsonResponse(false, 'Field wajib belum diisi');
if (numRows("SELECT id FROM balita WHERE nomor_peserta='$nomor'") > 0) jsonResponse(false, 'Nomor peserta sudah digunakan');

$id_opensid = (int)($_POST['id_penduduk_opensid'] ?? 0);
$nik_anak = trim($_POST['nik_anak'] ?? '');
$status_integrasi = 'belum';

// Jika ada NIK, coba tautkan ke OpenSID
if ($id_opensid > 0) {
    $status_integrasi = 'terhubung';
} elseif ($nik_anak !== '' && opensid_available()) {
    $pend = getPendudukOpenSIDByNik($nik_anak);
    if ($pend) {
        $id_opensid = (int)$pend['id_penduduk'];
        $status_integrasi = 'terhubung';
    } else {
        $status_integrasi = 'manual'; // NIK diisi manual, belum ada di OpenSID
    }
}

$fields = [
  'nomor_peserta'=>$nomor,
  'id_penduduk_opensid'=>$id_opensid > 0 ? $id_opensid : 'NULL',
  'status_integrasi'=>$status_integrasi,
  'nik_anak'=>escape($nik_anak),
  'no_kk'=>escape($_POST['no_kk']??''),
  'nama_lengkap'=>$nama,
  'jenis_kelamin'=>$jk,
  'tempat_lahir'=>escape($_POST['tempat_lahir']??''),
  'tanggal_lahir'=>$tgl,
  'anak_ke'=>(int)($_POST['anak_ke']??1),
  'status_anak'=>escape($_POST['status_anak']??'kandung'),
  'golongan_darah'=>escape($_POST['golongan_darah']??'Tidak Tahu'),
  'berat_lahir'=>(float)($_POST['berat_lahir']??0),
  'tinggi_lahir'=>(float)($_POST['tinggi_lahir']??0),
  'nama_ayah'=>escape($_POST['nama_ayah']??''),
  'nik_ayah'=>escape($_POST['nik_ayah']??''),
  'pekerjaan_ayah'=>escape($_POST['pekerjaan_ayah']??''),
  'nama_ibu'=>escape($_POST['nama_ibu']??''),
  'nik_ibu'=>escape($_POST['nik_ibu']??''),
  'pekerjaan_ibu'=>escape($_POST['pekerjaan_ibu']??''),
  'no_hp'=>escape($_POST['no_hp']??''),
  'dusun'=>escape($_POST['dusun']??''),
  'rt'=>escape($_POST['rt']??''),
  'rw'=>escape($_POST['rw']??''),
  'desa'=>escape($_POST['desa']??''),
  'kecamatan'=>escape($_POST['kecamatan']??''),
  'kabupaten'=>escape($_POST['kabupaten']??''),
  'provinsi'=>escape($_POST['provinsi']??''),
  'alamat_lengkap'=>escape($_POST['alamat_lengkap']??''),
  'status_asi'=>escape($_POST['status_asi']??''),
  'riwayat_alergi'=>escape($_POST['riwayat_alergi']??''),
  'riwayat_penyakit'=>escape($_POST['riwayat_penyakit']??''),
  'status_imunisasi'=>escape($_POST['status_imunisasi']??'Dalam Proses'),
  'bpjs_kis'=>escape($_POST['bpjs_kis']??''),
  'status_aktif'=>isset($_POST['status_aktif'])?1:1
];

$uploads = ['foto_anak'=>'balita','foto_kk'=>'balita','foto_bpjs'=>'balita'];
foreach ($uploads as $field => $folder) {
    if (!empty($_FILES[$field]['name'])) {
        $up = uploadFile($_FILES[$field], $folder);
        if ($up['success']) $fields[$field] = $up['filename'];
    }
}

$cols = [];
$vals = [];
foreach ($fields as $k => $v) {
    $cols[] = $k;
    if ($v === 'NULL') {
        $vals[] = 'NULL';
    } else {
        $vals[] = "'" . $v . "'";
    }
}
$sql = "INSERT INTO balita (" . implode(',', $cols) . ") VALUES (" . implode(',', $vals) . ")";
if (query($sql)) {
    $newId = lastInsertId();
    if ($id_opensid > 0) {
        @query("INSERT INTO log_integrasi_opensid (modul, referensi_id, id_penduduk_opensid, nik, aksi, keterangan, user_id)
            VALUES ('balita', $newId, $id_opensid, '" . escape($nik_anak) . "', 'hubungkan', 'Saat pendaftaran balita', " . (int)($_SESSION['user_id'] ?? 0) . ")");
    }
    jsonResponse(true, 'Data balita berhasil disimpan!' . ($status_integrasi === 'terhubung' ? ' (Terhubung OpenSID)' : ''), ['id' => $newId]);
} else {
    jsonResponse(false, 'Gagal menyimpan data. Pastikan migrasi integrasi OpenSID sudah dijalankan.');
}
