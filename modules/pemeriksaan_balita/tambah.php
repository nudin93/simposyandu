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

$page_title = 'Tambah Pemeriksaan Balita';
require_once __DIR__ . '/../../includes/header.php';
$balita_id = (int)($_GET['balita_id'] ?? 0);
$balita = fetchOne("SELECT *, TIMESTAMPDIFF(MONTH, tanggal_lahir, CURDATE()) as umur_bulan FROM balita WHERE id=$balita_id");
// Data awal: lokal + OpenSID (untuk opsi awal select)
$semua_balita = fetchAll("SELECT id, nama_lengkap, nomor_peserta, tanggal_lahir, nama_ibu,
    id_penduduk_opensid,
    TIMESTAMPDIFF(MONTH, tanggal_lahir, CURDATE()) AS umur_bulan
    FROM balita
    WHERE (status_aktif=1 OR status_aktif IS NULL)
      AND tanggal_lahir IS NOT NULL
      AND TIMESTAMPDIFF(MONTH, tanggal_lahir, CURDATE()) BETWEEN 0 AND 59
    ORDER BY nama_lengkap ASC
    LIMIT 300") ?: [];

// Tandai sumber lokal
foreach ($semua_balita as &$bl) {
    $bl['sumber'] = 'lokal';
    $bl['select_id'] = (string)$bl['id'];
}
unset($bl);

// Gabung OpenSID yang belum ada di lokal
if (opensid_available() && function_exists('getBalitaOpenSID')) {
    $seen_oid = [];
    $seen_nik = [];
    foreach ($semua_balita as $bl) {
        $oid = (int)($bl['id_penduduk_opensid'] ?? 0);
        if ($oid) $seen_oid[$oid] = true;
        $nik = trim($bl['nik_anak'] ?? '');
        if ($nik !== '') $seen_nik[$nik] = true;
    }
    foreach (getBalitaOpenSID(59, 0) as $p) {
        $oid = (int)($p['id_penduduk'] ?? 0);
        $nik = trim($p['nik'] ?? '');
        if ($oid && isset($seen_oid[$oid])) continue;
        if ($nik !== '' && isset($seen_nik[$nik])) continue;
        $semua_balita[] = [
            'id' => 0,
            'select_id' => 'os:' . $oid,
            'nama_lengkap' => $p['nama'] ?? '',
            'nomor_peserta' => '',
            'tanggal_lahir' => $p['tanggal_lahir'] ?? '',
            'nama_ibu' => $p['nama_ibu'] ?? '',
            'umur_bulan' => (int)($p['umur_bulan'] ?? 0),
            'sumber' => 'opensid',
            'id_penduduk_opensid' => $oid,
            'nik_anak' => $nik,
        ];
        if ($oid) $seen_oid[$oid] = true;
        if ($nik !== '') $seen_nik[$nik] = true;
        if (count($semua_balita) >= 500) break;
    }
}

$kader_list = fetchAll("SELECT id, nama FROM kader WHERE status=1");
$petugas_default = $user['id'];
?>
<section class="content-header"><div class="container-fluid"><div class="row mb-2">
  <div class="col-sm-6"><h1><i class="fas fa-stethoscope me-2 text-success"></i>Pemeriksaan Balita</h1></div>
  <div class="col-sm-6"><ol class="breadcrumb float-sm-end">
    <li class="breadcrumb-item"><a href="../../dashboard.php">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="index.php">Pemeriksaan</a></li>
    <li class="breadcrumb-item active">Tambah</li>
  </ol></div>
</div></div></section>
<section class="content"><div class="container-fluid">
<form method="POST" action="../../ajax/save_pemeriksaan_balita.php" id="formPmx" class="form-ajax">
<input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
<div class="row">
  <div class="col-md-8">
    <div class="card">
      <div class="card-header bg-success text-white"><h5 class="mb-0"><i class="fas fa-notes-medical me-2"></i>Data Pemeriksaan</h5></div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-8">
            <label class="form-label fw-semibold">Cari Nama Balita <span class="text-danger">*</span></label>
            <div class="position-relative">
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
                <input type="text" id="cariBalita" class="form-control form-control-lg"
                  placeholder="Ketik huruf awal nama, contoh: A ..."
                  autocomplete="off"
                  value="<?= $balita ? htmlspecialchars($balita['nama_lengkap']) : '' ?>">
              </div>
              <div id="hasilCariBalita" class="list-group shadow w-100"></div>
            </div>
            <input type="hidden" name="balita_id" id="balita_id" value="<?= (int)$balita_id ?>" required>
            <small class="text-muted">Ketik untuk mencari dari <strong>OpenSID</strong> &amp; Posyandu, lalu <strong>klik nama</strong> yang muncul.</small>
          </div>
          <div class="col-md-4">
            <label class="form-label fw-semibold">Tanggal Pemeriksaan <span class="text-danger">*</span></label>
            <input type="date" name="tanggal_pemeriksaan" class="form-control datepicker" value="<?= date('Y-m-d') ?>" required>
          </div>
        </div>
        <?php if ($balita): ?>
        <div class="alert alert-info mt-3" id="infoBalita">
          <strong><?= htmlspecialchars($balita['nama_lengkap']) ?></strong> | <?= $balita['jenis_kelamin']=='L'?'Laki-laki':'Perempuan' ?> | Umur: <strong><?= hitungUmur($balita['tanggal_lahir']) ?></strong> (<?= $balita['umur_bulan'] ?> bulan)
        </div>
        <?php else: ?>
        <div id="infoBalita" class="d-none mt-3"></div>
        <?php endif; ?>
        <hr>
        <h6 class="fw-bold text-primary"><i class="fas fa-weight me-2"></i>Antropometri</h6>
        <div class="row g-3">
          <div class="col-6 col-md-3">
            <label class="form-label fw-semibold">Berat Badan <span class="text-danger">*</span></label>
            <div class="input-group"><input type="number" name="berat_badan" id="bb" class="form-control" placeholder="0.0" step="0.1" min="1" max="50" required onchange="hitungStatusGizi()"><span class="input-group-text">kg</span></div>
          </div>
          <div class="col-6 col-md-3">
            <label class="form-label fw-semibold">Tinggi Badan <span class="text-danger">*</span></label>
            <div class="input-group"><input type="number" name="tinggi_badan" id="tb" class="form-control" placeholder="0.0" step="0.1" min="20" max="150" required onchange="hitungStatusGizi()"><span class="input-group-text">cm</span></div>
          </div>
          <div class="col-6 col-md-3">
            <label class="form-label fw-semibold">Lingkar Kepala</label>
            <div class="input-group"><input type="number" name="lingkar_kepala" class="form-control" placeholder="0.0" step="0.1"><span class="input-group-text">cm</span></div>
          </div>
          <div class="col-6 col-md-3">
            <label class="form-label fw-semibold">Lingkar Lengan</label>
            <div class="input-group"><input type="number" name="lingkar_lengan" class="form-control" placeholder="0.0" step="0.1"><span class="input-group-text">cm</span></div>
          </div>
        </div>
        <div class="row g-3 mt-1">
          <div class="col-6 col-md-3">
            <label class="form-label fw-semibold">Suhu Tubuh</label>
            <div class="input-group"><input type="number" name="suhu_tubuh" class="form-control" placeholder="36.5" step="0.1" min="34" max="42"><span class="input-group-text">°C</span></div>
          </div>
          <div class="col-6 col-md-3">
            <label class="form-label fw-semibold">Denyut Nadi</label>
            <div class="input-group"><input type="number" name="denyut_nadi" class="form-control" placeholder="80" min="40" max="200"><span class="input-group-text">bpm</span></div>
          </div>
          <div class="col-6 col-md-3">
            <label class="form-label fw-semibold">Nafsu Makan</label>
            <select name="nafsu_makan" class="form-select select2"><option>Baik</option><option>Kurang</option><option>Buruk</option></select>
          </div>
          <div class="col-6 col-md-3">
            <label class="form-label fw-semibold">Status ASI</label>
            <select name="status_asi" class="form-select select2"><option value="Ya">Masih ASI</option><option value="Tidak">Tidak ASI</option></select>
          </div>
        </div>
        <hr>
        <h6 class="fw-bold text-info"><i class="fas fa-syringe me-2"></i>Tindakan</h6>
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label fw-semibold">Imunisasi Diberikan</label>
            <input type="text" name="imunisasi_diberikan" class="form-control" placeholder="Contoh: Polio 3, DPT-HB-Hib 2">
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">Vitamin Diberikan</label>
            <input type="text" name="vitamin_diberikan" class="form-control" placeholder="Contoh: Vitamin A Merah">
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">Keluhan</label>
            <textarea name="keluhan" class="form-control" rows="2" placeholder="Keluhan yang disampaikan orang tua"></textarea>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">Penanganan</label>
            <textarea name="penanganan" class="form-control" rows="2" placeholder="Tindakan yang diberikan"></textarea>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">Catatan Kader</label>
            <textarea name="catatan_kader" class="form-control" rows="2" placeholder="Catatan tambahan"></textarea>
          </div>
          <div class="col-md-3">
            <label class="form-label fw-semibold">Jadwal Kontrol</label>
            <input type="date" name="jadwal_kontrol" class="form-control datepicker">
          </div>
          <div class="col-md-3">
            <label class="form-label fw-semibold">Petugas</label>
            <select name="petugas_id" class="form-select select2">
              <?php foreach ($kader_list as $k): ?>
              <option value="<?= $k['id'] ?>" <?= $k['id']==$petugas_default?'selected':'' ?>><?= htmlspecialchars($k['nama']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
      </div>
      <div class="card-footer d-flex justify-content-end gap-2">
        <a href="index.php" class="btn btn-secondary btn-lg"><i class="fas fa-times me-2"></i>Batal</a>
        <button type="submit" class="btn btn-success btn-lg"><i class="fas fa-save me-2"></i>Simpan Pemeriksaan</button>
      </div>
    </div>
  </div>
  <!-- Analisa Otomatis -->
  <div class="col-md-4">
    <div class="card sticky-top" style="top:80px">
      <div class="card-header bg-info text-white"><h5 class="mb-0"><i class="fas fa-brain me-2"></i>Analisa Otomatis</h5></div>
      <div class="card-body">
        <div class="text-center mb-3">
          <div id="statusGiziDisplay" class="py-3 rounded-3 bg-light">
            <i class="fas fa-calculator fa-2x text-muted mb-2 d-block"></i>
            <p class="text-muted mb-0">Isi data antropometri untuk analisa</p>
          </div>
        </div>
        <div id="analisaDetail" class="d-none">
          <table class="table table-sm table-borderless">
            <tr><th>IMT</th><td id="imtVal">-</td></tr>
            <tr><th>Status Gizi</th><td id="statusGiziVal">-</td></tr>
            <tr><th>Risiko Stunting</th><td id="stuntingVal">-</td></tr>
          </table>
          <input type="hidden" name="imt" id="imt_hidden">
          <input type="hidden" name="status_gizi" id="status_gizi_hidden">
          <input type="hidden" name="risiko_stunting" id="risiko_stunting_hidden">
          <input type="hidden" name="umur_saat_periksa" id="umur_saat_periksa_hidden">
        </div>
        <div class="alert alert-warning mt-2 small"><i class="fas fa-info-circle me-1"></i>Analisa berdasarkan standar WHO & Kemenkes RI</div>
      </div>
    </div>
    <!-- Riwayat BB -->
    <?php if ($balita): ?>
    <?php $riwayat = fetchAll("SELECT tanggal_pemeriksaan, berat_badan, tinggi_badan, status_gizi FROM pemeriksaan_balita WHERE balita_id=$balita_id ORDER BY tanggal_pemeriksaan DESC LIMIT 5"); ?>
    <?php if ($riwayat): ?>
    <div class="card mt-3">
      <div class="card-header"><h6 class="mb-0"><i class="fas fa-history me-2"></i>Riwayat Terakhir</h6></div>
      <div class="card-body p-0">
        <div class="list-group list-group-flush">
          <?php foreach ($riwayat as $r): ?>
          <div class="list-group-item py-2">
            <div class="d-flex justify-content-between">
              <small class="text-muted"><?= formatTanggal($r['tanggal_pemeriksaan']) ?></small>
              <span class="badge bg-<?= $r['status_gizi']=='Normal'?'success':($r['status_gizi']=='Kurang'?'warning':'danger') ?>"><?= $r['status_gizi'] ?></span>
            </div>
            <div class="small">BB: <b><?= $r['berat_badan'] ?> kg</b> | TB: <b><?= $r['tinggi_badan'] ?> cm</b></div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
    <?php endif; ?>
    <?php endif; ?>
  </div>
</div>
</form>
</div></section>
<script>
let umurBulan = <?= $balita ? $balita['umur_bulan'] : 0 ?>;
function loadBalitaInfo(id) {
  if (!id) return;
  $.get('../../ajax/get_balita.php?id=' + id, function(r) {
    if (r.data) {
      const b = r.data; umurBulan = b.umur_bulan;
      $('#infoBalita').removeClass('d-none').addClass('alert alert-info').html(
        '<strong>' + b.nama_lengkap + '</strong> | ' + (b.jenis_kelamin=='L'?'Laki-laki':'Perempuan') + ' | Umur: <strong>' + b.umur + '</strong> (' + b.umur_bulan + ' bulan)'
      );
    }
  }, 'json');
}
function hitungStatusGizi() {
  const bb = parseFloat($('#bb').val()); const tb = parseFloat($('#tb').val());
  if (!bb || !tb || !umurBulan) return;
  const imt = (bb / Math.pow(tb/100, 2)).toFixed(2);
  let status = 'Normal'; let sc = 'success';
  if (imt < 12) { status = 'Buruk'; sc = 'danger'; }
  else if (imt < 14) { status = 'Kurang'; sc = 'warning'; }
  else if (imt > 18) { status = 'Lebih'; sc = 'info'; }
  const tbStandar = {3:56.4,6:63.3,9:69.1,12:73.8,18:80.7,24:86.4,36:94.2,48:100.7,60:106.7};
  let stunting = 'Tidak'; let stSc = 'success'; let batas = 0;
  for (const [bln, tinggi] of Object.entries(tbStandar)) { if (umurBulan <= bln) { batas = tinggi; break; } }
  if (batas > 0) { const p = (tb/batas)*100; if (p<85) { stunting='Stunting'; stSc='danger'; } else if (p<90) { stunting='Risiko'; stSc='warning'; } }
  $('#statusGiziDisplay').html('<span class="badge bg-' + sc + ' fs-6 px-4 py-2">' + status + '</span>');
  $('#analisaDetail').removeClass('d-none');
  $('#imtVal').text(imt); $('#statusGiziVal').html('<span class="badge bg-' + sc + '">' + status + '</span>');
  $('#stuntingVal').html('<span class="badge bg-' + stSc + '">' + stunting + '</span>');
  $('#imt_hidden').val(imt); $('#status_gizi_hidden').val(status); $('#risiko_stunting_hidden').val(stunting); $('#umur_saat_periksa_hidden').val(umurBulan);
}
$(document).ready(function() {
  if (<?= $balita_id ?>) loadBalitaInfo(<?= $balita_id ?>);
});
</script>
<?php
$extra_js = '<script>window.APP_URL="' . APP_URL . '";window.CSRF_TOKEN="' . csrfToken() . '";</script>'
  . '<script src="' . APP_URL . '/assets/js/cari-balita.js?v=2"></script>'
  . '<script>
(function () {
  function onPick(b, localId) {
    if (typeof loadBalitaInfo === "function" && localId) loadBalitaInfo(localId);
  }
  function afterBoot() {
    var existing = document.getElementById("balita_id");
    if (existing && existing.value && existing.value !== "0" && typeof loadBalitaInfo === "function") {
      loadBalitaInfo(existing.value);
    }
  }
  if (typeof bootCariBalita === "function") {
    bootCariBalita({
      minLen: 1,
      input: "#cariBalita",
      hasil: "#hasilCariBalita",
      hidden: "#balita_id",
      info: "#infoBalita",
      csrf: window.CSRF_TOKEN,
      app: window.APP_URL,
      onSelect: onPick
    });
    afterBoot();
  } else {
    console.error("bootCariBalita tidak tersedia");
  }
})();
</script>';
include __DIR__ . '/../../includes/footer.php';
?>
