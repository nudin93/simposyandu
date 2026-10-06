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

$page_title = 'Pemeriksaan Lansia';
require_once __DIR__ . '/../../includes/header.php';
$data = fetchAll("SELECT pl.*, l.nama, l.jenis_kelamin FROM pemeriksaan_lansia pl JOIN lansia l ON pl.lansia_id=l.id ORDER BY pl.tanggal_pemeriksaan DESC");
?>
<section class="content-header"><div class="container-fluid"><div class="row mb-2">
  <div class="col-sm-6"><h1><i class="fas fa-user-md me-2 text-warning"></i>Pemeriksaan Lansia</h1></div>
  <div class="col-sm-6"><ol class="breadcrumb float-sm-end"><li class="breadcrumb-item"><a href="../../dashboard.php">Dashboard</a></li><li class="breadcrumb-item active">Pemeriksaan Lansia</li></ol></div>
</div></div></section>
<section class="content"><div class="container-fluid">
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="mb-0"><i class="fas fa-list me-2"></i>Riwayat Pemeriksaan</h5>
    <a href="tambah.php" class="btn btn-warning"><i class="fas fa-plus me-1"></i>Tambah Pemeriksaan</a>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-hover datatable">
        <thead><tr><th>No</th><th>Tanggal</th><th>Nama Lansia</th><th>Tek. Darah</th><th>Gula Darah</th><th>Kolesterol</th><th>Risiko</th><th>Aksi</th></tr></thead>
        <tbody>
        <?php foreach ($data as $i => $p): ?>
        <tr>
          <td><?= $i+1 ?></td>
          <td><?= formatTanggal($p['tanggal_pemeriksaan']) ?></td>
          <td><?= htmlspecialchars($p['nama']) ?></td>
          <td><?= $p['tekanan_darah']??'-' ?></td>
          <td><?= $p['gula_darah'] ? $p['gula_darah'].' mg/dL' : '-' ?></td>
          <td><?= $p['kolesterol'] ? $p['kolesterol'].' mg/dL' : '-' ?></td>
          <td>
            <?php if ($p['risiko_hipertensi']) echo '<span class="badge bg-danger me-1">Hipertensi</span>'; ?>
            <?php if ($p['risiko_diabetes']) echo '<span class="badge bg-warning text-dark me-1">DM</span>'; ?>
            <?php if ($p['risiko_kolesterol']) echo '<span class="badge bg-info text-dark me-1">Kolesterol</span>'; ?>
            <?php if (!$p['risiko_hipertensi']&&!$p['risiko_diabetes']&&!$p['risiko_kolesterol']) echo '<span class="badge bg-success">Normal</span>'; ?>
          </td>
          <td><button class="btn btn-sm btn-danger" onclick="confirmDelete('<?= APP_URL ?>/ajax/delete.php?type=pemeriksaan_lansia&id=<?= $p['id'] ?>','Pemeriksaan ini')"><i class="fas fa-trash"></i></button></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
</div></section>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
