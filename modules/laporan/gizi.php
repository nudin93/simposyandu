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

$page_title = 'Laporan Gizi';
require_once __DIR__ . '/../../includes/header.php';

$tgl_dari = $_GET['tgl_dari'] ?? date('Y-m-01');
$tgl_sampai = $_GET['tgl_sampai'] ?? date('Y-m-d');
$dusun = $_GET['dusun'] ?? '';
$status_gizi = $_GET['status_gizi'] ?? '';

$where = ["pb.tanggal_pemeriksaan BETWEEN '".escape($tgl_dari)."' AND '".escape($tgl_sampai)."'"];
if ($dusun !== '') $where[] = "b.dusun = '".escape($dusun)."'";
if ($status_gizi !== '') $where[] = "pb.status_gizi = '".escape($status_gizi)."'";
$whereSql = implode(' AND ', $where);

$counts = fetchAll("
  SELECT pb.status_gizi, COUNT(*) as jml
  FROM pemeriksaan_balita pb
  JOIN balita b ON b.id = pb.balita_id
  WHERE $whereSql
  GROUP BY pb.status_gizi
");
$map = [];
foreach ($counts as $c) $map[$c['status_gizi']] = (int)$c['jml'];
$normal = $map['Normal'] ?? 0;
$kurang = $map['Kurang'] ?? 0;
$buruk  = $map['Buruk'] ?? 0;
$lebih  = ($map['Lebih'] ?? 0) + ($map['Obesitas'] ?? 0);

$data = fetchAll("
  SELECT b.nomor_peserta, b.nama_lengkap, b.jenis_kelamin, b.dusun,
         pb.tanggal_pemeriksaan, pb.berat_badan, pb.tinggi_badan, pb.status_gizi, pb.umur_saat_periksa
  FROM pemeriksaan_balita pb
  JOIN balita b ON b.id = pb.balita_id
  WHERE $whereSql
  ORDER BY pb.tanggal_pemeriksaan DESC LIMIT 500
");

$dusunList = fetchAll("SELECT DISTINCT dusun FROM balita WHERE dusun IS NOT NULL AND dusun != '' ORDER BY dusun");
$chartLabels = array_keys($map);
$chartData = array_values($map);
?>
<section class="content-header">
  <div class="container-fluid"><div class="row mb-2">
    <div class="col-sm-6"><h1><i class="fas fa-chart-pie me-2 text-warning"></i>Laporan Gizi</h1></div>
    <div class="col-sm-6"><ol class="breadcrumb float-sm-end">
      <li class="breadcrumb-item"><a href="<?= APP_URL ?>/modules/laporan/index.php">Laporan</a></li>
      <li class="breadcrumb-item active">Gizi</li>
    </ol></div>
  </div></div>
</section>
<section class="content"><div class="container-fluid">
  <div class="card mb-3 no-print"><div class="card-body">
    <form method="GET" class="row g-2 align-items-end">
      <div class="col-6 col-md-2"><label class="form-label small mb-1">Dari</label><input type="date" name="tgl_dari" class="form-control form-control-sm" value="<?= htmlspecialchars($tgl_dari) ?>"></div>
      <div class="col-6 col-md-2"><label class="form-label small mb-1">Sampai</label><input type="date" name="tgl_sampai" class="form-control form-control-sm" value="<?= htmlspecialchars($tgl_sampai) ?>"></div>
      <div class="col-6 col-md-2"><label class="form-label small mb-1">Dusun</label>
        <select name="dusun" class="form-select form-select-sm"><option value="">Semua</option>
        <?php foreach ($dusunList as $d): ?><option value="<?= htmlspecialchars($d['dusun']) ?>" <?= $dusun===$d['dusun']?'selected':'' ?>><?= htmlspecialchars($d['dusun']) ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="col-6 col-md-2"><label class="form-label small mb-1">Status Gizi</label>
        <select name="status_gizi" class="form-select form-select-sm">
          <option value="">Semua</option>
          <?php foreach (['Normal','Kurang','Buruk','Lebih','Obesitas'] as $s): ?><option value="<?= $s ?>" <?= $status_gizi===$s?'selected':'' ?>><?= $s ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="col-12 mt-2">
        <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter me-1"></i>Filter</button>
        <a href="?" class="btn btn-secondary btn-sm">Reset</a>
        <button type="button" class="btn btn-success btn-sm" onclick="window.location='<?= APP_URL ?>/modules/laporan/export.php?type=gizi&'+new URLSearchParams(window.location.search).toString()"><i class="fas fa-file-excel me-1"></i>Excel</button>
        <button type="button" class="btn btn-danger btn-sm" onclick="window.open('<?= APP_URL ?>/modules/laporan/print.php?type=gizi&'+new URLSearchParams(window.location.search).toString(), '_blank')"><i class="fas fa-file-pdf me-1"></i>PDF / Cetak</button>
      </div>
    </form>
  </div></div>

  <div class="row mb-3">
    <div class="col-6 col-md-3 mb-2"><div class="small-box bg-success mb-0"><div class="inner py-2"><h3 class="mb-0"><?= $normal ?></h3><p class="mb-0 small">Gizi Baik / Normal</p></div><div class="icon"><i class="fas fa-check-circle"></i></div></div></div>
    <div class="col-6 col-md-3 mb-2"><div class="small-box bg-warning mb-0"><div class="inner py-2"><h3 class="mb-0"><?= $kurang ?></h3><p class="mb-0 small">Gizi Kurang</p></div><div class="icon"><i class="fas fa-exclamation-circle"></i></div></div></div>
    <div class="col-6 col-md-3 mb-2"><div class="small-box bg-danger mb-0"><div class="inner py-2"><h3 class="mb-0"><?= $buruk ?></h3><p class="mb-0 small">Gizi Buruk</p></div><div class="icon"><i class="fas fa-times-circle"></i></div></div></div>
    <div class="col-6 col-md-3 mb-2"><div class="small-box bg-info mb-0"><div class="inner py-2"><h3 class="mb-0"><?= $lebih ?></h3><p class="mb-0 small">Gizi Lebih / Obesitas</p></div><div class="icon"><i class="fas fa-plus-circle"></i></div></div></div>
  </div>

  <div class="row mb-3">
    <div class="col-md-5 mb-3">
      <div class="card"><div class="card-header"><h6 class="mb-0">Grafik Status Gizi</h6></div>
        <div class="card-body"><canvas id="chartGizi" height="200"></canvas></div>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-header"><h6 class="mb-0">Detail Status Gizi Balita</h6></div>
    <div class="card-body p-0"><div class="table-responsive">
      <table class="table table-sm table-hover table-striped mb-0">
        <thead class="table-light"><tr>
          <th>No</th><th>No. Peserta</th><th>Nama</th><th>L/P</th><th>Dusun</th>
          <th>Tgl Periksa</th><th>Usia (bln)</th><th>BB</th><th>TB</th><th>Status Gizi</th>
        </tr></thead>
        <tbody>
        <?php if (empty($data)): ?><tr><td colspan="10" class="text-center text-muted py-4">Tidak ada data</td></tr>
        <?php else: foreach ($data as $i => $r): ?>
          <tr>
            <td><?= $i+1 ?></td>
            <td><?= htmlspecialchars($r['nomor_peserta']) ?></td>
            <td><?= htmlspecialchars($r['nama_lengkap']) ?></td>
            <td><?= $r['jenis_kelamin'] ?></td>
            <td><?= htmlspecialchars($r['dusun']??'-') ?></td>
            <td><?= formatTanggal($r['tanggal_pemeriksaan']) ?></td>
            <td><?= $r['umur_saat_periksa'] ?? '-' ?></td>
            <td><?= $r['berat_badan'] ?? '-' ?></td>
            <td><?= $r['tinggi_badan'] ?? '-' ?></td>
            <td>
              <?php $gc=['Normal'=>'success','Kurang'=>'warning','Buruk'=>'danger','Lebih'=>'info','Obesitas'=>'dark']; ?>
              <span class="badge bg-<?= $gc[$r['status_gizi']]??'secondary' ?>"><?= $r['status_gizi'] ?></span>
            </td>
          </tr>
        <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div></div>
  </div>
</div></section>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function(){
  var el = document.getElementById('chartGizi');
  if(el) new Chart(el.getContext('2d'),{type:'doughnut',data:{labels:<?= json_encode($chartLabels) ?>,datasets:[{data:<?= json_encode($chartData) ?>,backgroundColor:['#28a745','#ffc107','#dc3545','#17a2b8','#343a40']}]},options:{responsive:true,plugins:{legend:{position:'bottom'}}});
});
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
