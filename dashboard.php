<?php
$page_title = 'Dashboard';
require_once __DIR__ . '/includes/header.php';

$total_keluarga = 0;
$total_penduduk = 0;
$opensidOk = opensid_available();
if ($opensidOk) {
    $r = opensid_fetchOne("
        SELECT COUNT(*) AS c
        FROM tweb_keluarga k
        INNER JOIN tweb_penduduk p ON p.id = k.nik_kepala AND p.status_dasar = 1
    ");
    $total_keluarga = (int)($r['c'] ?? 0);
    if ($total_keluarga === 0) {
        $r = opensid_fetchOne("
            SELECT COUNT(DISTINCT p.id_kk) AS c
            FROM tweb_penduduk p
            WHERE p.status_dasar = 1 AND p.kk_level = 1 AND p.id_kk > 0
        ");
        $total_keluarga = (int)($r['c'] ?? 0);
    }
    $r = opensid_fetchOne("SELECT COUNT(*) AS c FROM tweb_penduduk WHERE status_dasar = 1");
    $total_penduduk = (int)($r['c'] ?? 0);
}

$pengaturan = getPengaturan();
$nama_desa = $pengaturan['nama_desa'] ?? 'Desa';
$user = currentUser();
?>
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6"><h1><i class="fas fa-home me-2 text-primary"></i>Beranda</h1></div>
    </div>
  </div>
</section>
<section class="content">
<div class="container-fluid">

  <div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-3">
      <h5 class="mb-1">Selamat datang, <?= htmlspecialchars($user['nama'] ?? 'Pengguna') ?></h5>
      <p class="text-muted mb-0 small"><?= htmlspecialchars($pengaturan['nama_aplikasi'] ?? 'SIMPOSYANDU') ?> · <?= htmlspecialchars($nama_desa) ?></p>
    </div>
  </div>

  <!-- Hanya Total Penduduk & Total Keluarga -->
  <div class="row">
    <div class="col-12 col-md-6 mb-3">
      <div class="small-box bg-success mb-0">
        <div class="inner">
          <h3><?= number_format($total_penduduk) ?></h3>
          <p>Total Penduduk</p>
        </div>
        <a href="<?= APP_URL ?>/modules/penduduk/index.php" class="small-box-footer">Lihat Data Penduduk <i class="fas fa-arrow-circle-right"></i></a>
      </div>
    </div>
    <div class="col-12 col-md-6 mb-3">
      <div class="small-box bg-primary mb-0">
        <div class="inner">
          <h3><?= number_format($total_keluarga) ?></h3>
          <p>Total Keluarga</p>
        </div>
        <a href="<?= APP_URL ?>/modules/keluarga/index.php" class="small-box-footer">Lihat Data Keluarga <i class="fas fa-arrow-circle-right"></i></a>
      </div>
    </div>
  </div>

  <div class="text-center mt-2 mb-3">
    <a href="<?= APP_URL ?>/modules/statistik/index.php" class="btn btn-outline-primary btn-sm">
      <i class="fas fa-chart-pie me-1"></i> Lihat Statistik Posyandu
    </a>
  </div>

</div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
