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

$page_title = 'Detail Bayi';
require_once __DIR__ . '/../../includes/header.php';
$id = (int)($_GET['id'] ?? 0);
$r = $id ? fetchOne("SELECT *, TIMESTAMPDIFF(MONTH, tanggal_lahir, CURDATE()) as umur_bulan FROM bayi WHERE id=$id") : null;
if (!$r) { echo '<div class="alert alert-warning m-3">Data tidak ditemukan.</div>'; include __DIR__.'/../../includes/footer.php'; exit; }
$riwayat = [];
try { $riwayat = fetchAll("SELECT * FROM pemeriksaan_bayi WHERE bayi_id=$id ORDER BY tanggal_pemeriksaan DESC"); } catch(Throwable $e){}
?>
<section class="content-header"><div class="container-fluid"><div class="row mb-2">
  <div class="col-sm-6"><h1><i class="fas fa-baby me-2"></i><?= htmlspecialchars($r['nama_lengkap']) ?></h1></div>
  <div class="col-sm-6"><ol class="breadcrumb float-sm-end"><li class="breadcrumb-item"><a href="index.php">Bayi</a></li><li class="breadcrumb-item active">Detail</li></ol></div>
</div></div></section>
<section class="content"><div class="container-fluid">
<div class="row">
  <div class="col-md-4">
    <div class="card"><div class="card-header bg-info text-white">Identitas</div><div class="card-body">
      <table class="table table-sm"><tr><th>No. Peserta</th><td><?= htmlspecialchars($r['nomor_peserta']) ?></td></tr>
      <tr><th>NIK</th><td><?= htmlspecialchars($r['nik_bayi']??'-') ?></td></tr>
      <tr><th>No KK</th><td><?= htmlspecialchars($r['no_kk']??'-') ?></td></tr>
      <tr><th>JK</th><td><?= $r['jenis_kelamin'] ?></td></tr>
      <tr><th>Tgl Lahir</th><td><?= formatTanggal($r['tanggal_lahir']) ?> (<?= (int)$r['umur_bulan'] ?> bln)</td></tr>
      <tr><th>BB/PB Lahir</th><td><?= $r['berat_lahir']??'-' ?> kg / <?= $r['panjang_lahir']??'-' ?> cm</td></tr>
      <tr><th>ASI</th><td><?= htmlspecialchars($r['asi_eksklusif']??'-') ?></td></tr>
      <tr><th>Ibu</th><td><?= htmlspecialchars($r['nama_ibu']??'-') ?></td></tr>
      <tr><th>Alamat</th><td><?= htmlspecialchars($r['alamat_lengkap']??'-') ?></td></tr>
      </table>
      <a href="../pemeriksaan_bayi/tambah.php?bayi_id=<?= $id ?>" class="btn btn-success btn-sm"><i class="fas fa-stethoscope"></i> Pemeriksaan</a>
    </div></div>
  </div>
  <div class="col-md-8">
    <div class="card"><div class="card-header">Riwayat Pemeriksaan</div><div class="card-body table-responsive">
      <table class="table table-sm table-hover"><thead><tr><th>Tanggal</th><th>Umur</th><th>BB</th><th>PB</th><th>LK</th><th>Gizi</th><th>Stunting</th></tr></thead><tbody>
      <?php foreach ($riwayat as $p): ?>
      <tr><td><?= formatTanggal($p['tanggal_pemeriksaan']) ?></td><td><?= $p['umur_bulan'] ?> bln</td>
      <td><?= $p['berat_badan'] ?></td><td><?= $p['panjang_badan'] ?></td><td><?= $p['lingkar_kepala'] ?></td>
      <td><?= $p['status_gizi'] ?></td><td><?= $p['risiko_stunting'] ?></td></tr>
      <?php endforeach; ?>
      <?php if (!$riwayat): ?><tr><td colspan="7" class="text-muted text-center">Belum ada pemeriksaan</td></tr><?php endif; ?>
      </tbody></table>
    </div></div>
  </div>
</div>
</div></section>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
