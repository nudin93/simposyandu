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

$page_title = 'Laporan Lansia';
require_once __DIR__ . '/../../includes/header.php';

$tgl_dari   = $_GET['tgl_dari'] ?? date('Y-m-01');
$tgl_sampai = $_GET['tgl_sampai'] ?? date('Y-m-d');
$cari       = trim($_GET['cari'] ?? '');

$where = ["l.status_aktif=1"];
if ($cari !== '') $where[] = "(l.nama LIKE '%".escape($cari)."%' OR l.nomor_peserta LIKE '%".escape($cari)."%')";
$whereSql = implode(' AND ', $where);
$total = numRows("SELECT id FROM lansia l WHERE $whereSql");

$whereP = ["pl.tanggal_pemeriksaan BETWEEN '".escape($tgl_dari)."' AND '".escape($tgl_sampai)."'"];
if ($cari !== '') $whereP[] = "(l.nama LIKE '%".escape($cari)."%' OR l.nomor_peserta LIKE '%".escape($cari)."%')";
$wherePSql = implode(' AND ', $whereP);

$data = fetchAll("
  SELECT l.nomor_peserta, l.nama, l.jenis_kelamin, l.tanggal_lahir,
         pl.tanggal_pemeriksaan, pl.berat_badan, pl.tinggi_badan, pl.tekanan_darah,
         pl.gula_darah, pl.kolesterol, pl.risiko_hipertensi, pl.risiko_diabetes
  FROM pemeriksaan_lansia pl
  JOIN lansia l ON l.id = pl.lansia_id
  WHERE $wherePSql
  ORDER BY pl.tanggal_pemeriksaan DESC LIMIT 500
");

$hipertensi = numRows("SELECT pl.id FROM pemeriksaan_lansia pl JOIN lansia l ON l.id=pl.lansia_id WHERE $wherePSql AND pl.risiko_hipertensi=1");
$diabetes   = numRows("SELECT pl.id FROM pemeriksaan_lansia pl JOIN lansia l ON l.id=pl.lansia_id WHERE $wherePSql AND pl.risiko_diabetes=1");
?>
<section class="content-header">
  <div class="container-fluid"><div class="row mb-2">
    <div class="col-sm-6"><h1><i class="fas fa-user-injured me-2 text-primary"></i>Laporan Lansia</h1></div>
    <div class="col-sm-6"><ol class="breadcrumb float-sm-end">
      <li class="breadcrumb-item"><a href="<?= APP_URL ?>/modules/laporan/index.php">Laporan</a></li>
      <li class="breadcrumb-item active">Lansia</li>
    </ol></div>
  </div></div>
</section>
<section class="content"><div class="container-fluid">
  <div class="card mb-3 no-print"><div class="card-body">
    <form method="GET" class="row g-2 align-items-end">
      <div class="col-6 col-md-3"><label class="form-label small mb-1">Dari</label><input type="date" name="tgl_dari" class="form-control form-control-sm" value="<?= htmlspecialchars($tgl_dari) ?>"></div>
      <div class="col-6 col-md-3"><label class="form-label small mb-1">Sampai</label><input type="date" name="tgl_sampai" class="form-control form-control-sm" value="<?= htmlspecialchars($tgl_sampai) ?>"></div>
      <div class="col-6 col-md-3"><label class="form-label small mb-1">Cari</label><input type="text" name="cari" class="form-control form-control-sm" value="<?= htmlspecialchars($cari) ?>" placeholder="Nama / No"></div>
      <div class="col-12 mt-2">
        <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter me-1"></i>Filter</button>
        <a href="?" class="btn btn-secondary btn-sm">Reset</a>
        <button type="button" class="btn btn-success btn-sm" onclick="window.location='<?= APP_URL ?>/modules/laporan/export.php?type=lansia&'+new URLSearchParams(window.location.search).toString()"><i class="fas fa-file-excel me-1"></i>Excel</button>
        <button type="button" class="btn btn-danger btn-sm" onclick="window.open('<?= APP_URL ?>/modules/laporan/print.php?type=lansia&'+new URLSearchParams(window.location.search).toString(), '_blank')"><i class="fas fa-file-pdf me-1"></i>PDF / Cetak</button>
      </div>
    </form>
  </div></div>

  <div class="row mb-3">
    <div class="col-6 col-md-3 mb-2"><div class="small-box bg-primary mb-0"><div class="inner py-2"><h3 class="mb-0"><?= $total ?></h3><p class="mb-0 small">Lansia Aktif</p></div><div class="icon"><i class="fas fa-user-injured"></i></div></div></div>
    <div class="col-6 col-md-3 mb-2"><div class="small-box bg-info mb-0"><div class="inner py-2"><h3 class="mb-0"><?= count($data) ?></h3><p class="mb-0 small">Pemeriksaan</p></div><div class="icon"><i class="fas fa-stethoscope"></i></div></div></div>
    <div class="col-6 col-md-3 mb-2"><div class="small-box bg-danger mb-0"><div class="inner py-2"><h3 class="mb-0"><?= $hipertensi ?></h3><p class="mb-0 small">Risiko Hipertensi</p></div><div class="icon"><i class="fas fa-heartbeat"></i></div></div></div>
    <div class="col-6 col-md-3 mb-2"><div class="small-box bg-warning mb-0"><div class="inner py-2"><h3 class="mb-0"><?= $diabetes ?></h3><p class="mb-0 small">Risiko Diabetes</p></div><div class="icon"><i class="fas fa-tint"></i></div></div></div>
  </div>

  <div class="card">
    <div class="card-header"><h6 class="mb-0">Riwayat Pemeriksaan Kesehatan Lansia</h6></div>
    <div class="card-body p-0"><div class="table-responsive">
      <table class="table table-sm table-hover table-striped mb-0">
        <thead class="table-light"><tr>
          <th>No</th><th>No. Peserta</th><th>Nama</th><th>L/P</th><th>Umur</th>
          <th>Tgl Periksa</th><th>BB</th><th>TB</th><th>Tekanan Darah</th><th>Gula Darah</th><th>Kolesterol</th>
        </tr></thead>
        <tbody>
        <?php if (empty($data)): ?><tr><td colspan="11" class="text-center text-muted py-4">Tidak ada data</td></tr>
        <?php else: foreach ($data as $i => $r): ?>
          <tr>
            <td><?= $i+1 ?></td>
            <td><?= htmlspecialchars($r['nomor_peserta']) ?></td>
            <td><?= htmlspecialchars($r['nama']) ?></td>
            <td><?= $r['jenis_kelamin'] ?></td>
            <td><?= hitungUmur($r['tanggal_lahir']) ?></td>
            <td><?= formatTanggal($r['tanggal_pemeriksaan']) ?></td>
            <td><?= $r['berat_badan'] ?? '-' ?></td>
            <td><?= $r['tinggi_badan'] ?? '-' ?></td>
            <td><?= htmlspecialchars($r['tekanan_darah']??'-') ?></td>
            <td><?= $r['gula_darah'] ?? '-' ?></td>
            <td><?= $r['kolesterol'] ?? '-' ?></td>
          </tr>
        <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div></div>
  </div>
</div></section>

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
