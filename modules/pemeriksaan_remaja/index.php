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
 * SIMPOSYANDU - Daftar Pemeriksaan Remaja
 */
$page_title = 'Pemeriksaan Remaja';
require_once __DIR__ . '/../../includes/header.php';

$data = [];
try {
    $data = fetchAll("
        SELECT pr.*, r.nama_lengkap, r.nik, r.id AS remaja_id, r.jenis_kelamin, r.kategori,
               TIMESTAMPDIFF(YEAR, r.tanggal_lahir, CURDATE()) AS umur
        FROM pemeriksaan_remaja pr
        JOIN remaja r ON r.id = pr.remaja_id
        ORDER BY pr.tanggal_pemeriksaan DESC, pr.id DESC
        LIMIT 500
    ") ?: [];
} catch (Throwable $e) {
    $data = [];
}

$total = count($data);
$n_anemia = count(array_filter($data, fn($r) => !empty($r['status_anemia'])));
$n_gizi = count(array_filter($data, fn($r) => in_array($r['status_gizi'] ?? '', ['Kurus', 'Gemuk', 'Obesitas'])));
$canDel = function_exists('canInput') ? canInput() : true;
?>
<section class="content-header">
  <div class="container-fluid"><div class="row mb-2">
    <div class="col-sm-6"><h1><i class="fas fa-stethoscope me-2 text-warning"></i>Pemeriksaan Remaja</h1></div>
    <div class="col-sm-6"><ol class="breadcrumb float-sm-end">
      <li class="breadcrumb-item"><a href="<?= APP_URL ?>/dashboard.php">Dashboard</a></li>
      <li class="breadcrumb-item active">Pemeriksaan Remaja</li>
    </ol></div>
  </div></div>
</section>
<section class="content"><div class="container-fluid">
  <div class="row g-2 mb-3">
    <div class="col-6 col-md-4"><div class="card border-0 shadow-sm"><div class="card-body py-2 text-center">
      <div class="fs-4 fw-bold"><?= number_format($total) ?></div><small class="text-muted">Total Pemeriksaan</small>
    </div></div></div>
    <div class="col-6 col-md-4"><div class="card border-0 shadow-sm"><div class="card-body py-2 text-center">
      <div class="fs-4 fw-bold text-danger"><?= number_format($n_anemia) ?></div><small class="text-muted">Anemia</small>
    </div></div></div>
    <div class="col-6 col-md-4"><div class="card border-0 shadow-sm"><div class="card-body py-2 text-center">
      <div class="fs-4 fw-bold text-warning"><?= number_format($n_gizi) ?></div><small class="text-muted">Gizi tidak normal</small>
    </div></div></div>
  </div>

  <div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
      <h5 class="mb-0"><i class="fas fa-list me-2 text-warning"></i>Riwayat Pemeriksaan</h5>
      <a href="<?= APP_URL ?>/modules/pemeriksaan_remaja/tambah.php" class="btn btn-sm btn-warning">
        <i class="fas fa-stethoscope me-1"></i>Periksa Baru
      </a>
    </div>
    <div class="card-body table-responsive">
      <table class="table table-hover datatable align-middle" style="width:100%">
        <thead class="table-light">
          <tr>
            <th>No</th><th>Tanggal</th><th>Nama</th><th>Umur</th>
            <th>BB/TB</th><th>IMT</th><th>Gizi</th><th>Hb</th><th>Anemia</th><th>Aksi</th>
          </tr>
        </thead>
        <tbody>
        <?php if (empty($data)): ?>
          <tr>
            <td colspan="10" class="text-center text-muted py-4">
              Belum ada data pemeriksaan.<br>
              Klik <strong>Periksa Baru</strong> di atas, atau dari <a href="<?= APP_URL ?>/modules/remaja/index.php">Data Remaja</a> pilih <strong>Aksi → Pemeriksaan</strong>.
            </td>
          </tr>
        <?php else: foreach ($data as $i => $row): ?>
          <tr>
            <td><?= $i + 1 ?></td>
            <td><?= formatTanggal($row['tanggal_pemeriksaan']) ?></td>
            <td>
              <div class="fw-semibold"><?= htmlspecialchars($row['nama_lengkap']) ?></div>
              <small class="text-muted"><?= htmlspecialchars($row['nik'] ?: '-') ?></small>
            </td>
            <td><?= (int)($row['umur'] ?? 0) ?> th</td>
            <td><?= $row['berat_badan'] ?? '-' ?> / <?= $row['tinggi_badan'] ?? '-' ?></td>
            <td><?= $row['imt'] ?? '-' ?></td>
            <td><?= htmlspecialchars($row['status_gizi'] ?: '-') ?></td>
            <td><?= $row['hb'] ?? '-' ?></td>
            <td><?= !empty($row['status_anemia']) ? '<span class="badge bg-danger">Ya</span>' : '<span class="badge bg-success">Tidak</span>' ?></td>
            <td class="text-nowrap">
              <a href="<?= APP_URL ?>/modules/remaja/detail.php?id=<?= (int)$row['remaja_id'] ?>" class="btn btn-sm btn-outline-primary" title="Detail"><i class="fas fa-eye"></i></a>
              <?php if ($canDel): ?>
              <a href="<?= APP_URL ?>/modules/pemeriksaan_remaja/tambah.php?remaja_id=<?= (int)$row['remaja_id'] ?>" class="btn btn-sm btn-outline-warning" title="Periksa lagi"><i class="fas fa-stethoscope"></i></a>
              <button type="button" class="btn btn-sm btn-outline-danger"
                onclick="confirmDelete('<?= APP_URL ?>/ajax/delete.php?type=pemeriksaan_remaja&id=<?= (int)$row['id'] ?>','Pemeriksaan <?= htmlspecialchars($row['nama_lengkap'], ENT_QUOTES) ?>')"
                title="Hapus"><i class="fas fa-trash"></i></button>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div></section>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
