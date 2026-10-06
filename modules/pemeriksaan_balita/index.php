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

$page_title = 'Pemeriksaan Balita';
require_once __DIR__ . '/../../includes/header.php';
$data = fetchAll("SELECT pb.*, b.nama_lengkap, b.jenis_kelamin, b.tanggal_lahir FROM pemeriksaan_balita pb JOIN balita b ON pb.balita_id=b.id ORDER BY pb.tanggal_pemeriksaan DESC");
?>
<section class="content-header"><div class="container-fluid"><div class="row mb-2">
  <div class="col-sm-6"><h1><i class="fas fa-stethoscope me-2 text-success"></i>Pemeriksaan Balita</h1></div>
  <div class="col-sm-6"><ol class="breadcrumb float-sm-end"><li class="breadcrumb-item"><a href="../../dashboard.php">Dashboard</a></li><li class="breadcrumb-item active">Pemeriksaan Balita</li></ol></div>
</div></div></section>
<section class="content"><div class="container-fluid">
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
    <h5 class="mb-0"><i class="fas fa-list me-2"></i>Riwayat Pemeriksaan</h5>
    <a href="tambah.php" class="btn btn-success"><i class="fas fa-plus me-1"></i>Tambah Pemeriksaan</a>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-hover datatable">
        <thead><tr><th>No</th><th>Tanggal</th><th>Nama Balita</th><th>BB</th><th>TB</th><th>LK</th><th>Status Gizi</th><th>Stunting</th><th>Aksi</th></tr></thead>
        <tbody>
        <?php foreach ($data as $i => $p): ?>
        <tr>
          <td><?= $i+1 ?></td>
          <td><?= formatTanggal($p['tanggal_pemeriksaan']) ?></td>
          <td>
            <div class="fw-semibold"><?= htmlspecialchars($p['nama_lengkap']) ?></div>
            <small class="text-muted"><?= $p['jenis_kelamin']=='L'?'Laki-laki':'Perempuan' ?> | <?= hitungUmur($p['tanggal_lahir']) ?></small>
          </td>
          <td><?= $p['berat_badan'] ?> kg</td>
          <td><?= $p['tinggi_badan'] ?> cm</td>
          <td><?= $p['lingkar_kepala']??'-' ?> cm</td>
          <td><span class="badge bg-<?= $p['status_gizi']=='Normal'?'success':($p['status_gizi']=='Kurang'?'warning':'danger') ?>"><?= $p['status_gizi'] ?></span></td>
          <td><span class="badge bg-<?= $p['risiko_stunting']=='Tidak'?'success':($p['risiko_stunting']=='Risiko'?'warning':'danger') ?>"><?= $p['risiko_stunting'] ?></span></td>
          <td>
            <a href="detail.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>
            <button class="btn btn-sm btn-danger" onclick="confirmDelete('<?= APP_URL ?>/ajax/delete.php?type=pemeriksaan_balita&id=<?= $p['id'] ?>','Pemeriksaan <?= htmlspecialchars($p['nama_lengkap']) ?>')"><i class="fas fa-trash"></i></button>
          </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
</div></section>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
