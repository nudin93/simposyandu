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

$page_title = 'Detail Artikel';
require_once __DIR__ . '/../../includes/header.php';

$id = (int)($_GET['id'] ?? 0);
$row = $id ? fetchOne("
    SELECT a.*, k.nama AS nama_penulis
    FROM artikel a
    LEFT JOIN kader k ON k.id = a.penulis_id
    WHERE a.id = $id
") : null;

if (!$row) {
    echo '<section class="content"><div class="container-fluid"><div class="alert alert-danger">Artikel tidak ditemukan.</div></div></section>';
    include __DIR__ . '/../../includes/footer.php';
    exit;
}

$userId = (int)(currentUser()['id'] ?? 0);
$isAdm = isAdmin();
$isOwner = ((int)$row['penulis_id'] === $userId);

// Non-admin non-owner hanya boleh lihat yang diterbitkan
if ($row['status'] !== 'diterbitkan' && !$isAdm && !$isOwner) {
    echo '<section class="content"><div class="container-fluid"><div class="alert alert-warning">Artikel belum dipublikasikan.</div></div></section>';
    include __DIR__ . '/../../includes/footer.php';
    exit;
}

$statusBadge = [
    'draft' => 'secondary', 'menunggu' => 'warning',
    'diterbitkan' => 'success', 'ditolak' => 'danger',
];
$tgl = $row['tanggal_publish'] ?: substr($row['created_at'] ?? '', 0, 10);
?>
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1><i class="fas fa-newspaper me-2 text-primary"></i>Detail Artikel</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="<?= APP_URL ?>/dashboard.php">Dashboard</a></li>
          <li class="breadcrumb-item"><a href="index.php">Artikel</a></li>
          <li class="breadcrumb-item active">Detail</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<section class="content">
<div class="container-fluid">
  <div class="card shadow-sm border-0">
    <div class="card-body">
      <div class="mb-2">
        <span class="badge bg-info"><?= htmlspecialchars($row['kategori']) ?></span>
        <span class="badge bg-<?= $statusBadge[$row['status']] ?? 'secondary' ?>">
          <?= ucfirst($row['status']) ?>
        </span>
      </div>
      <h2 class="mb-3"><?= htmlspecialchars($row['judul']) ?></h2>
      <p class="text-muted mb-4">
        <i class="fas fa-user me-1"></i><?= htmlspecialchars($row['nama_penulis'] ?? 'Kader') ?>
        · <i class="fas fa-calendar me-1"></i><?= formatTanggal($tgl) ?>
      </p>

      <?php if (!empty($row['gambar'])): ?>
      <div class="mb-4 text-center">
        <img src="<?= APP_URL ?>/uploads/artikel/<?= htmlspecialchars($row['gambar']) ?>"
             alt="<?= htmlspecialchars($row['judul']) ?>"
             class="img-fluid rounded shadow-sm" style="max-height:420px">
      </div>
      <?php endif; ?>

      <div class="artikel-isi" style="line-height:1.75;font-size:1.05rem">
        <?php
          // Tampilkan HTML aman (sudah disanitasi saat simpan)
          $safe = $row['isi_artikel'];
          $safe = preg_replace('#<(script|iframe|object|embed|form)[^>]*>.*?</\1>#is', '', $safe);
          $safe = strip_tags($safe, '<p><br><b><strong><i><em><u><ul><ol><li><a><img><h1><h2><h3><h4><h5><h6><table><thead><tbody><tr><th><td><blockquote><pre><code><span><div><hr>');
          echo $safe;
        ?>
      </div>
    </div>
    <div class="card-footer bg-white">
      <a href="index.php" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i>Kembali
      </a>
      <?php if (($isOwner || $isAdm) && canInput()): ?>
      <a href="edit.php?id=<?= (int)$row['id'] ?>" class="btn btn-warning btn-sm">
        <i class="fas fa-edit me-1"></i>Edit
      </a>
      <?php endif; ?>
    </div>
  </div>
</div>
</section>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
