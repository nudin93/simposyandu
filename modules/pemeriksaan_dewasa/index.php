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
 * SIMPOSYANDU - Daftar Skrining PTM
 */
$page_title = 'Skrining PTM';
require_once __DIR__ . '/../../includes/header.php';

$data = [];
try {
    $data = fetchAll("
        SELECT pd.*, u.nama_lengkap, u.nik, u.id AS up_id,
               TIMESTAMPDIFF(YEAR, u.tanggal_lahir, CURDATE()) AS umur
        FROM pemeriksaan_dewasa pd
        JOIN usia_produktif u ON u.id = pd.usia_produktif_id
        ORDER BY pd.tanggal_pemeriksaan DESC, pd.id DESC
        LIMIT 500
    ") ?: [];
} catch (Throwable $e) {
    $data = [];
}

$total = count($data);
$n_ht = count(array_filter($data, fn($r) => !empty($r['risiko_hipertensi'])));
$n_dm = count(array_filter($data, fn($r) => !empty($r['risiko_diabetes'])));
$n_ob = count(array_filter($data, fn($r) => !empty($r['risiko_obesitas'])));
$canDel = function_exists('canInput') ? canInput() : true;
?>
<section class="content-header">
  <div class="container-fluid"><div class="row mb-2">
    <div class="col-sm-6"><h1><i class="fas fa-heartbeat me-2 text-danger"></i>Skrining PTM</h1></div>
    <div class="col-sm-6"><ol class="breadcrumb float-sm-end">
      <li class="breadcrumb-item"><a href="<?= APP_URL ?>/dashboard.php">Dashboard</a></li>
      <li class="breadcrumb-item active">Skrining PTM</li>
    </ol></div>
  </div></div>
</section>
<section class="content"><div class="container-fluid">
  <div class="row g-2 mb-3">
    <div class="col-6 col-md-3"><div class="card border-0 shadow-sm"><div class="card-body py-2 text-center">
      <div class="fs-4 fw-bold"><?= number_format($total) ?></div><small class="text-muted">Total Skrining</small>
    </div></div></div>
    <div class="col-6 col-md-3"><div class="card border-0 shadow-sm"><div class="card-body py-2 text-center">
      <div class="fs-4 fw-bold text-danger"><?= number_format($n_ht) ?></div><small class="text-muted">Risiko HT</small>
    </div></div></div>
    <div class="col-6 col-md-3"><div class="card border-0 shadow-sm"><div class="card-body py-2 text-center">
      <div class="fs-4 fw-bold text-warning"><?= number_format($n_dm) ?></div><small class="text-muted">Risiko DM</small>
    </div></div></div>
    <div class="col-6 col-md-3"><div class="card border-0 shadow-sm"><div class="card-body py-2 text-center">
      <div class="fs-4 fw-bold text-secondary"><?= number_format($n_ob) ?></div><small class="text-muted">Risiko Obesitas</small>
    </div></div></div>
  </div>

  <div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
      <h5 class="mb-0"><i class="fas fa-list me-2 text-danger"></i>Riwayat Skrining PTM</h5>
      <div class="d-flex flex-wrap gap-2">
        <a href="<?= APP_URL ?>/modules/pemeriksaan_dewasa/tambah.php" class="btn btn-sm btn-primary">
          <i class="fas fa-stethoscope me-1"></i>Skrining Baru
        </a>
        <a href="<?= APP_URL ?>/modules/usia_produktif/index.php" class="btn btn-sm btn-outline-primary">
          <i class="fas fa-list me-1"></i>Data Usia Produktif
        </a>
      </div>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table id="tableSkriningPTM" class="table table-hover table-striped align-middle w-100 datatable">
          <thead class="table-light">
            <tr>
              <th style="width:40px">No</th>
              <th>Tanggal</th>
              <th>Nama</th>
              <th>Umur</th>
              <th>BB/TB</th>
              <th>IMT</th>
              <th>TD</th>
              <th>Gula</th>
              <th>Risiko</th>
              <th style="width:90px">Aksi</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($data as $i => $r):
            $riskLabels = [];
            if (!empty($r['risiko_hipertensi'])) $riskLabels[] = 'HT';
            if (!empty($r['risiko_diabetes'])) $riskLabels[] = 'DM';
            if (!empty($r['risiko_obesitas'])) $riskLabels[] = 'Obesitas';
            $nama = (string)($r['nama_lengkap'] ?? '');
            $nik = (string)($r['nik'] ?? '');
            $td = (string)($r['tekanan_darah'] ?? '');
          ?>
            <tr>
              <td><?= $i + 1 ?></td>
              <td data-order="<?= htmlspecialchars($r['tanggal_pemeriksaan'] ?? '') ?>"><?= htmlspecialchars(function_exists('formatTanggal') ? formatTanggal($r['tanggal_pemeriksaan'] ?? '') : ($r['tanggal_pemeriksaan'] ?? '-')) ?></td>
              <td>
                <span class="fw-semibold"><?= htmlspecialchars($nama) ?></span>
                <?php if ($nik !== ''): ?><br><small class="text-muted"><?= htmlspecialchars($nik) ?></small><?php endif; ?>
              </td>
              <td><?= (int)($r['umur'] ?? 0) ?> th</td>
              <td><?= htmlspecialchars(($r['berat_badan'] ?? '-') . ' / ' . ($r['tinggi_badan'] ?? '-')) ?></td>
              <td><?= htmlspecialchars((string)($r['imt'] ?? '-')) ?></td>
              <td><?= htmlspecialchars($td !== '' ? $td : '-') ?></td>
              <td><?= htmlspecialchars((string)($r['gula_darah'] ?? '-')) ?></td>
              <td>
                <?php if ($riskLabels): ?>
                  <?php foreach ($riskLabels as $lb): ?>
                    <?php if ($lb === 'HT'): ?><span class="badge bg-danger">HT</span>
                    <?php elseif ($lb === 'DM'): ?><span class="badge bg-warning text-dark">DM</span>
                    <?php else: ?><span class="badge bg-secondary">Obesitas</span><?php endif; ?>
                  <?php endforeach; ?>
                <?php else: ?>
                  <span class="badge bg-success">Normal</span>
                <?php endif; ?>
              </td>
              <td class="text-nowrap">
                <a href="<?= APP_URL ?>/modules/pemeriksaan_dewasa/detail.php?id=<?= (int)$r['id'] ?>" class="btn btn-sm btn-outline-primary" title="Detail skrining"><i class="fas fa-eye"></i></a>
              <?php if (!empty($r['up_id'])): ?>
              <a href="<?= APP_URL ?>/modules/usia_produktif/detail.php?id=<?= (int)$r['up_id'] ?>" class="btn btn-sm btn-outline-secondary" title="Profil"><i class="fas fa-user"></i></a>
              <?php endif; ?>
                <?php if ($canDel): ?>
                <button type="button" class="btn btn-sm btn-outline-danger"
                  onclick="confirmDelete('<?= APP_URL ?>/ajax/delete.php?type=pemeriksaan_dewasa&id=<?= (int)$r['id'] ?>','Skrining <?= htmlspecialchars($nama, ENT_QUOTES) ?>')"
                  title="Hapus"><i class="fas fa-trash"></i></button>
                <?php endif; ?>
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
