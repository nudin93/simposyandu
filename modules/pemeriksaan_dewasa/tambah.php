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
 * SIMPOSYANDU - Skrining PTM (Usia Produktif)
 */
$page_title = 'Skrining PTM';
require_once __DIR__ . '/../../includes/header.php';

$id = (int)($_GET['id'] ?? $_GET['usia_produktif_id'] ?? 0);
$u = null;
if ($id > 0) {
    $u = fetchOne("SELECT *, TIMESTAMPDIFF(YEAR, tanggal_lahir, CURDATE()) AS umur_tahun FROM usia_produktif WHERE id=$id");
}

// Belum pilih sasaran → tampilkan daftar pilihan
if (!$u) {
    $daftar = [];
    try {
        $daftar = fetchAll("SELECT id, nomor_peserta, nama_lengkap, jenis_kelamin, nik, dusun,
            TIMESTAMPDIFF(YEAR, tanggal_lahir, CURDATE()) AS umur_tahun
            FROM usia_produktif
            WHERE (status_aktif=1 OR status_aktif IS NULL)
            ORDER BY nama_lengkap ASC
            LIMIT 500") ?: [];
    } catch (Throwable $e) {
        $daftar = [];
    }
    ?>
<section class="content-header">
  <div class="container-fluid"><div class="row mb-2">
    <div class="col-sm-6"><h1><i class="fas fa-stethoscope me-2 text-primary"></i>Skrining PTM</h1></div>
    <div class="col-sm-6"><ol class="breadcrumb float-sm-end">
      <li class="breadcrumb-item"><a href="<?= APP_URL ?>/dashboard.php">Dashboard</a></li>
      <li class="breadcrumb-item"><a href="<?= APP_URL ?>/modules/usia_produktif/index.php">Usia Produktif</a></li>
      <li class="breadcrumb-item active">Pilih</li>
    </ol></div>
  </div></div>
</section>
<section class="content"><div class="container-fluid">
  <div class="alert alert-info border-0 shadow-sm">
    <i class="fas fa-info-circle me-1"></i>
    Pilih sasaran usia produktif yang akan diskrining. Jika belum ada di daftar, daftar dulu dari
    <a href="<?= APP_URL ?>/modules/usia_produktif/index.php" class="fw-semibold">Data Usia Produktif</a>
    (tombol <strong>Daftarkan</strong>).
  </div>
  <div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
      <h5 class="mb-0"><i class="fas fa-users me-2 text-primary"></i>Pilih Usia Produktif Terdaftar</h5>
      <a href="<?= APP_URL ?>/modules/usia_produktif/index.php" class="btn btn-sm btn-outline-primary">
        <i class="fas fa-list me-1"></i>Ke Daftar
      </a>
    </div>
    <div class="card-body table-responsive">
      <table class="table table-hover datatable align-middle" style="width:100%">
        <thead class="table-light">
          <tr><th>No</th><th>Nama</th><th>L/P</th><th>Umur</th><th>NIK</th><th>Dusun</th><th>Aksi</th></tr>
        </thead>
        <tbody>
        <?php foreach ($daftar as $i => $row): ?>
          <tr>
            <td><?= $i + 1 ?></td>
            <td>
              <div class="fw-semibold"><?= htmlspecialchars($row['nama_lengkap']) ?></div>
              <small class="text-muted"><?= htmlspecialchars($row['nomor_peserta'] ?? '') ?></small>
            </td>
            <td><?= ($row['jenis_kelamin'] ?? '') === 'L' ? 'L' : 'P' ?></td>
            <td><?= (int)($row['umur_tahun'] ?? 0) ?> th</td>
            <td><code class="small"><?= htmlspecialchars($row['nik'] ?: '-') ?></code></td>
            <td><?= htmlspecialchars($row['dusun'] ?: '-') ?></td>
            <td>
              <a href="?usia_produktif_id=<?= (int)$row['id'] ?>" class="btn btn-sm btn-primary">
                <i class="fas fa-stethoscope me-1"></i>Skrining
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div></section>
<?php
    include __DIR__ . '/../../includes/footer.php';
    exit;
}
?>
<section class="content-header">
  <div class="container-fluid"><div class="row mb-2">
    <div class="col-sm-6"><h1><i class="fas fa-stethoscope me-2 text-primary"></i>Skrining PTM</h1></div>
    <div class="col-sm-6"><ol class="breadcrumb float-sm-end">
      <li class="breadcrumb-item"><a href="<?= APP_URL ?>/dashboard.php">Dashboard</a></li>
      <li class="breadcrumb-item"><a href="<?= APP_URL ?>/modules/usia_produktif/index.php">Usia Produktif</a></li>
      <li class="breadcrumb-item active">Skrining</li>
    </ol></div>
  </div></div>
</section>
<section class="content"><div class="container-fluid">
  <div class="alert alert-info border-0 d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div>
      <strong><?= htmlspecialchars($u['nama_lengkap']) ?></strong>
      · <?= (int)($u['umur_tahun'] ?? 0) ?> tahun
      · <?= (($u['jenis_kelamin'] ?? '') === 'L') ? 'Laki-laki' : 'Perempuan' ?>
      · NIK: <code><?= htmlspecialchars($u['nik'] ?: '-') ?></code>
    </div>
    <a href="<?= APP_URL ?>/modules/pemeriksaan_dewasa/tambah.php" class="btn btn-sm btn-outline-secondary">Ganti sasaran</a>
  </div>
  <div class="card border-0 shadow-sm">
    <div class="card-header bg-primary text-white"><h5 class="mb-0"><i class="fas fa-heartbeat me-2"></i>Form Skrining PTM</h5></div>
    <div class="card-body">
      <form id="formSkriningPTM" method="post" action="<?= APP_URL ?>/ajax/save_pemeriksaan_dewasa.php">
        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
        <input type="hidden" name="usia_produktif_id" value="<?= $id ?>">
        <div class="row g-3">
          <div class="col-md-3">
            <label class="form-label fw-semibold">Tanggal <span class="text-danger">*</span></label>
            <input type="date" name="tanggal_pemeriksaan" class="form-control" value="<?= date('Y-m-d') ?>" required>
          </div>
          <div class="col-md-3">
            <label class="form-label fw-semibold">Berat Badan (kg)</label>
            <input type="number" step="0.1" name="berat_badan" id="bbPTM" class="form-control">
          </div>
          <div class="col-md-3">
            <label class="form-label fw-semibold">Tinggi Badan (cm)</label>
            <input type="number" step="0.1" name="tinggi_badan" id="tbPTM" class="form-control">
          </div>
          <div class="col-md-3">
            <label class="form-label fw-semibold">IMT (otomatis)</label>
            <input type="text" name="imt" id="imtPTM" class="form-control bg-light" readonly>
          </div>
          <div class="col-md-3">
            <label class="form-label fw-semibold">Lingkar Perut (cm)</label>
            <input type="number" step="0.1" name="lingkar_perut" class="form-control">
          </div>
          <div class="col-md-3">
            <label class="form-label fw-semibold">Tekanan Darah</label>
            <input type="text" name="tekanan_darah" class="form-control" placeholder="120/80">
          </div>
          <div class="col-md-3">
            <label class="form-label fw-semibold">Gula Darah</label>
            <input type="number" step="0.1" name="gula_darah" class="form-control">
          </div>
          <div class="col-md-3">
            <label class="form-label fw-semibold">Kolesterol</label>
            <input type="number" step="0.1" name="kolesterol" class="form-control">
          </div>
          <div class="col-md-12">
            <label class="form-label fw-semibold">Faktor Risiko / Keluhan</label>
            <textarea name="faktor_risiko" class="form-control" rows="2"></textarea>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">Penanganan</label>
            <textarea name="penanganan" class="form-control" rows="2"></textarea>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">Rujukan</label>
            <textarea name="rujukan" class="form-control" rows="2"></textarea>
          </div>
        </div>
        <div class="mt-4 d-flex gap-2 flex-wrap">
          <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save me-2"></i>Simpan</button>
          <a href="<?= APP_URL ?>/modules/usia_produktif/detail.php?id=<?= $id ?>" class="btn btn-secondary btn-lg">Batal</a>
        </div>
      </form>
    </div>
  </div>
</div></section>
<?php
$__app = APP_URL;
$__id = $id;
$extra_js = <<<JS
<script>
\$(function(){
  function hitungIMT() {
    var b = parseFloat(\$('#bbPTM').val()) || 0;
    var t = parseFloat(\$('#tbPTM').val()) || 0;
    if (b > 0 && t > 0) {
      \$('#imtPTM').val((b / ((t/100)*(t/100))).toFixed(2));
    } else {
      \$('#imtPTM').val('');
    }
  }
  \$('#bbPTM, #tbPTM').on('input change', hitungIMT);
  \$('#formSkriningPTM').on('submit', function(e) {
    e.preventDefault();
    if (typeof showLoading === 'function') showLoading('Menyimpan...');
    \$.ajax({
      url: this.action,
      type: 'POST',
      data: new FormData(this),
      processData: false,
      contentType: false,
      dataType: 'json',
      success: function(r) {
        if (typeof hideLoading === 'function') hideLoading();
        if (r && r.success) {
          Swal.fire({icon:'success', title:'Berhasil!', text:r.message, timer:1800, showConfirmButton:false})
            .then(function(){ window.location.href = '{$__app}/modules/usia_produktif/detail.php?id={$__id}'; });
        } else {
          Swal.fire('Gagal', (r && r.message) ? r.message : 'Gagal menyimpan', 'error');
        }
      },
      error: function() {
        if (typeof hideLoading === 'function') hideLoading();
        Swal.fire('Error', 'Koneksi bermasalah', 'error');
      }
    });
  });
});
</script>
JS;
include __DIR__ . '/../../includes/footer.php';
?>
