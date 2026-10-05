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

$page_title = 'Detail Penduduk';
require_once __DIR__ . '/../../includes/header.php';
$id = (int)($_GET['id'] ?? 0);
$p = fetchOne("SELECT *, TIMESTAMPDIFF(YEAR, tanggal_lahir, CURDATE()) as umur FROM penduduk WHERE id=$id");
if (!$p) { header('Location: index.php'); exit; }
?>
<section class="content-header">
  <div class="container-fluid"><div class="row mb-2"><div class="col-sm-6"><h1>Detail Penduduk</h1></div></div></div>
</section>
<section class="content"><div class="container-fluid">
  <div class="card"><div class="card-body">
    <table class="table table-bordered">
      <tr><th width="200">NIK</th><td><code><?= htmlspecialchars($p['nik']) ?></code></td></tr>
      <tr><th>No. KK</th><td><?= htmlspecialchars($p['no_kk']??'-') ?></td></tr>
      <tr><th>Nama</th><td><?= htmlspecialchars($p['nama']) ?></td></tr>
      <tr><th>Jenis Kelamin</th><td><?= $p['jenis_kelamin']=='L'?'Laki-laki':'Perempuan' ?></td></tr>
      <tr><th>TTL</th><td><?= htmlspecialchars($p['tempat_lahir']??'-') ?>, <?= formatTanggal($p['tanggal_lahir']??'') ?></td></tr>
      <tr><th>Umur</th><td><?= $p['umur']??'-' ?> tahun</td></tr>
      <tr><th>Status dalam Keluarga</th><td><?= htmlspecialchars($p['status_dalam_keluarga']??'-') ?></td></tr>
      <tr><th>Dusun / RT/RW</th><td><?= htmlspecialchars($p['dusun']??'-') ?> / <?= ($p['rt']??'-').'/'.($p['rw']??'-') ?></td></tr>
      <tr><th>Alamat</th><td><?= htmlspecialchars($p['alamat']??'-') ?></td></tr>
      <tr><th>No. HP</th><td><?= htmlspecialchars($p['no_hp']??'-') ?></td></tr>
    </table>
    <a href="index.php" class="btn btn-secondary">Kembali</a>
    <a href="edit.php?id=<?= $id ?>" class="btn btn-warning">Edit</a>
  </div></div>
</div></section>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
