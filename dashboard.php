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

$page_title = 'Dashboard';
require_once __DIR__ . '/includes/header.php';
if ((($_SESSION['kader_role'] ?? '') === 'kader')) { echo '<script>location.replace(' . json_encode(APP_URL . '/kader_home.php') . ');</script>'; }

$total_keluarga = 0;
$total_penduduk = 0;
$opensidOk = opensid_available();
if ($opensidOk) {
    $r = opensid_fetchOne("
        SELECT COUNT(*) AS c
        FROM tweb_keluarga k
        INNER JOIN tweb_penduduk p ON p.id = k.nik_kepala AND p.status_dasar = 1
    ");
    $total_keluarga = (int)($r['c'] ?? 0);
    if ($total_keluarga === 0) {
        $r = opensid_fetchOne("
            SELECT COUNT(DISTINCT p.id_kk) AS c
            FROM tweb_penduduk p
            WHERE p.status_dasar = 1 AND p.kk_level = 1 AND p.id_kk > 0
        ");
        $total_keluarga = (int)($r['c'] ?? 0);
    }
    $r = opensid_fetchOne("SELECT COUNT(*) AS c FROM tweb_penduduk WHERE status_dasar = 1");
    $total_penduduk = (int)($r['c'] ?? 0);
}

$pengaturan = getPengaturan();
$nama_desa = $pengaturan['nama_desa'] ?? 'Desa';
$user = currentUser();

// Statistik tambahan dari tabel SIMPOSYANDU (tidak mengubah query lama)
$total_balita = 0; $total_bayi = 0; $total_bumil = 0; $total_lansia = 0;
$total_kader = 0; $total_kegiatan = 0; $total_remaja = 0;
try {
    $r = fetchOne("SELECT COUNT(*) AS c FROM balita WHERE status_aktif = 1 OR status_aktif IS NULL");
    $total_balita = (int)($r['c'] ?? 0);
} catch (Throwable $e) {}
try {
    $r = fetchOne("SELECT COUNT(*) AS c FROM bayi WHERE status_aktif = 1 OR status_aktif IS NULL");
    $total_bayi = (int)($r['c'] ?? 0);
} catch (Throwable $e) {}
try {
    $r = fetchOne("SELECT COUNT(*) AS c FROM ibu_hamil WHERE status_aktif = 1 OR status_aktif IS NULL");
    $total_bumil = (int)($r['c'] ?? 0);
} catch (Throwable $e) {}
try {
    $r = fetchOne("SELECT COUNT(*) AS c FROM lansia WHERE status_aktif = 1 OR status_aktif IS NULL");
    $total_lansia = (int)($r['c'] ?? 0);
} catch (Throwable $e) {}
try {
    $r = fetchOne("SELECT COUNT(*) AS c FROM kader WHERE status = 1");
    $total_kader = (int)($r['c'] ?? 0);
} catch (Throwable $e) {}
try {
    $r = fetchOne("SELECT COUNT(*) AS c FROM kegiatan_posyandu");
    $total_kegiatan = (int)($r['c'] ?? 0);
} catch (Throwable $e) {}
try {
    $r = fetchOne("SELECT COUNT(*) AS c FROM remaja WHERE status_aktif = 1 OR status_aktif IS NULL");
    $total_remaja = (int)($r['c'] ?? 0);
} catch (Throwable $e) {}

// ---- Monitoring PTM (Usia Produktif) ----
$total_up = 0;
$ptm_skrining = 0;
$ptm_ht = 0;
$ptm_dm = 0;
$ptm_ob = 0;
$ptm_bulan_ini = 0;
$ptm_risiko_list = [];
try {
    $r = fetchOne("SELECT COUNT(*) AS c FROM usia_produktif WHERE status_aktif = 1 OR status_aktif IS NULL");
    $total_up = (int)($r['c'] ?? 0);
} catch (Throwable $e) {}
try {
    // Orang yang pernah di-skrining (unique)
    $r = fetchOne("SELECT COUNT(DISTINCT usia_produktif_id) AS c FROM pemeriksaan_dewasa");
    $ptm_skrining = (int)($r['c'] ?? 0);
} catch (Throwable $e) {}
try {
    // Risiko dari pemeriksaan terakhir tiap orang (3 bulan)
    $r = fetchOne("SELECT COUNT(DISTINCT pd.usia_produktif_id) AS c
        FROM pemeriksaan_dewasa pd
        INNER JOIN (
            SELECT usia_produktif_id, MAX(tanggal_pemeriksaan) AS tgl
            FROM pemeriksaan_dewasa
            WHERE tanggal_pemeriksaan >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
            GROUP BY usia_produktif_id
        ) last ON last.usia_produktif_id = pd.usia_produktif_id AND last.tgl = pd.tanggal_pemeriksaan
        WHERE pd.risiko_hipertensi = 1");
    $ptm_ht = (int)($r['c'] ?? 0);
} catch (Throwable $e) {}
try {
    $r = fetchOne("SELECT COUNT(DISTINCT pd.usia_produktif_id) AS c
        FROM pemeriksaan_dewasa pd
        INNER JOIN (
            SELECT usia_produktif_id, MAX(tanggal_pemeriksaan) AS tgl
            FROM pemeriksaan_dewasa
            WHERE tanggal_pemeriksaan >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
            GROUP BY usia_produktif_id
        ) last ON last.usia_produktif_id = pd.usia_produktif_id AND last.tgl = pd.tanggal_pemeriksaan
        WHERE pd.risiko_diabetes = 1");
    $ptm_dm = (int)($r['c'] ?? 0);
} catch (Throwable $e) {}
try {
    $r = fetchOne("SELECT COUNT(DISTINCT pd.usia_produktif_id) AS c
        FROM pemeriksaan_dewasa pd
        INNER JOIN (
            SELECT usia_produktif_id, MAX(tanggal_pemeriksaan) AS tgl
            FROM pemeriksaan_dewasa
            WHERE tanggal_pemeriksaan >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
            GROUP BY usia_produktif_id
        ) last ON last.usia_produktif_id = pd.usia_produktif_id AND last.tgl = pd.tanggal_pemeriksaan
        WHERE pd.risiko_obesitas = 1");
    $ptm_ob = (int)($r['c'] ?? 0);
} catch (Throwable $e) {}
try {
    $r = fetchOne("SELECT COUNT(*) AS c FROM pemeriksaan_dewasa
        WHERE MONTH(tanggal_pemeriksaan)=MONTH(CURDATE()) AND YEAR(tanggal_pemeriksaan)=YEAR(CURDATE())");
    $ptm_bulan_ini = (int)($r['c'] ?? 0);
} catch (Throwable $e) {}
try {
    $ptm_risiko_list = fetchAll("
        SELECT u.id, u.nama_lengkap, u.jenis_kelamin, u.nik,
               TIMESTAMPDIFF(YEAR, u.tanggal_lahir, CURDATE()) AS umur,
               pd.tanggal_pemeriksaan, pd.tekanan_darah, pd.gula_darah, pd.imt,
               pd.risiko_hipertensi, pd.risiko_diabetes, pd.risiko_obesitas
        FROM pemeriksaan_dewasa pd
        INNER JOIN (
            SELECT usia_produktif_id, MAX(id) AS max_id
            FROM pemeriksaan_dewasa
            GROUP BY usia_produktif_id
        ) x ON x.max_id = pd.id
        INNER JOIN usia_produktif u ON u.id = pd.usia_produktif_id
        WHERE (pd.risiko_hipertensi=1 OR pd.risiko_diabetes=1 OR pd.risiko_obesitas=1)
          AND (u.status_aktif=1 OR u.status_aktif IS NULL)
        ORDER BY pd.tanggal_pemeriksaan DESC
        LIMIT 8
    ") ?: [];
} catch (Throwable $e) {
    $ptm_risiko_list = [];
}
$ptm_cakupan = ($total_up > 0) ? round(($ptm_skrining / $total_up) * 100, 1) : 0;
?>
<?php
$__isAdminDash = function_exists('isAdmin') && isAdmin();
$__nama_user = htmlspecialchars($user['nama'] ?? 'Kader');
$__role_user = htmlspecialchars(ucwords(str_replace('_', ' ', $user['role'] ?? 'kader')));
$__hari_id = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
$__bln_id = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
$__tgl_welcome = $__hari_id[(int)date('w')] . ', ' . date('d') . ' ' . $__bln_id[(int)date('n')] . ' ' . date('Y');
?>
<?php if ($__isAdminDash): ?>
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6"><h1><i class="fas fa-home me-2 text-primary"></i>Beranda</h1></div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="<?= APP_URL ?>/dashboard.php">Home</a></li>
          <li class="breadcrumb-item active">Dashboard</li>
        </ol>
      </div>
    </div>
  </div>
</section>
<section class="content">
<div class="container-fluid">

  <!-- Statistik Utama — tampilan ADMIN (klasik) -->
  <div class="row">
    <div class="col-6 col-md-3 col-lg-3 mb-3">
      <div class="small-box bg-success mb-0">
        <div class="inner"><h3><?= number_format($total_penduduk) ?></h3><p>Penduduk</p></div>
        <div class="icon"><i class="fas fa-users"></i></div>
        <a href="<?= APP_URL ?>/modules/penduduk/index.php" class="small-box-footer">Detail <i class="fas fa-arrow-circle-right"></i></a>
      </div>
    </div>
    <div class="col-6 col-md-3 col-lg-3 mb-3">
      <div class="small-box bg-primary mb-0">
        <div class="inner"><h3><?= number_format($total_keluarga) ?></h3><p>Keluarga</p></div>
        <div class="icon"><i class="fas fa-home"></i></div>
        <a href="<?= APP_URL ?>/modules/keluarga/index.php" class="small-box-footer">Detail <i class="fas fa-arrow-circle-right"></i></a>
      </div>
    </div>
    <div class="col-6 col-md-3 col-lg-3 mb-3">
      <div class="small-box bg-info mb-0">
        <div class="inner"><h3><?= number_format($total_balita) ?></h3><p>Balita</p></div>
        <div class="icon"><i class="fas fa-baby"></i></div>
        <a href="<?= APP_URL ?>/modules/balita/index.php" class="small-box-footer">Detail <i class="fas fa-arrow-circle-right"></i></a>
      </div>
    </div>
    <div class="col-6 col-md-3 col-lg-3 mb-3">
      <div class="small-box bg-warning mb-0">
        <div class="inner"><h3><?= number_format($total_bayi) ?></h3><p>Bayi</p></div>
        <div class="icon"><i class="fas fa-child"></i></div>
        <a href="<?= APP_URL ?>/modules/bayi/index.php" class="small-box-footer">Detail <i class="fas fa-arrow-circle-right"></i></a>
      </div>
    </div>
  </div>
  <div class="row">
    <div class="col-6 col-md-3 col-lg-3 mb-3">
      <div class="small-box bg-danger mb-0">
        <div class="inner"><h3><?= number_format($total_bumil) ?></h3><p>Ibu Hamil</p></div>
        <div class="icon"><i class="fas fa-female"></i></div>
        <a href="<?= APP_URL ?>/modules/ibu_hamil/index.php" class="small-box-footer">Detail <i class="fas fa-arrow-circle-right"></i></a>
      </div>
    </div>
    <div class="col-6 col-md-3 col-lg-3 mb-3">
      <div class="small-box bg-secondary mb-0">
        <div class="inner"><h3><?= number_format($total_lansia) ?></h3><p>Lansia</p></div>
        <div class="icon"><i class="fas fa-user-friends"></i></div>
        <a href="<?= APP_URL ?>/modules/lansia/index.php" class="small-box-footer">Detail <i class="fas fa-arrow-circle-right"></i></a>
      </div>
    </div>
    <div class="col-6 col-md-3 col-lg-3 mb-3">
      <div class="small-box bg-teal mb-0" style="background-color:#20c997!important">
        <div class="inner"><h3><?= number_format($total_kader) ?></h3><p>Kader</p></div>
        <div class="icon"><i class="fas fa-user-nurse"></i></div>
        <a href="<?= APP_URL ?>/modules/kader/index.php" class="small-box-footer">Detail <i class="fas fa-arrow-circle-right"></i></a>
      </div>
    </div>
    <div class="col-6 col-md-3 col-lg-3 mb-3">
      <div class="small-box bg-purple mb-0" style="background-color:#6f42c1!important">
        <div class="inner"><h3><?= number_format($total_kegiatan) ?></h3><p>Kegiatan</p></div>
        <div class="icon"><i class="fas fa-calendar-check"></i></div>
        <a href="<?= APP_URL ?>/modules/kegiatan_posyandu/index.php" class="small-box-footer">Detail <i class="fas fa-arrow-circle-right"></i></a>
      </div>
    </div>
  </div>
  <div class="text-center mt-2 mb-3">
    <a href="<?= APP_URL ?>/modules/statistik/index.php" class="btn btn-outline-primary btn-sm">
      <i class="fas fa-chart-pie me-1"></i> Lihat Statistik Posyandu
    </a>
  </div>

<?php else: ?>
<!-- ========== TAMPILAN KADER / BIDAN / KEPALA DESA (modern) ========== -->
<section class="content" style="padding-top:.5rem">
<div class="container-fluid">

  <div class="dash-hero mb-3">
    <div class="dash-hero-inner">
      <div class="dash-hero-text">
        <div class="dash-hero-emoji">☀️</div>
        <h2 class="dash-hero-title">Selamat datang,<br><span><?= $__nama_user ?>!</span> 👋</h2>
        <p class="dash-hero-meta">
          <i class="far fa-calendar-alt me-1"></i><?= $__tgl_welcome ?>
          <span class="mx-2">·</span>
          <i class="far fa-user me-1"></i><?= $__role_user ?>
        </p>
        <div class="dash-hero-actions">
          <a href="<?= APP_URL ?>/modules/pemeriksaan_balita/tambah.php" class="dash-pill dash-pill-light">
            <i class="fas fa-plus-circle"></i> Input Pemeriksaan
          </a>
          <a href="<?= APP_URL ?>/modules/jadwal/index.php" class="dash-pill dash-pill-accent">
            <i class="fas fa-calendar-check"></i> Jadwal Posyandu
          </a>
        </div>
      </div>
      <div class="dash-hero-art" aria-hidden="true">
        <div class="dash-hero-blob"></div>
        <i class="fas fa-clinic-medical"></i>
      </div>
    </div>
  </div>

  <div class="row g-3 mb-3">
    <div class="col-6 col-lg-3">
      <a href="<?= APP_URL ?>/modules/penduduk/index.php" class="text-decoration-none">
        <div class="dash-stat dash-stat-purple">
          <div class="dash-stat-top"><span class="dash-stat-icon"><i class="fas fa-users"></i></span><span class="dash-stat-tag">Penduduk</span></div>
          <div class="dash-stat-num"><?= number_format($total_penduduk) ?></div>
          <div class="dash-stat-desc">Data penduduk aktif</div>
          <div class="dash-stat-bar"></div>
        </div>
      </a>
    </div>
    <div class="col-6 col-lg-3">
      <a href="<?= APP_URL ?>/modules/balita/index.php" class="text-decoration-none">
        <div class="dash-stat dash-stat-green">
          <div class="dash-stat-top"><span class="dash-stat-icon"><i class="fas fa-baby"></i></span><span class="dash-stat-tag">Balita</span></div>
          <div class="dash-stat-num"><?= number_format($total_balita) ?></div>
          <div class="dash-stat-desc">Usia 0–59 bulan</div>
          <div class="dash-stat-bar"></div>
        </div>
      </a>
    </div>
    <div class="col-6 col-lg-3">
      <a href="<?= APP_URL ?>/modules/ibu_hamil/index.php" class="text-decoration-none">
        <div class="dash-stat dash-stat-pink">
          <div class="dash-stat-top"><span class="dash-stat-icon"><i class="fas fa-female"></i></span><span class="dash-stat-tag">Ibu Hamil</span></div>
          <div class="dash-stat-num"><?= number_format($total_bumil) ?></div>
          <div class="dash-stat-desc">Sasaran bumil aktif</div>
          <div class="dash-stat-bar"></div>
        </div>
      </a>
    </div>
    <div class="col-6 col-lg-3">
      <a href="<?= APP_URL ?>/modules/lansia/index.php" class="text-decoration-none">
        <div class="dash-stat dash-stat-orange">
          <div class="dash-stat-top"><span class="dash-stat-icon"><i class="fas fa-user-friends"></i></span><span class="dash-stat-tag">Lansia</span></div>
          <div class="dash-stat-num"><?= number_format($total_lansia) ?></div>
          <div class="dash-stat-desc">Usia ≥ 60 tahun</div>
          <div class="dash-stat-bar"></div>
        </div>
      </a>
    </div>
    <div class="col-6 col-lg-3">
      <a href="<?= APP_URL ?>/modules/bayi/index.php" class="text-decoration-none">
        <div class="dash-stat dash-stat-blue">
          <div class="dash-stat-top"><span class="dash-stat-icon"><i class="fas fa-child"></i></span><span class="dash-stat-tag">Bayi</span></div>
          <div class="dash-stat-num"><?= number_format($total_bayi) ?></div>
          <div class="dash-stat-desc">Data bayi terdaftar</div>
          <div class="dash-stat-bar"></div>
        </div>
      </a>
    </div>
    <div class="col-6 col-lg-3">
      <a href="<?= APP_URL ?>/modules/keluarga/index.php" class="text-decoration-none">
        <div class="dash-stat dash-stat-teal">
          <div class="dash-stat-top"><span class="dash-stat-icon"><i class="fas fa-home"></i></span><span class="dash-stat-tag">Keluarga</span></div>
          <div class="dash-stat-num"><?= number_format($total_keluarga) ?></div>
          <div class="dash-stat-desc">Kepala keluarga</div>
          <div class="dash-stat-bar"></div>
        </div>
      </a>
    </div>
    <div class="col-6 col-lg-3">
      <a href="<?= APP_URL ?>/modules/jadwal/index.php" class="text-decoration-none">
        <div class="dash-stat dash-stat-cyan">
          <div class="dash-stat-top"><span class="dash-stat-icon"><i class="fas fa-calendar-alt"></i></span><span class="dash-stat-tag">Jadwal</span></div>
          <div class="dash-stat-num"><?= number_format($total_kegiatan) ?></div>
          <div class="dash-stat-desc">Kegiatan posyandu</div>
          <div class="dash-stat-bar"></div>
        </div>
      </a>
    </div>
    <div class="col-6 col-lg-3">
      <a href="<?= APP_URL ?>/modules/pemeriksaan_balita/tambah.php" class="text-decoration-none">
        <div class="dash-stat dash-stat-violet">
          <div class="dash-stat-top"><span class="dash-stat-icon"><i class="fas fa-stethoscope"></i></span><span class="dash-stat-tag">Input</span></div>
          <div class="dash-stat-num" style="font-size:1.15rem">Pemeriksaan</div>
          <div class="dash-stat-desc">Input data layanan</div>
          <div class="dash-stat-bar"></div>
        </div>
      </a>
    </div>
  </div>

  <div class="text-center mt-1 mb-3">
    <a href="<?= APP_URL ?>/modules/statistik/index.php" class="btn btn-outline-primary btn-sm rounded-pill px-3">
      <i class="fas fa-chart-pie me-1"></i> Lihat Statistik Posyandu
    </a>
  </div>
<?php endif; ?>

  <!-- ==================== MONITORING PTM ==================== -->
  <div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2">
      <h5 class="mb-0"><i class="fas fa-heartbeat me-2 text-danger"></i>Monitoring PTM (Penyakit Tidak Menular)</h5>
      <div class="d-flex gap-2">
        <a href="<?= APP_URL ?>/modules/usia_produktif/index.php" class="btn btn-sm btn-outline-primary">
          <i class="fas fa-user-tie me-1"></i>Usia Produktif
        </a>
        <a href="<?= APP_URL ?>/modules/pemeriksaan_dewasa/index.php" class="btn btn-sm btn-outline-danger">
          <i class="fas fa-stethoscope me-1"></i>Skrining PTM
        </a>
        <a href="<?= APP_URL ?>/modules/analitik/prediktif.php" class="btn btn-sm btn-outline-secondary">
          <i class="fas fa-brain me-1"></i>Analitik Prediktif
        </a>
      </div>
    </div>
    <div class="card-body">
      <div class="row g-3 mb-3">
        <div class="col-6 col-md-3">
          <div class="border rounded p-3 text-center h-100 bg-light">
            <div class="fs-3 fw-bold text-primary"><?= number_format($total_up) ?></div>
            <small class="text-muted">Sasaran Usia Produktif</small>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="border rounded p-3 text-center h-100 bg-light">
            <div class="fs-3 fw-bold text-success"><?= number_format($ptm_skrining) ?></div>
            <small class="text-muted">Sudah Skrining</small>
            <div class="progress mt-2" style="height:6px">
              <div class="progress-bar bg-success" style="width:<?= min(100, $ptm_cakupan) ?>%"></div>
            </div>
            <small class="text-muted">Cakupan <?= $ptm_cakupan ?>%</small>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="border rounded p-3 text-center h-100 bg-light">
            <div class="fs-3 fw-bold text-info"><?= number_format($ptm_bulan_ini) ?></div>
            <small class="text-muted">Skrining Bulan Ini</small>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="border rounded p-3 text-center h-100 bg-light">
            <div class="fs-3 fw-bold text-warning"><?= number_format($ptm_ht + $ptm_dm + $ptm_ob) ?></div>
            <small class="text-muted">Total Risiko (12 bln)</small>
          </div>
        </div>
      </div>

      <div class="row g-3 mb-3">
        <div class="col-md-4">
          <div class="d-flex align-items-center border rounded p-3 h-100">
            <div class="rounded-circle bg-danger text-white d-flex align-items-center justify-content-center me-3" style="width:48px;height:48px">
              <i class="fas fa-heart"></i>
            </div>
            <div>
              <div class="fs-4 fw-bold text-danger mb-0"><?= number_format($ptm_ht) ?></div>
              <small class="text-muted">Risiko Hipertensi</small>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="d-flex align-items-center border rounded p-3 h-100">
            <div class="rounded-circle bg-warning text-dark d-flex align-items-center justify-content-center me-3" style="width:48px;height:48px">
              <i class="fas fa-tint"></i>
            </div>
            <div>
              <div class="fs-4 fw-bold text-warning mb-0"><?= number_format($ptm_dm) ?></div>
              <small class="text-muted">Risiko Diabetes</small>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="d-flex align-items-center border rounded p-3 h-100">
            <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center me-3" style="width:48px;height:48px">
              <i class="fas fa-weight"></i>
            </div>
            <div>
              <div class="fs-4 fw-bold text-secondary mb-0"><?= number_format($ptm_ob) ?></div>
              <small class="text-muted">Risiko Obesitas (IMT ≥25)</small>
            </div>
          </div>
        </div>
      </div>

      <h6 class="fw-semibold mb-2"><i class="fas fa-exclamation-triangle me-1 text-danger"></i>Kasus Berisiko (pemeriksaan terakhir)</h6>
      <?php if (empty($ptm_risiko_list)): ?>
        <p class="text-muted small mb-0">Belum ada kasus risiko PTM tercatat. Lakukan skrining dari menu Usia Produktif.</p>
      <?php else: ?>
      <div class="table-responsive">
        <table class="table table-sm table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Nama</th>
              <th>Umur</th>
              <th>Tanggal</th>
              <th>TD / Gula / IMT</th>
              <th>Risiko</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($ptm_risiko_list as $pr): ?>
            <tr>
              <td>
                <div class="fw-semibold"><?= htmlspecialchars($pr['nama_lengkap']) ?></div>
                <small class="text-muted"><?= htmlspecialchars($pr['nik'] ?: '-') ?></small>
              </td>
              <td><?= (int)$pr['umur'] ?> th</td>
              <td><?= formatTanggal($pr['tanggal_pemeriksaan']) ?></td>
              <td class="small">
                <?= htmlspecialchars($pr['tekanan_darah'] ?: '-') ?>
                / <?= $pr['gula_darah'] !== null ? $pr['gula_darah'] : '-' ?>
                / <?= $pr['imt'] !== null ? $pr['imt'] : '-' ?>
              </td>
              <td>
                <?php if (!empty($pr['risiko_hipertensi'])): ?><span class="badge bg-danger me-1">HT</span><?php endif; ?>
                <?php if (!empty($pr['risiko_diabetes'])): ?><span class="badge bg-warning text-dark me-1">DM</span><?php endif; ?>
                <?php if (!empty($pr['risiko_obesitas'])): ?><span class="badge bg-secondary">Obesitas</span><?php endif; ?>
              </td>
              <td>
                <a href="<?= APP_URL ?>/modules/usia_produktif/detail.php?id=<?= (int)$pr['id'] ?>" class="btn btn-xs btn-outline-primary btn-sm" title="Detail">
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
  <!-- ==================== END MONITORING PTM ==================== -->

  <?php
  // Artikel Terbaru Posyandu (hanya status diterbitkan)
  $artikel_terbaru = [];
  try {
      $artikel_terbaru = fetchAll("
          SELECT a.id, a.judul, a.isi_artikel, a.gambar, a.tanggal_publish, a.created_at, k.nama AS nama_penulis
          FROM artikel a
          LEFT JOIN kader k ON k.id = a.penulis_id
          WHERE a.status = 'diterbitkan'
          ORDER BY COALESCE(a.tanggal_publish, a.created_at) DESC
          LIMIT 6
      ");
  } catch (Throwable $e) {
      $artikel_terbaru = [];
  }
  if (!empty($artikel_terbaru)):
  ?>
  <div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
      <h5 class="mb-0"><i class="fas fa-newspaper me-2 text-primary"></i>Artikel Terbaru Posyandu</h5>
      <a href="<?= APP_URL ?>/modules/artikel/index.php" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
    </div>
    <div class="card-body">
      <div class="row">
        <?php foreach ($artikel_terbaru as $art):
            $ringkasan = mb_substr(strip_tags($art['isi_artikel']), 0, 120);
            if (mb_strlen($art['isi_artikel']) > 120) $ringkasan .= '…';
            $tglArt = $art['tanggal_publish'] ?: substr($art['created_at'] ?? '', 0, 10);
        ?>
        <div class="col-12 col-md-6 col-lg-4 mb-3">
          <div class="card h-100 shadow-sm border-0">
            <?php if (!empty($art['gambar'])): ?>
            <img src="<?= APP_URL ?>/uploads/artikel/<?= htmlspecialchars($art['gambar']) ?>"
                 class="card-img-top" alt="" style="height:140px;object-fit:cover">
            <?php else: ?>
            <div class="bg-light d-flex align-items-center justify-content-center" style="height:140px">
              <i class="fas fa-newspaper fa-2x text-muted"></i>
            </div>
            <?php endif; ?>
            <div class="card-body py-3">
              <h6 class="card-title mb-1">
                <a href="<?= APP_URL ?>/modules/artikel/detail.php?id=<?= (int)$art['id'] ?>" class="text-dark text-decoration-none">
                  <?= htmlspecialchars($art['judul']) ?>
                </a>
              </h6>
              <p class="small text-muted mb-1">
                <i class="fas fa-user me-1"></i><?= htmlspecialchars($art['nama_penulis'] ?? 'Kader') ?>
                · <?= formatTanggal($tglArt) ?>
              </p>
              <p class="card-text small mb-0"><?= htmlspecialchars($ringkasan) ?></p>
            </div>
            <div class="card-footer bg-white border-0 pt-0">
              <a href="<?= APP_URL ?>/modules/artikel/detail.php?id=<?= (int)$art['id'] ?>" class="btn btn-sm btn-outline-primary">
                Baca <i class="fas fa-arrow-right ms-1"></i>
              </a>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  <?php endif; ?>

</div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
