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

$page_title = 'Profil Saya';
require_once __DIR__ . '/../../includes/header.php';

$uid = (int)($user['id'] ?? 0);
$my_data = $uid > 0 ? fetchOne("SELECT * FROM kader WHERE id=" . $uid) : null;

if (!$my_data || !is_array($my_data)) {
    echo '<section class="content"><div class="container-fluid"><div class="alert alert-danger mt-3">
        Data profil tidak ditemukan. Silakan login ulang.
        <a href="' . htmlspecialchars(defined('APP_URL') ? APP_URL : '') . '/logout.php" class="alert-link">Keluar</a>
    </div></div></section>';
    include __DIR__ . '/../../includes/footer.php';
    exit;
}

$fotoUrl = !empty($my_data['foto'])
    ? APP_URL . '/uploads/kader/' . rawurlencode($my_data['foto'])
    : "data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 64 64'><circle cx='32' cy='32' r='32' fill='%23ccfbf1'/><circle cx='32' cy='24' r='11' fill='%230f766e'/><path d='M10 56c4-12 12-17 22-17s18 5 22 17' fill='%230f766e'/></svg>";
?>
<section class="content-header"><div class="container-fluid"><div class="row mb-2">
  <div class="col-sm-6"><h1><i class="fas fa-user-circle me-2"></i>Profil Saya</h1></div>
  <div class="col-sm-6"><ol class="breadcrumb float-sm-end"><li class="breadcrumb-item"><a href="<?= APP_URL ?>/dashboard.php">Dashboard</a></li><li class="breadcrumb-item active">Profil</li></ol></div>
</div></div></section>
<section class="content"><div class="container-fluid">
<div class="row">
  <div class="col-md-4">
    <div class="card card-primary card-outline">
      <div class="card-body box-profile">
        <div class="text-center">
          <img class="profile-user-img img-fluid img-circle" style="width:100px;height:100px;object-fit:cover"
            src="<?= htmlspecialchars($fotoUrl) ?>" alt="Foto Profil">
        </div>
        <h3 class="profile-username text-center"><?= htmlspecialchars($my_data['nama'] ?? '-') ?></h3>
        <p class="text-muted text-center"><?= htmlspecialchars($my_data['jabatan'] ?? '-') ?></p>
        <ul class="list-group list-group-unbordered mb-3">
          <li class="list-group-item"><b>Username</b><span class="float-end"><?= htmlspecialchars($my_data['username'] ?? '-') ?></span></li>
          <li class="list-group-item"><b>Role</b><span class="float-end"><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $my_data['role'] ?? '-'))) ?></span></li>
          <li class="list-group-item"><b>NIK</b><span class="float-end"><?= htmlspecialchars($my_data['nik'] ?? '-') ?></span></li>
          <li class="list-group-item"><b>No. HP</b><span class="float-end"><?= htmlspecialchars($my_data['no_hp'] ?? '-') ?></span></li>
        </ul>
      </div>
    </div>
  </div>
  <div class="col-md-8">
    <div class="card">
      <div class="card-header"><h5 class="mb-0"><i class="fas fa-edit me-2"></i>Edit Profil</h5></div>
      <div class="card-body">
        <form method="POST" action="<?= APP_URL ?>/ajax/update_profile.php" enctype="multipart/form-data" id="formProfile" class="form-ajax">
        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
        <input type="hidden" name="id" value="<?= (int)$my_data['id'] ?>">
        <div class="row g-3">
          <div class="col-md-6"><label class="form-label fw-semibold">Nama Lengkap</label><input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($my_data['nama']) ?>" required></div>
          <div class="col-md-6"><label class="form-label fw-semibold">NIK</label><input type="text" name="nik" class="form-control" value="<?= $my_data['nik']??'' ?>" maxlength="16"></div>
          <div class="col-md-6"><label class="form-label fw-semibold">No. HP</label><input type="text" name="no_hp" class="form-control" value="<?= $my_data['no_hp']??'' ?>"></div>
          <div class="col-12"><label class="form-label fw-semibold">Alamat</label><textarea name="alamat" class="form-control" rows="3"><?= htmlspecialchars($my_data['alamat']??'') ?></textarea></div>
          <div class="col-md-6"><label class="form-label fw-semibold">Foto Profil</label><input type="file" name="foto" class="form-control" accept=".jpg,.jpeg,.png"><small class="text-muted">Kosongkan jika tidak ubah foto</small></div>
          <div class="col-12"><hr><h6 class="fw-bold">Ganti Password</h6></div>
          <div class="col-md-4"><label class="form-label fw-semibold">Password Lama</label><input type="password" name="old_password" class="form-control" autocomplete="current-password"></div>
          <div class="col-md-4"><label class="form-label fw-semibold">Password Baru</label><input type="password" name="new_password" class="form-control" autocomplete="new-password"></div>
          <div class="col-md-4"><label class="form-label fw-semibold">Konfirmasi Password</label><input type="password" name="confirm_password" class="form-control" autocomplete="new-password"></div>
        </div>
        <div class="mt-4 d-flex justify-content-end"><button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save me-2"></i>Simpan Perubahan</button></div>
        </form>
      </div>
    </div>
  </div>
</div>
</div></section>
<?php
$extra_js = <<<'JS'
<script>
$(document).ready(function() {
  // Override form-ajax khusus profil agar popup lebih jelas
  $('#formProfile').off('submit').on('submit', function(e) {
    e.preventDefault();
    e.stopImmediatePropagation();
    var form = this;
    if (typeof showLoading === 'function') showLoading('Menyimpan profil...');
    $.ajax({
      url: form.action,
      type: 'POST',
      data: new FormData(form),
      processData: false,
      contentType: false,
      dataType: 'json',
      success: function(r) {
        if (typeof hideLoading === 'function') hideLoading();
        if (r && r.success) {
          Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: r.message || 'Profil berhasil diperbarui!',
            confirmButtonText: 'OK',
            confirmButtonColor: '#0f766e',
            timer: 2500,
            timerProgressBar: true,
            heightAuto: false
          }).then(function() {
            location.reload();
          });
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: (r && r.message) ? r.message : 'Terjadi kesalahan saat menyimpan profil',
            confirmButtonColor: '#dc3545',
            heightAuto: false
          });
        }
      },
      error: function(xhr) {
        if (typeof hideLoading === 'function') hideLoading();
        var msg = 'Koneksi bermasalah';
        try {
          var r = JSON.parse(xhr.responseText);
          if (r && r.message) msg = r.message;
        } catch (err) {}
        Swal.fire({
          icon: 'error',
          title: 'Error!',
          text: msg,
          confirmButtonColor: '#dc3545',
          heightAuto: false
        });
      }
    });
    return false;
  });
});
</script>
JS;
include __DIR__ . '/../../includes/footer.php';
?>
