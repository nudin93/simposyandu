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

$page_title = 'Laporan Imunisasi';
require_once __DIR__ . '/../../includes/header.php';

$tgl_dari = $_GET['tgl_dari'] ?? date('Y-01-01');
$tgl_sampai = $_GET['tgl_sampai'] ?? date('Y-m-d');
$jenis = $_GET['jenis'] ?? '';
$cari = trim($_GET['cari'] ?? '');
$status_filter = $_GET['status'] ?? ''; // sudah / belum

$where = ["1=1"];
if ($tgl_dari && $tgl_sampai) $where[] = "i.tanggal_imunisasi BETWEEN '".escape($tgl_dari)."' AND '".escape($tgl_sampai)."'";
if ($jenis !== '') $where[] = "i.jenis_imunisasi = '".escape($jenis)."'";
if ($cari !== '') $where[] = "(b.nama_lengkap LIKE '%".escape($cari)."%' OR b.nomor_peserta LIKE '%".escape($cari)."%')";
$whereSql = implode(' AND ', $where);

$data = fetchAll("
  SELECT i.*, b.nama_lengkap, b.nomor_peserta, b.jenis_kelamin, b.tanggal_lahir, b.dusun
  FROM imunisasi i
  JOIN balita b ON b.id = i.balita_id
  WHERE $whereSql
  ORDER BY i.tanggal_imunisasi DESC LIMIT 500
");

$total_imunisasi = count($data);
$balita_sudah = numRows("SELECT DISTINCT balita_id FROM imunisasi");
$balita_aktif = numRows("SELECT id FROM balita WHERE status_aktif=1");
$balita_belum = max(0, $balita_aktif - $balita_sudah);

// Chart jenis imunisasi
$jenisCount = fetchAll("SELECT jenis_imunisasi, COUNT(*) as jml FROM imunisasi i JOIN balita b ON b.id=i.balita_id WHERE $whereSql GROUP BY jenis_imunisasi ORDER BY jml DESC");
$chartLabels = array_column($jenisCount, 'jenis_imunisasi');
$chartData = array_map('intval', array_column($jenisCount, 'jml'));

$jenisList = ['BCG','Polio 1','Polio 2','Polio 3','Polio 4','DPT-HB-Hib 1','DPT-HB-Hib 2','DPT-HB-Hib 3','Campak','MR','Hepatitis B0','Hepatitis B1','Hepatitis B2','Hepatitis B3','PCV','Rotavirus','Lainnya'];
?>
<section class="content-header">
  <div class="container-fluid"><div class="row mb-2">
    <div class="col-sm-6"><h1><i class="fas fa-syringe me-2 text-success"></i>Laporan Imunisasi</h1></div>
    <div class="col-sm-6"><ol class="breadcrumb float-sm-end">
      <li class="breadcrumb-item"><a href="<?= APP_URL ?>/modules/laporan/index.php">Laporan</a></li>
      <li class="breadcrumb-item active">Imunisasi</li>
    </ol></div>
  </div></div>
</section>
<section class="content"><div class="container-fluid">
  <div class="card mb-3 no-print"><div class="card-body">
    <form method="GET" class="row g-2 align-items-end">
      <div class="col-6 col-md-2"><label class="form-label small mb-1">Dari</label><input type="date" name="tgl_dari" class="form-control form-control-sm" value="<?= htmlspecialchars($tgl_dari) ?>"></div>
      <div class="col-6 col-md-2"><label class="form-label small mb-1">Sampai</label><input type="date" name="tgl_sampai" class="form-control form-control-sm" value="<?= htmlspecialchars($tgl_sampai) ?>"></div>
      <div class="col-6 col-md-3"><label class="form-label small mb-1">Jenis Imunisasi</label>
        <select name="jenis" class="form-select form-select-sm"><option value="">Semua</option>
        <?php foreach ($jenisList as $j): ?><option value="<?= $j ?>" <?= $jenis===$j?'selected':'' ?>><?= $j ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="col-6 col-md-3"><label class="form-label small mb-1">Cari</label><input type="text" name="cari" class="form-control form-control-sm" value="<?= htmlspecialchars($cari) ?>" placeholder="Nama / No"></div>
      <div class="col-12 mt-2">
        <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter me-1"></i>Filter</button>
        <a href="?" class="btn btn-secondary btn-sm">Reset</a>
        <button type="button" class="btn btn-success btn-sm" onclick="window.location='<?= APP_URL ?>/modules/laporan/export.php?type=imunisasi&'+new URLSearchParams(window.location.search).toString()"><i class="fas fa-file-excel me-1"></i>Excel</button>
        <button type="button" class="btn btn-danger btn-sm" onclick="window.open('<?= APP_URL ?>/modules/laporan/print.php?type=imunisasi&'+new URLSearchParams(window.location.search).toString(), '_blank')"><i class="fas fa-file-pdf me-1"></i>PDF / Cetak</button>
      </div>
    </form>
  </div></div>

  <div class="row mb-3">
    <div class="col-6 col-md-3 mb-2"><div class="small-box bg-success mb-0"><div class="inner py-2"><h3 class="mb-0"><?= $total_imunisasi ?></h3><p class="mb-0 small">Total Imunisasi</p></div><div class="icon"><i class="fas fa-syringe"></i></div></div></div>
    <div class="col-6 col-md-3 mb-2"><div class="small-box bg-info mb-0"><div class="inner py-2"><h3 class="mb-0"><?= $balita_sudah ?></h3><p class="mb-0 small">Balita Sudah Imunisasi</p></div><div class="icon"><i class="fas fa-check"></i></div></div></div>
    <div class="col-6 col-md-3 mb-2"><div class="small-box bg-warning mb-0"><div class="inner py-2"><h3 class="mb-0"><?= $balita_belum ?></h3><p class="mb-0 small">Belum / Belum Lengkap</p></div><div class="icon"><i class="fas fa-exclamation"></i></div></div></div>
  </div>

  <div class="row mb-3">
    <div class="col-md-6 mb-3">
      <div class="card"><div class="card-header"><h6 class="mb-0">Distribusi Jenis Imunisasi</h6></div>
        <div class="card-body"><canvas id="chartJenis" height="200"></canvas></div>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-header"><h6 class="mb-0">Riwayat Imunisasi</h6></div>
    <div class="card-body p-0"><div class="table-responsive">
      <table class="table table-sm table-hover table-striped mb-0">
        <thead class="table-light"><tr>
          <th>No</th><th>No. Peserta</th><th>Nama Balita</th><th>L/P</th><th>Dusun</th>
          <th>Jenis Imunisasi</th><th>Dosis</th><th>Tanggal</th>
        </tr></thead>
        <tbody>
        <?php if (empty($data)): ?><tr><td colspan="8" class="text-center text-muted py-4">Tidak ada data</td></tr>
        <?php else: foreach ($data as $i => $r): ?>
          <tr>
            <td><?= $i+1 ?></td>
            <td><?= htmlspecialchars($r['nomor_peserta']) ?></td>
            <td><?= htmlspecialchars($r['nama_lengkap']) ?></td>
            <td><?= $r['jenis_kelamin'] ?></td>
            <td><?= htmlspecialchars($r['dusun']??'-') ?></td>
            <td><span class="badge bg-success"><?= htmlspecialchars($r['jenis_imunisasi']) ?></span></td>
            <td><?= htmlspecialchars($r['dosis']??'-') ?></td>
            <td><?= formatTanggal($r['tanggal_imunisasi']) ?></td>
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
  var el = document.getElementById('chartJenis');
  if(el) new Chart(el.getContext('2d'),{type:'bar',data:{labels:<?= json_encode($chartLabels) ?>,datasets:[{label:'Jumlah',data:<?= json_encode($chartData) ?>,backgroundColor:'rgba(40,167,69,0.7)'}]},options:{indexAxis:'y',responsive:true,plugins:{legend:{display:false}},scales:{x:{beginAtZero:true,ticks:{stepSize:1}}}});
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
