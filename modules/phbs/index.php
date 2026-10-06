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

$page_title = 'Pemantauan PHBS';
require_once __DIR__ . '/../../includes/header.php';
$__chk = @query("SHOW TABLES LIKE 'phbs'");
if (!$__chk || $__chk->num_rows == 0) {
    echo '<section class="content"><div class="container-fluid"><div class="alert alert-warning mt-3">';
    echo 'Tabel <b>phbs</b> belum ada. Jalankan file <code>database/migrasi_tabel_baru.sql</code> di phpMyAdmin.';
    echo ' <a href="' . APP_URL . '/modules/pengaturan/index.php" class="alert-link">Kembali</a></div></div></section>';
    include __DIR__ . '/../../includes/footer.php';
    exit;
}

$data = fetchAll("SELECT * FROM phbs ORDER BY dusun, nama_kepala");
?>
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6"><h1><i class="fas fa-hands-wash me-2 text-primary"></i>Pemantauan PHBS</h1></div>
      <div class="col-sm-6"><ol class="breadcrumb float-sm-end"><li class="breadcrumb-item"><a href="<?= APP_URL ?>/dashboard.php">Dashboard</a></li><li class="breadcrumb-item active">PHBS</li></ol></div>
    </div>
  </div>
</section>
<section class="content"><div class="container-fluid">
  <div class="card">
    <div class="card-header d-flex justify-content-between">
      <h5 class="mb-0"><i class="fas fa-list me-2"></i>Daftar PHBS per Keluarga</h5>
      <a href="tambah.php" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>Tambah / Update</a>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-hover datatable table-sm">
          <thead><tr>
            <th>No</th><th>No. KK</th><th>Kepala</th><th>Dusun</th><th>Cuci Tangan</th><th>Jamban</th><th>Sampah</th><th>Asap Rokok</th><th>Skor</th><th>Aksi</th>
          </tr></thead>
          <tbody>
          <?php foreach ($data as $i => $p): ?>
            <tr>
              <td><?= $i+1 ?></td>
              <td><code><?= htmlspecialchars($p['no_kk']) ?></code></td>
              <td><?= htmlspecialchars($p['nama_kepala']??'-') ?></td>
              <td><?= htmlspecialchars($p['dusun']??'-') ?></td>
              <td><?= $p['cuci_tangan'] ?></td>
              <td><?= $p['menggunakan_jamban'] ?></td>
              <td><?= $p['pengelolaan_sampah'] ?></td>
              <td><?= $p['bebas_asap_rokok'] ?></td>
              <td><span class="badge bg-<?= $p['skor_phbs']>=6?'success':($p['skor_phbs']>=4?'warning':'danger') ?>"><?= $p['skor_phbs'] ?>/8</span></td>
              <td>
                <a href="tambah.php?no_kk=<?= urlencode($p['no_kk']) ?>" class="btn btn-xs btn-warning" title="Edit"><i class="fas fa-edit"></i></a>
                <button type="button" class="btn btn-xs btn-danger" onclick="confirmDelete('<?= APP_URL ?>/ajax/delete.php?type=phbs&id=<?= $p['id'] ?>','PHBS <?= htmlspecialchars($p['nama_kepala']??$p['no_kk'], ENT_QUOTES) ?>')" title="Hapus"><i class="fas fa-trash"></i></button>
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
