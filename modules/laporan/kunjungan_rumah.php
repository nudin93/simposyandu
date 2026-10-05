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

$page_title = 'Laporan Kunjungan Rumah';
require_once __DIR__ . '/../../includes/header.php';
$pengaturan = getPengaturan();
$nama_desa = $pengaturan['nama_desa'] ?? 'Desa';
$tahun = (int)($_GET['tahun'] ?? date('Y'));
$bulan = (int)($_GET['bulan'] ?? date('n'));
$nama_bulan = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];
$awal = sprintf('%04d-%02d-01',$tahun,$bulan);
$akhir = date('Y-m-t', strtotime($awal));
$total=0; $list=[];
try { $r=fetchOne("SELECT COUNT(*) c FROM kunjungan_rumah WHERE tanggal BETWEEN '$awal' AND '$akhir'"); $total=(int)($r['c']??0); } catch(Throwable $e){}
try { $list=fetchAll("SELECT * FROM kunjungan_rumah WHERE tanggal BETWEEN '$awal' AND '$akhir' ORDER BY tanggal DESC LIMIT 200"); } catch(Throwable $e){}
?>
<section class="content-header"><div class="container-fluid"><div class="row mb-2">
<div class="col-sm-6"><h1><i class="fas fa-home me-2 text-success"></i>Laporan Kunjungan Rumah</h1></div>
<div class="col-sm-6"><ol class="breadcrumb float-sm-end"><li class="breadcrumb-item"><a href="index.php">Laporan</a></li><li class="breadcrumb-item active">Kunjungan Rumah</li></ol></div>
</div></div></section>
<section class="content"><div class="container-fluid">
<div class="card border-0 shadow-sm mb-3 no-print"><div class="card-body py-2">
<form method="get" class="row g-2 align-items-end">
<div class="col-auto"><select name="tahun" class="form-select form-select-sm"><?php for($y=date('Y');$y>=date('Y')-5;$y--):?><option value="<?=$y?>" <?=$y==$tahun?'selected':''?>><?=$y?></option><?php endfor;?></select></div>
<div class="col-auto"><select name="bulan" class="form-select form-select-sm"><?php foreach($nama_bulan as $k=>$v):?><option value="<?=$k?>" <?=$k==$bulan?'selected':''?>><?=$v?></option><?php endforeach;?></select></div>
<div class="col-auto"><button class="btn btn-sm btn-primary">Tampilkan</button></div>
<div class="col-auto ms-auto"><button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()"><i class="fas fa-print"></i> Cetak</button></div>
</form></div></div>
<div class="card border-0 shadow-sm"><div class="card-body">
<div class="text-center mb-3"><h5 class="fw-bold mb-0">LAPORAN KUNJUNGAN RUMAH KADER</h5>
<p class="small mb-0"><?=htmlspecialchars($nama_desa)?> · <?=$nama_bulan[$bulan]?> <?=$tahun?></p>
<p class="mb-0">Total: <strong><?=number_format($total)?></strong></p></div>
<div class="table-responsive"><table class="table table-bordered table-sm" style="font-size:0.85rem">
<thead class="table-primary"><tr><th>No</th><th>Tanggal</th><th>Sasaran / Nama</th><th>Keterangan</th></tr></thead>
<tbody>
<?php if(empty($list)):?><tr><td colspan="4" class="text-center text-muted">Tidak ada data bulan ini</td></tr>
<?php else: foreach($list as $i=>$row):?>
<tr><td><?=$i+1?></td><td><?=htmlspecialchars($row['tanggal']??'-')?></td>
<td><?=htmlspecialchars($row['nama']??$row['sasaran']??$row['nama_keluarga']??'-')?></td>
<td><?=htmlspecialchars($row['keterangan']??$row['catatan']??$row['tujuan']??'-')?></td></tr>
<?php endforeach; endif;?>
</tbody></table></div>
</div></div>
<div class="mt-2 no-print"><a href="index.php" class="btn btn-secondary btn-sm">Kembali</a></div>
</div></section>
<style>@media print{.no-print,.main-sidebar,.main-header,.content-header,.btn{display:none!important}body{background:#fff}}</style>
<?php include __DIR__.'/../../includes/footer.php'; ?>
