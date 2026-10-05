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

$page_title = 'Detail Keluarga (OpenSID)';
require_once __DIR__ . '/../../includes/header.php';

$no_kk = trim($_GET['no_kk'] ?? '');
$anggota = $no_kk ? getAnggotaKeluargaOpenSID($no_kk) : [];
$kepala = null;
foreach ($anggota as $a) {
    if (($a['status_dalam_keluarga'] ?? '') === 'Kepala Keluarga') { $kepala = $a; break; }
}
if (!$kepala && !empty($anggota)) $kepala = $anggota[0];
?>
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6"><h1><i class="fas fa-home me-2"></i>Detail Keluarga</h1></div>
      <div class="col-sm-6"><ol class="breadcrumb float-sm-end">
        <li class="breadcrumb-item"><a href="<?= APP_URL ?>/dashboard.php">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="index.php">Keluarga</a></li>
        <li class="breadcrumb-item active">Detail</li>
      </ol></div>
    </div>
  </div>
</section>
<section class="content"><div class="container-fluid">
  <?php if (empty($anggota)): ?>
  <div class="alert alert-warning">Keluarga tidak ditemukan.</div>
  <?php else: ?>
  <div class="card mb-3">
    <div class="card-header bg-primary text-white"><h5 class="mb-0">No. KK: <?= htmlspecialchars($no_kk) ?></h5></div>
    <div class="card-body">
      <p class="mb-1"><strong>Kepala:</strong> <?= htmlspecialchars($kepala['nama'] ?? '-') ?></p>
      <p class="mb-0"><strong>Alamat:</strong> <?= htmlspecialchars(($kepala['dusun'] ?? '') . ' RT ' . ($kepala['rt'] ?? '-') . '/RW ' . ($kepala['rw'] ?? '-')) ?></p>
    </div>
  </div>
  <div class="card">
    <div class="card-header"><h5 class="mb-0">Anggota Keluarga (<?= count($anggota) ?>)</h5></div>
    <div class="card-body table-responsive">
      <table class="table table-sm table-hover">
        <thead><tr><th>NIK</th><th>Nama</th><th>L/P</th><th>Tgl Lahir</th><th>Umur</th><th>Hubungan</th></tr></thead>
        <tbody>
          <?php foreach ($anggota as $a): ?>
          <tr>
            <td><code><?= htmlspecialchars($a['nik']) ?></code></td>
            <td><?= htmlspecialchars($a['nama']) ?></td>
            <td><?= $a['jenis_kelamin'] ?></td>
            <td><?= formatTanggal($a['tanggal_lahir'] ?? '') ?></td>
            <td><?= $a['umur'] ?? '-' ?> th</td>
            <td><?= htmlspecialchars($a['status_dalam_keluarga']) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php endif; ?>
  <a href="index.php" class="btn btn-secondary mt-2"><i class="fas fa-arrow-left me-1"></i>Kembali</a>
</div></section>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
