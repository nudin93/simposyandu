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

$page_title = 'Data Kader';
require_once __DIR__ . '/../../includes/header.php';
if (!isAdmin()) { echo '<script>window.location="../../dashboard.php";</script>'; exit; }
$data = fetchAll("SELECT * FROM kader ORDER BY nama");
?>
<section class="content-header"><div class="container-fluid"><div class="row mb-2">
  <div class="col-sm-6"><h1><i class="fas fa-users me-2 text-primary"></i>Data Kader</h1></div>
  <div class="col-sm-6"><ol class="breadcrumb float-sm-end"><li class="breadcrumb-item"><a href="../../dashboard.php">Dashboard</a></li><li class="breadcrumb-item active">Kader</li></ol></div>
</div></div></section>
<section class="content"><div class="container-fluid">
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="mb-0"><i class="fas fa-list me-2"></i>Daftar Kader</h5>
    <a href="tambah.php" class="btn btn-primary"><i class="fas fa-plus me-1"></i>Tambah Kader</a>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-hover datatable">
        <thead><tr><th>No</th><th>Foto</th><th>Nama</th><th>NIK</th><th>No. HP</th><th>Jabatan</th><th>Role</th><th>Status</th><th>Aksi</th></tr></thead>
        <tbody>
        <?php foreach ($data as $i => $k): ?>
        <tr>
          <td><?= $i+1 ?></td>
          <td><img src="<?= $k['foto'] ? APP_URL.'/uploads/kader/'.$k['foto'] : APP_URL.'/assets/img/user.png' ?>" class="img-circle" width="40" height="40" style="object-fit:cover"></td>
          <td><div class="fw-semibold"><?= htmlspecialchars($k['nama']) ?></div><small class="text-muted"><?= $k['username'] ?></small></td>
          <td><?= $k['nik']??'-' ?></td>
          <td><?= $k['no_hp']??'-' ?></td>
          <td><?= $k['jabatan'] ?></td>
          <td><span class="badge bg-<?= $k['role']=='admin'?'danger':($k['role']=='bidan'?'info':'success') ?>"><?= ucfirst($k['role']) ?></span></td>
          <td><span class="badge bg-<?= $k['status']?'success':'danger' ?>"><?= $k['status']?'Aktif':'Nonaktif' ?></span></td>
          <td>
            <a href="edit.php?id=<?= $k['id'] ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
            <?php if ($k['id'] != $user['id']): ?>
            <button class="btn btn-sm btn-danger" onclick="confirmDelete('<?= APP_URL ?>/ajax/delete.php?type=kader&id=<?= $k['id'] ?>','<?= htmlspecialchars($k['nama']) ?>')"><i class="fas fa-trash"></i></button>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
</div></section>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
