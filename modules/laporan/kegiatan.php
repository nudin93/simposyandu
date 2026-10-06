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

$page_title = 'Laporan Kegiatan Posyandu';
require_once __DIR__ . '/../../includes/header.php';

$tgl_dari = $_GET['tgl_dari'] ?? date('Y-01-01');
$tgl_sampai = $_GET['tgl_sampai'] ?? date('Y-m-d');
$jenis = $_GET['jenis'] ?? '';
$status = $_GET['status'] ?? '';

$where = ["j.tanggal_kegiatan BETWEEN '".escape($tgl_dari)."' AND '".escape($tgl_sampai)."'"];
if ($jenis !== '') $where[] = "j.jenis_kegiatan = '".escape($jenis)."'";
if ($status !== '') $where[] = "j.status = '".escape($status)."'";
$whereSql = implode(' AND ', $where);

$data = fetchAll("SELECT * FROM jadwal j WHERE $whereSql ORDER BY j.tanggal_kegiatan DESC");

// Estimasi peserta berdasarkan jenis kegiatan di tanggal yang sama (approx)
$rekapJenis = fetchAll("SELECT jenis_kegiatan, COUNT(*) as jml FROM jadwal j WHERE $whereSql GROUP BY jenis_kegiatan");
$chartLabels = array_column($rekapJenis, 'jenis_kegiatan');
$chartData = array_map('intval', array_column($rekapJenis, 'jml'));

$total = count($data);
$selesai = numRows("SELECT id FROM jadwal j WHERE $whereSql AND status='Selesai'");
$terjadwal = numRows("SELECT id FROM jadwal j WHERE $whereSql AND status='Terjadwal'");
?>
<section class="content-header">
  <div class="container-fluid"><div class="row mb-2">
    <div class="col-sm-6"><h1><i class="fas fa-calendar-check me-2 text-secondary"></i>Laporan Kegiatan Posyandu</h1></div>
    <div class="col-sm-6"><ol class="breadcrumb float-sm-end">
      <li class="breadcrumb-item"><a href="<?= APP_URL ?>/modules/laporan/index.php">Laporan</a></li>
      <li class="breadcrumb-item active">Kegiatan</li>
    </ol></div>
  </div></div>
</section>
<section class="content"><div class="container-fluid">
  <div class="card mb-3 no-print"><div class="card-body">
    <form method="GET" class="row g-2 align-items-end">
      <div class="col-6 col-md-2"><label class="form-label small mb-1">Dari</label><input type="date" name="tgl_dari" class="form-control form-control-sm" value="<?= htmlspecialchars($tgl_dari) ?>"></div>
      <div class="col-6 col-md-2"><label class="form-label small mb-1">Sampai</label><input type="date" name="tgl_sampai" class="form-control form-control-sm" value="<?= htmlspecialchars($tgl_sampai) ?>"></div>
      <div class="col-6 col-md-3"><label class="form-label small mb-1">Jenis Kegiatan</label>
        <select name="jenis" class="form-select form-select-sm"><option value="">Semua</option>
        <?php foreach (['Penimbangan Balita','Pemeriksaan Ibu Hamil','Pemeriksaan Lansia','Imunisasi','Penyuluhan','Lainnya'] as $j): ?>
        <option value="<?= $j ?>" <?= $jenis===$j?'selected':'' ?>><?= $j ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="col-6 col-md-2"><label class="form-label small mb-1">Status</label>
        <select name="status" class="form-select form-select-sm"><option value="">Semua</option>
        <?php foreach (['Terjadwal','Berlangsung','Selesai','Dibatalkan'] as $s): ?>
        <option value="<?= $s ?>" <?= $status===$s?'selected':'' ?>><?= $s ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="col-12 mt-2">
        <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter me-1"></i>Filter</button>
        <a href="?" class="btn btn-secondary btn-sm">Reset</a>
        <button type="button" class="btn btn-success btn-sm" onclick="window.location='<?= APP_URL ?>/modules/laporan/export.php?type=kegiatan&'+new URLSearchParams(window.location.search).toString()"><i class="fas fa-file-excel me-1"></i>Excel</button>
        <button type="button" class="btn btn-danger btn-sm" onclick="window.open('<?= APP_URL ?>/modules/laporan/print.php?type=kegiatan&'+new URLSearchParams(window.location.search).toString(), '_blank')"><i class="fas fa-file-pdf me-1"></i>PDF / Cetak</button>
      </div>
    </form>
  </div></div>

  <div class="row mb-3">
    <div class="col-6 col-md-4 mb-2"><div class="small-box bg-secondary mb-0"><div class="inner py-2"><h3 class="mb-0"><?= $total ?></h3><p class="mb-0 small">Total Kegiatan</p></div><div class="icon"><i class="fas fa-calendar"></i></div></div></div>
    <div class="col-6 col-md-4 mb-2"><div class="small-box bg-success mb-0"><div class="inner py-2"><h3 class="mb-0"><?= $selesai ?></h3><p class="mb-0 small">Selesai</p></div><div class="icon"><i class="fas fa-check"></i></div></div></div>
    <div class="col-6 col-md-4 mb-2"><div class="small-box bg-info mb-0"><div class="inner py-2"><h3 class="mb-0"><?= $terjadwal ?></h3><p class="mb-0 small">Terjadwal</p></div><div class="icon"><i class="fas fa-clock"></i></div></div></div>
  </div>

  <div class="row mb-3">
    <div class="col-md-6 mb-3">
      <div class="card"><div class="card-header"><h6 class="mb-0">Distribusi Jenis Kegiatan</h6></div>
        <div class="card-body"><canvas id="chartJenis" height="180"></canvas></div>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-header"><h6 class="mb-0">Daftar Kegiatan Posyandu</h6></div>
    <div class="card-body p-0"><div class="table-responsive">
      <table class="table table-sm table-hover table-striped mb-0">
        <thead class="table-light"><tr>
          <th>No</th><th>Nama Kegiatan</th><th>Tanggal</th><th>Jam</th><th>Lokasi</th>
          <th>Jenis</th><th>PJ</th><th>Status</th>
        </tr></thead>
        <tbody>
        <?php if (empty($data)): ?><tr><td colspan="8" class="text-center text-muted py-4">Tidak ada data</td></tr>
        <?php else: foreach ($data as $i => $r): ?>
          <tr>
            <td><?= $i+1 ?></td>
            <td><?= htmlspecialchars($r['nama_kegiatan']) ?></td>
            <td><?= formatTanggal($r['tanggal_kegiatan']) ?></td>
            <td><?= date('H:i', strtotime($r['jam'])) ?></td>
            <td><?= htmlspecialchars($r['lokasi']??'-') ?></td>
            <td><span class="badge bg-info"><?= $r['jenis_kegiatan'] ?></span></td>
            <td><?= htmlspecialchars($r['penanggung_jawab']??'-') ?></td>
            <td>
              <?php $sc=['Terjadwal'=>'secondary','Berlangsung'=>'success','Selesai'=>'primary','Dibatalkan'=>'danger']; ?>
              <span class="badge bg-<?= $sc[$r['status']]??'secondary' ?>"><?= $r['status'] ?></span>
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
  var el = document.getElementById('chartJenis');
  if(el) new Chart(el.getContext('2d'),{type:'doughnut',data:{labels:<?= json_encode($chartLabels) ?>,datasets:[{data:<?= json_encode($chartData) ?>,backgroundColor:['#3c8dbc','#6f42c1','#28a745','#ffc107','#dc3545','#6c757d']}]},options:{responsive:true,plugins:{legend:{position:'bottom'}}});
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
