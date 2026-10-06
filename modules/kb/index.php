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

$page_title = 'Keluarga Berencana';
require_once __DIR__ . '/../../includes/header.php';

$__chk = @query("SHOW TABLES LIKE 'kb'");
if (!$__chk || $__chk->num_rows == 0) {
    echo '<section class="content"><div class="container-fluid"><div class="alert alert-warning mt-3">';
    echo 'Tabel <b>kb</b> belum ada. Jalankan file <code>database/migrasi_tabel_baru.sql</code> di phpMyAdmin.';
    echo ' <a href="' . APP_URL . '/modules/pengaturan/index.php" class="alert-link">Kembali</a></div></div></section>';
    include __DIR__ . '/../../includes/footer.php';
    exit;
}

$data = fetchAll("SELECT * FROM kb ORDER BY tanggal_pelayanan DESC");
$rekap = fetchAll("SELECT jenis_kontrasepsi, COUNT(*) as jml FROM kb WHERE status='Aktif' GROUP BY jenis_kontrasepsi");
?>
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6"><h1><i class="fas fa-pills me-2 text-primary"></i>Keluarga Berencana (KB)</h1></div>
      <div class="col-sm-6"><ol class="breadcrumb float-sm-end"><li class="breadcrumb-item"><a href="<?= APP_URL ?>/dashboard.php">Dashboard</a></li><li class="breadcrumb-item active">KB</li></ol></div>
    </div>
  </div>
</section>
<section class="content"><div class="container-fluid">
<?php if (!empty($_GET['msg']) && $_GET['msg'] === 'success'): ?>
  <div class="alert alert-success">Data berhasil disimpan.</div>
<?php endif; ?>
  <div class="row mb-3">
    <?php foreach ($rekap as $r): ?>
    <div class="col-6 col-md-2 mb-2">
      <div class="small-box bg-info"><div class="inner"><h4><?= (int)$r['jml'] ?></h4><p class="mb-0 small"><?= htmlspecialchars($r['jenis_kontrasepsi']) ?></p></div></div>
    </div>
    <?php endforeach; ?>
  </div>
  <div class="card">
    <div class="card-header d-flex justify-content-between">
      <h5 class="mb-0"><i class="fas fa-list me-2"></i>Daftar Peserta KB</h5>
      <a href="tambah.php" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>Tambah</a>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-hover datatable">
          <thead><tr><th>No</th><th>Nama</th><th>NIK</th><th>Jenis KB</th><th>Tgl Pelayanan</th><th>Kontrol</th><th>Status</th><th>Aksi</th></tr></thead>
          <tbody>
          <?php foreach ($data as $i => $d): ?>
            <tr>
              <td><?= $i+1 ?></td>
              <td><?= htmlspecialchars($d['nama']) ?></td>
              <td><code><?= htmlspecialchars($d['nik'] ?? '-') ?></code></td>
              <td><span class="badge bg-primary"><?= htmlspecialchars($d['jenis_kontrasepsi']) ?></span></td>
              <td><?= !empty($d['tanggal_pelayanan']) ? formatTanggal($d['tanggal_pelayanan']) : '-' ?></td>
              <td><?= !empty($d['tanggal_kontrol']) ? formatTanggal($d['tanggal_kontrol']) : '-' ?></td>
              <td><?= ($d['status'] ?? '') == 'Aktif' ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-secondary">'.htmlspecialchars($d['status'] ?? '').'</span>' ?></td>
              <td>
                <button type="button" class="btn btn-xs btn-danger" onclick="confirmDelete('<?= APP_URL ?>/ajax/delete.php?type=kb&id=<?= (int)$d['id'] ?>','<?= htmlspecialchars($d['nama'], ENT_QUOTES) ?>')" title="Hapus"><i class="fas fa-trash"></i></button>
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
