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

$page_title = 'Data Vitamin';
require_once __DIR__ . '/../../includes/header.php';
$data = fetchAll("SELECT v.*, b.nama_lengkap, k.nama as petugas FROM vitamin v LEFT JOIN balita b ON v.balita_id=b.id LEFT JOIN kader k ON v.petugas_id=k.id ORDER BY v.tanggal_pemberian DESC");
?>
<section class="content-header"><div class="container-fluid"><div class="row mb-2">
  <div class="col-sm-6"><h1><i class="fas fa-pills me-2 text-success"></i>Data Vitamin</h1></div>
  <div class="col-sm-6"><ol class="breadcrumb float-sm-end"><li class="breadcrumb-item"><a href="../../dashboard.php">Dashboard</a></li><li class="breadcrumb-item active">Vitamin</li></ol></div>
</div></div></section>
<section class="content"><div class="container-fluid">
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="mb-0"><i class="fas fa-list me-2"></i>Riwayat Pemberian Vitamin</h5>
    <a href="tambah.php" class="btn btn-success"><i class="fas fa-plus me-1"></i>Tambah Vitamin</a>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-hover datatable">
        <thead><tr><th>No</th><th>Tanggal</th><th>Nama</th><th>Jenis Vitamin</th><th>Dosis</th><th>Petugas</th><th>Catatan</th><th>Aksi</th></tr></thead>
        <tbody>
        <?php foreach ($data as $i => $v): ?>
        <tr>
          <td><?= $i+1 ?></td><td><?= formatTanggal($v['tanggal_pemberian']) ?></td>
          <td><?= htmlspecialchars($v['nama_lengkap']??'-') ?></td>
          <td><span class="badge bg-success"><?= $v['jenis_vitamin'] ?></span></td>
          <td><?= $v['dosis']??'-' ?></td><td><?= htmlspecialchars($v['petugas']??'-') ?></td>
          <td><?= htmlspecialchars(substr($v['catatan']??'',0,50)) ?></td>
          <td><button class="btn btn-sm btn-danger" onclick="confirmDelete('<?= APP_URL ?>/ajax/delete.php?type=vitamin&id=<?= $v['id'] ?>','Data vitamin ini')"><i class="fas fa-trash"></i></button></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
</div></section>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
