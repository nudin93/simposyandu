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

$page_title = 'Data Imunisasi';
require_once __DIR__ . '/../../includes/header.php';
$data = fetchAll("SELECT i.*, b.nama_lengkap, k.nama as petugas FROM imunisasi i JOIN balita b ON i.balita_id=b.id LEFT JOIN kader k ON i.petugas_id=k.id ORDER BY i.tanggal_imunisasi DESC");
?>
<section class="content-header"><div class="container-fluid"><div class="row mb-2">
  <div class="col-sm-6"><h1><i class="fas fa-syringe me-2 text-info"></i>Data Imunisasi</h1></div>
  <div class="col-sm-6"><ol class="breadcrumb float-sm-end"><li class="breadcrumb-item"><a href="../../dashboard.php">Dashboard</a></li><li class="breadcrumb-item active">Imunisasi</li></ol></div>
</div></div></section>
<section class="content"><div class="container-fluid">
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="mb-0"><i class="fas fa-list me-2"></i>Riwayat Imunisasi</h5>
    <a href="tambah.php" class="btn btn-info"><i class="fas fa-plus me-1"></i>Tambah Imunisasi</a>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-hover datatable">
        <thead><tr><th>No</th><th>Tanggal</th><th>Nama Balita</th><th>Jenis Imunisasi</th><th>Dosis</th><th>Efek Samping</th><th>Petugas</th><th>Aksi</th></tr></thead>
        <tbody>
        <?php foreach ($data as $i => $imun): ?>
        <tr>
          <td><?= $i+1 ?></td><td><?= formatTanggal($imun['tanggal_imunisasi']) ?></td>
          <td><?= htmlspecialchars($imun['nama_lengkap']) ?></td>
          <td><span class="badge bg-info"><?= $imun['jenis_imunisasi'] ?></span></td>
          <td><?= $imun['dosis']??'-' ?></td><td><?= htmlspecialchars($imun['efek_samping']??'-') ?></td><td><?= htmlspecialchars($imun['petugas']??'-') ?></td>
          <td><button class="btn btn-sm btn-danger" onclick="confirmDelete('<?= APP_URL ?>/ajax/delete.php?type=imunisasi&id=<?= $imun['id'] ?>','Imunisasi ini')"><i class="fas fa-trash"></i></button></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
</div></section>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
