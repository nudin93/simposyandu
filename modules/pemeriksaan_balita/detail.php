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

$page_title = 'Detail Pemeriksaan';
require_once __DIR__ . '/../../includes/header.php';
$id = (int)($_GET['id'] ?? 0);
$p = fetchOne("SELECT pb.*, b.nama_lengkap, b.tanggal_lahir FROM pemeriksaan_balita pb JOIN balita b ON pb.balita_id=b.id WHERE pb.id=$id");
if (!$p) { echo '<script>window.location="index.php";</script>'; exit; }
?>
<section class="content-header"><div class="container-fluid"><div class="row mb-2">
  <div class="col-sm-6"><h1><i class="fas fa-stethoscope me-2"></i>Detail Pemeriksaan</h1></div>
  <div class="col-sm-6"><ol class="breadcrumb float-sm-end"><li class="breadcrumb-item"><a href="../../dashboard.php">Dashboard</a></li><li class="breadcrumb-item"><a href="index.php">Pemeriksaan</a></li><li class="breadcrumb-item active">Detail</li></ol></div>
</div></div></section>
<section class="content"><div class="container-fluid">
<div class="card">
  <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
    <h5 class="mb-0"><i class="fas fa-notes-medical me-2"></i>Hasil Pemeriksaan</h5>
    <span class="badge bg-light text-dark"><?= formatTanggal($p['tanggal_pemeriksaan']) ?></span>
  </div>
  <div class="card-body">
    <div class="row">
      <div class="col-md-6">
        <h6 class="text-primary fw-bold border-bottom pb-2">Data Anak</h6>
        <table class="table table-borderless table-sm"><tbody>
          <tr><th width="45%">Nama</th><td><?= htmlspecialchars($p['nama_lengkap']) ?></td></tr>
          <tr><th>Umur saat periksa</th><td><?= $p['umur_saat_periksa'] ?> bulan</td></tr>
          <tr><th>Jadwal Kontrol</th><td><?= formatTanggal($p['jadwal_kontrol']) ?></td></tr>
        </tbody></table>
        <h6 class="text-primary fw-bold border-bottom pb-2 mt-3">Antropometri</h6>
        <table class="table table-borderless table-sm"><tbody>
          <tr><th width="45%">Berat Badan</th><td><?= $p['berat_badan'] ?> kg</td></tr>
          <tr><th>Tinggi Badan</th><td><?= $p['tinggi_badan'] ?> cm</td></tr>
          <tr><th>Lingkar Kepala</th><td><?= $p['lingkar_kepala']??'-' ?> cm</td></tr>
          <tr><th>Lingkar Lengan</th><td><?= $p['lingkar_lengan']??'-' ?> cm</td></tr>
          <tr><th>Suhu Tubuh</th><td><?= $p['suhu_tubuh']??'-' ?> °C</td></tr>
          <tr><th>Denyut Nadi</th><td><?= $p['denyut_nadi']??'-' ?> bpm</td></tr>
          <tr><th>IMT</th><td><?= $p['imt'] ?></td></tr>
        </tbody></table>
      </div>
      <div class="col-md-6">
        <h6 class="text-primary fw-bold border-bottom pb-2">Hasil Analisa</h6>
        <div class="row g-3 mb-3">
          <div class="col-6"><div class="text-center p-3 rounded bg-<?= $p['status_gizi']=='Normal'?'success':($p['status_gizi']=='Kurang'?'warning':'danger') ?> text-white"><div class="fw-bold">Status Gizi</div><div class="h5 mb-0"><?= $p['status_gizi'] ?></div></div></div>
          <div class="col-6"><div class="text-center p-3 rounded bg-<?= $p['risiko_stunting']=='Tidak'?'success':($p['risiko_stunting']=='Risiko'?'warning':'danger') ?> text-white"><div class="fw-bold">Stunting</div><div class="h5 mb-0"><?= $p['risiko_stunting'] ?></div></div></div>
        </div>
        <h6 class="text-primary fw-bold border-bottom pb-2">Catatan</h6>
        <table class="table table-borderless table-sm"><tbody>
          <tr><th width="45%">Keluhan</th><td><?= htmlspecialchars($p['keluhan']??'-') ?></td></tr>
          <tr><th>Penanganan</th><td><?= htmlspecialchars($p['penanganan']??'-') ?></td></tr>
          <tr><th>Imunisasi</th><td><?= htmlspecialchars($p['imunisasi_diberikan']??'-') ?></td></tr>
          <tr><th>Vitamin</th><td><?= htmlspecialchars($p['vitamin_diberikan']??'-') ?></td></tr>
          <tr><th>Catatan Kader</th><td><?= htmlspecialchars($p['catatan_kader']??'-') ?></td></tr>
        </tbody></table>
      </div>
    </div>
  </div>
  <div class="card-footer"><a href="index.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i>Kembali</a></div>
</div>
</div></section>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
