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

$page_title = 'Pemeriksaan Ibu Hamil';
require_once __DIR__ . '/../../includes/header.php';
$ibu_hamil_id = (int)($_GET['ibu_hamil_id'] ?? 0);
$ibu = $ibu_hamil_id ? fetchOne("SELECT *, TIMESTAMPDIFF(WEEK, hpht, CURDATE()) as usia_minggu FROM ibu_hamil WHERE id=$ibu_hamil_id") : null;
$semua_ibu = fetchAll("SELECT id, nama, nomor_peserta, hpht FROM ibu_hamil WHERE status_aktif=1 ORDER BY nama");
$kader_list = fetchAll("SELECT id, nama FROM kader WHERE status=1");
?>
<section class="content-header"><div class="container-fluid"><div class="row mb-2">
  <div class="col-sm-6"><h1><i class="fas fa-heartbeat me-2 text-danger"></i>Pemeriksaan Ibu Hamil (ANC)</h1></div>
  <div class="col-sm-6"><ol class="breadcrumb float-sm-end"><li class="breadcrumb-item"><a href="../../dashboard.php">Dashboard</a></li><li class="breadcrumb-item"><a href="index.php">Pemeriksaan ANC</a></li><li class="breadcrumb-item active">Tambah</li></ol></div>
</div></div></section>
<section class="content"><div class="container-fluid">
<form method="POST" action="../../ajax/save_pemeriksaan_ibu_hamil.php" id="formANC">
<input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
<div class="row">
  <div class="col-md-8">
    <div class="card">
      <div class="card-header bg-danger text-white"><h5 class="mb-0"><i class="fas fa-stethoscope me-2"></i>Data Pemeriksaan ANC</h5></div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-8">
            <label class="form-label fw-semibold">Nama Ibu Hamil <span class="text-danger">*</span></label>
            <select name="ibu_hamil_id" id="ibuSelect" class="form-select select2" required onchange="loadIbuInfo(this.value)">
              <option value="">-- Pilih Ibu Hamil --</option>
              <?php foreach ($semua_ibu as $ib): ?><option value="<?= $ib['id'] ?>" data-hpht="<?= htmlspecialchars($ib['hpht'] ?? '') ?>" <?= $ib['id']==$ibu_hamil_id?'selected':'' ?>><?= htmlspecialchars($ib['nama']) ?> (<?= $ib['nomor_peserta'] ?>)</option><?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label fw-semibold">Tanggal Pemeriksaan <span class="text-danger">*</span></label>
            <input type="date" name="tanggal_pemeriksaan" id="tanggal_pemeriksaan" class="form-control datepicker" value="<?= date('Y-m-d') ?>" required onchange="hitungUsiaOtomatis()">
          </div>
          <div class="col-md-4">
            <label class="form-label fw-semibold">Usia Kehamilan</label>
            <div class="input-group">
              <input type="number" name="usia_kandungan" id="usia_kandungan" class="form-control bg-light" readonly value="<?= $ibu['usia_minggu'] ?? '' ?>" placeholder="Otomatis">
              <span class="input-group-text">minggu</span>
            </div>
            <small class="text-muted">Dihitung otomatis dari HPHT & tanggal periksa</small>
          </div>
        </div>
        <input type="hidden" id="hpht_ibu" value="<?= htmlspecialchars($ibu['hpht'] ?? '') ?>">
        <?php if ($ibu): ?>
        <div class="alert alert-info mt-3" id="infoIbuBox">
          <strong><?= htmlspecialchars($ibu['nama']) ?></strong> | HPHT: <span id="infoHpht"><?= formatTanggal($ibu['hpht']) ?></span> | Usia Kehamilan: <strong id="infoUsia"><?= (int)($ibu['usia_minggu'] ?? 0) ?> minggu</strong> | HPL: <?= formatTanggal($ibu['hpl']) ?>
        </div>
        <?php else: ?><div id="infoIbuBox" class="alert alert-info mt-3 d-none"></div><?php endif; ?>
        <hr>
        <h6 class="fw-bold text-primary"><i class="fas fa-weight me-2"></i>Pengukuran</h6>
        <div class="row g-3">
          <div class="col-6 col-md-3"><label class="form-label fw-semibold">Berat Badan <span class="text-danger">*</span></label><div class="input-group"><input type="number" name="berat_badan" class="form-control" step="0.1" min="30" max="150" required onchange="analyzeRisiko()"><span class="input-group-text">kg</span></div></div>
          <div class="col-6 col-md-3"><label class="form-label fw-semibold">Tekanan Darah <span class="text-danger">*</span></label><div class="input-group"><input type="text" name="tekanan_darah" id="tekdarah" class="form-control" placeholder="120/80" required onchange="analyzeRisiko()"><span class="input-group-text">mmHg</span></div></div>
          <div class="col-6 col-md-3"><label class="form-label fw-semibold">Tinggi Fundus</label><div class="input-group"><input type="number" name="tinggi_fundus" class="form-control" step="0.1"><span class="input-group-text">cm</span></div></div>
          <div class="col-6 col-md-3"><label class="form-label fw-semibold">LILA</label><div class="input-group"><input type="number" name="lila" id="lila" class="form-control" step="0.1" onchange="analyzeRisiko()"><span class="input-group-text">cm</span></div></div>
          <div class="col-6 col-md-3"><label class="form-label fw-semibold">DJJ (Detak Janin)</label><div class="input-group"><input type="number" name="djj" class="form-control" placeholder="120-160" min="60" max="200"><span class="input-group-text">bpm</span></div></div>
          <div class="col-6 col-md-3"><label class="form-label fw-semibold">Posisi Janin</label><select name="posisi_janin" class="form-select select2"><option value="">-- Pilih --</option><option>Kepala</option><option>Sungsang</option><option>Lintang</option><option>Miring</option></select></div>
          <div class="col-6 col-md-3"><label class="form-label fw-semibold">Gerakan Janin</label><select name="gerakan_janin" class="form-select select2"><option>Aktif</option><option>Kurang</option><option>Tidak Ada</option></select></div>
          <div class="col-6 col-md-3"><label class="form-label fw-semibold">Imunisasi TT</label><select name="imunisasi_tt" class="form-select select2"><option value="Tidak">Tidak</option><option>TT1</option><option>TT2</option><option>TT3</option><option>TT4</option><option>TT5</option></select></div>
        </div>
        <hr>
        <h6 class="fw-bold text-success"><i class="fas fa-pills me-2"></i>Tindakan & Catatan</h6>
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label fw-semibold">Tablet Fe</label>
            <div class="form-check form-switch mt-2"><input class="form-check-input" type="checkbox" name="tablet_fe" value="1" id="tabletFe"><label class="form-check-label" for="tabletFe">Diberikan</label></div>
          </div>
          <div class="col-md-8"><label class="form-label fw-semibold">Vitamin</label><input type="text" name="vitamin" class="form-control" placeholder="Jenis vitamin yang diberikan"></div>
          <div class="col-md-6"><label class="form-label fw-semibold">Keluhan Ibu</label><textarea name="keluhan" class="form-control" rows="3"></textarea></div>
          <div class="col-md-6"><label class="form-label fw-semibold">Riwayat Penyakit</label><textarea name="riwayat_penyakit" class="form-control" rows="3"></textarea></div>
          <div class="col-md-6"><label class="form-label fw-semibold">Pemeriksaan Lab</label><textarea name="pemeriksaan_lab" class="form-control" rows="2" placeholder="HB, goldar, protein urin, dll"></textarea></div>
          <div class="col-md-6"><label class="form-label fw-semibold">Catatan Bidan</label><textarea name="catatan_bidan" class="form-control" rows="2"></textarea></div>
          <div class="col-md-4"><label class="form-label fw-semibold">Jadwal Kontrol</label><input type="date" name="jadwal_kontrol" class="form-control datepicker"></div>
          <div class="col-md-4"><label class="form-label fw-semibold">Petugas</label><select name="petugas_id" class="form-select select2"><?php foreach ($kader_list as $k): ?><option value="<?= $k['id'] ?>" <?= $k['id']==$user['id']?'selected':'' ?>><?= htmlspecialchars($k['nama']) ?></option><?php endforeach; ?></select></div>
        </div>
      </div>
      <div class="card-footer d-flex justify-content-end gap-2">
        <a href="index.php" class="btn btn-secondary btn-lg"><i class="fas fa-times me-2"></i>Batal</a>
        <button type="submit" class="btn btn-danger btn-lg"><i class="fas fa-save me-2"></i>Simpan ANC</button>
      </div>
    </div>
  </div>
  <!-- Analisa Risiko -->
  <div class="col-md-4">
    <div class="card sticky-top" style="top:80px">
      <div class="card-header bg-warning"><h5 class="mb-0"><i class="fas fa-shield-alt me-2"></i>Analisa Risiko</h5></div>
      <div class="card-body">
        <div id="risikoDisplay" class="text-center py-3">
          <i class="fas fa-search fa-2x text-muted mb-2 d-block"></i>
          <p class="text-muted small">Isi data untuk melihat analisa risiko</p>
        </div>
        <div id="risikoDetail" class="d-none">
          <div class="d-flex flex-column gap-2">
            <div class="d-flex justify-content-between align-items-center p-2 rounded" id="risikoKEK" style="background:#f8f9fa"><span><i class="fas fa-circle me-2"></i>Risiko KEK</span><span class="badge" id="badgeKEK">-</span></div>
            <div class="d-flex justify-content-between align-items-center p-2 rounded" id="risikoHT" style="background:#f8f9fa"><span><i class="fas fa-circle me-2"></i>Hipertensi</span><span class="badge" id="badgeHT">-</span></div>
            <div class="d-flex justify-content-between align-items-center p-2 rounded" id="risikoAnemia" style="background:#f8f9fa"><span><i class="fas fa-circle me-2"></i>Risiko Anemia</span><span class="badge" id="badgeAnemia">-</span></div>
          </div>
          <div class="mt-3 p-3 rounded text-center" id="statusRisikoBox" style="background:#d1fae5">
            <strong id="statusRisikoText" class="fs-6">Normal</strong>
          </div>
        </div>
        <input type="hidden" name="risiko_kek" id="risiko_kek" value="0">
        <input type="hidden" name="risiko_hipertensi" id="risiko_ht" value="0">
        <input type="hidden" name="risiko_anemia" id="risiko_anemia" value="0">
        <input type="hidden" name="status_risiko" id="status_risiko" value="Normal">
      </div>
    </div>
  </div>
</div>
</form>
</div></section>
<?php
$extra_js = <<<'JS'
<script>
function hitungUsiaOtomatis() {
  var hpht = $('#hpht_ibu').val() || $('#ibuSelect option:selected').data('hpht') || '';
  var tgl = $('#tanggal_pemeriksaan').val() || '';
  if (!hpht) {
    $('#usia_kandungan').val('');
    $('#infoUsia').text('-');
    return;
  }
  var minggu = (typeof hitungUsiaKandungan === 'function')
    ? hitungUsiaKandungan(hpht, tgl)
    : (function() {
        var d = new Date(hpht);
        var target = tgl ? new Date(tgl) : new Date();
        var diff = Math.floor((target - d) / (1000*60*60*24));
        return diff < 0 ? 0 : Math.floor(diff / 7);
      })();
  $('#usia_kandungan').val(minggu);
  $('#infoUsia').text(minggu + ' minggu');
  if (hpht) {
    var parts = hpht.split('-');
    if (parts.length === 3) {
      $('#infoHpht').text(parts[2] + '/' + parts[1] + '/' + parts[0]);
    }
  }
}

function loadIbuInfo(id) {
  var opt = $('#ibuSelect option:selected');
  var hpht = opt.data('hpht') || '';
  $('#hpht_ibu').val(hpht);
  if (id && hpht) {
    $('#infoIbuBox').removeClass('d-none').html(
      '<strong>' + opt.text() + '</strong> | HPHT: <span id="infoHpht">' + hpht + '</span> | Usia Kehamilan: <strong id="infoUsia">-</strong>'
    );
  }
  hitungUsiaOtomatis();
}

function analyzeRisiko() {
  const lila = parseFloat($('#lila').val()); const td = $('#tekdarah').val();
  let kek=0, ht=0, anemia=0;
  if (lila > 0 && lila < 23.5) kek = 1;
  if (td) { const parts = td.split('/'); if (parts.length===2) { const sys=parseInt(parts[0]),dia=parseInt(parts[1]); if (sys>=140||dia>=90) ht=1; } }
  let status = 'Normal'; let bg = '#d1fae5'; let col = '#065f46';
  if (kek || ht || anemia) { status='Risiko Ringan'; bg='#fef3c7'; col='#92400e'; }
  if (ht && kek) { status='Risiko Tinggi'; bg='#fee2e2'; col='#991b1b'; }
  $('#badgeKEK').text(kek?'Berisiko':'Normal').removeClass().addClass('badge bg-'+(kek?'danger':'success'));
  $('#badgeHT').text(ht?'Berisiko':'Normal').removeClass().addClass('badge bg-'+(ht?'danger':'success'));
  $('#badgeAnemia').text(anemia?'Berisiko':'Normal').removeClass().addClass('badge bg-'+(anemia?'warning':'success'));
  $('#statusRisikoBox').css({background:bg});
  $('#statusRisikoText').text(status).css('color',col);
  $('#risikoDetail').removeClass('d-none');
  $('#risikoDisplay').addClass('d-none');
  $('#risiko_kek').val(kek); $('#risiko_ht').val(ht); $('#risiko_anemia').val(anemia); $('#status_risiko').val(status);
}
$(document).ready(function() {
  hitungUsiaOtomatis();
  $('#ibuSelect').on('change', function() { loadIbuInfo(this.value); });
  $('#tanggal_pemeriksaan').on('change input', hitungUsiaOtomatis);
  $('#formANC').on('submit', function(e) {
    e.preventDefault();
    showLoading('Menyimpan data pemeriksaan...');
    $.ajax({
      url: this.action, type:'POST', data: new FormData(this), processData:false, contentType:false, dataType:'json',
      success: function(r) {
        hideLoading();
        if (r && r.success) { Swal.fire({icon:'success',title:'Berhasil!',text:r.message,timer:2000,showConfirmButton:false}).then(()=>window.location.href='index.php'); }
        else { Swal.fire('Gagal!',(r && r.message)||'Error','error'); }
      },
      error: ()=>{ hideLoading(); Swal.fire('Error!','Koneksi bermasalah','error'); }
    });
  });
});
</script>
JS;
include __DIR__ . '/../../includes/footer.php';
?>
