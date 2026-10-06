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

$page_title = 'Statistik Posyandu';
require_once __DIR__ . '/../../includes/header.php';

$total_balita = numRows("SELECT id FROM balita WHERE status_aktif=1");
$total_ibu_hamil = numRows("SELECT id FROM ibu_hamil WHERE status_aktif=1");
$total_lansia = numRows("SELECT id FROM lansia WHERE status_aktif=1");
$total_stunting = numRows("SELECT pb.id FROM pemeriksaan_balita pb WHERE pb.risiko_stunting='Stunting' AND pb.tanggal_pemeriksaan >= DATE_SUB(NOW(), INTERVAL 3 MONTH)");
$total_risiko_stunting = numRows("SELECT pb.id FROM pemeriksaan_balita pb WHERE pb.risiko_stunting IN ('Risiko','Stunting') AND pb.tanggal_pemeriksaan >= DATE_SUB(NOW(), INTERVAL 3 MONTH)");
$total_kb = numRows("SELECT id FROM kb WHERE status='Aktif'");
$total_imunisasi = numRows("SELECT id FROM imunisasi");
$jamban_sendiri = numRows("SELECT id FROM sanitasi WHERE kepemilikan_jamban='Jamban Sendiri'");
$jamban_bersama = numRows("SELECT id FROM sanitasi WHERE kepemilikan_jamban='Jamban Bersama'");
$jamban_umum = numRows("SELECT id FROM sanitasi WHERE kepemilikan_jamban='Jamban Umum'");
$tanpa_jamban = numRows("SELECT id FROM sanitasi WHERE kepemilikan_jamban='Tidak Memiliki'");
$air_layak = numRows("SELECT id FROM sanitasi WHERE kondisi_air='Layak'");
$air_tidak = numRows("SELECT id FROM sanitasi WHERE kondisi_air IN ('Tidak Layak','Perlu Perlindungan')");

$gizi_normal = numRows("SELECT id FROM pemeriksaan_balita WHERE status_gizi='Normal' AND tanggal_pemeriksaan >= DATE_SUB(NOW(), INTERVAL 3 MONTH)");
$gizi_kurang = numRows("SELECT id FROM pemeriksaan_balita WHERE status_gizi='Kurang' AND tanggal_pemeriksaan >= DATE_SUB(NOW(), INTERVAL 3 MONTH)");
$gizi_buruk = numRows("SELECT id FROM pemeriksaan_balita WHERE status_gizi='Buruk' AND tanggal_pemeriksaan >= DATE_SUB(NOW(), INTERVAL 3 MONTH)");
$gizi_lebih = numRows("SELECT id FROM pemeriksaan_balita WHERE status_gizi IN ('Lebih','Obesitas') AND tanggal_pemeriksaan >= DATE_SUB(NOW(), INTERVAL 3 MONTH)");

$total_bayi = 0;
try { $total_bayi = numRows("SELECT id FROM bayi WHERE status_aktif=1"); } catch (Throwable $e) {}
$total_remaja = 0;
try { $total_remaja = numRows("SELECT id FROM remaja WHERE status_aktif=1"); } catch (Throwable $e) {}
$total_up = 0;
try { $total_up = numRows("SELECT id FROM usia_produktif WHERE status_aktif=1"); } catch (Throwable $e) {}
$total_kunjungan_bulan = 0;
try { $total_kunjungan_bulan = numRows("SELECT id FROM kunjungan_rumah WHERE MONTH(tanggal_kunjungan)=MONTH(CURDATE()) AND YEAR(tanggal_kunjungan)=YEAR(CURDATE())"); } catch (Throwable $e) {}
$total_kegiatan_bulan = 0;
try { $total_kegiatan_bulan = numRows("SELECT id FROM kegiatan_posyandu WHERE MONTH(tanggal_kegiatan)=MONTH(CURDATE()) AND YEAR(tanggal_kegiatan)=YEAR(CURDATE())"); } catch (Throwable $e) {}
$sasaran_posyandu = $total_balita + $total_ibu_hamil + $total_lansia + $total_bayi + $total_remaja + $total_up;

// Pelayanan per dusun (balita)
$dusun_labels = [];
$dusun_values = [];
try {
  $rows = fetchAll("SELECT IFNULL(NULLIF(TRIM(dusun),''), 'Tidak diketahui') AS dusun, COUNT(*) AS jml
    FROM balita WHERE status_aktif=1 GROUP BY dusun ORDER BY jml DESC LIMIT 8");
  foreach ($rows as $r) {
    $dusun_labels[] = $r['dusun'];
    $dusun_values[] = (int)$r['jml'];
  }
} catch (Throwable $e) {}

// Kunjungan 6 bulan terakhir
$bulan_labels = [];
$bulan_kunjungan = [];
$bulan_kegiatan = [];
$bulan_nama = [1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'Mei',6=>'Jun',7=>'Jul',8=>'Agu',9=>'Sep',10=>'Okt',11=>'Nov',12=>'Des'];
for ($i = 5; $i >= 0; $i--) {
  $ts = strtotime("-$i months");
  $y = date('Y', $ts);
  $m = (int)date('n', $ts);
  $bulan_labels[] = $bulan_nama[$m] . ' ' . $y;
  $jk = 0; $kg = 0;
  try {
    $jk = numRows("SELECT id FROM kunjungan_rumah WHERE YEAR(tanggal_kunjungan)=$y AND MONTH(tanggal_kunjungan)=$m");
  } catch (Throwable $e) {}
  try {
    $kg = numRows("SELECT id FROM kegiatan_posyandu WHERE YEAR(tanggal_kegiatan)=$y AND MONTH(tanggal_kegiatan)=$m");
  } catch (Throwable $e) {}
  $bulan_kunjungan[] = $jk;
  $bulan_kegiatan[] = $kg;
}
?>
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6"><h1><i class="fas fa-chart-pie me-2 text-primary"></i>Statistik Posyandu</h1></div>
    </div>
  </div>
</section>
<section class="content">
<div class="container-fluid">

  <!-- Ringkasan angka -->
  <div class="row">
    <div class="col-6 col-md-3 mb-3">
      <div class="small-box bg-navy mb-0"><div class="inner"><h3><?= number_format($sasaran_posyandu) ?></h3><p>Total Sasaran</p></div></div>
    </div>
    <div class="col-6 col-md-3 mb-3">
      <div class="small-box bg-danger mb-0"><div class="inner"><h3><?= number_format($total_stunting) ?></h3><p>Stunting</p></div></div>
    </div>
    <div class="col-6 col-md-3 mb-3">
      <div class="small-box bg-warning mb-0"><div class="inner"><h3><?= number_format($gizi_kurang + $gizi_buruk) ?></h3><p>Gizi Kurang/Buruk</p></div></div>
    </div>
    <div class="col-6 col-md-3 mb-3">
      <div class="small-box bg-success mb-0"><div class="inner"><h3><?= number_format($total_kegiatan_bulan) ?></h3><p>Kegiatan Bulan Ini</p></div></div>
    </div>
  </div>

  <div class="row">
    <!-- Siklus hidup -->
    <div class="col-md-6 mb-3">
      <div class="card h-100">
        <div class="card-header"><strong><i class="fas fa-users me-1 text-primary"></i> Sasaran per Siklus Hidup</strong></div>
        <div class="card-body chart-box"><canvas id="chartSiklus"></canvas></div>
      </div>
    </div>
    <!-- Status gizi -->
    <div class="col-md-6 mb-3">
      <div class="card h-100">
        <div class="card-header"><strong><i class="fas fa-apple-alt me-1 text-success"></i> Status Gizi Balita (3 bln)</strong></div>
        <div class="card-body chart-box"><canvas id="chartGizi"></canvas></div>
      </div>
    </div>
  </div>

  <div class="row">
    <!-- Stunting -->
    <div class="col-md-4 mb-3">
      <div class="card h-100">
        <div class="card-header"><strong><i class="fas fa-child me-1 text-danger"></i> Risiko Stunting</strong></div>
        <div class="card-body chart-box-sm"><canvas id="chartStunting"></canvas></div>
      </div>
    </div>
    <!-- Sanitasi -->
    <div class="col-md-4 mb-3">
      <div class="card h-100">
        <div class="card-header"><strong><i class="fas fa-toilet me-1 text-info"></i> Kepemilikan Jamban</strong></div>
        <div class="card-body chart-box-sm"><canvas id="chartJamban"></canvas></div>
      </div>
    </div>
    <!-- Air -->
    <div class="col-md-4 mb-3">
      <div class="card h-100">
        <div class="card-header"><strong><i class="fas fa-tint me-1 text-primary"></i> Kondisi Air</strong></div>
        <div class="card-body chart-box-sm"><canvas id="chartAir"></canvas></div>
      </div>
    </div>
  </div>

  <div class="row">
    <!-- Per dusun -->
    <div class="col-md-6 mb-3">
      <div class="card h-100">
        <div class="card-header"><strong><i class="fas fa-map-marker-alt me-1 text-warning"></i> Balita per Dusun</strong></div>
        <div class="card-body">
          <?php if (empty($dusun_labels)): ?>
          <p class="text-muted text-center mb-0 py-4">Belum ada data dusun</p>
          <?php else: ?>
          <div class="chart-box"><canvas id="chartDusun"></canvas></div>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <!-- Tren kegiatan -->
    <div class="col-md-6 mb-3">
      <div class="card h-100">
        <div class="card-header"><strong><i class="fas fa-chart-line me-1 text-success"></i> Tren 6 Bulan Terakhir</strong></div>
        <div class="card-body chart-box"><canvas id="chartTren"></canvas></div>
      </div>
    </div>
  </div>

  <!-- Kartu detail ringkas -->
  <div class="row">
    <div class="col-6 col-md-2 mb-2"><div class="card text-center p-2"><div class="fw-bold text-info fs-4"><?= $total_bayi ?></div><small>Bayi</small></div></div>
    <div class="col-6 col-md-2 mb-2"><div class="card text-center p-2"><div class="fw-bold text-info fs-4"><?= $total_balita ?></div><small>Balita</small></div></div>
    <div class="col-6 col-md-2 mb-2"><div class="card text-center p-2"><div class="fw-bold fs-4" style="color:#6f42c1"><?= $total_ibu_hamil ?></div><small>Ibu Hamil</small></div></div>
    <div class="col-6 col-md-2 mb-2"><div class="card text-center p-2"><div class="fw-bold text-warning fs-4"><?= $total_remaja ?></div><small>Remaja</small></div></div>
    <div class="col-6 col-md-2 mb-2"><div class="card text-center p-2"><div class="fw-bold text-primary fs-4"><?= $total_up ?></div><small>Usia Produktif</small></div></div>
    <div class="col-6 col-md-2 mb-2"><div class="card text-center p-2"><div class="fw-bold text-secondary fs-4"><?= $total_lansia ?></div><small>Lansia</small></div></div>
  </div>
  <div class="row">
    <div class="col-6 col-md-3 mb-2"><div class="card text-center p-2"><div class="fw-bold fs-5"><?= $total_kb ?></div><small>KB Aktif</small></div></div>
    <div class="col-6 col-md-3 mb-2"><div class="card text-center p-2"><div class="fw-bold fs-5"><?= $total_imunisasi ?></div><small>Imunisasi</small></div></div>
    <div class="col-6 col-md-3 mb-2"><div class="card text-center p-2"><div class="fw-bold fs-5"><?= $total_kunjungan_bulan ?></div><small>Kunjungan Rumah bln ini</small></div></div>
    <div class="col-6 col-md-3 mb-2"><div class="card text-center p-2"><div class="fw-bold fs-5"><?= $total_kegiatan_bulan ?></div><small>Kegiatan bln ini</small></div></div>
  </div>

</div>
</section>

<style>
.chart-box { position: relative; height: 240px; max-height: 240px; }
.chart-box-sm { position: relative; height: 200px; max-height: 200px; }
@media (max-width: 767.98px) {
  .chart-box { height: 200px; max-height: 200px; }
  .chart-box-sm { height: 180px; max-height: 180px; }
  .small-box .inner h3 { font-size: 1.4rem; }
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function(){
  const colors = {
    blue: '#3c8dbc', cyan: '#00c0ef', green: '#00a65a', yellow: '#f39c12',
    red: '#dd4b39', purple: '#9b59b6', gray: '#6c757d', teal: '#20c997', navy: '#001f3f'
  };
  Chart.defaults.font.family = "'Inter', system-ui, sans-serif";
  Chart.defaults.font.size = 11;

  // Siklus hidup - doughnut
  new Chart(document.getElementById('chartSiklus'), {
    type: 'doughnut',
    data: {
      labels: ['Bayi','Balita','Ibu Hamil','Remaja','Usia Produktif','Lansia'],
      datasets: [{
        data: [<?= (int)$total_bayi ?>, <?= (int)$total_balita ?>, <?= (int)$total_ibu_hamil ?>, <?= (int)$total_remaja ?>, <?= (int)$total_up ?>, <?= (int)$total_lansia ?>],
        backgroundColor: [colors.cyan, colors.blue, colors.purple, colors.yellow, colors.teal, colors.gray],
        borderWidth: 2,
        borderColor: '#fff'
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { position: 'bottom', labels: { boxWidth: 12, padding: 10 } }
      }
    }
  });

  // Status gizi - pie
  new Chart(document.getElementById('chartGizi'), {
    type: 'pie',
    data: {
      labels: ['Normal','Kurang','Buruk','Lebih/Obesitas'],
      datasets: [{
        data: [<?= (int)$gizi_normal ?>, <?= (int)$gizi_kurang ?>, <?= (int)$gizi_buruk ?>, <?= (int)$gizi_lebih ?>],
        backgroundColor: [colors.green, colors.yellow, colors.red, colors.purple],
        borderWidth: 2,
        borderColor: '#fff'
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { position: 'bottom', labels: { boxWidth: 12, padding: 10 } }
      }
    }
  });

  // Stunting - bar
  const stuntingTidak = Math.max(0, <?= (int)$total_balita ?> - <?= (int)$total_risiko_stunting ?>);
  new Chart(document.getElementById('chartStunting'), {
    type: 'bar',
    data: {
      labels: ['Normal','Risiko/Stunting'],
      datasets: [{
        label: 'Balita',
        data: [stuntingTidak, <?= (int)$total_risiko_stunting ?>],
        backgroundColor: [colors.green, colors.red],
        borderRadius: 6,
        maxBarThickness: 48
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: {
        y: { beginAtZero: true, ticks: { precision: 0 } },
        x: { grid: { display: false } }
      }
    }
  });

  // Jamban
  new Chart(document.getElementById('chartJamban'), {
    type: 'doughnut',
    data: {
      labels: ['Sendiri','Bersama','Umum','Tidak Punya'],
      datasets: [{
        data: [<?= (int)$jamban_sendiri ?>, <?= (int)$jamban_bersama ?>, <?= (int)$jamban_umum ?>, <?= (int)$tanpa_jamban ?>],
        backgroundColor: [colors.green, colors.blue, colors.yellow, colors.red],
        borderWidth: 2,
        borderColor: '#fff'
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, padding: 8, font: { size: 10 } } } }
    }
  });

  // Air
  new Chart(document.getElementById('chartAir'), {
    type: 'doughnut',
    data: {
      labels: ['Layak','Tidak/Perlu'],
      datasets: [{
        data: [<?= (int)$air_layak ?>, <?= (int)$air_tidak ?>],
        backgroundColor: [colors.cyan, colors.gray],
        borderWidth: 2,
        borderColor: '#fff'
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, padding: 8 } } }
    }
  });

  <?php if (!empty($dusun_labels)): ?>
  new Chart(document.getElementById('chartDusun'), {
    type: 'bar',
    data: {
      labels: <?= json_encode($dusun_labels, JSON_UNESCAPED_UNICODE) ?>,
      datasets: [{
        label: 'Balita',
        data: <?= json_encode($dusun_values) ?>,
        backgroundColor: colors.blue,
        borderRadius: 6,
        maxBarThickness: 36
      }]
    },
    options: {
      indexAxis: 'y',
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: {
        x: { beginAtZero: true, ticks: { precision: 0 } },
        y: { grid: { display: false } }
      }
    }
  });
  <?php endif; ?>

  // Tren
  new Chart(document.getElementById('chartTren'), {
    type: 'line',
    data: {
      labels: <?= json_encode($bulan_labels) ?>,
      datasets: [
        {
          label: 'Kunjungan Rumah',
          data: <?= json_encode($bulan_kunjungan) ?>,
          borderColor: colors.red,
          backgroundColor: 'rgba(221,75,57,.15)',
          fill: true,
          tension: 0.3,
          pointRadius: 4
        },
        {
          label: 'Kegiatan Posyandu',
          data: <?= json_encode($bulan_kegiatan) ?>,
          borderColor: colors.green,
          backgroundColor: 'rgba(0,166,90,.12)',
          fill: true,
          tension: 0.3,
          pointRadius: 4
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { position: 'bottom', labels: { boxWidth: 12 } } },
      scales: {
        y: { beginAtZero: true, ticks: { precision: 0 } },
        x: { grid: { display: false } }
      }
    }
  });
})();
</script>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
