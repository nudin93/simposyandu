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
 * Pengembang  : Zainudin Larau
 * Tahun       : 2026
 * ==========================================================
 */

$page_title = 'Tambah Kader';
require_once __DIR__ . '/../../includes/header.php';
if (!isAdmin()) { echo '<script>window.location="' . APP_URL . '/dashboard.php";</script>'; exit; }
?>
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0"><i class="fas fa-user-plus me-2"></i>Tambah Kader</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="<?= APP_URL ?>/dashboard.php">Beranda</a></li>
          <li class="breadcrumb-item"><a href="index.php">Kader</a></li>
          <li class="breadcrumb-item active">Tambah</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<section class="content">
<div class="container-fluid">
  <div class="card card-kader-modern">
    <div class="form-hero-kader">
      <h5><i class="fas fa-user-plus me-2"></i>Form Data Kader / User</h5>
      <p>Isi data petugas. Role Admin otomatis mendapat semua hak akses menu.</p>
    </div>
    <div class="card-body">
      <form method="POST" action="<?= APP_URL ?>/ajax/save_kader.php" enctype="multipart/form-data" id="formKader" class="form-ajax">
        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

        <div class="form-section-title"><i class="fas fa-id-card me-1"></i> Identitas</div>
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
            <input type="text" name="nama" class="form-control" required placeholder="Nama lengkap petugas">
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">NIK</label>
            <input type="text" name="nik" class="form-control" maxlength="16" placeholder="16 digit NIK">
          </div>
          <div class="col-md-4">
            <label class="form-label fw-semibold">Jenis Kelamin</label>
            <select name="jenis_kelamin" class="form-select select2">
              <option value="P">Perempuan</option>
              <option value="L">Laki-laki</option>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label fw-semibold">No. HP</label>
            <div class="input-group">
              <span class="input-group-text"><i class="fas fa-phone"></i></span>
              <input type="text" name="no_hp" class="form-control" placeholder="08xxxxxxxxxx">
            </div>
          </div>
          <div class="col-md-4">
            <label class="form-label fw-semibold">Jabatan</label>
            <select name="jabatan" class="form-select select2">
              <option>Kader</option>
              <option>Ketua Kader</option>
              <option>Bidan</option>
              <option>Admin</option>
            </select>
          </div>
          <div class="col-12">
            <label class="form-label fw-semibold">Alamat</label>
            <textarea name="alamat" class="form-control" rows="2" placeholder="Alamat lengkap"></textarea>
          </div>
        </div>

        <div class="form-section-title"><i class="fas fa-lock me-1"></i> Akun Login</div>
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label fw-semibold">Username <span class="text-danger">*</span></label>
            <input type="text" name="username" class="form-control" required autocomplete="off" placeholder="username">
          </div>
          <div class="col-md-4">
            <label class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
            <div class="input-group">
              <input type="password" name="password" class="form-control" required autocomplete="new-password" placeholder="Minimal 6 karakter">
              <button type="button" class="btn btn-outline-secondary" onclick="var i=this.previousElementSibling;i.type=i.type===\'password\'?\'text\':\'password\'"><i class="fas fa-eye"></i></button>
            </div>
          </div>
          <div class="col-md-4">
            <label class="form-label fw-semibold">Role</label>
            <select name="role" id="roleSelect" class="form-select select2">
              <option value="kader">Kader</option>
              <option value="bidan">Bidan</option>
              <option value="admin">Admin Desa</option>
              <option value="kepala_desa">Kepala Desa</option>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">Foto Profil</label>
            <input type="file" name="foto" class="form-control" accept=".jpg,.jpeg,.png">
            <small class="text-muted">JPG, PNG (Max 2MB)</small>
          </div>
        </div>

        <div id="permissionBox">
          <?= function_exists('renderPermissionCheckboxes') ? renderPermissionCheckboxes([]) : '<div class="alert alert-warning mt-3">Helper permission belum dimuat.</div>' ?>
        </div>

        <div class="mt-4 d-flex flex-wrap gap-2 justify-content-end">
          <a href="index.php" class="btn btn-secondary btn-lg"><i class="fas fa-times me-2"></i>Batal</a>
          <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save me-2"></i>Simpan Kader</button>
        </div>
      </form>
    </div>
  </div>
</div>
</section>
<?php
$extra_js = <<<'JS'
<script>
$(document).ready(function() {
  function togglePermBox() {
    var role = $('#roleSelect').val() || document.getElementById('roleSelect').value;
    var box = document.getElementById('permissionBox');
    if (box) box.style.display = (role === 'admin') ? 'none' : 'block';
  }
  $('#roleSelect').on('change', togglePermBox);
  togglePermBox();

  $('#formKader').off('submit').on('submit', function(e) {
    e.preventDefault();
    e.stopImmediatePropagation();
    if (typeof showLoading === 'function') showLoading('Menyimpan data kader...');
    $.ajax({
      url: this.action,
      type: 'POST',
      data: new FormData(this),
      processData: false,
      contentType: false,
      dataType: 'json',
      success: function(r) {
        if (typeof hideLoading === 'function') hideLoading();
        if (r && r.success) {
          Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: r.message || 'Data kader tersimpan',
            timer: 2200,
            showConfirmButton: false,
            heightAuto: false
          }).then(function() { window.location.href = 'index.php'; });
        } else {
          Swal.fire({ icon: 'error', title: 'Gagal!', text: (r && r.message) ? r.message : 'Terjadi kesalahan', heightAuto: false });
        }
      },
      error: function(xhr) {
        if (typeof hideLoading === 'function') hideLoading();
        var msg = 'Koneksi bermasalah';
        try { var r = JSON.parse(xhr.responseText); if (r.message) msg = r.message; } catch (err) {}
        Swal.fire({ icon: 'error', title: 'Error!', text: msg, heightAuto: false });
      }
    });
    return false;
  });
});
</script>
JS;
include __DIR__ . '/../../includes/footer.php';
?>
