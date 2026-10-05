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

$page_title = 'Laporan Sanitasi';
require_once __DIR__ . '/../../includes/header.php';
$dusun = $_GET['dusun'] ?? '';
$where = "WHERE 1=1";
if ($dusun) $where .= " AND dusun='".escape($dusun)."'";
$data = fetchAll("SELECT * FROM sanitasi $where ORDER BY dusun, nama_kepala");
$jamban = fetchAll("SELECT kepemilikan_jamban, COUNT(*) as jml FROM sanitasi $where GROUP BY kepemilikan_jamban");
$air = fetchAll("SELECT sumber_air, COUNT(*) as jml FROM sanitasi $where GROUP BY sumber_air");
$dusuns = fetchAll("SELECT DISTINCT dusun FROM sanitasi WHERE dusun IS NOT NULL AND dusun!='' ORDER BY dusun");
?>
<section class="content-header">
  <div class="container-fluid"><div class="row mb-2">
    <div class="col-sm-6"><h1><i class="fas fa-toilet me-2 text-primary"></i>Laporan Sanitasi</h1></div>
    <div class="col-sm-6"><ol class="breadcrumb float-sm-end"><li class="breadcrumb-item"><a href="index.php">Laporan</a></li><li class="breadcrumb-item active">Sanitasi</li></ol></div>
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
    <div class="col-md-6">
      <div class="card"><div class="card-header"><h6 class="mb-0">Rekap Kepemilikan Jamban</h6></div>
        <div class="card-body p-0"><table class="table table-sm mb-0"><?php foreach($jamban as $j): ?><tr><td><?= $j['kepemilikan_jamban'] ?></td><td class="text-end"><strong><?= $j['jml'] ?></strong></td></tr><?php endforeach; ?></table></div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card"><div class="card-header"><h6 class="mb-0">Rekap Sumber Air</h6></div>
        <div class="card-body p-0"><table class="table table-sm mb-0"><?php foreach($air as $a): ?><tr><td><?= $a['sumber_air'] ?></td><td class="text-end"><strong><?= $a['jml'] ?></strong></td></tr><?php endforeach; ?></table></div>
      </div>
    </div>
  </div>
  <div class="card"><div class="card-header"><h6 class="mb-0">Detail Sanitasi Keluarga</h6></div>
    <div class="card-body p-0"><div class="table-responsive">
      <table class="table table-sm table-hover mb-0">
        <thead class="table-light"><tr><th>No</th><th>No.KK</th><th>Kepala</th><th>Dusun</th><th>Jamban</th><th>Jenis</th><th>Sumber Air</th><th>Sampah</th><th>Limbah</th></tr></thead>
        <tbody>
        <?php foreach ($data as $i=>$r): ?>
          <tr><td><?= $i+1 ?></td><td><?= htmlspecialchars($r['no_kk']) ?></td><td><?= htmlspecialchars($r['nama_kepala']??'-') ?></td>
          <td><?= htmlspecialchars($r['dusun']??'-') ?></td><td><?= $r['kepemilikan_jamban'] ?></td><td><?= $r['jenis_jamban'] ?></td>
          <td><?= $r['sumber_air'] ?></td><td><?= $r['pengelolaan_sampah'] ?></td><td><?= $r['saluran_limbah'] ?></td></tr>
        <?php endforeach; ?>
        <?php if(empty($data)): ?><tr><td colspan="9" class="text-center text-muted">Tidak ada data</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div></div>
  </div>
</div></section>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
