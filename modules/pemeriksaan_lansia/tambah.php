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

$page_title = 'Pemeriksaan Lansia';
require_once __DIR__ . '/../../includes/header.php';
$lansia_id = (int)($_GET['lansia_id'] ?? 0);
$lansia = $lansia_id ? fetchOne("SELECT * FROM lansia WHERE id=$lansia_id") : null;
$semua_lansia = fetchAll("SELECT id, nama, nomor_peserta FROM lansia WHERE status_aktif=1 ORDER BY nama");
$kader_list = fetchAll("SELECT id, nama FROM kader WHERE status=1");
?>
<section class="content-header"><div class="container-fluid"><div class="row mb-2">
  <div class="col-sm-6"><h1><i class="fas fa-user-md me-2 text-warning"></i>Pemeriksaan Lansia</h1></div>
  <div class="col-sm-6"><ol class="breadcrumb float-sm-end"><li class="breadcrumb-item"><a href="../../dashboard.php">Dashboard</a></li><li class="breadcrumb-item"><a href="index.php">Pemeriksaan Lansia</a></li><li class="breadcrumb-item active">Tambah</li></ol></div>
</div></div></section>
<section class="content"><div class="container-fluid">
<form method="POST" action="../../ajax/save_pemeriksaan_lansia.php" id="formPmxLansia">
<input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
<div class="row">
  <div class="col-md-8">
    <div class="card">
      <div class="card-header bg-warning"><h5 class="mb-0"><i class="fas fa-stethoscope me-2"></i>Data Pemeriksaan Lansia</h5></div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-8">
            <label class="form-label fw-semibold">Nama Lansia <span class="text-danger">*</span></label>
            <select name="lansia_id" class="form-select select2" required onchange="loadLansiaInfo(this.value)">
              <option value="">-- Pilih Lansia --</option>
              <?php foreach ($semua_lansia as $l): ?><option value="<?= $l['id'] ?>" <?= $l['id']==$lansia_id?'selected':'' ?>><?= htmlspecialchars($l['nama']) ?> (<?= $l['nomor_peserta'] ?>)</option><?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-4"><label class="form-label fw-semibold">Tanggal Pemeriksaan <span class="text-danger">*</span></label><input type="date" name="tanggal_pemeriksaan" class="form-control datepicker" value="<?= date('Y-m-d') ?>" required></div>
        </div>
        <?php if ($lansia): ?>
        <div class="alert alert-info mt-3"><strong><?= htmlspecialchars($lansia['nama']) ?></strong> | <?= $lansia['jenis_kelamin']=='L'?'Laki-laki':'Perempuan' ?> | Usia: <?= hitungUmur($lansia['tanggal_lahir']) ?></div>
        <?php endif; ?>
        <hr>
        <h6 class="fw-bold text-primary"><i class="fas fa-heartbeat me-2"></i>Pengukuran Fisik</h6>
        <div class="row g-3">
          <div class="col-6 col-md-3"><label class="form-label fw-semibold">Berat Badan</label><div class="input-group"><input type="number" name="berat_badan" class="form-control" step="0.1" min="20" max="200"><span class="input-group-text">kg</span></div></div>
          <div class="col-6 col-md-3"><label class="form-label fw-semibold">Tinggi Badan</label><div class="input-group"><input type="number" name="tinggi_badan" class="form-control" step="0.1"><span class="input-group-text">cm</span></div></div>
          <div class="col-6 col-md-3"><label class="form-label fw-semibold">Tekanan Darah <span class="text-danger">*</span></label><div class="input-group"><input type="text" name="tekanan_darah" id="tdLansia" class="form-control" placeholder="120/80" required onchange="analyzeRisikoLansia()"><span class="input-group-text">mmHg</span></div></div>
          <div class="col-6 col-md-3"><label class="form-label fw-semibold">Denyut Nadi</label><div class="input-group"><input type="number" name="denyut_nadi" class="form-control" placeholder="80" min="40" max="200"><span class="input-group-text">bpm</span></div></div>
          <div class="col-6 col-md-4"><label class="form-label fw-semibold">Suhu Tubuh</label><div class="input-group"><input type="number" name="suhu_tubuh" class="form-control" step="0.1" placeholder="36.5"><span class="input-group-text">°C</span></div></div>
          <div class="col-6 col-md-4"><label class="form-label fw-semibold">Gula Darah</label><div class="input-group"><input type="number" name="gula_darah" id="gulaDarah" class="form-control" step="0.1" onchange="analyzeRisikoLansia()"><span class="input-group-text">mg/dL</span></div></div>
          <div class="col-6 col-md-4"><label class="form-label fw-semibold">Kolesterol</label><div class="input-group"><input type="number" name="kolesterol" id="kolesterol" class="form-control" step="0.1" onchange="analyzeRisikoLansia()"><span class="input-group-text">mg/dL</span></div></div>
          <div class="col-6 col-md-4"><label class="form-label fw-semibold">Asam Urat</label><div class="input-group"><input type="number" name="asam_urat" class="form-control" step="0.1"><span class="input-group-text">mg/dL</span></div></div>
        </div>
        <hr>
        <div class="row g-3">
          <div class="col-md-6"><label class="form-label fw-semibold">Keluhan</label><textarea name="keluhan" class="form-control" rows="3"></textarea></div>
          <div class="col-md-6"><label class="form-label fw-semibold">Obat Diberikan</label><textarea name="obat_diberikan" class="form-control" rows="3"></textarea></div>
          <div class="col-md-6"><label class="form-label fw-semibold">Vitamin</label><input type="text" name="vitamin" class="form-control"></div>
          <div class="col-md-6"><label class="form-label fw-semibold">Catatan Petugas</label><textarea name="catatan_petugas" class="form-control" rows="2"></textarea></div>
          <div class="col-md-4"><label class="form-label fw-semibold">Jadwal Kontrol</label><input type="date" name="jadwal_kontrol" class="form-control datepicker"></div>
          <div class="col-md-4"><label class="form-label fw-semibold">Petugas</label><select name="petugas_id" class="form-select select2"><?php foreach ($kader_list as $k): ?><option value="<?= $k['id'] ?>" <?= $k['id']==$user['id']?'selected':'' ?>><?= htmlspecialchars($k['nama']) ?></option><?php endforeach; ?></select></div>
        </div>
      </div>
      <div class="card-footer d-flex justify-content-end gap-2">
        <a href="index.php" class="btn btn-secondary btn-lg"><i class="fas fa-times me-2"></i>Batal</a>
        <button type="submit" class="btn btn-warning btn-lg"><i class="fas fa-save me-2"></i>Simpan</button>
      </div>
    </div>
  </div>
  <!-- Analisa -->
  <div class="col-md-4">
    <div class="card sticky-top" style="top:80px">
      <div class="card-header bg-danger text-white"><h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Analisa Risiko</h5></div>
      <div class="card-body">
        <div id="analisaLansia" class="d-none">
          <div class="d-flex flex-column gap-2 mb-3">
            <div class="d-flex justify-content-between align-items-center p-2 rounded bg-light"><span><i class="fas fa-heart me-1 text-danger"></i>Hipertensi</span><span class="badge" id="badgeHTLansia">-</span></div>
            <div class="d-flex justify-content-between align-items-center p-2 rounded bg-light"><span><i class="fas fa-tint me-1 text-warning"></i>Diabetes</span><span class="badge" id="badgeDM">-</span></div>
            <div class="d-flex justify-content-between align-items-center p-2 rounded bg-light"><span><i class="fas fa-circle me-1 text-info"></i>Kolesterol Tinggi</span><span class="badge" id="badgeKol">-</span></div>
          </div>
        </div>
        <div id="noAnalisaLansia" class="text-center py-3"><i class="fas fa-search fa-2x text-muted mb-2 d-block"></i><small class="text-muted">Isi tekanan darah, gula darah, dan kolesterol</small></div>
        <input type="hidden" name="risiko_hipertensi" id="risiko_ht_l" value="0">
        <input type="hidden" name="risiko_diabetes" id="risiko_dm" value="0">
        <input type="hidden" name="risiko_kolesterol" id="risiko_kol" value="0">
      </div>
    </div>
  </div>
</div>
</form>
</div></section>
<?php
$extra_js = <<<'JS'
<script>
function analyzeRisikoLansia() {
  const td = $('#tdLansia').val(); const gd = parseFloat($('#gulaDarah').val()); const kol = parseFloat($('#kolesterol').val());
  let ht=0, dm=0, kolHigh=0;
  if (td) { const p = td.split('/'); if (p.length===2) { if (parseInt(p[0])>=140||parseInt(p[1])>=90) ht=1; } }
  if (gd > 200) dm=1;
  if (kol > 200) kolHigh=1;
  $('#badgeHTLansia').text(ht?'Berisiko':'Normal').removeClass().addClass('badge bg-'+(ht?'danger':'success'));
  $('#badgeDM').text(dm?'Berisiko':'Normal').removeClass().addClass('badge bg-'+(dm?'warning':'success'));
  $('#badgeKol').text(kolHigh?'Berisiko':'Normal').removeClass().addClass('badge bg-'+(kolHigh?'warning':'success'));
  $('#analisaLansia').removeClass('d-none'); $('#noAnalisaLansia').addClass('d-none');
  $('#risiko_ht_l').val(ht); $('#risiko_dm').val(dm); $('#risiko_kol').val(kolHigh);
}
$(document).ready(function() {
  $('#formPmxLansia').on('submit', function(e) {
    e.preventDefault();
    showLoading('Menyimpan...');
    $.ajax({
      url: this.action,
      type: 'POST',
      data: new FormData(this),
      processData: false,
      contentType: false,
      dataType: 'json',
      success: function(r) {
        hideLoading();
        if (r && r.success) {
          Swal.fire({icon:'success',title:'Berhasil!',text:r.message,timer:2000,showConfirmButton:false})
            .then(function(){ window.location.href = 'index.php'; });
        } else {
          Swal.fire('Gagal!', (r && r.message) ? r.message : 'Terjadi kesalahan', 'error');
        }
      },
      error: function() {
        hideLoading();
        Swal.fire('Error!', 'Koneksi bermasalah', 'error');
      }
    });
  });
});
</script>
JS;
include __DIR__ . '/../../includes/footer.php';
?>
