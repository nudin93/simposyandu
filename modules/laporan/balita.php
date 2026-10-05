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

$page_title = 'Laporan Balita';
require_once __DIR__ . '/../../includes/header.php';

$tgl_dari   = $_GET['tgl_dari'] ?? date('Y-m-01');
$tgl_sampai = $_GET['tgl_sampai'] ?? date('Y-m-d');
$dusun      = $_GET['dusun'] ?? '';
$cari       = trim($_GET['cari'] ?? '');
$status_gizi= $_GET['status_gizi'] ?? '';
$risiko     = $_GET['risiko'] ?? '';

$where = ["b.status_aktif=1"];
if ($dusun !== '') $where[] = "b.dusun = '".escape($dusun)."'";
if ($cari !== '')  $where[] = "(b.nama_lengkap LIKE '%".escape($cari)."%' OR b.nomor_peserta LIKE '%".escape($cari)."%' OR b.nama_ibu LIKE '%".escape($cari)."%')";
$whereSql = implode(' AND ', $where);

// Rekap
$total_balita = numRows("SELECT id FROM balita b WHERE $whereSql");
$total_l = numRows("SELECT id FROM balita b WHERE $whereSql AND b.jenis_kelamin='L'");
$total_p = numRows("SELECT id FROM balita b WHERE $whereSql AND b.jenis_kelamin='P'");

// Pemeriksaan periode
$whereP = ["pb.tanggal_pemeriksaan BETWEEN '".escape($tgl_dari)."' AND '".escape($tgl_sampai)."'"];
if ($dusun !== '') $whereP[] = "b.dusun = '".escape($dusun)."'";
if ($status_gizi !== '') $whereP[] = "pb.status_gizi = '".escape($status_gizi)."'";
if ($risiko !== '') $whereP[] = "pb.risiko_stunting = '".escape($risiko)."'";
if ($cari !== '') $whereP[] = "(b.nama_lengkap LIKE '%".escape($cari)."%' OR b.nomor_peserta LIKE '%".escape($cari)."%')";
$wherePSql = implode(' AND ', $whereP);

$data = fetchAll("
  SELECT b.nomor_peserta, b.nama_lengkap, b.jenis_kelamin, b.tanggal_lahir, b.dusun, b.nama_ibu,
         pb.tanggal_pemeriksaan, pb.berat_badan, pb.tinggi_badan, pb.status_gizi, pb.risiko_stunting, pb.umur_saat_periksa
  FROM pemeriksaan_balita pb
  JOIN balita b ON b.id = pb.balita_id
  WHERE $wherePSql
  ORDER BY pb.tanggal_pemeriksaan DESC
  LIMIT 500
");

// Chart status gizi
$giziCount = fetchAll("
  SELECT pb.status_gizi, COUNT(*) as jml
  FROM pemeriksaan_balita pb
  JOIN balita b ON b.id = pb.balita_id
  WHERE $wherePSql
  GROUP BY pb.status_gizi
");
$chartLabels = []; $chartData = [];
foreach ($giziCount as $g) { $chartLabels[] = $g['status_gizi']; $chartData[] = (int)$g['jml']; }

$dusunList = fetchAll("SELECT DISTINCT dusun FROM balita WHERE dusun IS NOT NULL AND dusun != '' ORDER BY dusun");
?>
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6"><h1><i class="fas fa-baby me-2 text-info"></i>Laporan Balita</h1></div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="<?= APP_URL ?>/modules/laporan/index.php">Laporan</a></li>
          <li class="breadcrumb-item active">Balita</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<section class="content">
<div class="container-fluid">

  <!-- Filter -->
  <div class="card mb-3 no-print">
    <div class="card-body">
      <form method="GET" class="row g-2 align-items-end">
        <div class="col-6 col-md-2">
          <label class="form-label small mb-1">Dari Tanggal</label>
          <input type="date" name="tgl_dari" class="form-control form-control-sm" value="<?= htmlspecialchars($tgl_dari) ?>">
        </div>
        <div class="col-6 col-md-2">
          <label class="form-label small mb-1">Sampai</label>
          <input type="date" name="tgl_sampai" class="form-control form-control-sm" value="<?= htmlspecialchars($tgl_sampai) ?>">
        </div>
        <div class="col-6 col-md-2">
          <label class="form-label small mb-1">Dusun</label>
          <select name="dusun" class="form-select form-select-sm">
            <option value="">Semua</option>
            <?php foreach ($dusunList as $d): ?>
            <option value="<?= htmlspecialchars($d['dusun']) ?>" <?= $dusun===$d['dusun']?'selected':'' ?>><?= htmlspecialchars($d['dusun']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-6 col-md-2">
          <label class="form-label small mb-1">Status Gizi</label>
          <select name="status_gizi" class="form-select form-select-sm">
            <option value="">Semua</option>
            <?php foreach (['Normal','Kurang','Buruk','Lebih','Obesitas'] as $s): ?>
            <option value="<?= $s ?>" <?= $status_gizi===$s?'selected':'' ?>><?= $s ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-6 col-md-2">
          <label class="form-label small mb-1">Risiko Stunting</label>
          <select name="risiko" class="form-select form-select-sm">
            <option value="">Semua</option>
            <?php foreach (['Tidak','Risiko','Stunting'] as $s): ?>
            <option value="<?= $s ?>" <?= $risiko===$s?'selected':'' ?>><?= $s ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-6 col-md-2">
          <label class="form-label small mb-1">Cari</label>
          <input type="text" name="cari" class="form-control form-control-sm" placeholder="Nama / No. Peserta" value="<?= htmlspecialchars($cari) ?>">
        </div>
        <div class="col-12 col-md-12 mt-2">
          <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter me-1"></i>Filter</button>
          <a href="?" class="btn btn-secondary btn-sm"><i class="fas fa-redo me-1"></i>Reset</a>
          <button type="button" class="btn btn-success btn-sm" onclick="exportExcel()"><i class="fas fa-file-excel me-1"></i>Excel</button>
          <button type="button" class="btn btn-danger btn-sm" onclick="window.open('<?= APP_URL ?>/modules/laporan/print.php?type=balita&'+new URLSearchParams(window.location.search).toString(), '_blank')"><i class="fas fa-file-pdf me-1"></i>PDF / Cetak</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Rekap Cards -->
  <div class="row mb-3">
    <div class="col-6 col-md-3 mb-2">
      <div class="small-box bg-info mb-0">
        <div class="inner py-2"><h3 class="mb-0"><?= $total_balita ?></h3><p class="mb-0 small">Total Balita Aktif</p></div>
        <div class="icon"><i class="fas fa-baby"></i></div>
      </div>
    </div>
    <div class="col-6 col-md-3 mb-2">
      <div class="small-box bg-primary mb-0">
        <div class="inner py-2"><h3 class="mb-0"><?= $total_l ?></h3><p class="mb-0 small">Laki-laki</p></div>
        <div class="icon"><i class="fas fa-male"></i></div>
      </div>
    </div>
    <div class="col-6 col-md-3 mb-2">
      <div class="small-box bg-pink mb-0" style="background:#e83e8c!important">
        <div class="inner py-2"><h3 class="mb-0"><?= $total_p ?></h3><p class="mb-0 small">Perempuan</p></div>
        <div class="icon"><i class="fas fa-female"></i></div>
      </div>
    </div>
    <div class="col-6 col-md-3 mb-2">
      <div class="small-box bg-secondary mb-0">
        <div class="inner py-2"><h3 class="mb-0"><?= count($data) ?></h3><p class="mb-0 small">Data Pemeriksaan</p></div>
        <div class="icon"><i class="fas fa-clipboard-list"></i></div>
      </div>
    </div>
  </div>

  <!-- Chart -->
  <div class="row mb-3">
    <div class="col-md-6 mb-3">
      <div class="card h-100">
        <div class="card-header"><h6 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Distribusi Status Gizi (Periode)</h6></div>
        <div class="card-body"><canvas id="chartGizi" height="180"></canvas></div>
      </div>
    </div>
    <div class="col-md-6 mb-3">
      <div class="card h-100">
        <div class="card-header"><h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Ringkasan Filter</h6></div>
        <div class="card-body small">
          <p class="mb-1"><strong>Periode:</strong> <?= formatTanggal($tgl_dari) ?> s/d <?= formatTanggal($tgl_sampai) ?></p>
          <p class="mb-1"><strong>Dusun:</strong> <?= $dusun ?: 'Semua' ?></p>
          <p class="mb-1"><strong>Status Gizi:</strong> <?= $status_gizi ?: 'Semua' ?></p>
          <p class="mb-0"><strong>Risiko Stunting:</strong> <?= $risiko ?: 'Semua' ?></p>
        </div>
      </div>
    </div>
  </div>

  <!-- Table -->
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h6 class="mb-0"><i class="fas fa-table me-2"></i>Data Pertumbuhan & Status Gizi</h6>
      <span class="badge bg-secondary"><?= count($data) ?> baris</span>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-sm table-hover table-striped mb-0" id="tabelLaporan">
          <thead class="table-light">
            <tr>
              <th>No</th>
              <th>No. Peserta</th>
              <th>Nama Balita</th>
              <th>L/P</th>
              <th>Usia (bln)</th>
              <th>Dusun</th>
              <th>Tgl Periksa</th>
              <th>BB (kg)</th>
              <th>TB (cm)</th>
              <th>Status Gizi</th>
              <th>Risiko Stunting</th>
            </tr>
          </thead>
          <tbody>
          <?php if (empty($data)): ?>
            <tr><td colspan="11" class="text-center text-muted py-4">Tidak ada data sesuai filter</td></tr>
          <?php else: foreach ($data as $i => $r): ?>
            <tr>
              <td><?= $i+1 ?></td>
              <td><?= htmlspecialchars($r['nomor_peserta']) ?></td>
              <td><?= htmlspecialchars($r['nama_lengkap']) ?></td>
              <td><?= $r['jenis_kelamin'] ?></td>
              <td><?= $r['umur_saat_periksa'] ?? hitungUmurBulan($r['tanggal_lahir']) ?></td>
              <td><?= htmlspecialchars($r['dusun'] ?? '-') ?></td>
              <td><?= formatTanggal($r['tanggal_pemeriksaan']) ?></td>
              <td><?= $r['berat_badan'] ?? '-' ?></td>
              <td><?= $r['tinggi_badan'] ?? '-' ?></td>
              <td>
                <?php
                  $gc = ['Normal'=>'success','Kurang'=>'warning','Buruk'=>'danger','Lebih'=>'info','Obesitas'=>'dark'];
                  $c = $gc[$r['status_gizi']] ?? 'secondary';
                ?>
                <span class="badge bg-<?= $c ?>"><?= $r['status_gizi'] ?></span>
              </td>
              <td>
                <?php
                  $rc = ['Tidak'=>'success','Risiko'=>'warning','Stunting'=>'danger'];
                  $c = $rc[$r['risiko_stunting']] ?? 'secondary';
                ?>
                <span class="badge bg-<?= $c ?>"><?= $r['risiko_stunting'] ?></span>
              </td>
            </tr>
          <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

</div>
</section>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  var ctx = document.getElementById('chartGizi');
  if (ctx) {
    new Chart(ctx.getContext('2d'), {
      type: 'doughnut',
      data: {
        labels: <?= json_encode($chartLabels) ?>,
        datasets: [{
          data: <?= json_encode($chartData) ?>,
          backgroundColor: ['#28a745','#ffc107','#dc3545','#17a2b8','#343a40'],
          borderWidth: 1
        }]
      },
      options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
    });
  }
});

function exportExcel() {
  var params = new URLSearchParams(window.location.search);
  params.set('export', 'excel');
  window.location.href = '<?= APP_URL ?>/modules/laporan/export.php?type=balita&' + params.toString();
}
</script>
<style>
@media print {
  .no-print, .main-sidebar, .main-header, .main-footer, .navbar, .btn, .alert { display: none !important; }
  .content-wrapper, .content, .container-fluid, .wrapper { margin: 0 !important; padding: 0 !important; width: 100% !important; }
  .table th, .table td { border: 1px solid #333 !important; color: #000 !important; background: #fff !important; }
  .table thead th { background: #ddd !important; font-weight: bold !important; }
  .card { border: 1px solid #999 !important; box-shadow: none !important; }
  body { background: #fff !important; color: #000 !important; }
}
</style>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
