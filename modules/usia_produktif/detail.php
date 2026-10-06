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
 * SIMPOSYANDU - Detail Usia Produktif + Riwayat Skrining PTM
 */
$page_title = 'Detail Usia Produktif';
require_once __DIR__ . '/../../includes/header.php';

$id = (int)($_GET['id'] ?? $_GET['usia_produktif_id'] ?? 0);
$u = null;
if ($id > 0) {
    try {
        $u = fetchOne("SELECT *, TIMESTAMPDIFF(YEAR, tanggal_lahir, CURDATE()) AS umur_tahun FROM usia_produktif WHERE id = $id LIMIT 1");
    } catch (Throwable $e) {
        $u = null;
    }
}

if (!$u) {
    echo '<div class="alert alert-warning m-3">Data usia produktif tidak ditemukan.
        <a href="' . htmlspecialchars(APP_URL) . '/modules/usia_produktif/index.php">Kembali ke daftar</a></div>';
    include __DIR__ . '/../../includes/footer.php';
    exit;
}

$riwayat = [];
try {
    $riwayat = fetchAll("SELECT * FROM pemeriksaan_dewasa WHERE usia_produktif_id = $id ORDER BY tanggal_pemeriksaan DESC, id DESC") ?: [];
} catch (Throwable $e) {
    $riwayat = [];
}
$canInput = function_exists('canInput') ? canInput() : true;
$nama = (string)($u['nama_lengkap'] ?? '-');
$jk = ($u['jenis_kelamin'] ?? '') === 'L' ? 'Laki-laki' : 'Perempuan';
?>
<section class="content-header">
  <div class="container-fluid"><div class="row mb-2">
    <div class="col-sm-6"><h1><i class="fas fa-user-tie me-2 text-primary"></i>Detail Usia Produktif</h1></div>
    <div class="col-sm-6"><ol class="breadcrumb float-sm-end">
      <li class="breadcrumb-item"><a href="<?= APP_URL ?>/dashboard.php">Dashboard</a></li>
      <li class="breadcrumb-item"><a href="<?= APP_URL ?>/modules/usia_produktif/index.php">Usia Produktif</a></li>
      <li class="breadcrumb-item active">Detail</li>
    </ol></div>
  </div></div>
</section>
<section class="content"><div class="container-fluid">
  <div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
      <div class="row">
        <div class="col-md-8">
          <h3 class="fw-bold mb-1"><?= htmlspecialchars($nama) ?></h3>
          <p class="mb-1">
            <span class="badge bg-primary me-1"><?= htmlspecialchars($u['nomor_peserta'] ?? '-') ?></span>
            <span class="badge bg-info"><?= htmlspecialchars($jk) ?></span>
            <?php if (($u['status_integrasi'] ?? '') === 'terhubung'): ?>
              <span class="badge bg-success">OpenSID</span>
            <?php else: ?>
              <span class="badge bg-secondary">Manual</span>
            <?php endif; ?>
          </p>
          <p class="text-muted mb-0">
            <i class="fas fa-birthday-cake me-1"></i><?= htmlspecialchars(function_exists('formatTanggal') ? formatTanggal($u['tanggal_lahir'] ?? '') : ($u['tanggal_lahir'] ?? '-')) ?>
            · <strong><?= (int)($u['umur_tahun'] ?? 0) ?> tahun</strong>
          </p>
          <p class="text-muted mb-0">
            <i class="fas fa-fingerprint me-1"></i>NIK: <code><?= htmlspecialchars($u['nik'] ?: '-') ?></code>
            <?php if (!empty($u['no_kk'])): ?> · KK: <code><?= htmlspecialchars($u['no_kk']) ?></code><?php endif; ?>
          </p>
          <?php
            $alamat = trim(($u['alamat_lengkap'] ?? '') . ' ' . ($u['dusun'] ?? ''));
            if ($alamat !== '' || !empty($u['rt']) || !empty($u['rw'])):
          ?>
          <p class="text-muted mb-0">
            <i class="fas fa-map-marker-alt me-1"></i>
            <?= htmlspecialchars(trim($alamat . ' RT ' . ($u['rt'] ?? '-') . '/RW ' . ($u['rw'] ?? '-'))) ?>
          </p>
          <?php endif; ?>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
          <?php if ($canInput): ?>
          <a href="<?= APP_URL ?>/modules/pemeriksaan_dewasa/tambah.php?usia_produktif_id=<?= $id ?>" class="btn btn-success mb-2 w-100">
            <i class="fas fa-stethoscope me-2"></i>Skrining PTM
          </a>
          <?php endif; ?>
          <a href="<?= APP_URL ?>/modules/usia_produktif/index.php" class="btn btn-secondary w-100">
            <i class="fas fa-arrow-left me-2"></i>Kembali
          </a>
          <?php if ($canInput): ?>
          <button type="button" class="btn btn-outline-danger w-100 mt-2"
            onclick="confirmDelete('<?= APP_URL ?>/ajax/delete.php?type=usia_produktif&id=<?= $id ?>','<?= htmlspecialchars($nama, ENT_QUOTES) ?>')">
            <i class="fas fa-trash me-2"></i>Hapus
          </button>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <?php if (!empty($riwayat)): $last = $riwayat[0]; ?>
  <div class="row g-2 mb-3">
    <div class="col-6 col-md-3"><div class="info-box mb-0 shadow-sm">
      <span class="info-box-icon bg-primary"><i class="fas fa-weight"></i></span>
      <div class="info-box-content">
        <span class="info-box-text">Berat Badan</span>
        <span class="info-box-number"><?= $last['berat_badan'] !== null && $last['berat_badan'] !== '' ? htmlspecialchars($last['berat_badan']) . ' kg' : '-' ?></span>
      </div>
    </div></div>
    <div class="col-6 col-md-3"><div class="info-box mb-0 shadow-sm">
      <span class="info-box-icon bg-success"><i class="fas fa-ruler-vertical"></i></span>
      <div class="info-box-content">
        <span class="info-box-text">Tinggi Badan</span>
        <span class="info-box-number"><?= $last['tinggi_badan'] !== null && $last['tinggi_badan'] !== '' ? htmlspecialchars($last['tinggi_badan']) . ' cm' : '-' ?></span>
      </div>
    </div></div>
    <div class="col-6 col-md-3"><div class="info-box mb-0 shadow-sm">
      <span class="info-box-icon bg-info"><i class="fas fa-calculator"></i></span>
      <div class="info-box-content">
        <span class="info-box-text">IMT</span>
        <span class="info-box-number"><?= $last['imt'] !== null && $last['imt'] !== '' ? htmlspecialchars($last['imt']) : '-' ?></span>
      </div>
    </div></div>
    <div class="col-6 col-md-3"><div class="info-box mb-0 shadow-sm">
      <span class="info-box-icon bg-warning"><i class="fas fa-heartbeat"></i></span>
      <div class="info-box-content">
        <span class="info-box-text">Tekanan Darah</span>
        <span class="info-box-number"><?= htmlspecialchars($last['tekanan_darah'] ?: '-') ?></span>
      </div>
    </div></div>
  </div>
  <?php endif; ?>

  <div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
      <h5 class="mb-0"><i class="fas fa-history me-2 text-danger"></i>Riwayat Skrining PTM</h5>
      <?php if ($canInput): ?>
      <a href="<?= APP_URL ?>/modules/pemeriksaan_dewasa/tambah.php?usia_produktif_id=<?= $id ?>" class="btn btn-sm btn-primary">
        <i class="fas fa-plus me-1"></i>Skrining Baru
      </a>
      <?php endif; ?>
    </div>
    <div class="card-body table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>No</th><th>Tanggal</th><th>BB/TB</th><th>IMT</th><th>TD</th>
            <th>Gula</th><th>Kolesterol</th><th>Risiko</th><th>Aksi</th>
          </tr>
        </thead>
        <tbody>
        <?php if (empty($riwayat)): ?>
          <tr><td colspan="9" class="text-center text-muted py-4">Belum ada skrining. Klik <strong>Skrining PTM</strong> untuk menambah.</td></tr>
        <?php else: foreach ($riwayat as $i => $p): ?>
          <tr>
            <td><?= $i + 1 ?></td>
            <td><?= htmlspecialchars(function_exists('formatTanggal') ? formatTanggal($p['tanggal_pemeriksaan'] ?? '') : ($p['tanggal_pemeriksaan'] ?? '-')) ?></td>
            <td><?= htmlspecialchars(($p['berat_badan'] ?? '-') . ' / ' . ($p['tinggi_badan'] ?? '-')) ?></td>
            <td><?= htmlspecialchars((string)($p['imt'] ?? '-')) ?></td>
            <td><?= htmlspecialchars($p['tekanan_darah'] ?: '-') ?></td>
            <td><?= htmlspecialchars((string)($p['gula_darah'] ?? '-')) ?></td>
            <td><?= htmlspecialchars((string)($p['kolesterol'] ?? '-')) ?></td>
            <td>
              <?php
              $badges = [];
              if (!empty($p['risiko_hipertensi'])) $badges[] = '<span class="badge bg-danger">HT</span>';
              if (!empty($p['risiko_diabetes'])) $badges[] = '<span class="badge bg-warning text-dark">DM</span>';
              if (!empty($p['risiko_obesitas'])) $badges[] = '<span class="badge bg-secondary">Obesitas</span>';
              echo $badges ? implode(' ', $badges) : '<span class="badge bg-success">Normal</span>';
              ?>
            </td>
            <td>
              <a href="<?= APP_URL ?>/modules/pemeriksaan_dewasa/detail.php?id=<?= (int)$p['id'] ?>" class="btn btn-sm btn-outline-primary" title="Detail skrining"><i class="fas fa-eye"></i></a>
            </td>
          </tr>
        <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div></section>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
