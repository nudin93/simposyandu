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

$page_title = 'Tambah Data Balita';
echo $this->include('layouts/header_asli');
$nomor = generateNomorPeserta('BLT', 'balita');
$provinsi_list = ['Aceh','Sumatera Utara','Sumatera Barat','Riau','Jambi','Sumatera Selatan','Bengkulu','Lampung','Kepulauan Bangka Belitung','Kepulauan Riau','DKI Jakarta','Jawa Barat','Jawa Tengah','DI Yogyakarta','Jawa Timur','Banten','Bali','Nusa Tenggara Barat','Nusa Tenggara Timur','Kalimantan Barat','Kalimantan Tengah','Kalimantan Selatan','Kalimantan Timur','Kalimantan Utara','Sulawesi Utara','Sulawesi Tengah','Sulawesi Selatan','Sulawesi Tenggara','Gorontalo','Sulawesi Barat','Maluku','Maluku Utara','Papua Barat','Papua','Papua Selatan','Papua Tengah','Papua Pegunungan'];
?>
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6"><h1><i class="fas fa-baby me-2 text-primary"></i>Tambah Data Balita</h1></div>
      <div class="col-sm-6"><ol class="breadcrumb float-sm-end">
        <li class="breadcrumb-item"><a href="<?= APP_URL ?>/dashboard.php">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="index.php">Balita</a></li>
        <li class="breadcrumb-item active">Tambah</li>
      </ol></div>
    </div>
  </div>
</section>
<section class="content"><div class="container-fluid">

<!-- Pilih dari Data Penduduk -->
<div class="card border-primary mb-3">
  <div class="card-header bg-primary text-white"><h5 class="mb-0"><i class="fas fa-search me-2"></i>Pilih dari Data Penduduk / Keluarga</h5></div>
  <div class="card-body">
    <p class="text-muted small mb-2">
      <strong>Prioritas:</strong> Ambil data dari OpenSID (usia 0–59 bulan). 
      Cari nama anak di bawah — jika ditemukan, data terisi otomatis dan tidak perlu input manual.
      Form ini hanya untuk anak yang <u>belum ada di OpenSID</u>.
    </p>
    <div class="row g-2">
      <div class="col-md-8 position-relative">
        <input type="text" id="cariPenduduk" class="form-control" placeholder="Ketik nama anak..." autocomplete="off">
        <div id="hasilCari" class="list-group position-absolute shadow w-100" style="z-index:1050; max-height:280px; overflow-y:auto; display:none;"></div>
      </div>
      <div class="col-md-4">
        <a href="<?= APP_URL ?>/modules/keluarga/index.php" class="btn btn-outline-secondary w-100" target="_blank"><i class="fas fa-home me-1"></i>Tambah via Data Keluarga</a>
      </div>
    </div>
    <div id="infoTerpilih" class="alert alert-success mt-3 mb-0 d-none d-flex flex-wrap align-items-center justify-content-between gap-2">
      <span><i class="fas fa-check-circle me-1"></i>Terpilih: <strong id="namaTerpilih"></strong></span>
      <button type="button" class="btn btn-sm btn-outline-danger" id="btnHapusPilihan" title="Hapus pilihan & kosongkan form">
        <i class="fas fa-times me-1"></i>Hapus Pilihan
      </button>
    </div>
  </div>
</div>

<!-- Wizard Steps -->
<div class="wizard-steps mb-4" id="wizardSteps">
  <div class="wizard-step active" id="step-nav-1" onclick="goStep(1)"><i class="fas fa-baby me-1"></i>1. Biodata Anak</div>
  <div class="wizard-step" id="step-nav-2" onclick="goStep(2)"><i class="fas fa-users me-1"></i>2. Orang Tua</div>
  <div class="wizard-step" id="step-nav-3" onclick="goStep(3)"><i class="fas fa-map-marker-alt me-1"></i>3. Alamat</div>
  <div class="wizard-step" id="step-nav-4" onclick="goStep(4)"><i class="fas fa-heartbeat me-1"></i>4. Kesehatan</div>
  <div class="wizard-step" id="step-nav-5" onclick="goStep(5)"><i class="fas fa-upload me-1"></i>5. Upload</div>
</div>

<form method="POST" action="../../ajax/save_balita.php" enctype="multipart/form-data" id="formBalita" data-draft="1">
<input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
<input type="hidden" name="id_penduduk_opensid" id="f_id_opensid" value="0">

<!-- STEP 1: Biodata Anak -->
<div class="wizard-pane" id="pane-1">
<div class="card">
  <div class="card-header bg-primary text-white"><h5 class="mb-0"><i class="fas fa-baby me-2"></i>Biodata Anak</h5></div>
  <div class="card-body">
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label fw-semibold">Nomor Peserta <span class="text-danger">*</span></label>
        <div class="input-group">
          <span class="input-group-text"><i class="fas fa-id-badge"></i></span>
          <input type="text" name="nomor_peserta" class="form-control" value="<?= $nomor ?>" readonly required>
          <button type="button" class="btn btn-outline-secondary" onclick="generateNomor()"><i class="fas fa-sync"></i></button>
        </div>
      </div>
      <div class="col-md-6">
        <label class="form-label fw-semibold">NIK Anak</label>
        <div class="input-group">
          <span class="input-group-text"><i class="fas fa-fingerprint"></i></span>
          <input type="text" name="nik_anak" id="f_nik" class="form-control field-clearable" placeholder="16 digit NIK" maxlength="16" pattern="\d{16}">
          <button type="button" class="btn btn-outline-secondary btn-clear-field" data-target="#f_nik" title="Hapus isian"><i class="fas fa-times"></i></button>
          <div class="invalid-feedback">NIK harus 16 digit angka</div>
        </div>
      </div>
      <div class="col-md-6">
        <label class="form-label fw-semibold">No. Kartu Keluarga</label>
        <div class="input-group">
          <input type="text" name="no_kk" id="f_nokk" class="form-control field-clearable" placeholder="16 digit No KK" maxlength="16">
          <button type="button" class="btn btn-outline-secondary btn-clear-field" data-target="#f_nokk" title="Hapus isian"><i class="fas fa-times"></i></button>
        </div>
      </div>
      <div class="col-md-6">
        <label class="form-label fw-semibold">Nama Lengkap Anak <span class="text-danger">*</span></label>
        <div class="input-group">
          <input type="text" name="nama_lengkap" id="f_nama" class="form-control field-clearable" placeholder="Nama lengkap tanpa gelar" required>
          <button type="button" class="btn btn-outline-secondary btn-clear-field" data-target="#f_nama" title="Hapus isian"><i class="fas fa-times"></i></button>
        </div>
      </div>
      <div class="col-md-4">
        <label class="form-label fw-semibold">Jenis Kelamin <span class="text-danger">*</span></label>
        <select name="jenis_kelamin" id="f_jk" class="form-select select2" required>
          <option value="">-- Pilih --</option>
          <option value="L">Laki-laki</option>
          <option value="P">Perempuan</option>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label fw-semibold">Tempat Lahir</label>
        <input type="text" name="tempat_lahir" class="form-control" placeholder="Kota tempat lahir">
      </div>
      <div class="col-md-4">
        <label class="form-label fw-semibold">Tanggal Lahir <span class="text-danger">*</span></label>
        <input type="date" name="tanggal_lahir" id="tgl_lahir" class="form-control datepicker" required onchange="hitungUmurForm()">
      </div>
      <div class="col-md-4">
        <label class="form-label fw-semibold">Umur (Otomatis)</label>
        <input type="text" id="umur_display" class="form-control bg-light" readonly placeholder="Otomatis dari tanggal lahir">
      </div>
      <div class="col-md-4">
        <label class="form-label fw-semibold">Anak Ke-</label>
        <input type="number" name="anak_ke" class="form-control" value="1" min="1" max="20">
      </div>
      <div class="col-md-4">
        <label class="form-label fw-semibold">Status Anak</label>
        <select name="status_anak" class="form-select select2">
          <option value="kandung">Kandung</option>
          <option value="angkat">Angkat</option>
          <option value="tiri">Tiri</option>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label fw-semibold">Golongan Darah</label>
        <select name="golongan_darah" class="form-select select2">
          <option value="Tidak Tahu">Tidak Tahu</option>
          <option value="A">A</option><option value="B">B</option>
          <option value="AB">AB</option><option value="O">O</option>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label fw-semibold">Berat Lahir (kg)</label>
        <div class="input-group"><input type="number" name="berat_lahir" class="form-control" placeholder="0.0" step="0.1" min="0.5" max="10"><span class="input-group-text">kg</span></div>
      </div>
      <div class="col-md-4">
        <label class="form-label fw-semibold">Tinggi Lahir (cm)</label>
        <div class="input-group"><input type="number" name="tinggi_lahir" class="form-control" placeholder="0" step="0.1" min="20" max="70"><span class="input-group-text">cm</span></div>
      </div>
    </div>
  </div>
  <div class="card-footer d-flex justify-content-end">
    <button type="button" class="btn btn-primary btn-lg" onclick="nextStep(1)"><i class="fas fa-arrow-right me-2"></i>Selanjutnya</button>
  </div>
</div>
</div>

<!-- STEP 2: Orang Tua -->
<div class="wizard-pane d-none" id="pane-2">
<div class="card">
  <div class="card-header bg-success text-white"><h5 class="mb-0"><i class="fas fa-users me-2"></i>Biodata Orang Tua</h5></div>
  <div class="card-body">
    <h6 class="fw-bold text-primary mb-3"><i class="fas fa-male me-2"></i>Data Ayah</h6>
    <div class="row g-3 mb-4">
      <div class="col-md-6"><label class="form-label fw-semibold">Nama Ayah</label><div class="input-group"><input type="text" name="nama_ayah" id="f_nama_ayah" class="form-control field-clearable" placeholder="Nama lengkap ayah"><button type="button" class="btn btn-outline-secondary btn-clear-field" data-target="#f_nama_ayah" title="Hapus isian"><i class="fas fa-times"></i></button></div></div>
      <div class="col-md-6"><label class="form-label fw-semibold">NIK Ayah</label><div class="input-group"><input type="text" name="nik_ayah" id="f_nik_ayah" class="form-control field-clearable" placeholder="16 digit NIK" maxlength="16"><button type="button" class="btn btn-outline-secondary btn-clear-field" data-target="#f_nik_ayah" title="Hapus isian"><i class="fas fa-times"></i></button></div></div>
      <div class="col-md-6"><label class="form-label fw-semibold">Pekerjaan Ayah</label><select name="pekerjaan_ayah" id="f_pek_ayah" class="form-select select2"><option value="">-- Pilih --</option><?php foreach(['PNS','TNI/Polri','Petani','Nelayan','Pedagang','Wiraswasta','Buruh','Karyawan Swasta','Guru','Dokter/Tenaga Medis','Lainnya'] as $p): ?><option><?= $p ?></option><?php endforeach; ?></select></div>
    </div>
    <hr>
    <h6 class="fw-bold text-danger mb-3"><i class="fas fa-female me-2"></i>Data Ibu</h6>
    <div class="row g-3">
      <div class="col-md-6"><label class="form-label fw-semibold">Nama Ibu</label><div class="input-group"><input type="text" name="nama_ibu" id="f_nama_ibu" class="form-control field-clearable" placeholder="Nama lengkap ibu"><button type="button" class="btn btn-outline-secondary btn-clear-field" data-target="#f_nama_ibu" title="Hapus isian"><i class="fas fa-times"></i></button></div></div>
      <div class="col-md-6"><label class="form-label fw-semibold">NIK Ibu</label><div class="input-group"><input type="text" name="nik_ibu" id="f_nik_ibu" class="form-control field-clearable" placeholder="16 digit NIK" maxlength="16"><button type="button" class="btn btn-outline-secondary btn-clear-field" data-target="#f_nik_ibu" title="Hapus isian"><i class="fas fa-times"></i></button></div></div>
      <div class="col-md-6"><label class="form-label fw-semibold">Pekerjaan Ibu</label><select name="pekerjaan_ibu" id="f_pek_ibu" class="form-select select2"><option value="">-- Pilih --</option><?php foreach(['Ibu Rumah Tangga','PNS','Petani','Pedagang','Wiraswasta','Buruh','Karyawan Swasta','Guru','Dokter/Tenaga Medis','Lainnya'] as $p): ?><option><?= $p ?></option><?php endforeach; ?></select></div>
      <div class="col-md-6"><label class="form-label fw-semibold">Nomor HP Orang Tua</label><div class="input-group"><span class="input-group-text"><i class="fas fa-phone"></i></span><input type="text" name="no_hp" class="form-control" placeholder="08xxxxxxxxxx"></div></div>
    </div>
  </div>
  <div class="card-footer d-flex justify-content-between">
    <button type="button" class="btn btn-secondary btn-lg" onclick="prevStep(2)"><i class="fas fa-arrow-left me-2"></i>Kembali</button>
    <button type="button" class="btn btn-success btn-lg" onclick="nextStep(2)"><i class="fas fa-arrow-right me-2"></i>Selanjutnya</button>
  </div>
</div>
</div>

<!-- STEP 3: Alamat -->
<div class="wizard-pane d-none" id="pane-3">
<div class="card">
  <div class="card-header bg-warning"><h5 class="mb-0"><i class="fas fa-map-marker-alt me-2"></i>Alamat</h5></div>
  <div class="card-body">
    <div class="row g-3">
      <div class="col-md-3"><label class="form-label fw-semibold">Dusun</label><input type="text" name="dusun" id="f_dusun" class="form-control" placeholder="Nama dusun/lingkungan"></div>
      <div class="col-md-2"><label class="form-label fw-semibold">RT</label><input type="text" name="rt" class="form-control" placeholder="001" maxlength="5"></div>
      <div class="col-md-2"><label class="form-label fw-semibold">RW</label><input type="text" name="rw" class="form-control" placeholder="001" maxlength="5"></div>
      <div class="col-md-5"><label class="form-label fw-semibold">Desa/Kelurahan</label><input type="text" name="desa" id="f_desa" class="form-control" value="<?= $pengaturan['nama_desa'] ?>" placeholder="Nama desa"></div>
      <div class="col-md-4"><label class="form-label fw-semibold">Kecamatan</label><input type="text" name="kecamatan" id="f_kecamatan" class="form-control" value="<?= $pengaturan['kecamatan'] ?>"></div>
      <div class="col-md-4"><label class="form-label fw-semibold">Kabupaten/Kota</label><input type="text" name="kabupaten" id="f_kabupaten" class="form-control" value="<?= $pengaturan['kabupaten'] ?>"></div>
      <div class="col-md-4">
        <label class="form-label fw-semibold">Provinsi</label>
        <select name="provinsi" id="f_provinsi" class="form-select select2">
          <?php foreach ($provinsi_list as $p): ?><option <?= $p==$pengaturan['provinsi']?'selected':'' ?>><?= $p ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="col-12">
        <label class="form-label fw-semibold">Alamat Lengkap</label>
        <textarea name="alamat_lengkap" id="f_alamat" class="form-control" rows="3" placeholder="Jalan, nomor rumah, RT/RW, dusun..."></textarea>
      </div>
    </div>
  </div>
  <div class="card-footer d-flex justify-content-between">
    <button type="button" class="btn btn-secondary btn-lg" onclick="prevStep(3)"><i class="fas fa-arrow-left me-2"></i>Kembali</button>
    <button type="button" class="btn btn-warning btn-lg" onclick="nextStep(3)"><i class="fas fa-arrow-right me-2"></i>Selanjutnya</button>
  </div>
</div>
</div>

<!-- STEP 4: Kesehatan -->
<div class="wizard-pane d-none" id="pane-4">
<div class="card">
  <div class="card-header bg-danger text-white"><h5 class="mb-0"><i class="fas fa-heartbeat me-2"></i>Data Kesehatan</h5></div>
  <div class="card-body">
    <div class="row g-3">
      <div class="col-md-4">
        <label class="form-label fw-semibold">Status ASI</label>
        <select name="status_asi" class="form-select select2">
          <option>ASI Eksklusif</option><option>Susu Formula</option><option>Keduanya</option>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label fw-semibold">Status Imunisasi</label>
        <select name="status_imunisasi" class="form-select select2">
          <option>Dalam Proses</option><option>Lengkap</option><option>Belum Lengkap</option>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label fw-semibold">No. BPJS/KIS</label>
        <input type="text" name="bpjs_kis" class="form-control" placeholder="Nomor BPJS/KIS">
      </div>
      <div class="col-md-6">
        <label class="form-label fw-semibold">Riwayat Alergi</label>
        <textarea name="riwayat_alergi" class="form-control" rows="3" placeholder="Alergi makanan, obat, dll. (kosongkan jika tidak ada)"></textarea>
      </div>
      <div class="col-md-6">
        <label class="form-label fw-semibold">Riwayat Penyakit</label>
        <textarea name="riwayat_penyakit" class="form-control" rows="3" placeholder="Riwayat penyakit sebelumnya (kosongkan jika tidak ada)"></textarea>
      </div>
      <div class="col-md-4">
        <label class="form-label fw-semibold">Status Aktif</label>
        <div class="form-check form-switch mt-2">
          <input class="form-check-input" type="checkbox" name="status_aktif" id="statusAktif" value="1" checked>
          <label class="form-check-label fw-semibold" for="statusAktif">Aktif di Posyandu</label>
        </div>
      </div>
    </div>
  </div>
  <div class="card-footer d-flex justify-content-between">
    <button type="button" class="btn btn-secondary btn-lg" onclick="prevStep(4)"><i class="fas fa-arrow-left me-2"></i>Kembali</button>
    <button type="button" class="btn btn-danger btn-lg" onclick="nextStep(4)"><i class="fas fa-arrow-right me-2"></i>Selanjutnya</button>
  </div>
</div>
</div>

<!-- STEP 5: Upload -->
<div class="wizard-pane d-none" id="pane-5">
<div class="card">
  <div class="card-header bg-secondary text-white"><h5 class="mb-0"><i class="fas fa-upload me-2"></i>Upload Dokumen</h5></div>
  <div class="card-body">
    <div class="row g-3">
      <?php foreach([['foto_anak','Foto Anak','fas fa-camera','jpg, jpeg, png'],['foto_kk','Kartu Keluarga','fas fa-file-image','jpg, jpeg, png, pdf'],['foto_bpjs','Kartu BPJS/KIS','fas fa-id-card','jpg, jpeg, png, pdf']] as [$name,$label,$icon,$types]): ?>
      <div class="col-md-4">
        <div class="card border-dashed" style="border:2px dashed #dee2e6;border-radius:12px">
          <div class="card-body text-center p-4">
            <i class="<?= $icon ?> fa-3x text-muted mb-3 d-block"></i>
            <label class="form-label fw-semibold"><?= $label ?></label>
            <input type="file" name="<?= $name ?>" class="form-control" accept=".jpg,.jpeg,.png,.pdf" onchange="previewImg(this, '<?= $name ?>_prev')">
            <small class="text-muted mt-1 d-block"><?= $types ?> (Max 5MB)</small>
            <img id="<?= $name ?>_prev" src="" class="img-fluid mt-2 d-none" style="max-height:150px;border-radius:8px">
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
</div>
  <div class="card-footer d-flex justify-content-between align-items-center">
    <button type="button" class="btn btn-secondary btn-lg" onclick="prevStep(5)"><i class="fas fa-arrow-left me-2"></i>Kembali</button>
    <div class="d-flex gap-2">
      <button type="button" class="btn btn-outline-secondary btn-lg" onclick="saveDraft()"><i class="fas fa-save me-2"></i>Simpan Draft</button>
      <button type="submit" class="btn btn-primary btn-lg" id="btnSimpan"><i class="fas fa-check me-2"></i>Simpan Data</button>
    </div>
  </div>
</div>
</div>
</form>
</div></section>
<?php
ob_start();
?>
<script>
let currentStep = 1; const totalSteps = 5;
function goStep(n) {
  $('#pane-' + currentStep).addClass('d-none'); $('#step-nav-' + currentStep).removeClass('active').addClass('done');
  currentStep = n;
  $('#pane-' + n).removeClass('d-none'); $('#step-nav-' + n).addClass('active').removeClass('done');
}
function nextStep(n) {
  if (!validateStep(n)) return;
  goStep(n + 1);
  window.scrollTo(0, 0);
}
function prevStep(n) { goStep(n - 1); window.scrollTo(0, 0); }
function validateStep(n) {
  let valid = true;
  $('#pane-' + n + ' [required]').each(function() {
    if (!$(this).val()) { $(this).addClass('is-invalid'); valid = false; }
    else $(this).removeClass('is-invalid');
  });
  if (!valid) { Swal.fire('Perhatian!', 'Lengkapi semua field yang wajib diisi', 'warning'); }
  return valid;
}
function hitungUmurForm() {
  const tgl = $('#tgl_lahir').val();
  if (!tgl) { $('#umur_display').val(''); return; }
  const umur = (typeof hitungUmur === 'function') ? hitungUmur(tgl) : '';
  // Hitung bulan untuk aturan balita 0-59
  var d1 = new Date(tgl);
  var d2 = new Date();
  var bulan = (d2.getFullYear()-d1.getFullYear())*12 + (d2.getMonth()-d1.getMonth());
  if (d2.getDate() < d1.getDate()) bulan--;
  var label = umur || (bulan + ' bulan');
  if (bulan > 59) {
    $('#umur_display').val(label + ' — MELEBIHI usia balita (max 59 bulan)');
    $('#umur_display').addClass('border-danger');
  } else if (bulan < 0) {
    $('#umur_display').val('Tanggal lahir tidak valid');
    $('#umur_display').addClass('border-danger');
  } else {
    $('#umur_display').val(label + ' (' + bulan + ' bulan)');
    $('#umur_display').removeClass('border-danger');
  }
}
function previewImg(input, prevId) {
  const file = input.files[0];
  if (file && file.type.startsWith('image/')) {
    const reader = new FileReader();
    reader.onload = e => { $('#'+prevId).attr('src', e.target.result).removeClass('d-none'); };
    reader.readAsDataURL(file);
  }
}
function generateNomor() {
  $.get('../../ajax/generate_nomor.php?type=balita', function(r) {
    if (r.nomor) $('[name=nomor_peserta]').val(r.nomor);
  }, 'json');
}
function saveDraft() {
  const key = 'draft_balita';
  const data = {};
  $('#formBalita').find('input,select,textarea').each(function() {
    const n = $(this).attr('name'); if (n && $(this).attr('type') !== 'file') data[n] = $(this).val();
  });
  localStorage.setItem(key, JSON.stringify(data));
  Swal.fire({ icon: 'success', title: 'Draft Tersimpan!', text: 'Data dapat dilanjutkan nanti', timer: 2000, showConfirmButton: false });
}
$(document).ready(function() {
  // Hitung umur saat load jika tanggal sudah ada
  if ($('#tgl_lahir').val()) hitungUmurForm();

  $('#formBalita').on('submit', function(e) {
    e.preventDefault();
    // Validasi field wajib
    var nama = $('#f_nama').val();
    var jk = $('#f_jk').val();
    var tgl = $('#tgl_lahir').val();
    if (!nama || !jk || !tgl) {
      Swal.fire('Lengkapi data', 'Nama, jenis kelamin, dan tanggal lahir wajib diisi.', 'warning');
      if (typeof goStep === 'function') goStep(1);
      return;
    }
    // Aturan usia balita: 0–59 bulan
    var d1 = new Date(tgl), d2 = new Date();
    var bulan = (d2.getFullYear()-d1.getFullYear())*12 + (d2.getMonth()-d1.getMonth());
    if (d2.getDate() < d1.getDate()) bulan--;
    if (bulan < 0 || bulan > 59) {
      Swal.fire('Usia tidak sesuai', 'Data balita hanya untuk usia 0–59 bulan. Usia saat ini: ' + bulan + ' bulan.', 'warning');
      if (typeof goStep === 'function') goStep(1);
      return;
    }
    showLoading('Menyimpan data balita...');
    var fd = new FormData(this);
    $.ajax({
      url: this.action,
      type: 'POST',
      data: fd,
      processData: false,
      contentType: false,
      dataType: 'json',
      success: function(r) {
        hideLoading();
        if (r && r.success) {
          try { localStorage.removeItem('draft_balita'); } catch(e) {}
          Swal.fire({ icon: 'success', title: 'Berhasil!', text: r.message || 'Data tersimpan', showConfirmButton: false, timer: 2000 })
            .then(function() { window.location.href = 'index.php'; });
        } else {
          Swal.fire('Gagal!', (r && r.message) ? r.message : 'Terjadi kesalahan', 'error');
        }
      },
      error: function(xhr) {
        hideLoading();
        var msg = 'Koneksi bermasalah';
        try {
          var j = JSON.parse(xhr.responseText);
          if (j.message) msg = j.message;
        } catch(e) {}
        Swal.fire('Error!', msg, 'error');
      }
    });
  });
});
</script>
<script>
/** Format tanggal ke YYYY-MM-DD untuk input type=date */
function formatTglInput(tgl) {
  if (!tgl) return '';
  tgl = String(tgl).trim();
  if (/^\d{4}-\d{2}-\d{2}/.test(tgl)) return tgl.substring(0, 10);
  // dd/mm/yyyy atau dd-mm-yyyy
  var m = tgl.match(/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})$/);
  if (m) {
    var d = m[1].padStart(2,'0'), mo = m[2].padStart(2,'0'), y = m[3];
    return y + '-' + mo + '-' + d;
  }
  // yyyymmdd
  m = tgl.match(/^(\d{4})(\d{2})(\d{2})$/);
  if (m) return m[1]+'-'+m[2]+'-'+m[3];
  try {
    var dt = new Date(tgl);
    if (!isNaN(dt.getTime())) {
      return dt.getFullYear() + '-' + String(dt.getMonth()+1).padStart(2,'0') + '-' + String(dt.getDate()).padStart(2,'0');
    }
  } catch(e) {}
  return '';
}

/** Isi semua field form balita dari data penduduk/OpenSID */
function isiOtomatisBalita(p) {
  if (!p) return;
  // Identitas anak
  $('#f_id_opensid').val(p.id_penduduk || p.id || 0);
  $('#f_nik').val(p.nik || '');
  $('#f_nokk').val(p.no_kk || '');
  $('#f_nama').val(p.nama || p.nama_lengkap || '');
  var jk = p.jenis_kelamin || '';
  if (jk === '1' || jk === 1) jk = 'L';
  if (jk === '2' || jk === 2) jk = 'P';
  if (jk) {
    $('#f_jk').val(jk);
    if ($('#f_jk').hasClass('select2-hidden-accessible')) {
      $('#f_jk').trigger('change');
    }
  }
  $('input[name="tempat_lahir"]').val(p.tempat_lahir || '');
  var tgl = formatTglInput(p.tanggal_lahir || p.tanggallahir || '');
  $('#tgl_lahir').val(tgl);
  if (typeof hitungUmurForm === 'function') hitungUmurForm();

  // Orang tua
  $('#f_nama_ayah').val(p.nama_ayah || '');
  $('#f_nik_ayah').val(p.nik_ayah || p.ayah_nik || '');
  $('#f_pek_ayah').val(p.pekerjaan_ayah || p.pekerjaan || '').trigger('change');
  $('#f_nama_ibu').val(p.nama_ibu || '');
  $('#f_nik_ibu').val(p.nik_ibu || p.ibu_nik || '');
  $('#f_pek_ibu').val(p.pekerjaan_ibu || '').trigger('change');
  $('input[name="no_hp"]').val(p.no_hp || p.telepon || '');

  // Alamat
  $('#f_dusun').val(p.dusun || '');
  $('input[name="rt"]').val(p.rt || '');
  $('input[name="rw"]').val(p.rw || '');
  $('#f_alamat').val(p.alamat || p.alamat_lengkap || p.alamat_sekarang || '');
  if (p.desa) $('#f_desa').val(p.desa);
  if (p.kecamatan) $('#f_kecamatan').val(p.kecamatan);
  if (p.kabupaten) $('#f_kabupaten').val(p.kabupaten);
  if (p.provinsi) $('#f_provinsi').val(p.provinsi);

  // Info terpilih
  $('#infoTerpilih').removeClass('d-none');
  var label = (p.nama || '') + (p.nik ? ' (' + p.nik + ')' : '');
  $('#namaTerpilih').text(label);

  // Ke step 1 agar user lihat data terisi
  if (typeof goStep === 'function') goStep(1);

  if (typeof Swal !== 'undefined') {
    Swal.fire({
      icon: 'success',
      title: 'Data terisi otomatis',
      text: 'Identitas, orang tua, dan alamat sudah diisi. Lengkapi data kesehatan lalu simpan.',
      timer: 2200,
      showConfirmButton: false
    });
  }
}

/** Kosongkan field yang terisi dari OpenSID / pilihan salah */
function hapusPilihanBalita() {
  $('#f_id_opensid').val(0);
  $('#f_nik, #f_nokk, #f_nama, #tgl_lahir').val('');
  $('#f_jk').val('').trigger('change');
  $('input[name="tempat_lahir"]').val('');
  $('#umur_display').val('').removeClass('border-danger');
  $('#f_nama_ayah, #f_nik_ayah, #f_nama_ibu, #f_nik_ibu').val('');
  $('#f_pek_ayah, #f_pek_ibu').val('').trigger('change');
  $('input[name="no_hp"]').val('');
  $('#f_dusun, #f_alamat').val('');
  $('input[name="rt"], input[name="rw"]').val('');
  $('#cariPenduduk').val('');
  $('#infoTerpilih').addClass('d-none');
  $('#namaTerpilih').text('');
  if (typeof goStep === 'function') goStep(1);
  if (typeof Swal !== 'undefined') {
    Swal.fire({ icon: 'info', title: 'Pilihan dihapus', text: 'Form dikosongkan. Anda bisa cari ulang atau isi manual.', timer: 1800, showConfirmButton: false });
  }
}

/* using shared helper if available */
var timerCari = null;
$('#cariPenduduk').on('input', function() {
  clearTimeout(timerCari);
  var q = $(this).val().trim();
  if (q.length < 1) { $('#hasilCari').hide().empty(); return; }
  timerCari = setTimeout(function() {
    $.getJSON('<?= APP_URL ?>/ajax/cari_penduduk.php', {q: q}, function(res) {
      var box = $('#hasilCari').empty();
      if (!res.data || !res.data.length) {
        box.append('<div class="list-group-item text-muted">Tidak ditemukan. Daftarkan anak di Data Keluarga (Tambah Anggota).</div>').show();
        return;
      }
      res.data.forEach(function(p) {
        var item = $('<a href="#" class="list-group-item list-group-item-action"></a>');
        item.html('<strong>'+p.nama+'</strong> <code class="ms-1">'+p.nik+'</code><br><small class="text-muted">KK: '+(p.no_kk||'-')+' | '+(p.umur||'?')+' th | Hub: '+(p.status_dalam_keluarga||'')+' | '+(p.dusun||'')+'</small>');
        item.on('click', function(e) {
          e.preventDefault();
          isiOtomatisBalita(p);
          box.hide();
          $('#cariPenduduk').val(p.nama || '');
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

// Hapus pilihan OpenSID / reset form otomatis
$(document).on('click', '#btnHapusPilihan', function(e) {
  e.preventDefault();
  hapusPilihanBalita();
});

// Tombol × per field — hapus isian manual yang tidak sesuai
$(document).on('click', '.btn-clear-field', function(e) {
  e.preventDefault();
  var target = $(this).data('target');
  if (!target) return;
  var $el = $(target);
  if ($el.is('select')) {
    $el.val('').trigger('change');
  } else {
    $el.val('').trigger('input').trigger('change');
  }
  $el.focus();
});
</script>
<?php
$extra_js = ob_get_clean();
echo $this->include('layouts/footer_asli');
