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

$page_title = 'Pengaturan Aplikasi';
require_once __DIR__ . '/../../includes/header.php';
if (!isAdmin()) { echo '<script>window.location="../../dashboard.php";</script>'; exit; }
$set = getPengaturan();
$set = is_array($set) ? $set : [];
?>
<section class="content-header"><div class="container-fluid"><div class="row mb-2">
  <div class="col-sm-6"><h1><i class="fas fa-cog me-2 text-primary"></i>Pengaturan Aplikasi</h1></div>
  <div class="col-sm-6"><ol class="breadcrumb float-sm-end"><li class="breadcrumb-item"><a href="../../dashboard.php">Dashboard</a></li><li class="breadcrumb-item active">Pengaturan</li></ol></div>
</div></div></section>
<section class="content"><div class="container-fluid">
<div class="card">
  <div class="card-header bg-primary text-white"><h5 class="mb-0"><i class="fas fa-cog me-2"></i>Pengaturan Sistem</h5></div>
  <div class="card-body">
    <form method="POST" action="../../ajax/save_pengaturan.php" enctype="multipart/form-data" id="formPengaturan">
    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
    <div class="row g-4">
      <div class="col-12"><h6 class="fw-bold text-primary border-bottom pb-2"><i class="fas fa-info-circle me-2"></i>Informasi Posyandu</h6></div>
      <div class="col-md-6"><label class="form-label fw-semibold">Nama Aplikasi</label><input type="text" name="nama_aplikasi" class="form-control" value="<?= htmlspecialchars($set['nama_aplikasi']??'') ?>"></div>
      <div class="col-md-6"><label class="form-label fw-semibold">Nama Posyandu</label><input type="text" name="nama_posyandu" class="form-control" value="<?= htmlspecialchars($set['nama_posyandu']??'') ?>"></div>
      <div class="col-md-4"><label class="form-label fw-semibold">Nama Desa</label><input type="text" name="nama_desa" class="form-control" value="<?= htmlspecialchars($set['nama_desa']??'') ?>"></div>
      <div class="col-md-4"><label class="form-label fw-semibold">Kecamatan</label><input type="text" name="kecamatan" class="form-control" value="<?= htmlspecialchars($set['kecamatan']??'') ?>"></div>
      <div class="col-md-4"><label class="form-label fw-semibold">Kabupaten</label><input type="text" name="kabupaten" class="form-control" value="<?= htmlspecialchars($set['kabupaten']??'') ?>"></div>
      <div class="col-md-4"><label class="form-label fw-semibold">Provinsi</label><input type="text" name="provinsi" class="form-control" value="<?= htmlspecialchars($set['provinsi']??'') ?>"></div>
      <div class="col-md-4"><label class="form-label fw-semibold">No. Kontak</label><input type="text" name="nomor_kontak" class="form-control" value="<?= htmlspecialchars($set['nomor_kontak']??'') ?>"></div>
      <div class="col-md-4"><label class="form-label fw-semibold">Email</label><input type="email" name="email" class="form-control" value="<?= htmlspecialchars($set['email']??'') ?>"></div>
      <div class="col-12"><h6 class="fw-bold text-primary border-bottom pb-2 mt-2"><i class="fas fa-palette me-2"></i>Tampilan</h6></div>
      <div class="col-md-4">
        <label class="form-label fw-semibold">Warna Tema</label>
        <select name="warna_tema" class="form-select select2">
          <?php foreach(['blue','green','red','purple','orange','teal','indigo','cyan','pink'] as $w): ?>
          <option value="<?= $w ?>" <?= ($set['warna_tema']??'blue')==$w?'selected':'' ?>><?= ucfirst($w) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label fw-semibold">Dark Mode Default</label>
        <div class="form-check form-switch mt-2"><input class="form-check-input" type="checkbox" name="dark_mode" id="darkMode" value="1" <?= ($set['dark_mode']??0)?'checked':'' ?>><label class="form-check-label" for="darkMode">Aktifkan Dark Mode</label></div>
      </div>
      <div class="col-12"><h6 class="fw-bold text-primary border-bottom pb-2 mt-2"><i class="fas fa-image me-2"></i>Logo & Favicon</h6></div>
      <div class="col-md-4">
        <label class="form-label fw-semibold">Logo Aplikasi</label>
        <?php if (!empty($set['logo'])): ?><div class="mb-2"><img src="<?= APP_URL ?>/uploads/settings/<?= htmlspecialchars($set['logo']) ?>" style="max-height:60px;object-fit:contain" alt="Logo"></div><?php endif; ?>
        <input type="file" name="logo" class="form-control" accept=".jpg,.jpeg,.png,.svg">
        <small class="text-muted">JPG, PNG, SVG (Max 2MB)</small>
      </div>
      <div class="col-md-4">
        <label class="form-label fw-semibold">Favicon</label>
        <input type="file" name="favicon" class="form-control" accept=".ico,.png">
        <small class="text-muted">ICO, PNG</small>
      </div>

      <div class="col-12"><h6 class="fw-bold text-primary border-bottom pb-2 mt-3"><i class="fas fa-shield-alt me-2"></i>Google reCAPTCHA (Keamanan Login)</h6></div>
      <div class="col-12">
        <div class="alert alert-info py-2 small mb-3">
          <i class="fas fa-info-circle me-1"></i>
          Daftar di <a href="https://www.google.com/recaptcha/admin" target="_blank" rel="noopener">Google reCAPTCHA Admin</a>
          → pilih <b>reCAPTCHA v2</b> → “I’m not a robot” Checkbox → isi domain situs Anda.
          Setelah Site Key & Secret Key diisi dan disimpan, kotak “Saya bukan robot” muncul di halaman login.
          Kosongkan keduanya jika ingin menonaktifkan reCAPTCHA.
        </div>
      </div>
      <div class="col-md-6">
        <label class="form-label fw-semibold">reCAPTCHA Site Key</label>
        <input type="text" name="recaptcha_site_key" class="form-control" placeholder="6Le..." value="<?= htmlspecialchars($set['recaptcha_site_key'] ?? '') ?>" autocomplete="off">
        <small class="text-muted">Kunci publik (boleh terlihat di halaman login)</small>
      </div>
      <div class="col-md-6">
        <label class="form-label fw-semibold">reCAPTCHA Secret Key</label>
        <input type="password" name="recaptcha_secret_key" class="form-control" placeholder="6Le..." value="<?= htmlspecialchars($set['recaptcha_secret_key'] ?? '') ?>" autocomplete="new-password" id="recaptchaSecret">
        <small class="text-muted">Kunci rahasia (hanya di server)
          <a href="javascript:void(0)" onclick="var i=document.getElementById('recaptchaSecret');i.type=i.type==='password'?'text':'password'">tampilkan/sembunyikan</a>
        </small>
      </div>
    </div>
    <div class="mt-4 d-flex justify-content-end"><button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save me-2"></i>Simpan Pengaturan</button></div>
    </form>
  </div>
</div>
</div></section>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
<script>
(function() {
  var form = document.getElementById('formPengaturan');
  if (!form) return;
  form.addEventListener('submit', function(e) {
    e.preventDefault();
    if (typeof showLoading === 'function') showLoading('Menyimpan pengaturan...');
    var fd = new FormData(form);
    fetch(form.action, { method: 'POST', body: fd })
      .then(function(res) { return res.json(); })
      .then(function(r) {
        if (typeof hideLoading === 'function') hideLoading();
        if (r && r.success) {
          if (typeof Swal !== 'undefined') {
            Swal.fire({ icon: 'success', title: 'Berhasil!', text: r.message || 'Pengaturan tersimpan', timer: 2000, showConfirmButton: false, heightAuto: false })
              .then(function() { location.reload(); });
          } else {
            alert(r.message || 'Pengaturan tersimpan');
            location.reload();
          }
        } else {
          var msg = (r && r.message) ? r.message : 'Gagal menyimpan';
          if (typeof Swal !== 'undefined') Swal.fire('Gagal!', msg, 'error');
          else alert(msg);
        }
      })
      .catch(function() {
        if (typeof hideLoading === 'function') hideLoading();
        if (typeof Swal !== 'undefined') Swal.fire('Error!', 'Koneksi bermasalah', 'error');
        else alert('Koneksi bermasalah');
      });
  });
})();
</script>
