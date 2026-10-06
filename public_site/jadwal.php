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
$page_title = 'Jadwal · ' . $nama_posyandu;
$bulanIndo = [1=>'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
$list = [];
try {
    $list = fetchAll("SELECT * FROM jadwal
        WHERE status IN ('Terjadwal','Berlangsung') AND tanggal_kegiatan >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
        ORDER BY tanggal_kegiatan ASC, jam ASC LIMIT 30") ?: [];
} catch (Throwable $e) {}
include __DIR__ . '/_layout_top.php';
?>
<section class="section">
  <div class="container">
    <h1 class="section-title">Jadwal Kegiatan Posyandu</h1>
    <p class="text-muted small mb-4">Jadwal yang berstatus terjadwal / berlangsung. Pastikan hadir tepat waktu.</p>
    <div class="panel">
      <div class="panel-b">
        <?php if (empty($list)): ?>
          <p class="text-muted mb-0">Belum ada jadwal dipublikasikan.</p>
        <?php else: foreach ($list as $j):
          $tgl = $j['tanggal_kegiatan'] ?? '';
          $ts = $tgl ? strtotime($tgl) : false;
          $d = $ts ? date('d', $ts) : '-';
          $m = $ts ? ($bulanIndo[(int)date('n', $ts)] ?? '') : '';
          $y = $ts ? date('Y', $ts) : '';
        ?>
        <div class="jadwal-item">
          <div class="jadwal-date" style="min-width:72px">
            <div class="d"><?= $d ?></div>
            <div class="m"><?= htmlspecialchars(substr($m, 0, 3)) ?></div>
          </div>
          <div>
            <div class="fw-semibold"><?= htmlspecialchars($j['nama_kegiatan'] ?? 'Kegiatan') ?></div>
            <div class="small text-muted mb-1">
              <?= $ts ? ($d . ' ' . $m . ' ' . $y) : '-' ?>
              <?php if (!empty($j['jam'])): ?> · <?= htmlspecialchars(substr($j['jam'], 0, 5)) ?> WIB<?php endif; ?>
            </div>
            <div class="small"><i class="fas fa-map-marker-alt me-1 text-danger"></i><?= htmlspecialchars($j['lokasi'] ?? '-') ?></div>
            <?php if (!empty($j['penanggung_jawab'])): ?>
              <div class="small text-muted"><i class="fas fa-user-nurse me-1"></i><?= htmlspecialchars($j['penanggung_jawab']) ?></div>
            <?php endif; ?>
            <?php if (!empty($j['jenis_kegiatan'])): ?>
              <span class="badge mt-1" style="background:#ccfbf1;color:#115e59"><?= htmlspecialchars($j['jenis_kegiatan']) ?></span>
            <?php endif; ?>
          </div>
        </div>
        <?php endforeach; endif; ?>
      </div>
    </div>
  </div>
</section>
<?php include __DIR__ . '/_layout_bottom.php'; ?>
