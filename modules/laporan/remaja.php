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

$page_title = 'Laporan Remaja';
require_once __DIR__ . '/../../includes/header.php';
$pengaturan = getPengaturan();
$nama_desa = $pengaturan['nama_desa'] ?? 'Desa';
$tahun = (int)($_GET['tahun'] ?? date('Y'));
$bulan = (int)($_GET['bulan'] ?? date('n'));
$nama_bulan = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];
$awal = sprintf('%04d-%02d-01',$tahun,$bulan);
$akhir = date('Y-m-t', strtotime($awal));
$total=0;$l=0;$p=0;$periksa=0;
try {
  $r=fetchOne("SELECT COUNT(*) c, SUM(CASE WHEN jenis_kelamin IN ('L','Laki-laki','1') THEN 1 ELSE 0 END) l, SUM(CASE WHEN jenis_kelamin IN ('P','Perempuan','2') THEN 1 ELSE 0 END) p FROM remaja WHERE status_aktif=1 OR status_aktif IS NULL");
  $total=(int)($r['c']??0); $l=(int)($r['l']??0); $p=(int)($r['p']??0);
} catch(Throwable $e){}
try { $r=fetchOne("SELECT COUNT(*) c FROM pemeriksaan_remaja WHERE tanggal_pemeriksaan BETWEEN '$awal' AND '$akhir'"); $periksa=(int)($r['c']??0); } catch(Throwable $e){}
$list=[];
try { $list=fetchAll("SELECT * FROM remaja WHERE status_aktif=1 OR status_aktif IS NULL ORDER BY nama LIMIT 200"); } catch(Throwable $e){}
?>
<section class="content-header"><div class="container-fluid"><div class="row mb-2">
<div class="col-sm-6"><h1><i class="fas fa-user-graduate me-2 text-success"></i>Laporan Remaja</h1></div>
<div class="col-sm-6"><ol class="breadcrumb float-sm-end"><li class="breadcrumb-item"><a href="<?=APP_URL?>/dashboard.php">Beranda</a></li><li class="breadcrumb-item"><a href="index.php">Laporan</a></li><li class="breadcrumb-item active">Remaja</li></ol></div>
</div></div></section>
<section class="content"><div class="container-fluid">
<div class="card border-0 shadow-sm mb-3 no-print"><div class="card-body py-2">
<form method="get" class="row g-2 align-items-end">
<div class="col-auto"><select name="tahun" class="form-select form-select-sm"><?php for($y=date('Y');$y>=date('Y')-5;$y--):?><option value="<?=$y?>" <?=$y==$tahun?'selected':''?>><?=$y?></option><?php endfor;?></select></div>
<div class="col-auto"><select name="bulan" class="form-select form-select-sm"><?php foreach($nama_bulan as $k=>$v):?><option value="<?=$k?>" <?=$k==$bulan?'selected':''?>><?=$v?></option><?php endforeach;?></select></div>
<div class="col-auto"><button class="btn btn-sm btn-primary">Tampilkan</button></div>
<div class="col-auto ms-auto"><button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()"><i class="fas fa-print"></i> Cetak</button></div>
</form></div></div>
<div class="card border-0 shadow-sm" id="area-cetak"><div class="card-body">
<div class="text-center mb-3"><h5 class="fw-bold mb-0">LAPORAN DATA REMAJA POSYANDU</h5>
<p class="small mb-0"><?=htmlspecialchars($nama_desa)?> · <?=$nama_bulan[$bulan]?> <?=$tahun?></p></div>
<div class="row text-center mb-3">
<div class="col-4"><div class="border rounded p-2"><div class="fs-4 fw-bold"><?=number_format($total)?></div><small>Total Remaja</small></div></div>
<div class="col-4"><div class="border rounded p-2"><div class="fs-4 fw-bold"><?=number_format($l)?> / <?=number_format($p)?></div><small>L / P</small></div></div>
<div class="col-4"><div class="border rounded p-2"><div class="fs-4 fw-bold"><?=number_format($periksa)?></div><small>Periksa Bulan Ini</small></div></div>
</div>
<div class="table-responsive"><table class="table table-bordered table-sm" style="font-size:0.85rem">
<thead class="table-primary"><tr><th>No</th><th>Nama</th><th>JK</th><th>Tgl Lahir</th><th>Alamat</th></tr></thead>
<tbody>
<?php if(empty($list)):?><tr><td colspan="5" class="text-center text-muted">Tidak ada data</td></tr>
<?php else: foreach($list as $i=>$row):?>
<tr><td><?=$i+1?></td><td><?=htmlspecialchars($row['nama']??$row['nama_lengkap']??'-')?></td>
<td><?=htmlspecialchars($row['jenis_kelamin']??'-')?></td>
<td><?=htmlspecialchars($row['tanggal_lahir']??'-')?></td>
<td><?=htmlspecialchars($row['alamat_lengkap']??$row['alamat']??'-')?></td></tr>
<?php endforeach; endif;?>
</tbody></table></div>
</div></div>
<div class="mt-2 no-print"><a href="index.php" class="btn btn-secondary btn-sm">Kembali</a></div>
</div></section>
<style>@media print{.no-print,.main-sidebar,.main-header,.content-header,.btn{display:none!important}body{background:#fff}}</style>
<?php include __DIR__.'/../../includes/footer.php'; ?>
