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

$page_title = 'Laporan Ibu Hamil';
require_once __DIR__ . '/../../includes/header.php';

$tgl_dari   = $_GET['tgl_dari'] ?? date('Y-m-01');
$tgl_sampai = $_GET['tgl_sampai'] ?? date('Y-m-d');
$dusun      = $_GET['dusun'] ?? '';
$cari       = trim($_GET['cari'] ?? '');
$status_risiko = $_GET['status_risiko'] ?? '';

$where = ["ih.status_aktif=1"];
if ($dusun !== '') $where[] = "ih.dusun = '".escape($dusun)."'";
if ($cari !== '')  $where[] = "(ih.nama LIKE '%".escape($cari)."%' OR ih.nomor_peserta LIKE '%".escape($cari)."%')";
$whereSql = implode(' AND ', $where);

$total_aktif = numRows("SELECT id FROM ibu_hamil ih WHERE $whereSql");

$whereP = ["pih.tanggal_pemeriksaan BETWEEN '".escape($tgl_dari)."' AND '".escape($tgl_sampai)."'"];
if ($dusun !== '') $whereP[] = "ih.dusun = '".escape($dusun)."'";
if ($status_risiko !== '') $whereP[] = "pih.status_risiko = '".escape($status_risiko)."'";
if ($cari !== '') $whereP[] = "(ih.nama LIKE '%".escape($cari)."%' OR ih.nomor_peserta LIKE '%".escape($cari)."%')";
$wherePSql = implode(' AND ', $whereP);

$data = fetchAll("
  SELECT ih.nomor_peserta, ih.nama, ih.hpht, ih.hpl, ih.kehamilan_ke, ih.dusun, ih.no_hp,
         pih.tanggal_pemeriksaan, pih.usia_kandungan, pih.berat_badan, pih.tekanan_darah,
         pih.status_risiko, pih.risiko_kek, pih.risiko_anemia
  FROM pemeriksaan_ibu_hamil pih
  JOIN ibu_hamil ih ON ih.id = pih.ibu_hamil_id
  WHERE $wherePSql
  ORDER BY pih.tanggal_pemeriksaan DESC LIMIT 500
");

// Rekap bulanan
$rekapBulanan = fetchAll("
  SELECT DATE_FORMAT(pih.tanggal_pemeriksaan,'%Y-%m') as bulan, COUNT(*) as jml
  FROM pemeriksaan_ibu_hamil pih
  JOIN ibu_hamil ih ON ih.id = pih.ibu_hamil_id
  WHERE $wherePSql
  GROUP BY DATE_FORMAT(pih.tanggal_pemeriksaan,'%Y-%m')
  ORDER BY bulan
");
$chartLabels = []; $chartData = [];
foreach ($rekapBulanan as $r) { $chartLabels[] = $r['bulan']; $chartData[] = (int)$r['jml']; }

$dusunList = fetchAll("SELECT DISTINCT dusun FROM ibu_hamil WHERE dusun IS NOT NULL AND dusun != '' ORDER BY dusun");
?>
<section class="content-header">
  <div class="container-fluid"><div class="row mb-2">
    <div class="col-sm-6"><h1><i class="fas fa-female me-2" style="color:#6f42c1"></i>Laporan Ibu Hamil</h1></div>
    <div class="col-sm-6"><ol class="breadcrumb float-sm-end">
      <li class="breadcrumb-item"><a href="<?= APP_URL ?>/modules/laporan/index.php">Laporan</a></li>
      <li class="breadcrumb-item active">Ibu Hamil</li>
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
      <div class="col-6 col-md-2"><label class="form-label small mb-1">Status Risiko</label>
        <select name="status_risiko" class="form-select form-select-sm">
          <option value="">Semua</option>
          <?php foreach (['Normal','Risiko Ringan','Risiko Tinggi'] as $s): ?><option value="<?= $s ?>" <?= $status_risiko===$s?'selected':'' ?>><?= $s ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="col-6 col-md-2"><label class="form-label small mb-1">Cari</label><input type="text" name="cari" class="form-control form-control-sm" value="<?= htmlspecialchars($cari) ?>" placeholder="Nama / No"></div>
      <div class="col-12 mt-2">
        <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter me-1"></i>Filter</button>
        <a href="?" class="btn btn-secondary btn-sm">Reset</a>
        <button type="button" class="btn btn-success btn-sm" onclick="window.location='<?= APP_URL ?>/modules/laporan/export.php?type=ibu_hamil&'+new URLSearchParams(window.location.search).toString()"><i class="fas fa-file-excel me-1"></i>Excel</button>
        <button type="button" class="btn btn-danger btn-sm" onclick="window.open('<?= APP_URL ?>/modules/laporan/print.php?type=ibu_hamil&'+new URLSearchParams(window.location.search).toString(), '_blank')"><i class="fas fa-file-pdf me-1"></i>PDF / Cetak</button>
      </div>
    </form>
  </div></div>

  <div class="row mb-3">
    <div class="col-6 col-md-4 mb-2"><div class="small-box bg-purple mb-0" style="background:#6f42c1!important"><div class="inner py-2"><h3 class="mb-0"><?= $total_aktif ?></h3><p class="mb-0 small">Ibu Hamil Aktif</p></div><div class="icon"><i class="fas fa-female"></i></div></div></div>
    <div class="col-6 col-md-4 mb-2"><div class="small-box bg-info mb-0"><div class="inner py-2"><h3 class="mb-0"><?= count($data) ?></h3><p class="mb-0 small">Pemeriksaan Periode</p></div><div class="icon"><i class="fas fa-stethoscope"></i></div></div></div>
  </div>

  <div class="row mb-3">
    <div class="col-md-6 mb-3">
      <div class="card"><div class="card-header"><h6 class="mb-0">Rekap Pemeriksaan Bulanan</h6></div>
        <div class="card-body"><canvas id="chartBulanan" height="180"></canvas></div>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-header"><h6 class="mb-0">Data Pemeriksaan Kehamilan</h6></div>
    <div class="card-body p-0"><div class="table-responsive">
      <table class="table table-sm table-hover table-striped mb-0">
        <thead class="table-light"><tr>
          <th>No</th><th>No. Peserta</th><th>Nama</th><th>Dusun</th><th>Tgl Periksa</th>
          <th>Usia Kandungan (mg)</th><th>BB</th><th>TD</th><th>Kehamilan Ke</th><th>Status Risiko</th>
        </tr></thead>
        <tbody>
        <?php if (empty($data)): ?><tr><td colspan="10" class="text-center text-muted py-4">Tidak ada data</td></tr>
        <?php else: foreach ($data as $i => $r): ?>
          <tr>
            <td><?= $i+1 ?></td>
            <td><?= htmlspecialchars($r['nomor_peserta']) ?></td>
            <td><?= htmlspecialchars($r['nama']) ?></td>
            <td><?= htmlspecialchars($r['dusun']??'-') ?></td>
            <td><?= formatTanggal($r['tanggal_pemeriksaan']) ?></td>
            <td><?= $r['usia_kandungan'] ?? '-' ?></td>
            <td><?= $r['berat_badan'] ?? '-' ?></td>
            <td><?= htmlspecialchars($r['tekanan_darah']??'-') ?></td>
            <td><?= $r['kehamilan_ke'] ?></td>
            <td><span class="badge bg-<?= $r['status_risiko']==='Normal'?'success':($r['status_risiko']==='Risiko Tinggi'?'danger':'warning') ?>"><?= $r['status_risiko'] ?></span></td>
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
  var el = document.getElementById('chartBulanan');
  if(el) new Chart(el.getContext('2d'),{type:'bar',data:{labels:<?= json_encode($chartLabels) ?>,datasets:[{label:'Jumlah Pemeriksaan',data:<?= json_encode($chartData) ?>,backgroundColor:'rgba(111,66,193,0.7)'}]},options:{responsive:true,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true,ticks:{stepSize:1}}}});
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
