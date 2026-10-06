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

/** @var string $page_title */
$page_title = $page_title ?? ($app_name . ' · ' . $nama_desa);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="Portal publik <?= htmlspecialchars($nama_posyandu) ?>, <?= htmlspecialchars($nama_desa) ?>. Statistik sasaran, jadwal, dan informasi kesehatan masyarakat.">
  <title><?= htmlspecialchars($page_title) ?></title>
  <link rel="icon" href="<?= htmlspecialchars($favicon_url) ?>">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="assets/public.css?v=20260823">
  <link rel="stylesheet" href="assets/public-pro.css?v=20260926">
</head>
<body>
<header class="pub-topbar">
  <div class="container d-flex flex-wrap justify-content-between align-items-center gap-2 py-1">
    <small class="text-white-50">
      <?php if ($kecamatan || $kabupaten): ?>
        <?= htmlspecialchars(trim($kecamatan . ($kabupaten ? ', ' . $kabupaten : ''))) ?>
        <?= $provinsi ? ' · ' . htmlspecialchars($provinsi) : '' ?>
      <?php else: ?>
        Portal Informasi Posyandu
      <?php endif; ?>
    </small>
    <small>
      <?php if ($kontak): ?><span class="me-3"><i class="fas fa-phone me-1"></i><?= htmlspecialchars($kontak) ?></span><?php endif; ?>
      <a href="<?= APP_URL ?>/index.php" class="text-white text-decoration-none"><i class="fas fa-sign-in-alt me-1"></i>Login Kader</a>
    </small>
  </div>
</header>

<nav class="navbar navbar-expand-lg pub-nav sticky-top">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center gap-2" href="index.php">
      <?php if ($logo_url): ?>
        <img src="<?= htmlspecialchars($logo_url) ?>" alt="Logo" class="pub-logo">
      <?php else: ?>
        <span class="pub-logo-icon"><i class="fas fa-clinic-medical"></i></span>
      <?php endif; ?>
      <span>
        <strong class="d-block lh-1"><?= htmlspecialchars($nama_posyandu) ?></strong>
        <small class="opacity-75" style="font-size:.72rem"><?= htmlspecialchars($nama_desa) ?></small>
      </span>
    </a>
    <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navPub">
      <i class="fas fa-bars text-white"></i>
    </button>
    <div class="collapse navbar-collapse" id="navPub">
      <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1">
        <li class="nav-item"><a class="nav-link <?= public_nav_active('index.php') ?>" href="index.php">Beranda</a></li>
        <li class="nav-item"><a class="nav-link <?= public_nav_active('statistik.php') ?>" href="statistik.php">Statistik</a></li>
        <li class="nav-item"><a class="nav-link <?= public_nav_active('jadwal.php') ?>" href="jadwal.php">Jadwal</a></li>
        <li class="nav-item"><a class="nav-link <?= public_nav_active('artikel.php') ?>" href="artikel.php">Artikel</a></li>
        <li class="nav-item"><a class="nav-link <?= public_nav_active('profil.php') ?>" href="profil.php">Profil</a></li>
        <li class="nav-item ms-lg-2">
          <a class="btn btn-sm btn-light px-3" href="<?= APP_URL ?>/index.php"><i class="fas fa-user-nurse me-1"></i>Kader</a>
        </li>
      </ul>
    </div>
  </div>
</nav>
