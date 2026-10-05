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
 * SIMPOSYANDU - Analitik Prediktif Penyakit Kronis (PTM)
 * Model skor berbasis kriteria klinis Kemenkes / WHO Asia
 * ==========================================================
 */
$page_title = 'Analitik Prediktif PTM';
require_once __DIR__ . '/../../includes/header.php';

/**
 * Parse tekanan darah "120/80" → [sistolik, diastolik]
 */
function parseTD($td) {
    $s = 0; $d = 0;
    if (preg_match('/(\d+)\s*[\/\\\\]\s*(\d+)/', (string)$td, $m)) {
        $s = (int)$m[1]; $d = (int)$m[2];
    } elseif (preg_match('/(\d+)/', (string)$td, $m)) {
        $s = (int)$m[1];
    }
    return [$s, $d];
}

/**
 * Hitung skor risiko kronis individu (0–100) + faktor
 * Model transparan, bukan black-box ML.
 */
function hitungSkorPTM(array $p) {
    $skor = 0;
    $faktor = [];
    $rekomendasi = [];

    $umur = (int)($p['umur'] ?? 0);
    $jk = $p['jenis_kelamin'] ?? 'L';
    $imt = $p['imt'] !== null && $p['imt'] !== '' ? (float)$p['imt'] : null;
    $lp = $p['lingkar_perut'] !== null && $p['lingkar_perut'] !== '' ? (float)$p['lingkar_perut'] : null;
    $gd = $p['gula_darah'] !== null && $p['gula_darah'] !== '' ? (float)$p['gula_darah'] : null;
    $kol = $p['kolesterol'] !== null && $p['kolesterol'] !== '' ? (float)$p['kolesterol'] : null;
    list($sis, $dias) = parseTD($p['tekanan_darah'] ?? '');

    // 1) Usia
    if ($umur >= 60) {
        $skor += 15; $faktor[] = 'Usia ≥60 th (+15)';
    } elseif ($umur >= 45) {
        $skor += 10; $faktor[] = 'Usia ≥45 th (+10)';
    } elseif ($umur >= 35) {
        $skor += 5; $faktor[] = 'Usia ≥35 th (+5)';
    }

    // 2) Tekanan darah (Kemenkes)
    if ($sis >= 160 || $dias >= 100) {
        $skor += 30; $faktor[] = "Hipertensi stadium 2 ({$sis}/{$dias}) (+30)";
        $rekomendasi[] = 'Rujuk faskes untuk manajemen hipertensi';
    } elseif ($sis >= 140 || $dias >= 90) {
        $skor += 25; $faktor[] = "Hipertensi ({$sis}/{$dias}) (+25)";
        $rekomendasi[] = 'Kontrol TD rutin, edukasi diet rendah garam';
    } elseif ($sis >= 130 || $dias >= 85) {
        $skor += 15; $faktor[] = "Pra-hipertensi ({$sis}/{$dias}) (+15)";
        $rekomendasi[] = 'Pantau TD, anjurkan aktivitas fisik';
    }

    // 3) Gula darah (acak / sewaktu sederhana)
    if ($gd !== null) {
        if ($gd >= 200) {
            $skor += 30; $faktor[] = "Gula darah tinggi ({$gd}) (+30)";
            $rekomendasi[] = 'Rujuk konfirmasi diabetes (GDP/HbA1c)';
        } elseif ($gd >= 140) {
            $skor += 20; $faktor[] = "Gula darah meningkat ({$gd}) (+20)";
            $rekomendasi[] = 'Edukasi pola makan, skrining ulang 3 bulan';
        } elseif ($gd >= 100) {
            $skor += 10; $faktor[] = "Gula darah borderline ({$gd}) (+10)";
        }
    }

    // 4) IMT
    if ($imt !== null) {
        if ($imt >= 30) {
            $skor += 20; $faktor[] = "Obesitas IMT {$imt} (+20)";
            $rekomendasi[] = 'Konseling gizi & penurunan berat badan';
        } elseif ($imt >= 25) {
            $skor += 12; $faktor[] = "Overweight IMT {$imt} (+12)";
            $rekomendasi[] = 'Edukasi gizi seimbang';
        } elseif ($imt < 18.5) {
            $skor += 5; $faktor[] = "Kurus IMT {$imt} (+5)";
        }
    }

    // 5) Lingkar perut (Asia: L>90, P>80)
    if ($lp !== null) {
        $batas = ($jk === 'P') ? 80 : 90;
        if ($lp > $batas) {
            $skor += 15; $faktor[] = "Lingkar perut {$lp} cm >{$batas} (+15)";
            $rekomendasi[] = 'Risiko metabolik sentral — kurangi lemak perut';
        }
    }

    // 6) Kolesterol
    if ($kol !== null) {
        if ($kol >= 240) {
            $skor += 15; $faktor[] = "Kolesterol tinggi ({$kol}) (+15)";
            $rekomendasi[] = 'Anjurkan cek lipid lengkap & diet rendah lemak';
        } elseif ($kol >= 200) {
            $skor += 8; $faktor[] = "Kolesterol borderline ({$kol}) (+8)";
        }
    }

    // Flag risiko biner dari DB
    if (!empty($p['risiko_hipertensi']) && $sis < 140) {
        // sudah dicatat tapi TD tidak terparse — tetap hitung
        if ($skor < 25) { $skor += 20; $faktor[] = 'Flag risiko hipertensi (+20)'; }
    }
    if (!empty($p['risiko_diabetes']) && ($gd === null || $gd < 140)) {
        if ($skor < 20) { $skor += 20; $faktor[] = 'Flag risiko diabetes (+20)'; }
    }
    if (!empty($p['risiko_obesitas']) && ($imt === null || $imt < 25)) {
        if ($skor < 12) { $skor += 12; $faktor[] = 'Flag risiko obesitas (+12)'; }
    }

    if ($skor > 100) $skor = 100;

    if ($skor >= 75) {
        $level = 'sangat_tinggi';
        $label = 'Sangat Tinggi';
        $warna = 'danger';
        if (!$rekomendasi) $rekomendasi[] = 'Prioritas rujukan & tindak lanjut intensif';
    } elseif ($skor >= 50) {
        $level = 'tinggi';
        $label = 'Tinggi';
        $warna = 'warning';
        if (!$rekomendasi) $rekomendasi[] = 'Kunjungan kontrol 1–3 bulan';
    } elseif ($skor >= 25) {
        $level = 'sedang';
        $label = 'Sedang';
        $warna = 'info';
        if (!$rekomendasi) $rekomendasi[] = 'Edukasi PHBS & skrining ulang 6 bulan';
    } else {
        $level = 'rendah';
        $label = 'Rendah';
        $warna = 'success';
        if (!$rekomendasi) $rekomendasi[] = 'Pertahankan gaya hidup sehat';
    }

    return [
        'skor' => $skor,
        'level' => $level,
        'label' => $label,
        'warna' => $warna,
        'faktor' => $faktor,
        'rekomendasi' => array_values(array_unique($rekomendasi)),
    ];
}

// ---- Ambil pemeriksaan terakhir tiap individu ----
$rows = [];
try {
    $rows = fetchAll("
        SELECT
            u.id AS up_id,
            u.nama_lengkap,
            u.nik,
            u.jenis_kelamin,
            u.dusun,
            TIMESTAMPDIFF(YEAR, u.tanggal_lahir, CURDATE()) AS umur,
            pd.id AS periksa_id,
            pd.tanggal_pemeriksaan,
            pd.berat_badan,
            pd.tinggi_badan,
            pd.imt,
            pd.lingkar_perut,
            pd.tekanan_darah,
            pd.gula_darah,
            pd.kolesterol,
            pd.risiko_hipertensi,
            pd.risiko_diabetes,
            pd.risiko_obesitas
        FROM usia_produktif u
        INNER JOIN (
            SELECT usia_produktif_id, MAX(id) AS max_id
            FROM pemeriksaan_dewasa
            GROUP BY usia_produktif_id
        ) x ON x.usia_produktif_id = u.id
        INNER JOIN pemeriksaan_dewasa pd ON pd.id = x.max_id
        WHERE (u.status_aktif = 1 OR u.status_aktif IS NULL)
        ORDER BY pd.tanggal_pemeriksaan DESC
    ") ?: [];
} catch (Throwable $e) {
    $rows = [];
}

// Skor + tren (bandingkan dengan pemeriksaan sebelumnya jika ada)
$hasil = [];
$dist = ['rendah' => 0, 'sedang' => 0, 'tinggi' => 0, 'sangat_tinggi' => 0];
$sum_skor = 0;

foreach ($rows as $r) {
    $score = hitungSkorPTM($r);

    // Tren: ambil 1 pemeriksaan sebelumnya
    $prev = null;
    try {
        $prev = fetchOne("SELECT * FROM pemeriksaan_dewasa
            WHERE usia_produktif_id = " . (int)$r['up_id'] . "
              AND id < " . (int)$r['periksa_id'] . "
            ORDER BY id DESC LIMIT 1");
    } catch (Throwable $e) {}

    $tren = 'stabil';
    $tren_label = 'Stabil';
    $delta = 0;
    if ($prev) {
        $prevData = array_merge($r, $prev);
        $prevData['umur'] = $r['umur'];
        $prevData['jenis_kelamin'] = $r['jenis_kelamin'];
        $prevScore = hitungSkorPTM($prevData);
        $delta = $score['skor'] - $prevScore['skor'];
        if ($delta >= 10) { $tren = 'naik'; $tren_label = 'Meningkat'; }
        elseif ($delta <= -10) { $tren = 'turun'; $tren_label = 'Menurun'; }
    }

    $item = array_merge($r, [
        'skor' => $score['skor'],
        'level' => $score['level'],
        'label' => $score['label'],
        'warna' => $score['warna'],
        'faktor' => $score['faktor'],
        'rekomendasi' => $score['rekomendasi'],
        'tren' => $tren,
        'tren_label' => $tren_label,
        'delta' => $delta,
    ]);
    $hasil[] = $item;
    $dist[$score['level']]++;
    $sum_skor += $score['skor'];
}

usort($hasil, function ($a, $b) {
    if ($b['skor'] !== $a['skor']) return $b['skor'] <=> $a['skor'];
    return strcmp($a['nama_lengkap'], $b['nama_lengkap']);
});

$total = count($hasil);
$avg = $total > 0 ? round($sum_skor / $total, 1) : 0;
$prioritas = array_values(array_filter($hasil, fn($h) => in_array($h['level'], ['tinggi', 'sangat_tinggi'])));
$escalating = array_values(array_filter($hasil, fn($h) => $h['tren'] === 'naik'));
?>
<section class="content-header">
  <div class="container-fluid"><div class="row mb-2">
    <div class="col-sm-6">
      <h1><i class="fas fa-brain me-2 text-purple" style="color:#6f42c1"></i>Analitik Prediktif Penyakit Kronis</h1>
    </div>
    <div class="col-sm-6">
      <ol class="breadcrumb float-sm-end">
        <li class="breadcrumb-item"><a href="<?= APP_URL ?>/dashboard.php">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="<?= APP_URL ?>/modules/statistik/index.php">Statistik</a></li>
        <li class="breadcrumb-item active">Prediktif PTM</li>
      </ol>
    </div>
  </div></div>
</section>

<section class="content"><div class="container-fluid">

  <div class="alert alert-light border shadow-sm">
    <strong><i class="fas fa-info-circle me-1 text-primary"></i>Model skor klinis (bukan AI black-box)</strong>
    Skor 0–100 disusun dari usia, tekanan darah, gula darah, IMT, lingkar perut, dan kolesterol
    sesuai ambang Kemenkes/WHO Asia. Digunakan untuk <em>stratifikasi risiko</em> dan prioritas tindak lanjut Posyandu.
  </div>

  <!-- KPI -->
  <div class="row g-2 mb-3">
    <div class="col-6 col-md-3">
      <div class="card border-0 shadow-sm"><div class="card-body py-3 text-center">
        <div class="fs-3 fw-bold text-primary"><?= number_format($total) ?></div>
        <small class="text-muted">Individu teranalisis</small>
      </div></div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card border-0 shadow-sm"><div class="card-body py-3 text-center">
        <div class="fs-3 fw-bold"><?= $avg ?></div>
        <small class="text-muted">Rata-rata skor risiko</small>
      </div></div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card border-0 shadow-sm"><div class="card-body py-3 text-center">
        <div class="fs-3 fw-bold text-danger"><?= number_format(count($prioritas)) ?></div>
        <small class="text-muted">Prioritas (tinggi+)</small>
      </div></div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card border-0 shadow-sm"><div class="card-body py-3 text-center">
        <div class="fs-3 fw-bold text-warning"><?= number_format(count($escalating)) ?></div>
        <small class="text-muted">Tren risiko meningkat</small>
      </div></div>
    </div>
  </div>

  <div class="row mb-3">
    <!-- Distribusi -->
    <div class="col-md-4 mb-3">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-header bg-white"><strong><i class="fas fa-chart-pie me-1"></i>Distribusi Risiko</strong></div>
        <div class="card-body">
          <canvas id="chartDist" height="220"></canvas>
          <ul class="list-unstyled mb-0 mt-3 small">
            <li class="d-flex justify-content-between py-1"><span class="badge bg-success">Rendah</span><strong><?= $dist['rendah'] ?></strong></li>
            <li class="d-flex justify-content-between py-1"><span class="badge bg-info">Sedang</span><strong><?= $dist['sedang'] ?></strong></li>
            <li class="d-flex justify-content-between py-1"><span class="badge bg-warning text-dark">Tinggi</span><strong><?= $dist['tinggi'] ?></strong></li>
            <li class="d-flex justify-content-between py-1"><span class="badge bg-danger">Sangat Tinggi</span><strong><?= $dist['sangat_tinggi'] ?></strong></li>
          </ul>
        </div>
      </div>
    </div>

    <!-- Prioritas tindak lanjut -->
    <div class="col-md-8 mb-3">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
          <strong><i class="fas fa-exclamation-triangle me-1 text-danger"></i>Prioritas Tindak Lanjut</strong>
          <span class="badge bg-danger"><?= count($prioritas) ?> orang</span>
        </div>
        <div class="card-body p-0">
          <?php if (empty($prioritas)): ?>
            <p class="text-muted text-center py-4 mb-0">Tidak ada individu berisiko tinggi saat ini.</p>
          <?php else: ?>
          <div class="table-responsive">
            <table class="table table-sm table-hover mb-0 align-middle">
              <thead class="table-light">
                <tr>
                  <th>Nama</th><th>Umur</th><th>Skor</th><th>Level</th><th>Tren</th><th>Rekomendasi utama</th><th></th>
                </tr>
              </thead>
              <tbody>
              <?php foreach (array_slice($prioritas, 0, 10) as $h): ?>
                <tr>
                  <td>
                    <div class="fw-semibold"><?= htmlspecialchars($h['nama_lengkap']) ?></div>
                    <small class="text-muted"><?= htmlspecialchars($h['dusun'] ?: '-') ?></small>
                  </td>
                  <td><?= (int)$h['umur'] ?> th</td>
                  <td><span class="fw-bold"><?= $h['skor'] ?></span></td>
                  <td><span class="badge bg-<?= $h['warna'] ?>"><?= $h['label'] ?></span></td>
                  <td>
                    <?php if ($h['tren'] === 'naik'): ?>
                      <span class="text-danger"><i class="fas fa-arrow-up"></i> +<?= $h['delta'] ?></span>
                    <?php elseif ($h['tren'] === 'turun'): ?>
                      <span class="text-success"><i class="fas fa-arrow-down"></i> <?= $h['delta'] ?></span>
                    <?php else: ?>
                      <span class="text-muted">—</span>
                    <?php endif; ?>
                  </td>
                  <td class="small"><?= htmlspecialchars($h['rekomendasi'][0] ?? '-') ?></td>
                  <td>
                    <a class="btn btn-xs btn-sm btn-outline-primary"
                       href="<?= APP_URL ?>/modules/usia_produktif/detail.php?id=<?= (int)$h['up_id'] ?>">
                      <i class="fas fa-eye"></i>
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Tabel lengkap -->
  <div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2">
      <strong><i class="fas fa-list me-1"></i>Skor Risiko Individu (pemeriksaan terakhir)</strong>
      <div class="d-flex gap-2">
        <select id="filterLevel" class="form-select form-select-sm" style="width:auto">
          <option value="">Semua level</option>
          <option value="sangat_tinggi">Sangat Tinggi</option>
          <option value="tinggi">Tinggi</option>
          <option value="sedang">Sedang</option>
          <option value="rendah">Rendah</option>
        </select>
        <a href="<?= APP_URL ?>/modules/pemeriksaan_dewasa/index.php" class="btn btn-sm btn-outline-danger">Data Skrining</a>
      </div>
    </div>
    <div class="card-body table-responsive">
      <table class="table table-hover align-middle" id="tblPrediktif" style="width:100%">
        <thead class="table-light">
          <tr>
            <th>No</th>
            <th>Nama</th>
            <th>L/P</th>
            <th>Umur</th>
            <th>Tgl Periksa</th>
            <th>TD</th>
            <th>Gula</th>
            <th>IMT</th>
            <th>Skor</th>
            <th>Level</th>
            <th>Tren</th>
            <th>Faktor utama</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
        <?php if (empty($hasil)): ?>
          <tr><td colspan="13" class="text-center text-muted py-4">
            Belum ada data skrining PTM. Daftarkan usia produktif dan lakukan pemeriksaan terlebih dahulu.
          </td></tr>
        <?php else: foreach ($hasil as $i => $h): ?>
          <tr data-level="<?= htmlspecialchars($h['level']) ?>">
            <td><?= $i + 1 ?></td>
            <td>
              <div class="fw-semibold"><?= htmlspecialchars($h['nama_lengkap']) ?></div>
              <small class="text-muted"><?= htmlspecialchars($h['nik'] ?: '-') ?></small>
            </td>
            <td><?= ($h['jenis_kelamin'] ?? '') === 'L' ? 'L' : 'P' ?></td>
            <td><?= (int)$h['umur'] ?></td>
            <td><?= formatTanggal($h['tanggal_pemeriksaan']) ?></td>
            <td><?= htmlspecialchars($h['tekanan_darah'] ?: '-') ?></td>
            <td><?= $h['gula_darah'] !== null ? $h['gula_darah'] : '-' ?></td>
            <td><?= $h['imt'] !== null ? $h['imt'] : '-' ?></td>
            <td>
              <div class="progress" style="height:18px;min-width:70px">
                <div class="progress-bar bg-<?= $h['warna'] ?>" style="width:<?= max(8, $h['skor']) ?>%"><?= $h['skor'] ?></div>
              </div>
            </td>
            <td><span class="badge bg-<?= $h['warna'] ?>"><?= $h['label'] ?></span></td>
            <td>
              <?php if ($h['tren'] === 'naik'): ?>
                <span class="badge bg-danger"><i class="fas fa-arrow-up"></i> Naik</span>
              <?php elseif ($h['tren'] === 'turun'): ?>
                <span class="badge bg-success"><i class="fas fa-arrow-down"></i> Turun</span>
              <?php else: ?>
                <span class="text-muted small">Stabil</span>
              <?php endif; ?>
            </td>
            <td class="small" style="max-width:220px">
              <?= $h['faktor'] ? htmlspecialchars(implode('; ', array_slice($h['faktor'], 0, 2))) : '-' ?>
            </td>
            <td class="text-nowrap">
              <button type="button" class="btn btn-sm btn-outline-secondary btn-detail-skor"
                data-nama="<?= htmlspecialchars($h['nama_lengkap'], ENT_QUOTES) ?>"
                data-skor="<?= (int)$h['skor'] ?>"
                data-label="<?= htmlspecialchars($h['label'], ENT_QUOTES) ?>"
                data-faktor="<?= htmlspecialchars(implode("\n", $h['faktor']), ENT_QUOTES) ?>"
                data-rek="<?= htmlspecialchars(implode("\n", $h['rekomendasi']), ENT_QUOTES) ?>">
                <i class="fas fa-search-plus"></i>
              </button>
              <a href="<?= APP_URL ?>/modules/usia_produktif/detail.php?id=<?= (int)$h['up_id'] ?>" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-user"></i>
              </a>
            </td>
          </tr>
        <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="card border-0 shadow-sm mt-3">
    <div class="card-header bg-white"><strong><i class="fas fa-book me-1"></i>Keterangan Model Skor</strong></div>
    <div class="card-body small">
      <div class="row">
        <div class="col-md-6">
          <ul class="mb-0">
            <li><strong>Usia:</strong> ≥35 (+5), ≥45 (+10), ≥60 (+15)</li>
            <li><strong>Tekanan darah:</strong> pra-HT (+15), HT (+25), stadium 2 (+30)</li>
            <li><strong>Gula darah:</strong> 100–139 (+10), 140–199 (+20), ≥200 (+30)</li>
          </ul>
        </div>
        <div class="col-md-6">
          <ul class="mb-0">
            <li><strong>IMT:</strong> overweight ≥25 (+12), obesitas ≥30 (+20)</li>
            <li><strong>Lingkar perut:</strong> L&gt;90 / P&gt;80 cm (+15)</li>
            <li><strong>Kolesterol:</strong> ≥200 (+8), ≥240 (+15)</li>
          </ul>
        </div>
      </div>
      <p class="text-muted mb-0 mt-2">
        Level: Rendah 0–24 · Sedang 25–49 · Tinggi 50–74 · Sangat Tinggi 75–100.
        Tren dihitung jika ada ≥2 pemeriksaan (perubahan skor ±10 poin).
      </p>
    </div>
  </div>

</div></section>

<?php
$__dist = json_encode([
    (int)$dist['rendah'],
    (int)$dist['sedang'],
    (int)$dist['tinggi'],
    (int)$dist['sangat_tinggi'],
]);
$extra_js = <<<JS
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
\$(function(){
  var dist = {$__dist};
  if (document.getElementById('chartDist')) {
    new Chart(document.getElementById('chartDist'), {
      type: 'doughnut',
      data: {
        labels: ['Rendah','Sedang','Tinggi','Sangat Tinggi'],
        datasets: [{
          data: dist,
          backgroundColor: ['#28a745','#17a2b8','#ffc107','#dc3545'],
          borderWidth: 2,
          borderColor: '#fff'
        }]
      },
      options: {
        responsive: true,
        plugins: { legend: { display: false } }
      }
    });
  }

  \$('#filterLevel').on('change', function(){
    var v = \$(this).val();
    \$('#tblPrediktif tbody tr').each(function(){
      if (!v || \$(this).data('level') === v) \$(this).show();
      else \$(this).hide();
    });
  });

  \$(document).on('click', '.btn-detail-skor', function(){
    var nama = \$(this).data('nama');
    var skor = \$(this).data('skor');
    var label = \$(this).data('label');
    var faktor = String(\$(this).data('faktor') || '-').split('\\n').filter(Boolean);
    var rek = String(\$(this).data('rek') || '-').split('\\n').filter(Boolean);
    var html = '<p class="mb-2">Skor: <strong>' + skor + '</strong> — <span class="badge bg-secondary">' + label + '</span></p>';
    html += '<p class="fw-semibold mb-1">Faktor skor:</p><ul class="text-start small">';
    faktor.forEach(function(f){ html += '<li>' + \$('<div>').text(f).html() + '</li>'; });
    html += '</ul><p class="fw-semibold mb-1">Rekomendasi:</p><ul class="text-start small">';
    rek.forEach(function(f){ html += '<li>' + \$('<div>').text(f).html() + '</li>'; });
    html += '</ul>';
    Swal.fire({ title: nama, html: html, width: 480 });
  });
});
</script>
JS;
include __DIR__ . '/../../includes/footer.php';
?>
