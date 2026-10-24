<?php
/**
 * ==========================================================
 * SIMPOSYANDU - Sistem Informasi Posyandu Terintegrasi
 * ==========================================================
 * Modul: Nama-nama Kader (direktori read-only)
 * Ditambahkan tanpa merubah struktur yang ada.
 * Akses: semua user login sesuai hak 'nama_kader'.
 * Kelola data (tambah/edit/hapus) tetap di Pengguna / Kader (admin).
 * ==========================================================
 */

$page_title = 'Nama-nama Kader';
require_once __DIR__ . '/../../includes/header.php';

// Guard tambahan (header.php juga menjaga via $__map)
if (function_exists('canViewMenu') && !canViewMenu('nama_kader')) {
    echo '<script>alert("Anda tidak memiliki hak akses ke menu ini."); window.location="' . APP_URL . '/dashboard.php";</script>';
    exit;
}

$q = trim($_GET['q'] ?? '');
$data = [];
try {
    if ($q !== '') {
        $qs = escape($q);
        $data = fetchAll("SELECT id, nama, nik, jenis_kelamin, no_hp, jabatan, foto, role, status FROM kader WHERE (nama LIKE '%$qs%' OR jabatan LIKE '%$qs%' OR no_hp LIKE '%$qs%') ORDER BY status DESC, nama ASC");
    } else {
        $data = fetchAll("SELECT id, nama, nik, jenis_kelamin, no_hp, jabatan, foto, role, status FROM kader ORDER BY status DESC, nama ASC");
    }
} catch (Throwable $e) {
    $data = [];
}
?>
<section class="content-header"><div class="container-fluid"><div class="row mb-2">
  <div class="col-sm-6"><h1 class="m-0"><i class="fas fa-id-card-alt me-2 text-primary"></i>Nama-nama Kader</h1></div>
  <div class="col-sm-6"><ol class="breadcrumb float-sm-end"><li class="breadcrumb-item"><a href="<?= APP_URL ?>/dashboard.php">Beranda</a></li><li class="breadcrumb-item active">Nama-nama Kader</li></ol></div>
</div></div></section>
<section class="content"><div class="container-fluid">
<div class="card card-outline card-primary shadow-sm">
  <div class="card-header bg-white">
    <form method="get" class="row g-2 align-items-center">
      <div class="col-md-6">
        <div class="input-group">
          <span class="input-group-text"><i class="fas fa-search"></i></span>
          <input type="text" name="q" class="form-control" placeholder="Cari nama / jabatan / no. HP..." value="<?= htmlspecialchars($q) ?>">
        </div>
      </div>
      <div class="col-md-6 text-md-end">
        <button type="submit" class="btn btn-primary"><i class="fas fa-search me-1"></i>Cari</button>
        <?php if ($q !== ''): ?><a href="index.php" class="btn btn-secondary"><i class="fas fa-times me-1"></i>Reset</a><?php endif; ?>
        <span class="text-muted small ms-2"><?= count($data) ?> kader</span>
      </div>
    </form>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0 align-middle datatable">
        <thead class="table-light"><tr><th style="width:44px">No</th><th style="width:56px">Foto</th><th>Nama</th><th>Jabatan</th><th>No. HP</th><th>Role</th><th>Status</th></tr></thead>
        <tbody>
        <?php if (empty($data)): ?>
          <tr><td colspan="7" class="text-center text-muted py-5"><i class="fas fa-users fa-2x mb-2 d-block"></i>Tidak ada data kader.</td></tr>
        <?php else: foreach ($data as $i => $k): ?>
        <tr>
          <td class="text-muted"><?= $i+1 ?></td>
          <td>
            <?php if (!empty($k['foto'])): ?>
              <img src="<?= APP_URL ?>/uploads/kader/<?= rawurlencode($k['foto']) ?>" class="img-circle elevation-1" width="40" height="40" style="object-fit:cover" alt="Foto" onerror="this.style.display='none'">
            <?php else: ?>
              <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-secondary text-white" style="width:40px;height:40px;"><i class="fas fa-user"></i></span>
            <?php endif; ?>
          </td>
          <td><div class="fw-semibold"><?= htmlspecialchars($k['nama']) ?></div></td>
          <td><?= htmlspecialchars($k['jabatan'] ?: '-') ?></td>
          <td><?= htmlspecialchars($k['no_hp'] ?: '-') ?></td>
          <td><span class="badge bg-<?= ($k['role'] ?? '')=='admin'?'danger':((($k['role'] ?? '')=='bidan')?'info':'success') ?>"><?= htmlspecialchars(ucfirst($k['role'] ?? '-')) ?></span></td>
          <td><span class="badge bg-<?= !empty($k['status'])?'success':'secondary' ?>"><?= !empty($k['status'])?'Aktif':'Nonaktif' ?></span></td>
        </tr>
        <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
  <div class="card-footer text-muted small">
    <i class="fas fa-info-circle me-1"></i>Data hanya tampil. Untuk tambah / edit / nonaktifkan, gunakan menu <strong>Pengaturan &gt; Pengguna / Kader</strong> (admin).
  </div>
</div>
</div></section>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
