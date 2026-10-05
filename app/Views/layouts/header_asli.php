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

// CI4: init via Filter + helpers
requireLogin();

// Guard akses halaman berdasarkan hak menu
// profile.php (Profil Saya) SELALU boleh untuk user yang login
$__script = $_SERVER['SCRIPT_NAME'] ?? ($_SERVER['PHP_SELF'] ?? '');
$__folder = basename(dirname($__script));
$__page   = basename($__script);
$__uri    = $_SERVER['REQUEST_URI'] ?? '';
$__isOwnProfile = (
    ($__folder === 'kader' && $__page === 'profile.php')
    || (strpos($__uri, '/modules/kader/profile.php') !== false)
    || (strpos($__script, '/modules/kader/profile.php') !== false)
);
if (!$__isOwnProfile && function_exists('canViewMenu')) {
    $__map = [
        'statistik' => 'statistik',
        'analitik' => 'statistik',
        'penduduk' => 'penduduk', 'keluarga' => 'keluarga',
        'ibu_hamil' => 'ibu_hamil', 'pemeriksaan_ibu_hamil' => 'pemeriksaan_ibu_hamil',
        'bayi' => 'bayi', 'pemeriksaan_bayi' => 'pemeriksaan_bayi',
        'balita' => 'balita', 'pemeriksaan_balita' => 'pemeriksaan_balita',
        'imunisasi' => 'imunisasi', 'vitamin' => 'vitamin',
        'remaja' => 'remaja', 'pemeriksaan_remaja' => 'pemeriksaan_remaja',
        'lansia' => 'lansia', 'pemeriksaan_lansia' => 'pemeriksaan_lansia',
        'usia_produktif' => 'usia_produktif', 'pemeriksaan_dewasa' => 'pemeriksaan_dewasa',
        'kb' => 'kb',
        'kegiatan_posyandu' => 'kegiatan_posyandu', 'kunjungan_rumah' => 'kunjungan_rumah', 'jadwal' => 'jadwal',
        'artikel' => 'artikel',
        'sanitasi' => 'sanitasi', 'phbs' => 'phbs',
        'laporan' => 'laporan', 'kartu' => 'kartu',
        'kader' => 'kader', 'backup' => 'backup', 'pengaturan' => 'pengaturan',
    ];
    if (isset($__map[$__folder]) && !canViewMenu($__map[$__folder])) {
        echo '<script>alert("Anda tidak memiliki hak akses ke menu ini."); window.location="' . (defined("APP_URL") ? APP_URL : "") . '/dashboard.php";</script>';
        exit;
    }
}

$pengaturan = getPengaturan();
$pengaturan = is_array($pengaturan) ? $pengaturan : [];
$user = currentUser();
$app_name = $pengaturan['nama_aplikasi'] ?? 'SIMPOSYANDU';
$posyandu = $pengaturan['nama_posyandu'] ?? 'Posyandu';
$warna = $pengaturan['warna_tema'] ?? 'blue';
$dark = !empty($pengaturan['dark_mode']) ? 'dark-mode' : '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="<?= csrfToken() ?>">
  <title><?= $page_title ?? $app_name ?> | <?= $app_name ?></title>
  <script>window.CSRF = '<?= csrfToken() ?>'; var CSRF = window.CSRF;</script>
  <?php
  $_fav = trim($pengaturan['favicon'] ?? '');
  $_fav_url = APP_URL . '/assets/img/favicon.ico';
  if ($_fav !== '' && file_exists(FCPATH . 'uploads/settings/' . $_fav)) {
      $_fav_url = APP_URL . '/uploads/settings/' . rawurlencode($_fav);
  }
?>
  <link rel="icon" type="image/x-icon" href="<?= htmlspecialchars($_fav_url) ?>">
  <!-- Google Font (Source Sans Pro = OpenSID style + Inter fallback) -->
  <link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@300;400;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <!-- Bootstrap 5 -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <!-- AdminLTE 3 -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2.0/dist/css/adminlte.min.css">
  <!-- Select2 -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">
  <!-- Flatpickr -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <!-- DataTables -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
  <!-- Custom CSS -->
  <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/custom.css?v=20260928e">
  <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/pro-modern.css?v=20260928e">
  <style id="sidepush-critical">
  /* Sidebar SELALU fixed — tidak ikut scroll halaman */
  .main-sidebar,.opensid-sidebar{
    position:fixed!important;top:0!important;left:0!important;
    height:100vh!important;height:100dvh!important;
    max-height:100vh!important;max-height:100dvh!important;
    overflow:hidden!important;margin-left:0!important;z-index:1040!important;
    transition:transform .3s ease,width .3s ease!important
  }
  .main-sidebar .sidebar,.opensid-sidebar .sidebar{
    height:calc(100vh - 60px)!important;height:calc(100dvh - 60px)!important;
    overflow-y:auto!important;overflow-x:hidden!important;-webkit-overflow-scrolling:touch
  }
  .content-wrapper,.main-footer,.main-header{transition:margin .3s ease,transform .3s ease!important}
  @media(min-width:992px){
    body:not(.sidebar-collapse) .content-wrapper,body:not(.sidebar-collapse) .main-footer,body:not(.sidebar-collapse) .main-header{margin-left:250px!important}
    body.sidebar-collapse .content-wrapper,body.sidebar-collapse .main-footer,body.sidebar-collapse .main-header{margin-left:73.6px!important}
    .main-sidebar,.opensid-sidebar{width:250px!important;transform:none!important}
    body.sidebar-collapse .main-sidebar,body.sidebar-collapse .opensid-sidebar{width:73.6px!important}
  }
  @media(max-width:991.98px){
    body.sidebar-open{overflow-x:hidden!important}
    .main-sidebar,.opensid-sidebar{width:250px!important;transform:translateX(-105%)!important}
    body.sidebar-open .main-sidebar,body.sidebar-open.sidebar-collapse .main-sidebar,body.sidebar-open.sidebar-closed .main-sidebar,
    body.sidebar-open .opensid-sidebar{transform:translateX(0)!important;margin-left:0!important;box-shadow:0 0 40px rgba(15,23,42,.25)!important}
    body.sidebar-open .content-wrapper,body.sidebar-open .main-footer,body.sidebar-open .main-header{transform:translateX(250px)!important}
    body.sidebar-open::before{display:none!important}
  }
  </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed text-sm <?= $dark ?>">
<script>
/* Definisi awal agar tombol garis-3 selalu aktif walau footer belum termuat */
window.toggleSidePush = window.toggleSidePush || function() {
  try {
    if (window.innerWidth >= 992) {
      document.body.classList.remove('sidebar-open');
      document.body.classList.toggle('sidebar-collapse');
    } else {
      var isOpen = document.body.classList.toggle('sidebar-open');
      if (isOpen) {
        document.body.classList.remove('sidebar-collapse');
        document.body.classList.remove('sidebar-closed');
      }
    }
  } catch (e) {}
  return false;
};
</script>
<div class="wrapper">

<!-- Navbar - OpenSID style (solid blue) -->
<nav class="main-header navbar navbar-expand navbar-dark opensid-navbar">
  <ul class="navbar-nav">
    <li class="nav-item">
      <a class="nav-link btn-toggle-sidebar" href="#" role="button" onclick="return toggleSidePush()" title="Menu" id="btn-toggle-sidebar"><i class="fas fa-bars"></i></a>
    </li>
  </ul>
  <ul class="navbar-nav mx-auto">
    <li class="nav-item">
      <a href="<?= APP_URL ?>/dashboard.php" class="nav-link opensid-brand"><?= $app_name ?></a>
    </li>
  </ul>
  <ul class="navbar-nav">
<?php
$_avatar_svg = "data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 64 64'><circle cx='32' cy='32' r='32' fill='%23ccfbf1'/><circle cx='32' cy='24' r='11' fill='%230f766e'/><path d='M10 56c4-12 12-17 22-17s18 5 22 17' fill='%230f766e'/></svg>";
$_u_foto_url = !empty($user['foto']) ? APP_URL . '/uploads/kader/' . rawurlencode($user['foto']) : $_avatar_svg;
?>
    <li class="nav-item">
      <a href="#" class="nav-link position-relative" id="bellBtn" title="Pembaruan">
        <i class="fas fa-bell"></i>
        <span id="bellBadge" class="badge bg-danger position-absolute top-0 start-100 translate-middle rounded-pill" style="display:none;font-size:.6rem;">0</span>
      </a>
    </li>
    <li class="nav-item dropdown user-menu">
      <a href="#" class="nav-link" data-bs-toggle="dropdown">
        <img src="<?= htmlspecialchars($_u_foto_url) ?>" class="user-image img-circle elevation-1" alt="User" style="width:28px;height:28px;object-fit:cover;" onerror="this.onerror=null;this.src='<?= $_avatar_svg ?>'">
      </a>
      <ul class="dropdown-menu dropdown-menu-end">
        <li class="user-header bg-primary">
          <img src="<?= htmlspecialchars($_u_foto_url) ?>" class="img-circle elevation-2" onerror="this.onerror=null;this.src='<?= $_avatar_svg ?>'">
          <p><?= htmlspecialchars($user['nama']) ?> <small><?= ucfirst($user['role']) ?></small></p>
        </li>
        <li class="user-footer">
          <a href="<?= APP_URL ?>/modules/kader/profile.php" class="btn btn-default btn-flat">Profil</a>
          <a href="<?= APP_URL ?>/logout.php" class="btn btn-default btn-flat float-end">Keluar</a>
        </li>
      </ul>
    </li>
    <?php if (function_exists('isAdmin') && isAdmin()): ?>
    <li class="nav-item">
      <a class="nav-link" href="<?= APP_URL ?>/modules/pengaturan/index.php" title="Pengaturan"><i class="fas fa-cog"></i></a>
    </li>
    <?php endif; ?>
  </ul>
</nav>

<?php include __DIR__ . '/sidebar.php'; ?>
<div class="content-wrapper">
