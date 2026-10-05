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

$page_title = 'Backup Database';
require_once __DIR__ . '/../../includes/header.php';
if (!isAdmin()) { echo '<script>window.location="../../dashboard.php";</script>'; exit; }
// List backup files
$backup_dir = __DIR__ . '/../../uploads/backup/';
if (!is_dir($backup_dir)) mkdir($backup_dir, 0755, true);
$backups = glob($backup_dir . '*.sql');
$backups = array_reverse($backups);
?>
<section class="content-header"><div class="container-fluid"><div class="row mb-2">
  <div class="col-sm-6"><h1><i class="fas fa-database me-2 text-primary"></i>Backup Database</h1></div>
  <div class="col-sm-6"><ol class="breadcrumb float-sm-end"><li class="breadcrumb-item"><a href="../../dashboard.php">Dashboard</a></li><li class="breadcrumb-item active">Backup</li></ol></div>
</div></div></section>
<section class="content"><div class="container-fluid">
<div class="row">
  <div class="col-md-6">
    <div class="card">
      <div class="card-header bg-success text-white"><h5 class="mb-0"><i class="fas fa-download me-2"></i>Buat Backup</h5></div>
      <div class="card-body">
        <p>Backup akan mengunduh file SQL yang berisi seluruh data database.</p>
        <form id="formBackup" method="POST" action="<?= APP_URL ?>/ajax/backup_database.php">
          <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
          <div class="mb-3"><label class="form-label fw-semibold">Nama Backup (Opsional)</label><input type="text" name="nama_backup" class="form-control" placeholder="Contoh: backup_bulan_juni" value="backup_<?= date('Y-m-d_His') ?>"></div>
          <button type="submit" class="btn btn-success btn-lg w-100"><i class="fas fa-download me-2"></i>Unduh Backup Sekarang</button>
        </form>
      </div>
    </div>
    <div class="card mt-3">
      <div class="card-header bg-warning"><h5 class="mb-0"><i class="fas fa-upload me-2"></i>Restore Database</h5></div>
      <div class="card-body">
        <div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i><strong>Perhatian!</strong> Restore akan mengganti SELURUH data. Pastikan Anda memiliki backup terlebih dahulu!</div>
        <form id="formRestore" method="POST" action="<?= APP_URL ?>/ajax/restore_database.php" enctype="multipart/form-data">
          <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
          <div class="mb-3"><label class="form-label fw-semibold">Upload File SQL</label><input type="file" name="sql_file" class="form-control" accept=".sql" required></div>
          <button type="submit" class="btn btn-danger btn-lg w-100" onclick="return confirm('Yakin mau restore? Semua data akan diganti!')"><i class="fas fa-upload me-2"></i>Restore Database</button>
        </form>
      </div>
    </div>
  </div>
  <div class="col-md-6">
    <div class="card">
      <div class="card-header"><h5 class="mb-0"><i class="fas fa-history me-2"></i>Riwayat Backup</h5></div>
      <div class="card-body p-0">
        <?php if ($backups): ?>
        <div class="list-group list-group-flush">
          <?php foreach (array_slice($backups, 0, 10) as $b): ?>
          <?php $fname = basename($b); $fsize = round(filesize($b)/1024,1); $fdate = date('d/m/Y H:i', filemtime($b)); ?>
          <div class="list-group-item d-flex justify-content-between align-items-center">
            <div><div class="fw-semibold small"><i class="fas fa-file-archive me-2 text-success"></i><?= $fname ?></div><small class="text-muted"><?= $fdate ?> &bull; <?= $fsize ?> KB</small></div>
            <div class="d-flex gap-1">
              <a href="<?= APP_URL ?>/ajax/download_backup.php?file=<?= urlencode($fname) ?>" class="btn btn-sm btn-success"><i class="fas fa-download"></i></a>
              <button class="btn btn-sm btn-danger" onclick="confirmDelete('<?= APP_URL ?>/ajax/delete_backup.php?file=<?= urlencode($fname) ?>','File backup ini')"><i class="fas fa-trash"></i></button>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php else: ?><div class="text-center py-4 text-muted"><i class="fas fa-inbox fa-2x mb-2 d-block"></i>Belum ada riwayat backup</div><?php endif; ?>
      </div>
    </div>
  </div>
</div>
</div></section>
<script>
$('#formBackup').on('submit', function(e) {
  e.preventDefault();
  Swal.fire({ title: 'Memproses Backup...', html: 'Sedang mengambil data...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
  $.ajax({
    url: this.action, type: 'POST', data: $(this).serialize(),
    xhrFields: { responseType: 'blob' },
    success: function(blob, status, xhr) {
      Swal.close();
      const fn = xhr.getResponseHeader('Content-Disposition')?.match(/filename="(.+)"/)?.[1] || 'backup.sql';
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a'); a.href = url; a.download = fn; a.click(); URL.revokeObjectURL(url);
      showToast('Backup berhasil diunduh!', 'success');
    },
    error: () => { Swal.close(); Swal.fire('Error!', 'Gagal membuat backup', 'error'); }
  });
});
</script>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
