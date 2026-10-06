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

$page_title = 'Profil Penduduk (OpenSID)';
require_once __DIR__ . '/../../includes/header.php';

$id = (int)($_GET['id'] ?? 0);
$p = $id ? getPendudukOpenSIDById($id) : null;

if (!$p) {
    echo '<section class="content"><div class="container-fluid"><div class="alert alert-warning mt-3">
    Data penduduk tidak ditemukan di OpenSID atau koneksi gagal.
    <a href="index.php" class="alert-link">Kembali</a></div></div></section>';
    include __DIR__ . '/../../includes/footer.php';
    exit;
}

$anggota = [];
if (!empty($p['no_kk'])) {
    $anggota = getAnggotaKeluargaOpenSID($p['no_kk']);
}
?>
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6"><h1><i class="fas fa-user me-2 text-primary"></i>Profil Penduduk</h1></div>
      <div class="col-sm-6"><ol class="breadcrumb float-sm-end">
        <li class="breadcrumb-item"><a href="<?= APP_URL ?>/dashboard.php">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="index.php">Penduduk</a></li>
        <li class="breadcrumb-item active">Profil</li>
      </ol></div>
    </div>
  </div>
</section>
<section class="content"><div class="container-fluid">
<div class="row">
    <div class="col-md-6">
      <div class="card">
        <div class="card-header bg-primary text-white"><h5 class="mb-0">Identitas</h5></div>
        <div class="card-body">
          <table class="table table-sm table-borderless mb-0">
            <tr><th width="40%">NIK</th><td><code><?= htmlspecialchars($p['nik']) ?></code></td></tr>
            <tr><th>No. KK</th><td><?= htmlspecialchars($p['no_kk'] ?: '-') ?></td></tr>
            <tr><th>Nama</th><td class="fw-semibold"><?= htmlspecialchars($p['nama']) ?></td></tr>
            <tr><th>Jenis Kelamin</th><td><?= $p['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan' ?></td></tr>
            <tr><th>Tempat / Tgl Lahir</th><td><?= htmlspecialchars($p['tempat_lahir'] ?: '-') ?>, <?= formatTanggal($p['tanggal_lahir']) ?></td></tr>
            <tr><th>Umur</th><td><?= htmlspecialchars((string)($p['umur'] ?? '-')) ?> tahun</td></tr>
            <tr><th>Status dalam KK</th><td><?= htmlspecialchars($p['status_dalam_keluarga']) ?></td></tr>
            <tr><th>Pendidikan</th><td><?= htmlspecialchars($p['pendidikan'] ?: '-') ?></td></tr>
            <tr><th>Pekerjaan</th><td><?= htmlspecialchars($p['pekerjaan'] ?: '-') ?></td></tr>
            <tr><th>No. HP</th><td><?= htmlspecialchars($p['no_hp'] ?: '-') ?></td></tr>
          </table>
        </div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card">
        <div class="card-header"><h5 class="mb-0">Alamat & Orang Tua</h5></div>
        <div class="card-body">
          <table class="table table-sm table-borderless mb-0">
            <tr><th width="40%">Alamat</th><td><?= htmlspecialchars($p['alamat'] ?: '-') ?></td></tr>
            <tr><th>Dusun</th><td><?= htmlspecialchars($p['dusun'] ?: '-') ?></td></tr>
            <tr><th>RT / RW</th><td><?= htmlspecialchars(($p['rt'] ?: '-') . ' / ' . ($p['rw'] ?: '-')) ?></td></tr>
            <tr><th>Nama Ayah</th><td><?= htmlspecialchars($p['nama_ayah'] ?: '-') ?></td></tr>
            <tr><th>NIK Ayah</th><td><?= htmlspecialchars($p['nik_ayah'] ?: '-') ?></td></tr>
            <tr><th>Nama Ibu</th><td><?= htmlspecialchars($p['nama_ibu'] ?: '-') ?></td></tr>
            <tr><th>NIK Ibu</th><td><?= htmlspecialchars($p['nik_ibu'] ?: '-') ?></td></tr>
            <tr><th>ID OpenSID</th><td><code><?= (int)$p['id_penduduk'] ?></code></td></tr>
          </table>
        </div>
      </div>
    </div>
  </div>

  <?php if (!empty($anggota)): ?>
  <div class="card mt-3">
    <div class="card-header"><h5 class="mb-0"><i class="fas fa-home me-1"></i>Anggota Keluarga (No. KK: <?= htmlspecialchars($p['no_kk']) ?>)</h5></div>
    <div class="card-body table-responsive">
      <table class="table table-sm table-hover">
        <thead><tr><th>NIK</th><th>Nama</th><th>L/P</th><th>Umur</th><th>Hubungan</th></tr></thead>
        <tbody>
          <?php foreach ($anggota as $a): ?>
          <tr>
            <td><code><?= htmlspecialchars($a['nik']) ?></code></td>
            <td><?= htmlspecialchars($a['nama']) ?></td>
            <td><?= $a['jenis_kelamin'] ?></td>
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
