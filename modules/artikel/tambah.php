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

$page_title = 'Form Artikel';
require_once __DIR__ . '/../../includes/header.php';
if (!canInput()) {
    echo '<script>window.location="' . APP_URL . '/dashboard.php";</script>';
    exit;
}
$kategoriList = ['Kesehatan', 'Gizi', 'Imunisasi', 'Posyandu', 'Ibu & Anak', 'Lainnya'];
?>
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0">Form Artikel</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="<?= APP_URL ?>/dashboard.php"><i class="fas fa-home"></i> Beranda</a></li>
          <li class="breadcrumb-item"><a href="index.php">Daftar Artikel</a></li>
          <li class="breadcrumb-item active">Form Artikel</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<section class="content">
<div class="container-fluid">

  <a href="index.php" class="btn btn-info btn-block mb-3 text-white">
    <i class="fas fa-arrow-left me-1"></i> Kembali Ke Daftar Artikel
  </a>

  <form id="formArtikel" enctype="multipart/form-data" method="post"
        action="<?= APP_URL ?>/ajax/save_artikel.php" autocomplete="off">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
    <input type="hidden" name="aksi" id="aksi" value="draft">

    <!-- Judul -->
    <div class="card shadow-sm border-0 mb-3">
      <div class="card-body">
        <div class="mb-0">
          <label class="form-label fw-semibold">Judul Artikel <span class="text-danger">*</span></label>
          <input type="text" name="judul" id="judul" class="form-control form-control-lg"
                 required minlength="5" maxlength="200"
                 placeholder="Judul Artikel">
          <div class="form-text text-danger" id="judulHelp">
            Judul artikel minimal 5 karakter dan maksimal 200 karakter
          </div>
        </div>
      </div>
    </div>

    <!-- Isi + TinyMCE -->
    <div class="card shadow-sm border-0 mb-3">
      <div class="card-body">
        <label class="form-label fw-semibold">Isi Artikel <span class="text-danger">*</span></label>
        <textarea name="isi_artikel" id="isi_artikel" class="form-control" rows="14"></textarea>
      </div>
    </div>

    <!-- Unggah Gambar -->
    <div class="card shadow-sm border-0 mb-3">
      <div class="card-header bg-white d-flex justify-content-between align-items-center py-2"
           data-bs-toggle="collapse" data-bs-target="#boxGambar" style="cursor:pointer">
        <span class="fw-semibold"><i class="fas fa-image me-2 text-primary"></i>Unggah Gambar</span>
        <i class="fas fa-plus text-muted"></i>
      </div>
      <div id="boxGambar" class="collapse show">
        <div class="card-body">
          <input type="file" name="gambar" id="gambar" class="form-control"
                 accept=".jpg,.jpeg,.png,image/jpeg,image/png">
          <small class="text-muted">Format: JPG / JPEG / PNG · Maksimal 2 MB</small>
          <div id="previewGambar" class="mt-2 d-none">
            <img src="" alt="Preview" class="img-thumbnail" style="max-height:160px">
          </div>
        </div>
      </div>
    </div>

    <!-- Pengaturan Lainnya -->
    <div class="card shadow-sm border-0 mb-3">
      <div class="card-header bg-white border-bottom border-primary border-2 py-2"
           data-bs-toggle="collapse" data-bs-target="#boxLainnya" style="cursor:pointer">
        <span class="fw-semibold text-primary"><i class="fas fa-cog me-2"></i>Pengaturan Lainnya</span>
        <i class="fas fa-minus float-end text-muted"></i>
      </div>
      <div id="boxLainnya" class="collapse show">
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Kategori</label>
              <select name="kategori" class="form-select">
                <?php foreach ($kategoriList as $k): ?>
                <option value="<?= htmlspecialchars($k) ?>"><?= htmlspecialchars($k) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Tanggal Posting</label>
              <input type="date" name="tanggal_publish" class="form-control" value="">
              <small class="text-muted">
                (Kosongkan jika ingin langsung diposting setelah disetujui; bisa diisi untuk jadwal)
              </small>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Aksi -->
    <div class="d-flex flex-wrap gap-2 justify-content-between mb-4">
      <a href="index.php" class="btn btn-danger">
        <i class="fas fa-times me-1"></i> Batal
      </a>
      <div class="d-flex flex-wrap gap-2">
        <button type="submit" class="btn btn-secondary"
                onclick="document.getElementById('aksi').value='draft'">
          <i class="fas fa-save me-1"></i> Simpan Draft
        </button>
        <button type="submit" class="btn btn-primary"
                onclick="document.getElementById('aksi').value='kirim'">
          <i class="fas fa-check me-1"></i> Simpan &amp; Kirim Publikasi
        </button>
      </div>
    </div>
  </form>

</div>
</section>

<script src="https://cdn.jsdelivr.net/npm/tinymce@6.8.3/tinymce.min.js" referrerpolicy="origin"></script>
<script>
(function() {
  tinymce.init({
    selector: '#isi_artikel',
    height: 360,
    menubar: false,
    plugins: 'lists link image table code autoresize',
    toolbar: 'undo redo | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist | link image | removeformat | code',
    branding: false,
    language: 'id',
    language_url: 'https://cdn.jsdelivr.net/npm/tinymce-i18n@23.10.9/langs6/id.js',
    content_style: 'body { font-family: Source Sans Pro, Inter, sans-serif; font-size: 15px; }',
    relative_urls: false,
    remove_script_host: false,
    convert_urls: true,
    setup: function(editor) {
      editor.on('change keyup', function() {
        editor.save();
      });
    }
  });

  // Validasi judul live
  var judul = document.getElementById('judul');
  var help = document.getElementById('judulHelp');
  function cekJudul() {
    var len = (judul.value || '').trim().length;
    if (len > 0 && (len < 5 || len > 200)) {
      help.classList.remove('d-none');
      judul.classList.add('is-invalid');
    } else {
      help.classList.add('d-none');
      judul.classList.remove('is-invalid');
    }
  }
  judul.addEventListener('input', cekJudul);
  help.classList.add('d-none');

  // Preview gambar
  document.getElementById('gambar').addEventListener('change', function(e) {
    var f = e.target.files[0];
    var box = document.getElementById('previewGambar');
    if (!f) { box.classList.add('d-none'); return; }
    if (f.size > 2 * 1024 * 1024) {
      alert('Ukuran gambar maksimal 2 MB');
      e.target.value = '';
      box.classList.add('d-none');
      return;
    }
    var reader = new FileReader();
    reader.onload = function(ev) {
      box.querySelector('img').src = ev.target.result;
      box.classList.remove('d-none');
    };
    reader.readAsDataURL(f);
  });

  document.getElementById('formArtikel').addEventListener('submit', function(e) {
    e.preventDefault();
    if (typeof tinymce !== 'undefined') tinymce.triggerSave();
    var j = (judul.value || '').trim();
    if (j.length < 5 || j.length > 200) {
      help.classList.remove('d-none');
      judul.classList.add('is-invalid');
      judul.focus();
      if (typeof showError === 'function') showError('Judul artikel minimal 5 dan maksimal 200 karakter');
      return;
    }
    var isi = (document.getElementById('isi_artikel').value || '').replace(/<[^>]+>/g, '').trim();
    if (!isi) {
      if (typeof showError === 'function') showError('Isi artikel wajib diisi');
      return;
    }
    ajaxSubmitForm(this, { redirect: 'index.php' });
  });
})();
</script>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
