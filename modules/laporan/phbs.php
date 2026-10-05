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

$page_title = 'Laporan PHBS';
require_once __DIR__ . '/../../includes/header.php';
$dusun = $_GET['dusun'] ?? '';
$where = "WHERE 1=1";
if ($dusun) $where .= " AND dusun='".escape($dusun)."'";
$data = fetchAll("SELECT * FROM phbs $where ORDER BY skor_phbs DESC, dusun");
$dusuns = fetchAll("SELECT DISTINCT dusun FROM phbs WHERE dusun IS NOT NULL AND dusun!='' ORDER BY dusun");
$avg = fetchOne("SELECT AVG(skor_phbs) as avg_skor FROM phbs $where");
?>
<section class="content-header">
  <div class="container-fluid"><div class="row mb-2">
    <div class="col-sm-6"><h1><i class="fas fa-hands-wash me-2 text-primary"></i>Laporan PHBS</h1></div>
    <div class="col-sm-6"><ol class="breadcrumb float-sm-end"><li class="breadcrumb-item"><a href="index.php">Laporan</a></li><li class="breadcrumb-item active">PHBS</li></ol></div>
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
  <div class="alert alert-info">Rata-rata skor PHBS: <strong><?= number_format($avg['avg_skor']??0,1) ?>/8</strong> | Total keluarga dipantau: <strong><?= count($data) ?></strong></div>
  <div class="card"><div class="card-header"><h6 class="mb-0">Detail PHBS per Keluarga</h6></div>
    <div class="card-body p-0"><div class="table-responsive">
      <table class="table table-sm table-hover mb-0">
        <thead class="table-light"><tr><th>No</th><th>No.KK</th><th>Kepala</th><th>Dusun</th><th>Cuci Tangan</th><th>Jamban</th><th>Sampah</th><th>Limbah</th><th>Asap Rokok</th><th>Skor</th></tr></thead>
        <tbody>
        <?php foreach ($data as $i=>$r): ?>
          <tr><td><?= $i+1 ?></td><td><?= htmlspecialchars($r['no_kk']) ?></td><td><?= htmlspecialchars($r['nama_kepala']??'-') ?></td>
          <td><?= htmlspecialchars($r['dusun']??'-') ?></td><td><?= $r['cuci_tangan'] ?></td><td><?= $r['menggunakan_jamban'] ?></td>
          <td><?= $r['pengelolaan_sampah'] ?></td><td><?= $r['pengelolaan_limbah'] ?></td><td><?= $r['bebas_asap_rokok'] ?></td>
          <td><span class="badge bg-<?= $r['skor_phbs']>=6?'success':($r['skor_phbs']>=4?'warning':'danger') ?>"><?= $r['skor_phbs'] ?>/8</span></td></tr>
        <?php endforeach; ?>
        <?php if(empty($data)): ?><tr><td colspan="10" class="text-center text-muted">Tidak ada data</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div></div>
  </div>
</div></section>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
