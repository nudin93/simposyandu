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

require_once __DIR__ . '/../config/init.php';
$pengaturan = getPengaturan();
$app_name = $pengaturan['nama_aplikasi'] ?? 'SIMPOSYANDU';
$nama_desa = $pengaturan['nama_desa'] ?? 'Desa';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Profil - <?= htmlspecialchars($app_name) ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@300;400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <style>
    :root { --opensid-blue: #3c8dbc; }
    body { font-family: 'Source Sans Pro', sans-serif; background: #f4f6f9; }
    .navbar-public { background: var(--opensid-blue); }
    .navbar-public .nav-link { color: rgba(255,255,255,.9) !important; }
    .section-title { border-left: 4px solid var(--opensid-blue); padding-left: 12px; }
  </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-public sticky-top">
  <div class="container">
    <a class="navbar-brand text-white fw-bold" href="index.php"><i class="fas fa-clinic-medical me-2"></i><?= htmlspecialchars($app_name) ?></a>
    <div class="collapse navbar-collapse show">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="index.php">Beranda</a></li>
        <li class="nav-item"><a class="nav-link active text-white" href="profil.php">Profil</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= APP_URL ?>/index.php">Login Admin</a></li>
      </ul>
    </div>
  </div>
</nav>
<div class="container py-5">
  <h2 class="section-title mb-4">Profil Posyandu</h2>
  <div class="card border-0 shadow-sm">
    <div class="card-body p-4">
      <h4><?= htmlspecialchars($pengaturan['nama_posyandu'] ?? 'Posyandu') ?></h4>
      <table class="table table-borderless mt-3">
        <tr><th width="180">Nama Desa</th><td><?= htmlspecialchars($nama_desa) ?></td></tr>
        <tr><th>Kecamatan</th><td><?= htmlspecialchars($pengaturan['kecamatan'] ?? '-') ?></td></tr>
        <tr><th>Kabupaten</th><td><?= htmlspecialchars($pengaturan['kabupaten'] ?? '-') ?></td></tr>
        <tr><th>Provinsi</th><td><?= htmlspecialchars($pengaturan['provinsi'] ?? '-') ?></td></tr>
        <tr><th>Kontak</th><td><?= htmlspecialchars($pengaturan['nomor_kontak'] ?? '-') ?></td></tr>
        <tr><th>Email</th><td><?= htmlspecialchars($pengaturan['email'] ?? '-') ?></td></tr>
      </table>
      <p class="text-muted mt-3">Posyandu adalah pusat kegiatan kesehatan masyarakat di tingkat desa yang melayani ibu hamil, bayi, balita, remaja, dan lansia.</p>
    </div>
  </div>
</div>
<footer class="text-center py-4 text-muted small">&copy; <?= date('Y') ?> <?= htmlspecialchars($app_name) ?></footer>
</body>
</html>
