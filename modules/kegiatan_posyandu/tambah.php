<?php
/**
 * POSYANDU DIGITAL DESA
 * Versi: 1.0.0
 * Dikembangkan oleh: Zainudin Larau
 *
 * Tujuan:
 * Aplikasi digital untuk membantu pendataan,
 * pelayanan, pemantauan, dan pelaporan kegiatan Posyandu Desa.
 */
/**
 * ==========================================================
 * SIMPOSYANDU - Sistem Informasi Posyandu Terintegrasi
 * ==========================================================
 *
 * Pengembang  : Zainudin Larau
 * Tahun       : 2026
 *
 * Deskripsi:
 * Aplikasi ini dikembangkan untuk membantu pengelolaan
 * data Posyandu secara digital, meliputi data balita,
 * ibu hamil, pemeriksaan kesehatan, pertumbuhan anak,
 * statistik, dan pelayanan masyarakat.
 *
 * Ketentuan:
 * Aplikasi ini merupakan karya pengembang dan
 * TIDAK DIPERJUALBELIKAN.
 *
 * Setiap penggunaan, penggandaan, perubahan kode,
 * atau pendistribusian aplikasi wajib mendapatkan
 * izin dari pemilik dan pengembang.
 *
 * Hak Cipta:
 * © 2026 Zainudin Larau
 * Semua Hak Dilindungi.
 *
 * ==========================================================
 */

$page_title='Catat Kegiatan Posyandu'; require_once __DIR__.'/../../includes/header.php';
$pengaturan=getPengaturan();
?>
<section class="content"><div class="container-fluid"><div class="card"><div class="card-header bg-success text-white">Form Kegiatan Posyandu — 5 Langkah</div><div class="card-body">
<form id="f" action="<?= APP_URL ?>/ajax/save_kegiatan_posyandu.php" method="post">

<input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
<div class="row g-3">
<div class="col-md-4"><label>Tanggal *</label><input type="date" name="tanggal_kegiatan" class="form-control" value="<?=date('Y-m-d')?>" required></div>
<div class="col-md-4"><label>Nama Posyandu *</label><input name="nama_posyandu" class="form-control" value="<?=htmlspecialchars($pengaturan['nama_posyandu']??'')?>" required></div>
<div class="col-md-4"><label>Lokasi</label><input name="lokasi" class="form-control"></div>
<div class="col-md-6"><label>Kader Bertugas</label><input name="kader_bertugas" class="form-control" value="<?=htmlspecialchars(currentUser()['nama']??'')?>"></div>
<div class="col-md-3"><label>Jumlah Sasaran</label><input type="number" name="jumlah_sasaran" class="form-control" value="0"></div>
<div class="col-md-3"><label>Jumlah Hadir</label><input type="number" name="jumlah_hadir" class="form-control" value="0"></div>
<div class="col-12"><hr><h6 class="text-success">5 Langkah Posyandu</h6></div>
<div class="col-md-6"><label>1. Pendaftaran</label><textarea name="langkah1_pendaftaran" class="form-control" rows="2" placeholder="Catatan pendaftaran peserta..."></textarea></div>
<div class="col-md-6"><label>2. Pengukuran</label><textarea name="langkah2_pengukuran" class="form-control" rows="2" placeholder="BB, TB, LILA, dll..."></textarea></div>
<div class="col-md-6"><label>3. Pencatatan</label><textarea name="langkah3_pencatatan" class="form-control" rows="2" placeholder="Pencatatan KMS / register..."></textarea></div>
<div class="col-md-6"><label>4. Pelayanan</label><textarea name="langkah4_pelayanan" class="form-control" rows="2" placeholder="Imunisasi, PMT, obat, dll..."></textarea></div>
<div class="col-md-12"><label>5. Penyuluhan</label><textarea name="langkah5_penyuluhan" class="form-control" rows="2" placeholder="Materi & peserta penyuluhan..."></textarea></div>
<div class="col-md-6"><label>Materi Penyuluhan</label><input name="materi_penyuluhan" class="form-control"></div>
<div class="col-md-3"><label>Status</label><select name="status" class="form-select"><option>Terjadwal</option><option>Berlangsung</option><option selected>Selesai</option><option>Dibatalkan</option></select></div>
<div class="col-md-12"><label>Keterangan</label><textarea name="keterangan" class="form-control" rows="2"></textarea></div>
</div>
<button class="btn btn-success mt-3"><i class="fas fa-save"></i> Simpan Kegiatan</button>
<a href="index.php" class="btn btn-secondary mt-3">Batal</a>
</form></div></div></div></section>
<script>f.onsubmit=e=>{e.preventDefault();ajaxSubmitForm(f,{redirect:'index.php'});};</script>
<?php include __DIR__.'/../../includes/footer.php'; ?>
