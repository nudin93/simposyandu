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

$page_title = 'Tambah Vitamin';
require_once __DIR__ . '/../../includes/header.php';
$pre_balita_id = (int)($_GET['balita_id'] ?? 0);
$pre_balita = $pre_balita_id ? fetchOne("SELECT id, nama_lengkap FROM balita WHERE id=$pre_balita_id") : null;
$balita_list = fetchAll("SELECT id, nama_lengkap, nomor_peserta FROM balita WHERE status_aktif=1 ORDER BY nama_lengkap");
$kader_list = fetchAll("SELECT id, nama FROM kader WHERE status=1");
?>
<section class="content-header"><div class="container-fluid"><div class="row mb-2">
  <div class="col-sm-6"><h1><i class="fas fa-pills me-2 text-success"></i>Tambah Vitamin</h1></div>
  <div class="col-sm-6"><ol class="breadcrumb float-sm-end"><li class="breadcrumb-item"><a href="../../dashboard.php">Dashboard</a></li><li class="breadcrumb-item"><a href="index.php">Vitamin</a></li><li class="breadcrumb-item active">Tambah</li></ol></div>
</div></div></section>
<section class="content"><div class="container-fluid">
<div class="card">
  <div class="card-header bg-success text-white"><h5 class="mb-0"><i class="fas fa-pills me-2"></i>Form Pemberian Vitamin</h5></div>
  <div class="card-body">
    <form method="POST" action="../../ajax/save_vitamin.php" id="formVitamin">
    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label fw-semibold">Cari Nama Balita <span class="text-danger">*</span></label>
        <div class="position-relative">
          <div class="input-group">
            <span class="input-group-text"><i class="fas fa-search"></i></span>
            <input type="text" id="cariBalita" class="form-control" placeholder="Ketik huruf awal nama, contoh: A ..." autocomplete="off" value="<?= $pre_balita ? htmlspecialchars($pre_balita['nama_lengkap']) : '' ?>">
          </div>
          <div id="hasilCariBalita" class="list-group shadow w-100"></div>
        </div>
        <input type="hidden" name="balita_id" id="balita_id" value="<?= (int)($pre_balita_id ?? 0) ?>" required>
        <div id="infoBalita" class="d-none"></div>
        <small class="text-muted">Ketik nama lalu klik hasil pencarian (OpenSID / Posyandu)</small>
      </div>
      <div class="col-md-6"><label class="form-label fw-semibold">Jenis Vitamin <span class="text-danger">*</span></label><select name="jenis_vitamin" class="form-select select2" required><option value="">-- Pilih --</option><?php foreach(['Vitamin A (Merah)','Vitamin A (Biru)','Vitamin D','Vitamin C','Zinc','Tablet Fe','Asam Folat','Multivitamin','Lainnya'] as $jv): ?><option><?= $jv ?></option><?php endforeach; ?></select></div>
      <div class="col-md-4"><label class="form-label fw-semibold">Dosis</label><input type="text" name="dosis" class="form-control" placeholder="Contoh: 1 kapsul"></div>
      <div class="col-md-4"><label class="form-label fw-semibold">Tanggal Pemberian <span class="text-danger">*</span></label><input type="date" name="tanggal_pemberian" class="form-control datepicker" value="<?= date('Y-m-d') ?>" required></div>
      <div class="col-md-4"><label class="form-label fw-semibold">Petugas</label><select name="petugas_id" class="form-select select2"><?php foreach ($kader_list as $k): ?><option value="<?= $k['id'] ?>" <?= $k['id']==$user['id']?'selected':'' ?>><?= htmlspecialchars($k['nama']) ?></option><?php endforeach; ?></select></div>
      <div class="col-12"><label class="form-label fw-semibold">Catatan</label><textarea name="catatan" class="form-control" rows="3"></textarea></div>
    </div>
    <div class="mt-4 d-flex gap-2 justify-content-end">
      <a href="index.php" class="btn btn-secondary btn-lg"><i class="fas fa-times me-2"></i>Batal</a>
      <button type="submit" class="btn btn-success btn-lg"><i class="fas fa-save me-2"></i>Simpan</button>
    </div>
    </form>
  </div>
</div>
</div></section>
<?php
$extra_js = '<script>window.APP_URL="' . APP_URL . '";window.CSRF_TOKEN="' . csrfToken() . '";</script>'
  . '<script src="' . APP_URL . '/assets/js/cari-balita.js?v=2"></script>'
  . '<script>
(function () {
  if (typeof bootCariBalita === "function") {
    bootCariBalita({
      minLen: 1,
      input: "#cariBalita",
      hasil: "#hasilCariBalita",
      hidden: "#balita_id",
      info: "#infoBalita",
      csrf: window.CSRF_TOKEN,
      app: window.APP_URL
    });
  }

  function bindForm() {
    var form = document.getElementById("formVitamin");
    if (!form) return;
    form.addEventListener("submit", function (e) {
      e.preventDefault();
      var balitaId = document.getElementById("balita_id");
      if (!balitaId || !balitaId.value || balitaId.value === "0") {
        if (window.Swal) Swal.fire("Lengkapi data", "Pilih balita: ketik nama lalu klik hasil pencarian.", "warning");
        else alert("Pilih balita terlebih dahulu");
        return;
      }
      if (typeof showLoading === "function") showLoading("Menyimpan...");
      var fd = new FormData(form);
      fetch(form.action, { method: "POST", body: fd, credentials: "same-origin" })
        .then(function (r) { return r.json(); })
        .then(function (res) {
          if (typeof hideLoading === "function") hideLoading();
          if (res && res.success) {
            if (window.Swal) {
              Swal.fire({ icon: "success", title: "Berhasil!", text: res.message || "Tersimpan", timer: 1800, showConfirmButton: false })
                .then(function () { window.location.href = "index.php"; });
            } else {
              alert(res.message || "Tersimpan");
              window.location.href = "index.php";
            }
          } else {
            var msg = (res && res.message) ? res.message : "Gagal menyimpan";
            if (window.Swal) Swal.fire("Gagal!", msg, "error");
            else alert(msg);
          }
        })
        .catch(function () {
          if (typeof hideLoading === "function") hideLoading();
          if (window.Swal) Swal.fire("Error!", "Koneksi bermasalah atau respons tidak valid", "error");
          else alert("Koneksi bermasalah");
        });
    });
  }
  if (document.readyState === "loading") document.addEventListener("DOMContentLoaded", bindForm);
  else bindForm();
})();
</script>';
include __DIR__ . '/../../includes/footer.php';
?>