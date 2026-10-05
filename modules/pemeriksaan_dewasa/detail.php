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
 * SIMPOSYANDU - Detail 1 hasil Skrining PTM
 */
$page_title = 'Detail Skrining PTM';
require_once __DIR__ . '/../../includes/header.php';

$id = (int)($_GET['id'] ?? 0);
$row = null;
if ($id > 0) {
    try {
        $row = fetchOne("
            SELECT pd.*, u.nama_lengkap, u.nik, u.nomor_peserta, u.jenis_kelamin, u.id AS up_id,
                   TIMESTAMPDIFF(YEAR, u.tanggal_lahir, CURDATE()) AS umur
            FROM pemeriksaan_dewasa pd
            LEFT JOIN usia_produktif u ON u.id = pd.usia_produktif_id
            WHERE pd.id = $id
            LIMIT 1
        ");
    } catch (Throwable $e) {
        $row = null;
    }
}

if (!$row) {
    echo '<div class="alert alert-warning m-3">Data skrining tidak ditemukan.
        <a href="' . htmlspecialchars(APP_URL) . '/modules/pemeriksaan_dewasa/index.php">Kembali</a></div>';
    include __DIR__ . '/../../includes/footer.php';
    exit;
}

$upId = (int)($row['up_id'] ?? $row['usia_produktif_id'] ?? 0);
?>
<section class="content-header">
  <div class="container-fluid"><div class="row mb-2">
    <div class="col-sm-6"><h1><i class="fas fa-heartbeat me-2 text-danger"></i>Detail Skrining PTM</h1></div>
    <div class="col-sm-6"><ol class="breadcrumb float-sm-end">
      <li class="breadcrumb-item"><a href="<?= APP_URL ?>/dashboard.php">Dashboard</a></li>
      <li class="breadcrumb-item"><a href="<?= APP_URL ?>/modules/pemeriksaan_dewasa/index.php">Skrining PTM</a></li>
      <li class="breadcrumb-item active">Detail</li>
    </ol></div>
  </div></div>
</section>
<section class="content"><div class="container-fluid">
  <div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
      <div class="d-flex flex-wrap justify-content-between gap-2">
        <div>
          <h3 class="fw-bold mb-1"><?= htmlspecialchars($row['nama_lengkap'] ?? '-') ?></h3>
          <p class="mb-0 text-muted">
            NIK: <code><?= htmlspecialchars($row['nik'] ?: '-') ?></code>
            · <?= (int)($row['umur'] ?? 0) ?> tahun
            · <?= formatTanggal($row['tanggal_pemeriksaan'] ?? '') ?>
          </p>
        </div>
        <div class="d-flex flex-wrap gap-2">
          <?php if ($upId > 0): ?>
          <a href="<?= APP_URL ?>/modules/usia_produktif/detail.php?id=<?= $upId ?>" class="btn btn-outline-primary btn-sm">
            <i class="fas fa-user me-1"></i>Profil
          </a>
          <a href="<?= APP_URL ?>/modules/pemeriksaan_dewasa/tambah.php?usia_produktif_id=<?= $upId ?>" class="btn btn-success btn-sm">
            <i class="fas fa-stethoscope me-1"></i>Skrining lagi
          </a>
          <?php endif; ?>
          <a href="<?= APP_URL ?>/modules/pemeriksaan_dewasa/index.php" class="btn btn-secondary btn-sm">Kembali</a>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-2 mb-3">
    <div class="col-6 col-md-3"><div class="card border-0 shadow-sm"><div class="card-body text-center py-3">
      <div class="text-muted small">BB / TB</div>
      <div class="fs-5 fw-bold"><?= htmlspecialchars(($row['berat_badan'] ?? '-') . ' / ' . ($row['tinggi_badan'] ?? '-')) ?></div>
    </div></div></div>
    <div class="col-6 col-md-3"><div class="card border-0 shadow-sm"><div class="card-body text-center py-3">
      <div class="text-muted small">IMT</div>
      <div class="fs-5 fw-bold"><?= htmlspecialchars((string)($row['imt'] ?? '-')) ?></div>
    </div></div></div>
    <div class="col-6 col-md-3"><div class="card border-0 shadow-sm"><div class="card-body text-center py-3">
      <div class="text-muted small">Tekanan Darah</div>
      <div class="fs-5 fw-bold"><?= htmlspecialchars($row['tekanan_darah'] ?: '-') ?></div>
    </div></div></div>
    <div class="col-6 col-md-3"><div class="card border-0 shadow-sm"><div class="card-body text-center py-3">
      <div class="text-muted small">Gula Darah</div>
      <div class="fs-5 fw-bold"><?= htmlspecialchars((string)($row['gula_darah'] ?? '-')) ?></div>
    </div></div></div>
  </div>

  <div class="card border-0 shadow-sm">
    <div class="card-header bg-white"><h5 class="mb-0">Hasil Lengkap</h5></div>
    <div class="card-body">
      <table class="table table-bordered mb-0">
        <tr><th style="width:35%">Lingkar Perut</th><td><?= htmlspecialchars((string)($row['lingkar_perut'] ?? '-')) ?> cm</td></tr>
        <tr><th>Kolesterol</th><td><?= htmlspecialchars((string)($row['kolesterol'] ?? '-')) ?></td></tr>
        <tr><th>Faktor Risiko / Keluhan</th><td><?= nl2br(htmlspecialchars($row['faktor_risiko'] ?: ($row['keluhan'] ?? '-') ?: '-')) ?></td></tr>
        <tr><th>Penanganan</th><td><?= nl2br(htmlspecialchars($row['penanganan'] ?: '-')) ?></td></tr>
        <tr><th>Rujukan</th><td><?= nl2br(htmlspecialchars($row['rujukan'] ?: '-')) ?></td></tr>
        <tr><th>Risiko</th><td>
          <?php
          $badges = [];
          if (!empty($row['risiko_hipertensi'])) $badges[] = '<span class="badge bg-danger">Hipertensi</span>';
          if (!empty($row['risiko_diabetes'])) $badges[] = '<span class="badge bg-warning text-dark">Diabetes</span>';
          if (!empty($row['risiko_obesitas'])) $badges[] = '<span class="badge bg-secondary">Obesitas</span>';
          echo $badges ? implode(' ', $badges) : '<span class="badge bg-success">Normal</span>';
          ?>
        </td></tr>
      </table>
    </div>
  </div>
</div></section>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
