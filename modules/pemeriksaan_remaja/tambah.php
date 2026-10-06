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
 * SIMPOSYANDU - Pemeriksaan Anak Sekolah & Remaja
 */
$page_title = 'Pemeriksaan Remaja';
require_once __DIR__ . '/../../includes/header.php';

$id = (int)($_GET['id'] ?? $_GET['remaja_id'] ?? 0);
$r = null;
if ($id > 0) {
    $r = fetchOne("SELECT *, TIMESTAMPDIFF(YEAR, tanggal_lahir, CURDATE()) AS umur_tahun FROM remaja WHERE id=$id");
}

// Jika belum pilih remaja → tampilkan daftar pilihan
if (!$r) {
    $daftar = [];
    try {
        $daftar = fetchAll("SELECT id, nomor_peserta, nama_lengkap, jenis_kelamin, nik, kategori,
            TIMESTAMPDIFF(YEAR, tanggal_lahir, CURDATE()) AS umur_tahun
            FROM remaja
            WHERE (status_aktif=1 OR status_aktif IS NULL)
            ORDER BY nama_lengkap ASC
            LIMIT 300") ?: [];
    } catch (Throwable $e) {
        $daftar = [];
    }
    ?>
<section class="content-header">
  <div class="container-fluid"><div class="row mb-2">
    <div class="col-sm-6"><h1><i class="fas fa-stethoscope me-2 text-warning"></i>Pemeriksaan Remaja</h1></div>
    <div class="col-sm-6"><ol class="breadcrumb float-sm-end">
      <li class="breadcrumb-item"><a href="<?= APP_URL ?>/dashboard.php">Dashboard</a></li>
      <li class="breadcrumb-item"><a href="<?= APP_URL ?>/modules/remaja/index.php">Remaja</a></li>
      <li class="breadcrumb-item active">Pilih</li>
    </ol></div>
  </div></div>
</section>
<section class="content"><div class="container-fluid">
  <div class="alert alert-info border-0 shadow-sm">
    <i class="fas fa-info-circle me-1"></i>
    Pilih remaja yang akan diperiksa. Jika belum ada di daftar, daftar dulu dari
    <a href="<?= APP_URL ?>/modules/remaja/index.php" class="fw-semibold">Data Remaja</a>
    (tombol <strong>Daftarkan</strong>).
  </div>
  <div class="card border-0 shadow-sm">
    <div class="card-header bg-white"><h5 class="mb-0"><i class="fas fa-users me-2 text-warning"></i>Pilih Remaja Terdaftar</h5></div>
    <div class="card-body table-responsive">
      <table class="table table-hover datatable align-middle" style="width:100%">
        <thead class="table-light">
          <tr><th>No</th><th>Nama</th><th>L/P</th><th>Umur</th><th>NIK</th><th>Kategori</th><th>Aksi</th></tr>
        </thead>
        <tbody>
        <?php if (empty($daftar)): ?>
          <tr><td colspan="7" class="text-center text-muted py-4">
            Belum ada remaja terdaftar di Posyandu.<br>
            <a href="<?= APP_URL ?>/modules/remaja/index.php" class="btn btn-warning btn-sm mt-2">
              <i class="fas fa-user-plus me-1"></i>Ke Data Remaja
            </a>
          </td></tr>
        <?php else: foreach ($daftar as $i => $row): ?>
          <tr>
            <td><?= $i + 1 ?></td>
            <td>
              <div class="fw-semibold"><?= htmlspecialchars($row['nama_lengkap']) ?></div>
              <small class="text-muted"><?= htmlspecialchars($row['nomor_peserta'] ?? '') ?></small>
            </td>
            <td><?= ($row['jenis_kelamin'] ?? '') === 'L' ? 'L' : 'P' ?></td>
            <td><?= (int)($row['umur_tahun'] ?? 0) ?> th</td>
            <td><code class="small"><?= htmlspecialchars($row['nik'] ?: '-') ?></code></td>
            <td><?= htmlspecialchars($row['kategori'] ?? '-') ?></td>
            <td>
              <a href="?remaja_id=<?= (int)$row['id'] ?>" class="btn btn-sm btn-warning">
                <i class="fas fa-stethoscope me-1"></i>Periksa
              </a>
            </td>
          </tr>
        <?php endforeach; endif; ?>
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
    <div class="col-sm-6"><h1><i class="fas fa-stethoscope me-2 text-warning"></i>Pemeriksaan Remaja</h1></div>
    <div class="col-sm-6"><ol class="breadcrumb float-sm-end">
      <li class="breadcrumb-item"><a href="<?= APP_URL ?>/dashboard.php">Dashboard</a></li>
      <li class="breadcrumb-item"><a href="<?= APP_URL ?>/modules/remaja/index.php">Remaja</a></li>
      <li class="breadcrumb-item active">Pemeriksaan</li>
    </ol></div>
  </div></div>
</section>
<section class="content"><div class="container-fluid">
  <div class="alert alert-warning border-0 d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div>
      <strong><?= htmlspecialchars($r['nama_lengkap']) ?></strong>
      · <?= (int)($r['umur_tahun'] ?? 0) ?> tahun
      · <?= (($r['jenis_kelamin'] ?? '') === 'L') ? 'Laki-laki' : 'Perempuan' ?>
      · NIK: <code><?= htmlspecialchars($r['nik'] ?: '-') ?></code>
      <?php if (!empty($r['kategori'])): ?>
        · <span class="badge bg-secondary"><?= htmlspecialchars($r['kategori']) ?></span>
      <?php endif; ?>
    </div>
    <a href="<?= APP_URL ?>/modules/pemeriksaan_remaja/tambah.php" class="btn btn-sm btn-outline-secondary">Ganti remaja</a>
  </div>
  <div class="card border-0 shadow-sm">
    <div class="card-header bg-warning"><h5 class="mb-0"><i class="fas fa-notes-medical me-2"></i>Form Pemeriksaan</h5></div>
    <div class="card-body">
      <form id="formPeriksaRemaja" method="post" action="<?= APP_URL ?>/ajax/save_pemeriksaan_remaja.php">
        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
        <input type="hidden" name="remaja_id" value="<?= $id ?>">
        <div class="row g-3">
          <div class="col-md-3">
            <label class="form-label fw-semibold">Tanggal <span class="text-danger">*</span></label>
            <input type="date" name="tanggal_pemeriksaan" class="form-control" value="<?= date('Y-m-d') ?>" required>
          </div>
          <div class="col-md-3">
            <label class="form-label fw-semibold">Berat Badan (kg)</label>
            <input type="number" step="0.1" min="0" name="berat_badan" id="bbRemaja" class="form-control">
          </div>
          <div class="col-md-3">
            <label class="form-label fw-semibold">Tinggi Badan (cm)</label>
            <input type="number" step="0.1" min="0" name="tinggi_badan" id="tbRemaja" class="form-control">
          </div>
          <div class="col-md-3">
            <label class="form-label fw-semibold">IMT (otomatis)</label>
            <input type="text" name="imt" id="imtRemaja" class="form-control bg-light" readonly>
          </div>
          <div class="col-md-3">
            <label class="form-label fw-semibold">Hb (g/dL)</label>
            <input type="number" step="0.1" min="0" name="hb" class="form-control" placeholder="contoh: 12.5">
          </div>
          <div class="col-md-3">
            <label class="form-label fw-semibold">Tekanan Darah</label>
            <input type="text" name="tekanan_darah" class="form-control" placeholder="110/70">
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">Edukasi Kesehatan</label>
            <textarea name="edukasi_kesehatan" class="form-control" rows="2" placeholder="Materi yang diberikan"></textarea>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">Keluhan</label>
            <textarea name="keluhan" class="form-control" rows="2"></textarea>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">Penanganan / Catatan</label>
            <textarea name="penanganan" class="form-control" rows="2"></textarea>
          </div>
        </div>
        <div class="mt-4 d-flex gap-2 flex-wrap">
          <button type="submit" class="btn btn-warning btn-lg"><i class="fas fa-save me-2"></i>Simpan</button>
          <a href="<?= APP_URL ?>/modules/remaja/detail.php?id=<?= $id ?>" class="btn btn-secondary btn-lg">Batal</a>
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
    var b = parseFloat(\$('#bbRemaja').val()) || 0;
    var t = parseFloat(\$('#tbRemaja').val()) || 0;
    if (b > 0 && t > 0) {
      \$('#imtRemaja').val((b / ((t/100)*(t/100))).toFixed(2));
    } else {
      \$('#imtRemaja').val('');
    }
  }
  \$('#bbRemaja, #tbRemaja').on('input change', hitungIMT);
  \$('#formPeriksaRemaja').on('submit', function(e) {
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
            .then(function(){ window.location.href = '{$__app}/modules/remaja/detail.php?id={$__id}'; });
        } else {
          Swal.fire('Gagal', (r && r.message) ? r.message : 'Gagal menyimpan', 'error');
        }
      },
      error: function(xhr) {
        if (typeof hideLoading === 'function') hideLoading();
        var msg = 'Koneksi bermasalah';
        try { var j = JSON.parse(xhr.responseText); if (j && j.message) msg = j.message; } catch(e) {}
        Swal.fire('Error', msg, 'error');
      }
    });
  });
});
</script>
JS;
include __DIR__ . '/../../includes/footer.php';
?>
