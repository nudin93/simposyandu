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

$page_title='Catat Kunjungan Rumah'; require_once __DIR__.'/../../includes/header.php';
?>
<section class="content"><div class="container-fluid"><div class="card"><div class="card-header bg-danger text-white">Form Kunjungan Rumah</div><div class="card-body">
<form id="f" action="<?= APP_URL ?>/ajax/save_kunjungan_rumah.php" method="post">

<input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
<div class="row g-3">
<div class="col-md-4"><label>Tanggal *</label><input type="date" name="tanggal_kunjungan" class="form-control" value="<?=date('Y-m-d')?>" required></div>
<div class="col-md-4"><label>No KK</label><input name="no_kk" class="form-control" maxlength="16"></div>
<div class="col-md-4"><label>Prioritas *</label>
<select name="prioritas" class="form-select" required>
<option>Balita Stunting</option><option>Ibu Hamil Risiko Tinggi</option><option>Lansia Sakit</option>
<option>Tidak Hadir Posyandu</option><option>Lainnya</option>
</select></div>
<div class="col-md-6"><label>Nama Keluarga / Kepala *</label><input name="nama_keluarga" class="form-control" required></div>
<div class="col-md-6"><label>Alamat</label><input name="alamat" class="form-control"></div>
<div class="col-md-4"><label>Dusun</label><input name="dusun" class="form-control"></div>
<div class="col-md-2"><label>RT</label><input name="rt" class="form-control"></div>
<div class="col-md-2"><label>RW</label><input name="rw" class="form-control"></div>
<div class="col-md-4"><label>Status Tindak Lanjut</label>
<select name="status_tindak_lanjut" class="form-select"><option>Belum</option><option>Proses</option><option>Selesai</option></select></div>
<div class="col-md-12"><label>Masalah Ditemukan *</label><textarea name="masalah_ditemukan" class="form-control" rows="2" required></textarea></div>
<div class="col-md-6"><label>Tindakan</label><textarea name="tindakan" class="form-control" rows="2"></textarea></div>
<div class="col-md-6"><label>Rujukan</label><textarea name="rujukan" class="form-control" rows="2"></textarea></div>
<div class="col-md-12"><label>Catatan</label><textarea name="catatan" class="form-control" rows="2"></textarea></div>
</div>
<button class="btn btn-danger mt-3"><i class="fas fa-save"></i> Simpan</button>
<a href="index.php" class="btn btn-secondary mt-3">Batal</a>
</form></div></div></div></section>
<script>f.onsubmit=e=>{e.preventDefault();ajaxSubmitForm(f,{redirect:'index.php'});};</script>
<?php include __DIR__.'/../../includes/footer.php'; ?>
