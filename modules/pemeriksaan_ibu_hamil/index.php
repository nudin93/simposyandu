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

$page_title = 'Pemeriksaan Ibu Hamil';
require_once __DIR__ . '/../../includes/header.php';
$data = fetchAll("SELECT pih.*, ih.nama, k.nama as petugas FROM pemeriksaan_ibu_hamil pih JOIN ibu_hamil ih ON pih.ibu_hamil_id=ih.id LEFT JOIN kader k ON pih.petugas_id=k.id ORDER BY pih.tanggal_pemeriksaan DESC");
?>
<section class="content-header"><div class="container-fluid"><div class="row mb-2">
  <div class="col-sm-6"><h1><i class="fas fa-heartbeat me-2 text-danger"></i>Pemeriksaan Ibu Hamil (ANC)</h1></div>
  <div class="col-sm-6"><ol class="breadcrumb float-sm-end"><li class="breadcrumb-item"><a href="../../dashboard.php">Dashboard</a></li><li class="breadcrumb-item active">ANC</li></ol></div>
</div></div></section>
<section class="content"><div class="container-fluid">
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="mb-0"><i class="fas fa-list me-2"></i>Riwayat Pemeriksaan ANC</h5>
    <a href="tambah.php" class="btn btn-danger"><i class="fas fa-plus me-1"></i>Tambah ANC</a>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-hover datatable">
        <thead><tr><th>No</th><th>Tanggal</th><th>Nama Ibu</th><th>BB</th><th>Tek. Darah</th><th>DJJ</th><th>Usia Kandungan</th><th>Status Risiko</th><th>Petugas</th><th>Aksi</th></tr></thead>
        <tbody>
        <?php foreach ($data as $i => $p): ?>
        <tr>
          <td><?= $i+1 ?></td><td><?= formatTanggal($p['tanggal_pemeriksaan']) ?></td>
          <td class="fw-semibold"><?= htmlspecialchars($p['nama']) ?></td>
          <td><?= $p['berat_badan'] ?> kg</td>
          <td><?= $p['tekanan_darah']??'-' ?></td>
          <td><?= $p['djj'] ? $p['djj'].' bpm' : '-' ?></td>
          <td><?= $p['usia_kandungan'] ? $p['usia_kandungan'].' minggu' : '-' ?></td>
          <td><span class="badge bg-<?= $p['status_risiko']=='Normal'?'success':($p['status_risiko']=='Risiko Ringan'?'warning':'danger') ?>"><?= $p['status_risiko'] ?></span></td>
          <td><?= htmlspecialchars($p['petugas']??'-') ?></td>
          <td><button class="btn btn-sm btn-danger" onclick="confirmDelete('<?= APP_URL ?>/ajax/delete.php?type=pemeriksaan_ibu_hamil&id=<?= $p['id'] ?>','ANC ini')"><i class="fas fa-trash"></i></button></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
</div></section>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
