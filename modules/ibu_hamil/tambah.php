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

$page_title = 'Tambah Ibu Hamil';
require_once __DIR__ . '/../../includes/header.php';
$nomor = generateNomorPeserta('IBH', 'ibu_hamil');
$provinsi_list = ['Aceh','Sumatera Utara','Sumatera Barat','Riau','Jambi','Sumatera Selatan','Bengkulu','Lampung','Kepulauan Bangka Belitung','Kepulauan Riau','DKI Jakarta','Jawa Barat','Jawa Tengah','DI Yogyakarta','Jawa Timur','Banten','Bali','Nusa Tenggara Barat','Nusa Tenggara Timur','Kalimantan Barat','Kalimantan Tengah','Kalimantan Selatan','Kalimantan Timur','Kalimantan Utara','Sulawesi Utara','Sulawesi Tengah','Sulawesi Selatan','Sulawesi Tenggara','Gorontalo','Sulawesi Barat','Maluku','Maluku Utara','Papua Barat','Papua'];
?>
<section class="content-header"><div class="container-fluid"><div class="row mb-2">
  <div class="col-sm-6"><h1><i class="fas fa-female me-2 text-danger"></i>Tambah Ibu Hamil</h1></div>
  <div class="col-sm-6"><ol class="breadcrumb float-sm-end">
    <li class="breadcrumb-item"><a href="../../dashboard.php">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="index.php">Ibu Hamil</a></li>
    <li class="breadcrumb-item active">Tambah</li>
  </ol></div>
</div></div></section>
<section class="content"><div class="container-fluid">

<!-- Pilih dari Data Penduduk (Perempuan) -->
<div class="card border-primary mb-3">
  <div class="card-header bg-primary text-white"><h5 class="mb-0"><i class="fas fa-search me-2"></i>Pilih dari Data Penduduk</h5></div>
  <div class="card-body">
    <p class="text-muted small mb-2">Cari warga <strong>perempuan</strong> yang sudah terdaftar. Identitas & data suami (dari KK) terisi otomatis.</p>
    <div class="row g-2">
      <div class="col-md-8 position-relative">
        <input type="text" id="cariPenduduk" class="form-control" placeholder="Ketik nama..." autocomplete="off">
        <div id="hasilCari" class="list-group position-absolute shadow w-100" style="z-index:1050; max-height:280px; overflow-y:auto; display:none;"></div>
      </div>
      <div class="col-md-4">
        <a href="<?= APP_URL ?>/modules/keluarga/index.php" class="btn btn-outline-secondary w-100" target="_blank"><i class="fas fa-home me-1"></i>Kelola via Data Keluarga</a>
      </div>
    </div>
    <div id="infoTerpilih" class="alert alert-success mt-3 mb-0 d-none"><i class="fas fa-check-circle me-1"></i>Terpilih: <strong id="namaTerpilih"></strong></div>
  </div>
</div>

<div class="wizard-steps mb-4">
  <div class="wizard-step active" id="step-nav-1" onclick="goStep(1)"><i class="fas fa-female me-1"></i>1. Biodata Ibu</div>
  <div class="wizard-step" id="step-nav-2" onclick="goStep(2)"><i class="fas fa-male me-1"></i>2. Data Suami</div>
  <div class="wizard-step" id="step-nav-3" onclick="goStep(3)"><i class="fas fa-baby-carriage me-1"></i>3. Kehamilan</div>
  <div class="wizard-step" id="step-nav-4" onclick="goStep(4)"><i class="fas fa-map-marker-alt me-1"></i>4. Alamat</div>
  <div class="wizard-step" id="step-nav-5" onclick="goStep(5)"><i class="fas fa-upload me-1"></i>5. Dokumen</div>
</div>
<form method="POST" action="../../ajax/save_ibu_hamil.php" enctype="multipart/form-data" id="formIbuHamil" data-draft="1">
<input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

<!-- STEP 1: Biodata Ibu -->
<div class="wizard-pane" id="pane-1">
<div class="card">
  <div class="card-header bg-danger text-white"><h5 class="mb-0"><i class="fas fa-female me-2"></i>Biodata Ibu</h5></div>
  <div class="card-body">
    <div class="row g-3">
      <div class="col-md-6"><label class="form-label fw-semibold">No. Peserta</label>
        <div class="input-group"><span class="input-group-text"><i class="fas fa-id-badge"></i></span><input type="text" name="nomor_peserta" class="form-control" value="<?= $nomor ?>" readonly required></div></div>
      <div class="col-md-6"><label class="form-label fw-semibold">NIK</label><input type="text" name="nik" id="f_nik" class="form-control" readonly placeholder="16 digit NIK" maxlength="16"></div>
      <div class="col-12"><label class="form-label fw-semibold">Nama Ibu <span class="text-danger">*</span></label><input type="text" name="nama" id="f_nama" class="form-control" placeholder="Nama lengkap ibu" readonly required></div>
      <div class="col-md-4"><label class="form-label fw-semibold">Tempat Lahir</label><input type="text" name="tempat_lahir" id="f_tempat" class="form-control" placeholder="Kota" readonly></div>
      <div class="col-md-4"><label class="form-label fw-semibold">Tanggal Lahir</label>
        <input type="date" name="tanggal_lahir" id="tgl_lahir_ibu" readonly class="form-control datepicker" onchange="hitungUmur($('#tgl_lahir_ibu').val(),'#umur_ibu')"></div>
      <div class="col-md-4"><label class="form-label fw-semibold">Umur (Otomatis)</label><input type="text" id="umur_ibu" class="form-control bg-light" readonly></div>
      <div class="col-md-3"><label class="form-label fw-semibold">Golongan Darah</label><select name="golongan_darah" class="form-select select2"><option value="Tidak Tahu">Tidak Tahu</option><option>A</option><option>B</option><option>AB</option><option>O</option></select></div>
      <div class="col-md-3"><label class="form-label fw-semibold">Pendidikan</label><select name="pendidikan" class="form-select select2"><option>SD</option><option>SMP</option><option>SMA/SMK</option><option>D3</option><option>S1</option><option>S2/S3</option></select></div>
      <div class="col-md-3"><label class="form-label fw-semibold">Pekerjaan</label><select name="pekerjaan" class="form-select select2"><option>Ibu Rumah Tangga</option><option>PNS</option><option>Karyawan Swasta</option><option>Wiraswasta</option><option>Petani</option><option>Guru</option><option>Lainnya</option></select></div>
      <div class="col-md-3"><label class="form-label fw-semibold">No. HP</label><div class="input-group"><span class="input-group-text"><i class="fas fa-phone"></i></span><input type="text" name="no_hp" id="f_hp" class="form-control" placeholder="08xxxxxxxxxx"></div></div>
    </div>
  </div>
  <div class="card-footer d-flex justify-content-end"><button type="button" class="btn btn-danger btn-lg" onclick="nextStep(1)"><i class="fas fa-arrow-right me-2"></i>Selanjutnya</button></div>
</div></div>

<!-- STEP 2: Suami -->
<div class="wizard-pane d-none" id="pane-2">
<div class="card">
  <div class="card-header bg-primary text-white"><h5 class="mb-0"><i class="fas fa-male me-2"></i>Data Suami</h5></div>
  <div class="card-body"><div class="row g-3">
    <div class="col-md-6"><label class="form-label fw-semibold">Nama Suami</label><input type="text" name="nama_suami" id="f_nama_suami" class="form-control" placeholder="Nama lengkap suami"></div>
    <div class="col-md-6"><label class="form-label fw-semibold">NIK Suami</label><input type="text" name="nik_suami" id="f_nik_suami" class="form-control" placeholder="16 digit NIK" maxlength="16"></div>
    <div class="col-md-6"><label class="form-label fw-semibold">Pekerjaan Suami</label><select name="pekerjaan_suami" class="form-select select2"><option>PNS</option><option>TNI/Polri</option><option>Petani</option><option>Nelayan</option><option>Pedagang</option><option>Wiraswasta</option><option>Buruh</option><option>Karyawan Swasta</option><option>Lainnya</option></select></div>
    <div class="col-md-6"><label class="form-label fw-semibold">No. HP Suami</label><div class="input-group"><span class="input-group-text"><i class="fas fa-phone"></i></span><input type="text" name="no_hp_suami" class="form-control" placeholder="08xxxxxxxxxx"></div></div>
  </div></div>
  <div class="card-footer d-flex justify-content-between">
    <button type="button" class="btn btn-secondary btn-lg" onclick="prevStep(2)"><i class="fas fa-arrow-left me-2"></i>Kembali</button>
    <button type="button" class="btn btn-primary btn-lg" onclick="nextStep(2)"><i class="fas fa-arrow-right me-2"></i>Selanjutnya</button>
  </div>
</div></div>

<!-- STEP 3: Kehamilan -->
<div class="wizard-pane d-none" id="pane-3">
<div class="card">
  <div class="card-header bg-success text-white"><h5 class="mb-0"><i class="fas fa-baby-carriage me-2"></i>Data Kehamilan</h5></div>
  <div class="card-body"><div class="row g-3">
    <div class="col-md-3"><label class="form-label fw-semibold">Kehamilan Ke-</label><input type="number" name="kehamilan_ke" class="form-control" value="1" min="1" max="20" required></div>
    <div class="col-md-3"><label class="form-label fw-semibold">HPHT <span class="text-danger">*</span></label><input type="date" name="hpht" id="hpht" class="form-control datepicker" onchange="hitungHPLForm()" required></div>
    <div class="col-md-3"><label class="form-label fw-semibold">HPL (Otomatis)</label><input type="date" name="hpl" id="hpl" class="form-control bg-light" readonly></div>
    <div class="col-md-3"><label class="form-label fw-semibold">Usia Kehamilan</label><input type="text" id="usia_kandungan_display" class="form-control bg-light" readonly placeholder="Otomatis dari HPHT"><input type="hidden" name="usia_kehamilan" id="usia_kehamilan" value="0"></div>
    <div class="col-md-3"><label class="form-label fw-semibold">Berat Awal (kg)</label><div class="input-group"><input type="number" name="berat_awal" class="form-control" step="0.1" min="30" max="150"><span class="input-group-text">kg</span></div></div>
    <div class="col-md-3"><label class="form-label fw-semibold">Tinggi Badan (cm)</label><div class="input-group"><input type="number" name="tinggi_badan" class="form-control" step="0.1" min="100" max="200"><span class="input-group-text">cm</span></div></div>
    <div class="col-md-3"><label class="form-label fw-semibold">LILA (cm)</label><div class="input-group"><input type="number" name="lila" class="form-control" step="0.1"><span class="input-group-text">cm</span></div></div>
    <div class="col-md-3"><label class="form-label fw-semibold">No. BPJS/KIS</label><input type="text" name="bpjs_kis" class="form-control"></div>
    <div class="col-md-6"><label class="form-label fw-semibold">Riwayat Penyakit</label><textarea name="riwayat_penyakit" class="form-control" rows="3"></textarea></div>
    <div class="col-md-6"><label class="form-label fw-semibold">Riwayat Persalinan</label><textarea name="riwayat_persalinan" class="form-control" rows="3"></textarea></div>
  </div></div>
  <div class="card-footer d-flex justify-content-between">
    <button type="button" class="btn btn-secondary btn-lg" onclick="prevStep(3)"><i class="fas fa-arrow-left me-2"></i>Kembali</button>
    <button type="button" class="btn btn-success btn-lg" onclick="nextStep(3)"><i class="fas fa-arrow-right me-2"></i>Selanjutnya</button>
  </div>
</div></div>

<!-- STEP 4: Alamat -->
<div class="wizard-pane d-none" id="pane-4">
<div class="card">
  <div class="card-header bg-warning"><h5 class="mb-0"><i class="fas fa-map-marker-alt me-2"></i>Alamat</h5></div>
  <div class="card-body"><div class="row g-3">
    <div class="col-md-3"><label class="form-label fw-semibold">Dusun</label><input type="text" name="dusun" id="f_dusun" class="form-control"></div>
    <div class="col-md-2"><label class="form-label fw-semibold">RT</label><input type="text" name="rt" id="f_rt" class="form-control" maxlength="5"></div>
    <div class="col-md-2"><label class="form-label fw-semibold">RW</label><input type="text" name="rw" id="f_rw" class="form-control" maxlength="5"></div>
    <div class="col-md-5"><label class="form-label fw-semibold">Desa</label><input type="text" name="desa" class="form-control" value="<?= $pengaturan['nama_desa'] ?>"></div>
    <div class="col-md-4"><label class="form-label fw-semibold">Kecamatan</label><input type="text" name="kecamatan" class="form-control" value="<?= $pengaturan['kecamatan'] ?>"></div>
    <div class="col-md-4"><label class="form-label fw-semibold">Kabupaten</label><input type="text" name="kabupaten" class="form-control" value="<?= $pengaturan['kabupaten'] ?>"></div>
    <div class="col-md-4"><label class="form-label fw-semibold">Provinsi</label><select name="provinsi" class="form-select select2"><?php foreach ($provinsi_list as $p): ?><option <?= $p==$pengaturan['provinsi']?'selected':'' ?>><?= $p ?></option><?php endforeach; ?></select></div>
    <div class="col-12"><label class="form-label fw-semibold">Alamat Lengkap</label><textarea name="alamat_lengkap" id="f_alamat" class="form-control" rows="3"></textarea></div>
  </div></div>
  <div class="card-footer d-flex justify-content-between">
    <button type="button" class="btn btn-secondary btn-lg" onclick="prevStep(4)"><i class="fas fa-arrow-left me-2"></i>Kembali</button>
    <button type="button" class="btn btn-warning btn-lg" onclick="nextStep(4)"><i class="fas fa-arrow-right me-2"></i>Selanjutnya</button>
  </div>
</div></div>

<!-- STEP 5: Dokumen -->
<div class="wizard-pane d-none" id="pane-5">
<div class="card">
  <div class="card-header bg-secondary text-white"><h5 class="mb-0"><i class="fas fa-upload me-2"></i>Upload Dokumen</h5></div>
  <div class="card-body"><div class="row g-3">
    <?php foreach([['foto_ibu','Foto Ibu'],['foto_kia','Buku KIA'],['foto_ktp','KTP'],['foto_bpjs','BPJS/KIS']] as [$name,$label]): ?>
    <div class="col-6 col-md-3"><label class="form-label fw-semibold"><?= $label ?></label>
      <input type="file" name="<?= $name ?>" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
      <small class="text-muted">JPG, PNG, PDF (Max 5MB)</small></div>
    <?php endforeach; ?>
  </div></div>
  <div class="card-footer d-flex justify-content-between">
    <button type="button" class="btn btn-secondary btn-lg" onclick="prevStep(5)"><i class="fas fa-arrow-left me-2"></i>Kembali</button>
    <button type="submit" class="btn btn-danger btn-lg"><i class="fas fa-save me-2"></i>Simpan Data</button>
  </div>
</div></div>
</form>
</div></section>
<?php
ob_start();
?>
<script>
let currentStep = 1;
function goStep(n) {
  $('#pane-' + currentStep).addClass('d-none'); $('#step-nav-' + currentStep).removeClass('active').addClass('done');
  currentStep = n; $('#pane-' + n).removeClass('d-none'); $('#step-nav-' + n).addClass('active').removeClass('done');
}
function nextStep(n) { goStep(n+1); window.scrollTo(0,0); }
function prevStep(n) { goStep(n-1); window.scrollTo(0,0); }
function hitungHPLForm() {
  const hpht = $('#hpht').val(); if (!hpht) return;
  const hpl = hitungHPL(hpht); $('#hpl').val(hpl);
  const minggu = hitungUsiaKandungan(hpht);
  $('#usia_kandungan_display').val(minggu + ' minggu');
  $('#usia_kehamilan').val(minggu);
}
$(document).ready(function() {
  $('#formIbuHamil').on('submit', function(e) {
    e.preventDefault();
    showLoading('Menyimpan data ibu hamil...');
    $.ajax({
      url: this.action, type: 'POST', data: new FormData(this), processData: false, contentType: false,
      success: function(r) {
        hideLoading();
        if (r.success) { Swal.fire({icon:'success',title:'Berhasil!',text:r.message,timer:2000,showConfirmButton:false}).then(()=>window.location.href='index.php'); }
        else { Swal.fire('Gagal!', r.message||'Terjadi kesalahan', 'error'); }
      },
      error: () => { hideLoading(); Swal.fire('Error!', 'Koneksi bermasalah', 'error'); }
    });
  });
});
</script>
<script>
/* using shared helper if available */
var timerCari = null;
$('#cariPenduduk').on('input', function() {
  clearTimeout(timerCari);
  var q = $(this).val().trim();
  if (q.length < 1) { $('#hasilCari').hide().empty(); return; }
  timerCari = setTimeout(function() {
    $.getJSON('<?= APP_URL ?>/ajax/cari_penduduk.php', {q: q, jk: 'P'}, function(res) {
      var box = $('#hasilCari').empty();
      if (!res.data || !res.data.length) {
        box.append('<div class="list-group-item text-muted">Tidak ditemukan. Daftarkan dulu di Data Keluarga sebagai anggota.</div>').show();
        return;
      }
      res.data.forEach(function(p) {
        var item = $('<a href="#" class="list-group-item list-group-item-action"></a>');
        item.html('<strong>'+p.nama+'</strong> <code class="ms-1">'+p.nik+'</code><br><small class="text-muted">KK: '+(p.no_kk||'-')+' | '+(p.umur||'?')+' th | '+(p.dusun||'')+'</small>');
        item.on('click', function(e) {
          e.preventDefault();
          $('#f_nik').val(p.nik||'');
          $('#f_nama').val(p.nama||'');
          $('#f_tempat').val(p.tempat_lahir||'');
          $('#tgl_lahir_ibu').val(p.tanggal_lahir||'');
          if (typeof hitungUmur === 'function') hitungUmur(p.tanggal_lahir,'#umur_ibu');
          $('#f_hp').val(p.no_hp||'');
          $('#f_dusun').val(p.dusun||'');
          $('#f_rt').val(p.rt||'');
          $('#f_rw').val(p.rw||'');
          $('#f_alamat').val(p.alamat||'');
          $('input[name="no_kk"]').val(p.no_kk||'');
          // Suami otomatis hanya jika ada di KK sama; jika Kepala Keluarga/pisah KK → manual
          var idP = p.id_penduduk || p.id || 0;
          $('#f_nama_suami, #f_nik_suami').val('');
          $('input[name="no_hp_suami"]').val('');
          if (idP) {
            $.getJSON('<?= APP_URL ?>/ajax/get_suami_opensid.php', {id_penduduk: idP}, function(sr) {
              if (!sr || !sr.success) return;
              if (sr.manual_required) {
                if (typeof Swal !== 'undefined') {
                  Swal.fire({
                    icon: 'info',
                    title: 'Isi data suami manual',
                    text: sr.alasan || 'Ibu Kepala Keluarga atau pisah KK. Silakan isi data suami sendiri.',
                    timer: 2800,
                    showConfirmButton: false
                  });
                }
                return;
              }
              if (sr.data) {
                $('#f_nama_suami').val(sr.data.nama || '');
                $('#f_nik_suami').val(sr.data.nik || '');
                if (sr.data.pekerjaan) {
                  var $pek = $('select[name="pekerjaan_suami"]');
                  if ($pek.find('option').filter(function(){ return $(this).val() === sr.data.pekerjaan || $(this).text() === sr.data.pekerjaan; }).length) {
                    $pek.val(sr.data.pekerjaan).trigger('change');
                  } else {
                    $pek.append($('<option>').val(sr.data.pekerjaan).text(sr.data.pekerjaan)).val(sr.data.pekerjaan).trigger('change');
                  }
                }
                if (sr.data.no_hp) $('input[name="no_hp_suami"]').val(sr.data.no_hp);
              }
            });
          }
          $('#infoTerpilih').removeClass('d-none');
          $('#namaTerpilih').text(p.nama+' ('+p.nik+')');
          box.hide();
          $('#cariPenduduk').val(p.nama);
        });
        box.append(item);
      });
      box.show();
    });
  }, 200);
});
$(document).on('click', function(e) {
  if (!$(e.target).closest('#cariPenduduk, #hasilCari').length) $('#hasilCari').hide();
});
</script>
<?php
$extra_js = ob_get_clean();
include __DIR__ . '/../../includes/footer.php';
