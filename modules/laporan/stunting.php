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

$page_title = 'Laporan Stunting';
require_once __DIR__ . '/../../includes/header.php';

$tgl_dari = $_GET['tgl_dari'] ?? date('Y-01-01');
$tgl_sampai = $_GET['tgl_sampai'] ?? date('Y-m-d');
$dusun = $_GET['dusun'] ?? '';
$risiko = $_GET['risiko'] ?? '';

$where = ["pb.tanggal_pemeriksaan BETWEEN '".escape($tgl_dari)."' AND '".escape($tgl_sampai)."'"];
if ($dusun !== '') $where[] = "b.dusun = '".escape($dusun)."'";
if ($risiko !== '') $where[] = "pb.risiko_stunting = '".escape($risiko)."'";
else $where[] = "pb.risiko_stunting IN ('Risiko','Stunting')";
$whereSql = implode(' AND ', $where);

$data = fetchAll("
  SELECT b.nomor_peserta, b.nama_lengkap, b.jenis_kelamin, b.tanggal_lahir, b.dusun, b.nama_ibu,
         pb.tanggal_pemeriksaan, pb.berat_badan, pb.tinggi_badan, pb.umur_saat_periksa,
         pb.status_gizi, pb.risiko_stunting
  FROM pemeriksaan_balita pb
  JOIN balita b ON b.id = pb.balita_id
  WHERE $whereSql
  ORDER BY pb.tanggal_pemeriksaan DESC LIMIT 500
");

$jml_stunting = numRows("SELECT pb.id FROM pemeriksaan_balita pb JOIN balita b ON b.id=pb.balita_id WHERE pb.tanggal_pemeriksaan BETWEEN '".escape($tgl_dari)."' AND '".escape($tgl_sampai)."' ".($dusun?" AND b.dusun='".escape($dusun)."'":"")." AND pb.risiko_stunting='Stunting'");
$jml_risiko = numRows("SELECT pb.id FROM pemeriksaan_balita pb JOIN balita b ON b.id=pb.balita_id WHERE pb.tanggal_pemeriksaan BETWEEN '".escape($tgl_dari)."' AND '".escape($tgl_sampai)."' ".($dusun?" AND b.dusun='".escape($dusun)."'":"")." AND pb.risiko_stunting='Risiko'");

// Grafik bulanan
$bulanan = fetchAll("
  SELECT DATE_FORMAT(pb.tanggal_pemeriksaan,'%Y-%m') as bulan,
         SUM(CASE WHEN pb.risiko_stunting='Stunting' THEN 1 ELSE 0 END) as stunting,
         SUM(CASE WHEN pb.risiko_stunting='Risiko' THEN 1 ELSE 0 END) as risiko
  FROM pemeriksaan_balita pb
  JOIN balita b ON b.id = pb.balita_id
  WHERE pb.tanggal_pemeriksaan BETWEEN '".escape($tgl_dari)."' AND '".escape($tgl_sampai)."'
  ".($dusun?" AND b.dusun='".escape($dusun)."'":"")."
  GROUP BY DATE_FORMAT(pb.tanggal_pemeriksaan,'%Y-%m')
  ORDER BY bulan
");
$labels = array_column($bulanan, 'bulan');
$dataStunting = array_map('intval', array_column($bulanan, 'stunting'));
$dataRisiko = array_map('intval', array_column($bulanan, 'risiko'));

$dusunList = fetchAll("SELECT DISTINCT dusun FROM balita WHERE dusun IS NOT NULL AND dusun != '' ORDER BY dusun");
?>
<section class="content-header">
  <div class="container-fluid"><div class="row mb-2">
    <div class="col-sm-6"><h1><i class="fas fa-exclamation-triangle me-2 text-danger"></i>Laporan Stunting</h1></div>
    <div class="col-sm-6"><ol class="breadcrumb float-sm-end">
      <li class="breadcrumb-item"><a href="<?= APP_URL ?>/modules/laporan/index.php">Laporan</a></li>
      <li class="breadcrumb-item active">Stunting</li>
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
      <div class="col-6 col-md-2"><label class="form-label small mb-1">Kategori</label>
        <select name="risiko" class="form-select form-select-sm">
          <option value="">Risiko + Stunting</option>
          <option value="Stunting" <?= $risiko==='Stunting'?'selected':'' ?>>Stunting</option>
          <option value="Risiko" <?= $risiko==='Risiko'?'selected':'' ?>>Risiko</option>
        </select>
      </div>
      <div class="col-12 mt-2">
        <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter me-1"></i>Filter</button>
        <a href="?" class="btn btn-secondary btn-sm">Reset</a>
        <button type="button" class="btn btn-success btn-sm" onclick="window.location='<?= APP_URL ?>/modules/laporan/export.php?type=stunting&'+new URLSearchParams(window.location.search).toString()"><i class="fas fa-file-excel me-1"></i>Excel</button>
        <button type="button" class="btn btn-danger btn-sm" onclick="window.open('<?= APP_URL ?>/modules/laporan/print.php?type=stunting&'+new URLSearchParams(window.location.search).toString(), '_blank')"><i class="fas fa-file-pdf me-1"></i>PDF / Cetak</button>
      </div>
    </form>
  </div></div>

  <div class="row mb-3">
    <div class="col-6 col-md-4 mb-2"><div class="small-box bg-danger mb-0"><div class="inner py-2"><h3 class="mb-0"><?= $jml_stunting ?></h3><p class="mb-0 small">Balita Stunting</p></div><div class="icon"><i class="fas fa-exclamation-triangle"></i></div></div></div>
    <div class="col-6 col-md-4 mb-2"><div class="small-box bg-warning mb-0"><div class="inner py-2"><h3 class="mb-0"><?= $jml_risiko ?></h3><p class="mb-0 small">Risiko Stunting</p></div><div class="icon"><i class="fas fa-exclamation"></i></div></div></div>
    <div class="col-6 col-md-4 mb-2"><div class="small-box bg-secondary mb-0"><div class="inner py-2"><h3 class="mb-0"><?= count($data) ?></h3><p class="mb-0 small">Total Data</p></div><div class="icon"><i class="fas fa-list"></i></div></div></div>
  </div>

  <div class="row mb-3">
    <div class="col-12">
      <div class="card"><div class="card-header"><h6 class="mb-0">Grafik Perkembangan Stunting</h6></div>
        <div class="card-body"><canvas id="chartStunting" height="120"></canvas></div>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-header"><h6 class="mb-0">Daftar Balita Stunting / Risiko</h6></div>
    <div class="card-body p-0"><div class="table-responsive">
      <table class="table table-sm table-hover table-striped mb-0">
        <thead class="table-light"><tr>
          <th>No</th><th>No. Peserta</th><th>Nama</th><th>L/P</th><th>Usia (bln)</th><th>Dusun</th>
          <th>Tgl Periksa</th><th>BB</th><th>TB</th><th>Status Gizi</th><th>Risiko</th>
        </tr></thead>
        <tbody>
        <?php if (empty($data)): ?><tr><td colspan="11" class="text-center text-muted py-4">Tidak ada data</td></tr>
        <?php else: foreach ($data as $i => $r): ?>
          <tr>
            <td><?= $i+1 ?></td>
            <td><?= htmlspecialchars($r['nomor_peserta']) ?></td>
            <td><?= htmlspecialchars($r['nama_lengkap']) ?></td>
            <td><?= $r['jenis_kelamin'] ?></td>
            <td><?= $r['umur_saat_periksa'] ?? hitungUmurBulan($r['tanggal_lahir']) ?></td>
            <td><?= htmlspecialchars($r['dusun']??'-') ?></td>
            <td><?= formatTanggal($r['tanggal_pemeriksaan']) ?></td>
            <td><?= $r['berat_badan'] ?? '-' ?></td>
            <td><?= $r['tinggi_badan'] ?? '-' ?></td>
            <td><?= $r['status_gizi'] ?></td>
            <td><span class="badge bg-<?= $r['risiko_stunting']==='Stunting'?'danger':'warning' ?>"><?= $r['risiko_stunting'] ?></span></td>
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
  var el = document.getElementById('chartStunting');
  if(el) new Chart(el.getContext('2d'),{type:'line',data:{labels:<?= json_encode($labels) ?>,datasets:[
    {label:'Stunting',data:<?= json_encode($dataStunting) ?>,borderColor:'#dc3545',backgroundColor:'rgba(220,53,69,0.2)',fill:true,tension:0.3},
    {label:'Risiko',data:<?= json_encode($dataRisiko) ?>,borderColor:'#ffc107',backgroundColor:'rgba(255,193,7,0.2)',fill:true,tension:0.3}
  ]},options:{responsive:true,scales:{y:{beginAtZero:true,ticks:{stepSize:1}}}});
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
