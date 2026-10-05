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

$page_title = 'Laporan KB';
require_once __DIR__ . '/../../includes/header.php';
$dusun = $_GET['dusun'] ?? '';
$where = "WHERE status='Aktif'";
if ($dusun) $where .= " AND no_kk IN (SELECT no_kk FROM keluarga WHERE dusun='".escape($dusun)."')";
$data = fetchAll("SELECT * FROM kb $where ORDER BY tanggal_pelayanan DESC");
$rekap = fetchAll("SELECT jenis_kontrasepsi, COUNT(*) as jml FROM kb $where GROUP BY jenis_kontrasepsi");
$dusuns = fetchAll("SELECT DISTINCT dusun FROM keluarga WHERE dusun IS NOT NULL AND dusun!='' ORDER BY dusun");
?>
<section class="content-header">
  <div class="container-fluid"><div class="row mb-2">
    <div class="col-sm-6"><h1><i class="fas fa-pills me-2 text-primary"></i>Laporan Keluarga Berencana</h1></div>
    <div class="col-sm-6"><ol class="breadcrumb float-sm-end"><li class="breadcrumb-item"><a href="index.php">Laporan</a></li><li class="breadcrumb-item active">KB</li></ol></div>
  </div></div>
</section>
<section class="content"><div class="container-fluid">
  <div class="card mb-3 no-print"><div class="card-body">
    <form method="GET" class="row g-2 align-items-end">
      <div class="col-md-3"><label class="form-label small">Dusun</label>
        <select name="dusun" class="form-select form-select-sm"><option value="">Semua</option>
        <?php foreach ($dusuns as $d): ?><option value="<?= htmlspecialchars($d['dusun']) ?>" <?= $dusun==$d['dusun']?'selected':'' ?>><?= htmlspecialchars($d['dusun']) ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="col-auto"><button class="btn btn-primary btn-sm"><i class="fas fa-filter me-1"></i>Filter</button>
        <button type="button" class="btn btn-secondary btn-sm" onclick="window.print()"><i class="fas fa-print me-1"></i>Cetak</button>
      </div>
    </form>
  </div></div>
  <div class="row mb-3">
    <?php foreach ($rekap as $r): ?>
    <div class="col-6 col-md-2 mb-2"><div class="small-box bg-info mb-0"><div class="inner py-2"><h4 class="mb-0"><?= $r['jml'] ?></h4><p class="mb-0 small"><?= $r['jenis_kontrasepsi'] ?></p></div></div></div>
    <?php endforeach; ?>
  </div>
  <div class="card"><div class="card-header"><h6 class="mb-0">Daftar Peserta KB Aktif</h6></div>
    <div class="card-body p-0"><div class="table-responsive">
      <table class="table table-sm table-hover mb-0">
        <thead class="table-light"><tr><th>No</th><th>Nama</th><th>NIK</th><th>Jenis KB</th><th>Tgl Pelayanan</th><th>Kontrol</th></tr></thead>
        <tbody>
        <?php foreach ($data as $i=>$r): ?>
          <tr><td><?= $i+1 ?></td><td><?= htmlspecialchars($r['nama']) ?></td><td><?= htmlspecialchars($r['nik']??'-') ?></td>
          <td><?= $r['jenis_kontrasepsi'] ?></td><td><?= formatTanggal($r['tanggal_pelayanan']) ?></td>
          <td><?= $r['tanggal_kontrol']?formatTanggal($r['tanggal_kontrol']):'-' ?></td></tr>
        <?php endforeach; ?>
        <?php if(empty($data)): ?><tr><td colspan="6" class="text-center text-muted">Tidak ada data</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div></div>
  </div>
</div></section>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
