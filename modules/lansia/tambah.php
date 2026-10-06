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

$page_title = 'Tambah Data Lansia';
require_once __DIR__ . '/../../includes/header.php';
$nomor = generateNomorPeserta('LNS', 'lansia');
?>
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1><i class="fas fa-user-injured me-2 text-warning"></i>Tambah Data Lansia</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-end">
          <li class="breadcrumb-item"><a href="<?= APP_URL ?>/dashboard.php">Dashboard</a></li>
          <li class="breadcrumb-item"><a href="index.php">Lansia</a></li>
          <li class="breadcrumb-item active">Tambah</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<section class="content">
<div class="container-fluid">

  <!-- Pilih dari Data Penduduk -->
  <div class="card border-primary mb-3">
    <div class="card-header bg-primary text-white">
      <h5 class="mb-0"><i class="fas fa-search me-2"></i>Pilih dari Data Penduduk</h5>
    </div>
    <div class="card-body">
      <p class="text-muted small mb-2">Cari warga yang sudah terdaftar. Identitas terisi otomatis.</p>
      <div class="row g-2">
        <div class="col-md-8 position-relative">
          <input type="text" id="cariPenduduk" class="form-control form-control-lg" placeholder="Ketik nama..." autocomplete="off">
          <div id="hasilCari" class="list-group shadow border" style="position:absolute;left:0;right:0;z-index:2000;max-height:280px;overflow-y:auto;display:none;background:#fff;"></div>
        </div>
        <div class="col-md-4">
          <a href="<?= APP_URL ?>/modules/penduduk/tambah.php" class="btn btn-outline-secondary w-100" target="_blank">
            <i class="fas fa-user-plus me-1"></i>Belum ada? Daftarkan dulu
          </a>
        </div>
      </div>
      <div id="infoTerpilih" class="alert alert-success mt-3 mb-0 d-none">
        <i class="fas fa-check-circle me-1"></i>Penduduk terpilih: <strong id="namaTerpilih"></strong>
      </div>
    </div>
  </div>

  <form method="POST" action="<?= APP_URL ?>/ajax/save_lansia.php" id="formLansia">
    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
    <div class="card">
      <div class="card-header bg-warning">
        <h5 class="mb-0"><i class="fas fa-user-injured me-2"></i>Data Lansia</h5>
      </div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label fw-semibold">No. Peserta</label>
            <div class="input-group">
              <span class="input-group-text"><i class="fas fa-id-badge"></i></span>
              <input type="text" name="nomor_peserta" class="form-control" value="<?= htmlspecialchars($nomor) ?>" readonly required>
            </div>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">NIK</label>
            <input type="text" name="nik" id="f_nik" class="form-control" placeholder="Otomatis dari penduduk" maxlength="16" readonly>
          </div>
          <div class="col-md-8">
            <label class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
            <input type="text" name="nama" id="f_nama" class="form-control" required readonly>
          </div>
          <div class="col-md-4">
            <label class="form-label fw-semibold">Jenis Kelamin <span class="text-danger">*</span></label>
            <select name="jenis_kelamin" id="f_jk" class="form-select" required>
              <option value="">-- Pilih --</option>
              <option value="L">Laki-laki</option>
              <option value="P">Perempuan</option>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label fw-semibold">Tempat Lahir</label>
            <input type="text" name="tempat_lahir" id="f_tempat" class="form-control" readonly>
          </div>
          <div class="col-md-4">
            <label class="form-label fw-semibold">Tanggal Lahir <span class="text-danger">*</span></label>
            <input type="date" name="tanggal_lahir" id="f_tgl" class="form-control" required readonly>
          </div>
          <div class="col-md-4">
            <label class="form-label fw-semibold">Umur (Otomatis)</label>
            <input type="text" id="umur_display" class="form-control" readonly>
          </div>
          <div class="col-md-4">
            <label class="form-label fw-semibold">No. HP</label>
            <input type="text" name="no_hp" id="f_hp" class="form-control">
          </div>
          <div class="col-md-4">
            <label class="form-label fw-semibold">Pekerjaan</label>
            <input type="text" name="pekerjaan" id="f_pekerjaan" class="form-control">
          </div>
          <div class="col-md-4">
            <label class="form-label fw-semibold">Status Perkawinan</label>
            <select name="status_perkawinan" id="f_kawin" class="form-select">
              <option>Menikah</option>
              <option>Janda</option>
              <option>Duda</option>
              <option>Belum Menikah</option>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label fw-semibold">Pendidikan</label>
            <input type="text" name="pendidikan" id="f_pendidikan" class="form-control">
          </div>
          <div class="col-md-4">
            <label class="form-label fw-semibold">No. BPJS/KIS</label>
            <input type="text" name="bpjs_kis" class="form-control">
          </div>
          <div class="col-12">
            <label class="form-label fw-semibold">Alamat Lengkap <span class="text-danger">*</span></label>
            <textarea name="alamat_lengkap" id="f_alamat" class="form-control" rows="2" required></textarea>
          </div>
          <div class="col-12">
            <label class="form-label fw-semibold">Riwayat Penyakit</label>
            <textarea name="riwayat_penyakit" class="form-control" rows="2" placeholder="Hipertensi, diabetes, dll."></textarea>
          </div>
          <div class="col-md-4">
            <label class="form-label fw-semibold">Status Aktif</label>
            <div class="form-check form-switch mt-2">
              <input class="form-check-input" type="checkbox" name="status_aktif" value="1" checked>
              <label class="form-check-label">Aktif di Posyandu</label>
            </div>
          </div>
        </div>
      </div>
      <div class="card-footer d-flex justify-content-end gap-2">
        <a href="index.php" class="btn btn-secondary btn-lg"><i class="fas fa-times me-2"></i>Batal</a>
        <button type="submit" class="btn btn-warning btn-lg"><i class="fas fa-save me-2"></i>Simpan Data</button>
      </div>
    </div>
  </form>

</div>
</section>

<?php
$extra_js = '
<script>
function isiFormPenduduk(p) {
  $("#f_nik").val(p.nik || "").prop("readonly", true);
  $("#f_nama").val(p.nama || "").prop("readonly", true);
  $("#f_jk").val(p.jenis_kelamin || "");
  $("#f_tempat").val(p.tempat_lahir || "");
  $("#f_tgl").val(p.tanggal_lahir || "");
  $("#umur_display").val((p.umur != null && p.umur !== "") ? (p.umur + " Tahun") : "");
  $("#f_hp").val(p.no_hp || "");
  $("#f_pekerjaan").val(p.pekerjaan || "");
  $("#f_pendidikan").val(p.pendidikan || "");
  var alamat = (p.alamat || "");
  if (p.dusun) alamat += (alamat ? ", " : "") + "Dusun " + p.dusun;
  if (p.rt || p.rw) alamat += " RT " + (p.rt || "-") + "/RW " + (p.rw || "-");
  $("#f_alamat").val(alamat).prop("readonly", false);
  var sk = p.status_perkawinan || "";
  if (sk.indexOf("Kawin") >= 0 && sk.indexOf("Belum") < 0) $("#f_kawin").val("Menikah");
  else if (sk === "Janda" || sk === "Duda" || sk.indexOf("Cerai") >= 0) $("#f_kawin").val(p.jenis_kelamin == "P" ? "Janda" : "Duda");
  else if (sk) $("#f_kawin").val(sk);
  $("#infoTerpilih").removeClass("d-none");
  $("#namaTerpilih").text((p.nama || "") + (p.nik ? " (" + p.nik + ")" : ""));
}
$(function () {
  var timer = null;
  var $input = $("#cariPenduduk");
  var $hasil = $("#hasilCari");
  $input.on("input keyup", function () {
    clearTimeout(timer);
    var q = $(this).val().trim();
    if (q.length < 1) { $hasil.hide().empty(); return; }
    timer = setTimeout(function () {
      $.getJSON("' . APP_URL . '/ajax/cari_penduduk.php", { q: q })
        .done(function (res) {
          $hasil.empty();
          if (!res.data || !res.data.length) {
            $hasil.append(\'<div class="list-group-item text-muted small">Tidak ditemukan. Daftarkan di Data Penduduk/Keluarga.</div>\').show();
            return;
          }
          res.data.forEach(function (p) {
            var $a = $(\'<a href="#" class="list-group-item list-group-item-action py-2"></a>\');
            $a.html("<strong>" + (p.nama || "") + "</strong> <code class=\\"small ms-1\\">" + (p.nik || "") + "</code><br><small class=\\"text-muted\\">" + (p.umur != null ? p.umur + " th · " : "") + "KK: " + (p.no_kk || "-") + "</small>");
            $a.on("click", function (e) {
              e.preventDefault();
              isiFormPenduduk(p);
              $hasil.hide();
              $input.val(p.nama || "");
            });
            $hasil.append($a);
          });
          $hasil.show();
        })
        .fail(function (xhr) {
          $hasil.html(\'<div class="list-group-item text-danger small">Gagal memuat (\' + (xhr.status || "?") + \').</div>\').show();
        });
    }, 200);
  });
  $(document).on("click", function (e) {
    if (!$(e.target).closest("#cariPenduduk, #hasilCari").length) $hasil.hide();
  });
  $("#formLansia").on("submit", function (e) {
    e.preventDefault();
    if (!$("#f_nama").val()) {
      if (typeof Swal !== "undefined") Swal.fire("Pilih Penduduk", "Ketik nama lalu pilih dari daftar.", "warning");
      else alert("Pilih penduduk dulu");
      return;
    }
    if (typeof showLoading === "function") showLoading("Menyimpan...");
    $.ajax({
      url: this.action,
      type: "POST",
      data: new FormData(this),
      processData: false,
      contentType: false,
      dataType: "json",
      success: function (r) {
        if (typeof hideLoading === "function") hideLoading();
        if (r.success) {
          if (typeof Swal !== "undefined") {
            Swal.fire({ icon: "success", title: "Berhasil!", text: r.message, timer: 2000, showConfirmButton: false })
              .then(function () { location.href = "index.php"; });
          } else { alert(r.message); location.href = "index.php"; }
        } else {
          if (typeof Swal !== "undefined") Swal.fire("Gagal!", r.message || "Error", "error");
          else alert(r.message || "Gagal");
        }
      },
      error: function () {
        if (typeof hideLoading === "function") hideLoading();
        if (typeof Swal !== "undefined") Swal.fire("Error!", "Koneksi bermasalah", "error");
        else alert("Koneksi bermasalah");
      }
    });
  });
});
</script>
';
include __DIR__ . '/../../includes/footer.php';
