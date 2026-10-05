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

$page_title='Detail Kegiatan'; require_once __DIR__.'/../../includes/header.php';
$id=(int)($_GET['id']??0); $r=$id?fetchOne("SELECT * FROM kegiatan_posyandu WHERE id=$id"):null;
if(!$r){echo '<div class="alert alert-warning m-3">Tidak ditemukan</div>';include __DIR__.'/../../includes/footer.php';exit;}
?>
<section class="content"><div class="container-fluid"><div class="card"><div class="card-header">Kegiatan <?=formatTanggal($r['tanggal_kegiatan'])?> — <?=htmlspecialchars($r['nama_posyandu'])?></div>
<div class="card-body">
<table class="table table-sm"><tr><th width="200">Kader</th><td><?=htmlspecialchars($r['kader_bertugas']??'-')?></td></tr>
<tr><th>Sasaran / Hadir</th><td><?=$r['jumlah_sasaran']?> / <?=$r['jumlah_hadir']?></td></tr>
<tr><th>Status</th><td><?=$r['status']?></td></tr></table>
<h6 class="mt-3 text-success">5 Langkah</h6>
<ol>
<li><strong>Pendaftaran:</strong> <?=nl2br(htmlspecialchars($r['langkah1_pendaftaran']??'-'))?></li>
<li><strong>Pengukuran:</strong> <?=nl2br(htmlspecialchars($r['langkah2_pengukuran']??'-'))?></li>
<li><strong>Pencatatan:</strong> <?=nl2br(htmlspecialchars($r['langkah3_pencatatan']??'-'))?></li>
<li><strong>Pelayanan:</strong> <?=nl2br(htmlspecialchars($r['langkah4_pelayanan']??'-'))?></li>
<li><strong>Penyuluhan:</strong> <?=nl2br(htmlspecialchars($r['langkah5_penyuluhan']??'-'))?></li>
</ol>
<p><strong>Materi:</strong> <?=htmlspecialchars($r['materi_penyuluhan']??'-')?></p>
<a href="index.php" class="btn btn-secondary">Kembali</a>
</div></div></div></section>
<?php include __DIR__.'/../../includes/footer.php'; ?>
