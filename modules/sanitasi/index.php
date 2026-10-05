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
 * SIMPOSYANDU - Data Sanitasi & Kesehatan Lingkungan
 */
$page_title = 'Data Sanitasi';
require_once __DIR__ . '/../../includes/header.php';

$__chk = @query("SHOW TABLES LIKE 'sanitasi'");
if (!$__chk || $__chk->num_rows == 0) {
    echo '<section class="content"><div class="container-fluid"><div class="alert alert-warning mt-3">';
    echo 'Tabel <b>sanitasi</b> belum ada. Jalankan migrasi database di phpMyAdmin.';
    echo ' <a href="' . APP_URL . '/dashboard.php" class="alert-link">Kembali</a></div></div></section>';
    include __DIR__ . '/../../includes/footer.php';
    exit;
}

$q = trim($_GET['q'] ?? '');
$filterJamban = trim($_GET['jamban'] ?? '');
$allowedJamban = ['Jamban Sendiri', 'Jamban Bersama', 'Jamban Umum', 'Tidak Memiliki'];

$where = [];
if ($q !== '') {
    $s = escape($q);
    $where[] = "(nama_kepala LIKE '%$s%' OR no_kk LIKE '%$s%' OR dusun LIKE '%$s%')";
}
if ($filterJamban !== '' && in_array($filterJamban, $allowedJamban, true)) {
    $where[] = "kepemilikan_jamban = '" . escape($filterJamban) . "'";
}
$sqlWhere = $where ? ('WHERE ' . implode(' AND ', $where)) : '';
$data = fetchAll("SELECT * FROM sanitasi $sqlWhere ORDER BY dusun, nama_kepala") ?: [];

// Statistik semua pilihan kepemilikan jamban
$stats = [
    'total' => 0,
    'Jamban Sendiri' => 0,
    'Jamban Bersama' => 0,
    'Jamban Umum' => 0,
    'Tidak Memiliki' => 0,
    'air_layak' => 0,
];
try {
    $rows = fetchAll("SELECT kepemilikan_jamban, COUNT(*) AS c FROM sanitasi GROUP BY kepemilikan_jamban") ?: [];
    foreach ($rows as $r) {
        $k = $r['kepemilikan_jamban'] ?? '';
        $c = (int)($r['c'] ?? 0);
        $stats['total'] += $c;
        if (isset($stats[$k])) $stats[$k] = $c;
    }
    $stats['air_layak'] = (int)(fetchOne("SELECT COUNT(*) AS c FROM sanitasi WHERE kondisi_air='Layak'")['c'] ?? 0);
} catch (Throwable $e) {}

function badgeJamban($val) {
    $map = [
        'Jamban Sendiri'  => 'success',
        'Jamban Bersama'  => 'info',
        'Jamban Umum'     => 'warning',
        'Tidak Memiliki'  => 'danger',
    ];
    $cls = $map[$val] ?? 'secondary';
    $label = $val !== '' && $val !== null ? $val : '-';
    return '<span class="badge bg-' . $cls . '">' . htmlspecialchars($label) . '</span>';
}
?>
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6"><h1><i class="fas fa-toilet me-2 text-primary"></i>Data Sanitasi & Kesehatan Lingkungan</h1></div>
      <div class="col-sm-6"><ol class="breadcrumb float-sm-end">
        <li class="breadcrumb-item"><a href="<?= APP_URL ?>/dashboard.php">Dashboard</a></li>
        <li class="breadcrumb-item active">Sanitasi</li>
      </ol></div>
    </div>
  </div>
</section>
<section class="content"><div class="container-fluid">
  <div class="row g-2 mb-3">
    <div class="col-6 col-md-2">
      <div class="small-box bg-primary mb-0">
        <div class="inner py-2"><h3 class="fs-4"><?= (int)$stats['total'] ?></h3><p class="mb-0">Total KK</p></div>
      </div>
    </div>
    <div class="col-6 col-md-2">
      <div class="small-box bg-success mb-0">
        <div class="inner py-2"><h3 class="fs-4"><?= (int)$stats['Jamban Sendiri'] ?></h3><p class="mb-0">Jamban Sendiri</p></div>
      </div>
    </div>
    <div class="col-6 col-md-2">
      <div class="small-box bg-info mb-0">
        <div class="inner py-2"><h3 class="fs-4"><?= (int)$stats['Jamban Bersama'] ?></h3><p class="mb-0">Jamban Bersama</p></div>
      </div>
    </div>
    <div class="col-6 col-md-2">
      <div class="small-box bg-warning mb-0">
        <div class="inner py-2"><h3 class="fs-4"><?= (int)$stats['Jamban Umum'] ?></h3><p class="mb-0">Jamban Umum</p></div>
      </div>
    </div>
    <div class="col-6 col-md-2">
      <div class="small-box bg-danger mb-0">
        <div class="inner py-2"><h3 class="fs-4"><?= (int)$stats['Tidak Memiliki'] ?></h3><p class="mb-0">Tidak Memiliki</p></div>
      </div>
    </div>
    <div class="col-6 col-md-2">
      <div class="small-box bg-teal mb-0" style="background:#20c997!important">
        <div class="inner py-2"><h3 class="fs-4"><?= (int)$stats['air_layak'] ?></h3><p class="mb-0">Air Layak</p></div>
      </div>
    </div>
  </div>

  <div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
      <h5 class="mb-0"><i class="fas fa-list me-2"></i>Daftar Sanitasi Keluarga</h5>
      <div class="d-flex flex-wrap gap-2 align-items-center">
        <form method="get" class="d-flex flex-wrap gap-1 align-items-center">
          <select name="jamban" class="form-select form-select-sm" style="width:auto;min-width:160px" onchange="this.form.submit()">
            <option value="">Semua Kepemilikan</option>
            <?php foreach ($allowedJamban as $opt): ?>
            <option value="<?= htmlspecialchars($opt) ?>" <?= $filterJamban === $opt ? 'selected' : '' ?>><?= htmlspecialchars($opt) ?></option>
            <?php endforeach; ?>
          </select>
          <input type="text" name="q" class="form-control form-control-sm" placeholder="Cari nama / KK / dusun..." value="<?= htmlspecialchars($q) ?>" style="width:180px">
          <button class="btn btn-sm btn-outline-primary" type="submit"><i class="fas fa-search"></i></button>
          <?php if ($q !== '' || $filterJamban !== ''): ?>
          <a href="index.php" class="btn btn-sm btn-outline-secondary">Reset</a>
          <?php endif; ?>
        </form>
        <a href="tambah.php" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>Tambah / Update</a>
      </div>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-hover datatable table-sm align-middle w-100">
          <thead class="table-light">
            <tr>
              <th>No</th>
              <th>No. KK</th>
              <th>Kepala Keluarga</th>
              <th>Dusun</th>
              <th>Kepemilikan Jamban</th>
              <th>Jenis</th>
              <th>Kondisi</th>
              <th>Sumber Air</th>
              <th>Sampah</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($data as $i => $s): ?>
            <tr>
              <td><?= $i + 1 ?></td>
              <td><code><?= htmlspecialchars($s['no_kk'] ?? '') ?></code></td>
              <td class="fw-semibold"><?= htmlspecialchars($s['nama_kepala'] ?? '-') ?></td>
              <td><?= htmlspecialchars($s['dusun'] ?? '-') ?></td>
              <td><?= badgeJamban($s['kepemilikan_jamban'] ?? '') ?></td>
              <td><?= htmlspecialchars($s['jenis_jamban'] ?? '-') ?></td>
              <td><?= htmlspecialchars($s['kondisi_jamban'] ?? '-') ?></td>
              <td>
                <?= htmlspecialchars($s['sumber_air'] ?? '-') ?>
                <small class="text-muted">(<?= htmlspecialchars($s['kondisi_air'] ?? '-') ?>)</small>
              </td>
              <td><?= htmlspecialchars($s['pengelolaan_sampah'] ?? '-') ?></td>
              <td class="text-nowrap">
                <a href="tambah.php?no_kk=<?= urlencode($s['no_kk'] ?? '') ?>" class="btn btn-sm btn-warning" title="Edit"><i class="fas fa-edit"></i></a>
                <button type="button" class="btn btn-sm btn-danger"
                  onclick="confirmDelete('<?= APP_URL ?>/ajax/delete.php?type=sanitasi&id=<?= (int)$s['id'] ?>','Sanitasi <?= htmlspecialchars($s['nama_kepala'] ?? $s['no_kk'] ?? '', ENT_QUOTES) ?>')"
                  title="Hapus"><i class="fas fa-trash"></i></button>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php if (empty($data)): ?>
      <div class="text-center text-muted py-3">Belum ada data<?= $filterJamban ? ' untuk filter <strong>' . htmlspecialchars($filterJamban) . '</strong>' : '' ?>.</div>
      <?php endif; ?>
    </div>
  </div>
</div></section>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
