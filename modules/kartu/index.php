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

$page_title = 'Cetak Kartu Peserta';
require_once __DIR__ . '/../../includes/header.php';
$balita = fetchAll("SELECT id, nama_lengkap, nomor_peserta FROM balita WHERE status_aktif=1 ORDER BY nama_lengkap");
$ibu_hamil = fetchAll("SELECT id, nama, nomor_peserta FROM ibu_hamil WHERE status_aktif=1 ORDER BY nama");
$lansia = fetchAll("SELECT id, nama, nomor_peserta FROM lansia WHERE status_aktif=1 ORDER BY nama");
?>
<section class="content-header"><div class="container-fluid"><div class="row mb-2">
  <div class="col-sm-6"><h1><i class="fas fa-id-card me-2 text-secondary"></i>Cetak Kartu Peserta</h1></div>
  <div class="col-sm-6"><ol class="breadcrumb float-sm-end"><li class="breadcrumb-item"><a href="../../dashboard.php">Dashboard</a></li><li class="breadcrumb-item active">Cetak Kartu</li></ol></div>
</div></div></section>
<section class="content"><div class="container-fluid">
<div class="row">
  <div class="col-md-4">
    <div class="card">
      <div class="card-header bg-primary text-white"><h5 class="mb-0"><i class="fas fa-baby me-2"></i>Kartu Balita</h5></div>
      <div class="card-body">
        <div class="position-relative mb-2">
          <div class="input-group">
            <span class="input-group-text"><i class="fas fa-search"></i></span>
            <input type="text" id="cariBalita" class="form-control" placeholder="Ketik huruf awal nama, contoh: A ..." autocomplete="off">
          </div>
          <div id="hasilCariBalita" class="list-group shadow w-100"></div>
        </div>
        <input type="hidden" id="selectBalita" value="">
        <div id="infoBalita" class="d-none mb-2"></div>
        <button class="btn btn-primary w-100" onclick="cetakKartu('balita', $('#selectBalita').val())"><i class="fas fa-print me-2"></i>Cetak Kartu Balita</button>
      </div>
    </div>
    <div class="card mt-3">
      <div class="card-header bg-danger text-white"><h5 class="mb-0"><i class="fas fa-female me-2"></i>Kartu Ibu Hamil</h5></div>
      <div class="card-body">
        <select id="selectIbu" class="form-select select2 mb-3">
          <option value="">-- Pilih Ibu Hamil --</option>
          <?php foreach ($ibu_hamil as $ib): ?><option value="<?= $ib['id'] ?>"><?= htmlspecialchars($ib['nama']) ?> (<?= $ib['nomor_peserta'] ?>)</option><?php endforeach; ?>
        </select>
        <button class="btn btn-danger w-100" onclick="cetakKartu('ibu_hamil', $('#selectIbu').val())"><i class="fas fa-print me-2"></i>Cetak Kartu Ibu Hamil</button>
      </div>
    </div>
    <div class="card mt-3">
      <div class="card-header bg-warning"><h5 class="mb-0"><i class="fas fa-user-injured me-2"></i>Kartu Lansia</h5></div>
      <div class="card-body">
        <select id="selectLansia" class="form-select select2 mb-3">
          <option value="">-- Pilih Lansia --</option>
          <?php foreach ($lansia as $l): ?><option value="<?= $l['id'] ?>"><?= htmlspecialchars($l['nama']) ?> (<?= $l['nomor_peserta'] ?>)</option><?php endforeach; ?>
        </select>
        <button class="btn btn-warning w-100" onclick="cetakKartu('lansia', $('#selectLansia').val())"><i class="fas fa-print me-2"></i>Cetak Kartu Lansia</button>
      </div>
    </div>
  </div>
  <div class="col-md-8">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-eye me-2"></i>Preview Kartu</h5>
        <button class="btn btn-secondary no-print" onclick="window.print()"><i class="fas fa-print me-2"></i>Print</button>
      </div>
      <div class="card-body print-area" id="kartuPreview">
        <div class="text-center py-5 text-muted">
          <i class="fas fa-id-card fa-4x mb-3 d-block opacity-50"></i>
          <p>Pilih peserta dan klik cetak kartu</p>
        </div>
      </div>
    </div>
  </div>
</div>
</div></section>
<script>
function cetakKartu(type, id) {
  if (!id) { Swal.fire('Perhatian!', 'Pilih peserta terlebih dahulu', 'warning'); return; }
  showLoading('Membuat kartu...');
  $.get('cetak.php?type=' + type + '&id=' + id + '&preview=1', function(html) {
    hideLoading();
    $('#kartuPreview').html(html);
    if (typeof QRCode !== 'undefined') {
      new QRCode(document.getElementById('qrcode'), { text: type.toUpperCase() + '-' + id, width: 80, height: 80 });
    }
  });
}
</script>
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
      hidden: "#selectBalita",
      info: "#infoBalita",
      csrf: window.CSRF_TOKEN,
      app: window.APP_URL
    });
  }
})();
</script>';
include __DIR__ . '/../../includes/footer.php';
?>