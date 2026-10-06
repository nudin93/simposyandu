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

require_once __DIR__ . '/_init_public.php';
$stats = public_stats();
$page_title = 'Statistik · ' . $nama_posyandu;
$max = max(1, $stats['sasaran']);
$items = [
    ['Bayi', $stats['bayi'], '#0891b2'],
    ['Balita', $stats['balita'], '#059669'],
    ['Ibu Hamil', $stats['bumil'], '#db2777'],
    ['Remaja', $stats['remaja'], '#7c3aed'],
    ['Usia Produktif', $stats['usia_produktif'], '#0284c7'],
    ['Lansia', $stats['lansia'], '#d97706'],
];
include __DIR__ . '/_layout_top.php';
?>
<section class="section">
  <div class="container">
    <h1 class="section-title">Statistik Sasaran Posyandu</h1>
    <p class="text-muted small mb-4">Data agregat (jumlah) dari sistem Posyandu. Tidak menampilkan identitas individu.</p>

    <div class="row g-3 mb-4">
      <div class="col-6 col-md-3">
        <div class="panel panel-b text-center">
          <div class="text-muted small">Total Sasaran</div>
          <div class="fs-3 fw-bold" style="color:#0f766e"><?= number_format($stats['sasaran']) ?></div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="panel panel-b text-center">
          <div class="text-muted small">Jadwal aktif</div>
          <div class="fs-3 fw-bold"><?= number_format($stats['jadwal_aktif']) ?></div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="panel panel-b text-center">
          <div class="text-muted small">Skrining PTM</div>
          <div class="fs-3 fw-bold"><?= number_format($stats['skrining_ptm']) ?></div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="panel panel-b text-center">
          <div class="text-muted small">Data Sanitasi KK</div>
          <div class="fs-3 fw-bold"><?= number_format($stats['sanitasi']) ?></div>
        </div>
      </div>
    </div>

    <div class="row g-4">
      <div class="col-lg-7">
        <div class="panel">
          <div class="panel-h">Komposisi sasaran</div>
          <div class="panel-b">
            <?php foreach ($items as [$label, $val, $color]):
              $pct = round(($val / $max) * 100, 1);
            ?>
            <div class="bar-wrap">
              <div class="bar-label">
                <span><?= htmlspecialchars($label) ?></span>
                <span><strong><?= number_format($val) ?></strong> <span class="text-muted">(<?= $pct ?>%)</span></span>
              </div>
              <div class="bar-track"><div class="bar-fill" style="width:<?= min(100, $pct) ?>%;background:<?= $color ?>"></div></div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
      <div class="col-lg-5">
        <div class="panel mb-3">
          <div class="panel-h">Sanitasi lingkungan</div>
          <div class="panel-b">
            <div class="d-flex justify-content-between py-2 border-bottom">
              <span>KK terdata</span><strong><?= number_format($stats['sanitasi']) ?></strong>
            </div>
            <div class="d-flex justify-content-between py-2 border-bottom">
              <span>Jamban sendiri</span><strong class="text-success"><?= number_format($stats['jamban_sendiri']) ?></strong>
            </div>
            <div class="d-flex justify-content-between py-2">
              <span>Tanpa jamban</span><strong class="text-danger"><?= number_format($stats['tanpa_jamban']) ?></strong>
            </div>
          </div>
        </div>
        <div class="panel">
          <div class="panel-h">Kegiatan</div>
          <div class="panel-b">
            <div class="d-flex justify-content-between py-2">
              <span>Kegiatan posyandu tercatat</span>
              <strong><?= number_format($stats['kegiatan']) ?></strong>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
<?php include __DIR__ . '/_layout_bottom.php'; ?>
