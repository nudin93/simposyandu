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

/**
 * Halaman Publik SIMPOSYANDU - Gaya Website Desa OpenSID
 * Hanya menampilkan statistik & informasi, bukan data pribadi.
 */
require_once __DIR__ . '/../config/init.php';

$pengaturan = getPengaturan();
$pengaturan = is_array($pengaturan) ? $pengaturan : [];
$app_name = $pengaturan['nama_aplikasi'] ?? 'SIMPOSYANDU';
$nama_desa = $pengaturan['nama_desa'] ?? 'Desa';
$nama_posyandu = $pengaturan['nama_posyandu'] ?? 'Posyandu';

$logo_file = trim($pengaturan['logo'] ?? '');
$logo_url = '';
if ($logo_file !== '' && file_exists(__DIR__ . '/../uploads/settings/' . $logo_file)) {
    $logo_url = APP_URL . '/uploads/settings/' . rawurlencode($logo_file);
}
$favicon_file = trim($pengaturan['favicon'] ?? '');
$favicon_url = APP_URL . '/assets/img/favicon.ico';
if ($favicon_file !== '' && file_exists(__DIR__ . '/../uploads/settings/' . $favicon_file)) {
    $favicon_url = APP_URL . '/uploads/settings/' . rawurlencode($favicon_file);
}

// Statistik publik (hanya jumlah)
$total_balita = 0; $total_bayi = 0; $total_bumil = 0; $total_lansia = 0; $total_kegiatan = 0;
try { $r = fetchOne("SELECT COUNT(*) AS c FROM balita WHERE status_aktif=1 OR status_aktif IS NULL"); $total_balita = (int)($r['c']??0); } catch(Throwable $e){}
try { $r = fetchOne("SELECT COUNT(*) AS c FROM bayi WHERE status_aktif=1 OR status_aktif IS NULL"); $total_bayi = (int)($r['c']??0); } catch(Throwable $e){}
try { $r = fetchOne("SELECT COUNT(*) AS c FROM ibu_hamil WHERE status_aktif=1 OR status_aktif IS NULL"); $total_bumil = (int)($r['c']??0); } catch(Throwable $e){}
try { $r = fetchOne("SELECT COUNT(*) AS c FROM lansia WHERE status_aktif=1 OR status_aktif IS NULL"); $total_lansia = (int)($r['c']??0); } catch(Throwable $e){}
try { $r = fetchOne("SELECT COUNT(*) AS c FROM kegiatan_posyandu"); $total_kegiatan = (int)($r['c']??0); } catch(Throwable $e){}

// Artikel publik
$artikel = [];
try {
    $artikel = fetchAll("SELECT id, judul, isi_artikel, gambar, tanggal_publish, created_at FROM artikel WHERE status='diterbitkan' ORDER BY COALESCE(tanggal_publish, created_at) DESC LIMIT 6");
} catch(Throwable $e){}

// Jadwal terdekat
$jadwal = [];
try {
    $jadwal = fetchAll("SELECT * FROM jadwal WHERE tanggal >= CURDATE() ORDER BY tanggal ASC LIMIT 5");
} catch(Throwable $e){}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($app_name) ?> - <?= htmlspecialchars($nama_desa) ?></title>
  <link rel="icon" href="<?= htmlspecialchars($favicon_url) ?>">
  <link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@300;400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <style>
    :root { --opensid-blue: #3c8dbc; --opensid-dark: #222d32; }
    body { font-family: 'Source Sans Pro', sans-serif; background: #f4f6f9; }
    .navbar-public { background: var(--opensid-blue); }
    .navbar-public .nav-link { color: rgba(255,255,255,.9) !important; }
    .navbar-public .nav-link:hover, .navbar-public .nav-link.active { color: #fff !important; background: rgba(0,0,0,.1); }
    .hero { background: linear-gradient(135deg, var(--opensid-blue), #2c6aa0); color: #fff; padding: 3rem 0; }
    .stat-card { border: none; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,.08); transition: transform .2s; }
    .stat-card:hover { transform: translateY(-3px); }
    .stat-card .icon { font-size: 2rem; opacity: .8; }
    .footer-public { background: var(--opensid-dark); color: #a0a0a0; padding: 2rem 0; margin-top: 3rem; }
    .footer-public a { color: #ccc; }
    .section-title { border-left: 4px solid var(--opensid-blue); padding-left: 12px; margin-bottom: 1.5rem; }
  </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-public sticky-top">
  <div class="container">
    <a class="navbar-brand text-white fw-bold d-flex align-items-center" href="index.php">
      <?php if ($logo_url): ?>
        <img src="<?= htmlspecialchars($logo_url) ?>" alt="Logo" style="height:36px;width:auto;object-fit:contain;margin-right:10px;border-radius:6px;background:#fff;padding:2px;">
      <?php else: ?>
        <i class="fas fa-clinic-medical me-2"></i>
      <?php endif; ?>
      <?= htmlspecialchars($app_name) ?>
    </a>
    <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navPublic">
      <i class="fas fa-bars text-white"></i>
    </button>
    <div class="collapse navbar-collapse" id="navPublic">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link active" href="index.php">Beranda</a></li>
        <li class="nav-item"><a class="nav-link" href="profil.php">Profil</a></li>
        <li class="nav-item"><a class="nav-link" href="artikel.php">Artikel</a></li>
        <li class="nav-item"><a class="nav-link" href="jadwal.php">Jadwal</a></li>
        <li class="nav-item"><a class="nav-link" href="statistik.php">Statistik</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= APP_URL ?>/index.php"><i class="fas fa-lock me-1"></i>Login Admin</a></li>
      </ul>
    </div>
  </div>
</nav>

<section class="hero">
  <div class="container text-center">
    <?php if ($logo_url): ?>
      <img src="<?= htmlspecialchars($logo_url) ?>" alt="Logo" class="mb-3" style="height:72px;width:auto;object-fit:contain;background:rgba(255,255,255,.15);padding:8px;border-radius:12px;">
    <?php endif; ?>
    <h1 class="fw-bold mb-2"><?= htmlspecialchars($nama_posyandu) ?></h1>
    <p class="lead mb-0 opacity-90"><?= htmlspecialchars($nama_desa) ?> · Kecamatan <?= htmlspecialchars($pengaturan['kecamatan'] ?? '') ?></p>
    <p class="mt-3 opacity-75">Sistem Informasi Posyandu terintegrasi dengan OpenSID</p>
  </div>
</section>

<section class="py-5">
  <div class="container">
    <h3 class="section-title">Statistik Posyandu</h3>
    <div class="row g-3">
      <div class="col-6 col-md-4 col-lg-2">
        <div class="card stat-card text-center p-3 h-100">
          <div class="icon text-info"><i class="fas fa-baby"></i></div>
          <h4 class="mb-0 mt-2"><?= number_format($total_balita) ?></h4>
          <small class="text-muted">Balita</small>
        </div>
      </div>
      <div class="col-6 col-md-4 col-lg-2">
        <div class="card stat-card text-center p-3 h-100">
          <div class="icon text-warning"><i class="fas fa-child"></i></div>
          <h4 class="mb-0 mt-2"><?= number_format($total_bayi) ?></h4>
          <small class="text-muted">Bayi</small>
        </div>
      </div>
      <div class="col-6 col-md-4 col-lg-2">
        <div class="card stat-card text-center p-3 h-100">
          <div class="icon text-danger"><i class="fas fa-female"></i></div>
          <h4 class="mb-0 mt-2"><?= number_format($total_bumil) ?></h4>
          <small class="text-muted">Ibu Hamil</small>
        </div>
      </div>
      <div class="col-6 col-md-4 col-lg-2">
        <div class="card stat-card text-center p-3 h-100">
          <div class="icon text-secondary"><i class="fas fa-user-friends"></i></div>
          <h4 class="mb-0 mt-2"><?= number_format($total_lansia) ?></h4>
          <small class="text-muted">Lansia</small>
        </div>
      </div>
      <div class="col-6 col-md-4 col-lg-2">
        <div class="card stat-card text-center p-3 h-100">
          <div class="icon text-success"><i class="fas fa-calendar-check"></i></div>
          <h4 class="mb-0 mt-2"><?= number_format($total_kegiatan) ?></h4>
          <small class="text-muted">Kegiatan</small>
        </div>
      </div>
    </div>
  </div>
</section>

<?php if (!empty($jadwal)): ?>
<section class="py-4 bg-white">
  <div class="container">
    <h3 class="section-title">Jadwal Kegiatan Terdekat</h3>
    <div class="list-group">
      <?php foreach ($jadwal as $j): ?>
      <div class="list-group-item d-flex justify-content-between align-items-center">
        <div>
          <strong><?= htmlspecialchars($j['judul'] ?? $j['kegiatan'] ?? 'Kegiatan') ?></strong>
          <br><small class="text-muted"><i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars($j['lokasi'] ?? '-') ?></small>
        </div>
        <span class="badge bg-primary rounded-pill"><?= date('d M Y', strtotime($j['tanggal'] ?? 'now')) ?></span>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if (!empty($artikel)): ?>
<section class="py-5">
  <div class="container">
    <h3 class="section-title">Artikel Kesehatan</h3>
    <div class="row g-3">
      <?php foreach ($artikel as $a): 
        $ringkasan = mb_substr(strip_tags($a['isi_artikel'] ?? ''), 0, 100) . '…';
      ?>
      <div class="col-md-4">
        <div class="card h-100 border-0 shadow-sm">
          <div class="card-body">
            <h5 class="card-title"><?= htmlspecialchars($a['judul']) ?></h5>
            <p class="card-text small text-muted"><?= htmlspecialchars($ringkasan) ?></p>
            <a href="artikel_detail.php?id=<?= (int)$a['id'] ?>" class="btn btn-sm btn-outline-primary">Baca selengkapnya</a>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<footer class="footer-public">
  <div class="container text-center">
    <p class="mb-1">&copy; <?= date('Y') ?> <?= htmlspecialchars($nama_posyandu) ?> · <?= htmlspecialchars($nama_desa) ?></p>
    <p class="small mb-0">Powered by SIMPOSYANDU · Terintegrasi OpenSID</p>
  </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
