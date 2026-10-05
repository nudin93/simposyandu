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
$data = [];
try {
  $data = fetchAll("SELECT pb.*, b.nama_lengkap, b.nomor_peserta FROM pemeriksaan_bayi pb
    JOIN bayi b ON b.id=pb.bayi_id ORDER BY pb.tanggal_pemeriksaan DESC LIMIT 500");
} catch(Throwable $e){}
$canDel = function_exists('canInput') ? canInput() : true;
?>
<section class="content-header"><div class="container-fluid"><div class="row mb-2">
  <div class="col-sm-6"><h1><i class="fas fa-stethoscope me-2 text-info"></i>Pemeriksaan Bayi</h1></div>
  <div class="col-sm-6"><ol class="breadcrumb float-sm-end"><li class="breadcrumb-item"><a href="<?= APP_URL ?>/dashboard.php">Dashboard</a></li><li class="breadcrumb-item active">Pemeriksaan Bayi</li></ol></div>
</div></div></section>
<section class="content"><div class="container-fluid">
<div class="card">
  <div class="card-header d-flex justify-content-between"><h5 class="mb-0">Riwayat Pemeriksaan</h5>
    <a href="<?= APP_URL ?>/modules/bayi/index.php" class="btn btn-info btn-sm"><i class="fas fa-baby"></i> Pilih Bayi</a>
  </div>
  <div class="card-body table-responsive">
    <table class="table table-hover datatable" style="width:100%">
      <thead><tr><th>No</th><th>Tanggal</th><th>Bayi</th><th>Umur</th><th>BB</th><th>PB</th><th>Gizi</th><th>Stunting</th><th>Aksi</th></tr></thead>
      <tbody>
    <?php foreach ($data as $i=>$r): ?>
    <tr>
      <td><?= $i+1 ?></td>
      <td><?= formatTanggal($r['tanggal_pemeriksaan']) ?></td>
      <td><?= htmlspecialchars($r['nama_lengkap']) ?></td>
      <td><?= $r['umur_bulan'] ?> bln</td>
      <td><?= $r['berat_badan'] ?></td>
      <td><?= $r['panjang_badan'] ?></td>
      <td><?= $r['status_gizi'] ?></td>
      <td><?= $r['risiko_stunting'] ?></td>
      <td>
        <?php if ($canDel): ?>
        <button type="button" class="btn btn-sm btn-danger" onclick="confirmDelete('<?= APP_URL ?>/ajax/delete.php?type=pemeriksaan_bayi&id=<?= (int)$r['id'] ?>','Pemeriksaan <?= htmlspecialchars($r['nama_lengkap'], ENT_QUOTES) ?>')" title="Hapus"><i class="fas fa-trash"></i></button>
        <?php endif; ?>
      </td>
    </tr>
    <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
</div></section>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
