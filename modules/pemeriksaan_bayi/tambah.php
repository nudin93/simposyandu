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

$page_title = 'Pemeriksaan Bayi';
require_once __DIR__ . '/../../includes/header.php';
$bayi_id = (int)($_GET['bayi_id'] ?? 0);
$bayi = $bayi_id ? fetchOne("SELECT *, TIMESTAMPDIFF(MONTH, tanggal_lahir, CURDATE()) as umur_bulan FROM bayi WHERE id=$bayi_id") : null;
if (!$bayi) { echo '<div class="alert alert-warning m-3">Pilih bayi dari daftar terlebih dahulu.</div><a class="btn btn-info m-3" href="../bayi/index.php">Ke Data Bayi</a>'; include __DIR__.'/../../includes/footer.php'; exit; }
?>
<section class="content-header"><div class="container-fluid"><div class="row mb-2">
  <div class="col-sm-6"><h1>Pemeriksaan: <?= htmlspecialchars($bayi['nama_lengkap']) ?></h1></div>
</div></div></section>
<section class="content"><div class="container-fluid">
<div class="card"><div class="card-body">
<form id="formPeriksa" method="post" action="<?= APP_URL ?>/ajax/save_pemeriksaan_bayi.php">

<input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
  <input type="hidden" name="bayi_id" value="<?= $bayi_id ?>">
  <div class="row g-3">
    <div class="col-md-3"><label class="form-label">Tanggal <span class="text-danger">*</span></label><input type="date" name="tanggal_pemeriksaan" class="form-control" value="<?= date('Y-m-d') ?>" required></div>
    <div class="col-md-3"><label class="form-label">Umur (bulan)</label><input type="number" name="umur_bulan" class="form-control" value="<?= (int)$bayi['umur_bulan'] ?>"></div>
    <div class="col-md-3"><label class="form-label">Berat Badan (kg)</label><input type="number" step="0.01" name="berat_badan" class="form-control"></div>
    <div class="col-md-3"><label class="form-label">Panjang Badan (cm)</label><input type="number" step="0.1" name="panjang_badan" class="form-control"></div>
    <div class="col-md-3"><label class="form-label">Lingkar Kepala</label><input type="number" step="0.1" name="lingkar_kepala" class="form-control"></div>
    <div class="col-md-3"><label class="form-label">LILA</label><input type="number" step="0.1" name="lila" class="form-control"></div>
    <div class="col-md-3"><label class="form-label">ASI Eksklusif</label>
      <select name="asi_eksklusif" class="form-select"><option value="Ya">Ya</option><option value="Tidak">Tidak</option><option value="Sebagian">Sebagian</option></select></div>
    <div class="col-md-6"><label class="form-label">Imunisasi diberikan</label><input type="text" name="imunisasi_diberikan" class="form-control"></div>
    <div class="col-md-6"><label class="form-label">Vitamin</label><input type="text" name="vitamin_diberikan" class="form-control"></div>
    <div class="col-md-6"><label class="form-label">Keluhan</label><textarea name="keluhan" class="form-control" rows="2"></textarea></div>
    <div class="col-md-6"><label class="form-label">Penanganan / Catatan</label><textarea name="catatan" class="form-control" rows="2"></textarea></div>
  </div>
  <div class="mt-3"><button type="submit" class="btn btn-info"><i class="fas fa-save"></i> Simpan</button>
  <a href="../bayi/detail.php?id=<?= $bayi_id ?>" class="btn btn-secondary">Kembali</a></div>
</form>
</div></div>
</div></section>
<script>
document.getElementById('formPeriksa').addEventListener('submit', function(e){
  e.preventDefault();
  ajaxSubmitForm(this, { redirect: '../bayi/detail.php?id=<?= $bayi_id ?>' });
});
</script>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
