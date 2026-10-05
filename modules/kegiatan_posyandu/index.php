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

$page_title='Kegiatan Posyandu'; require_once __DIR__.'/../../includes/header.php';
$data=[]; try{$data=fetchAll("SELECT * FROM kegiatan_posyandu ORDER BY tanggal_kegiatan DESC");}catch(Throwable $e){}
$canDel = function_exists('canInput') ? canInput() : true;
?>
<section class="content-header"><div class="container-fluid"><div class="row mb-2">
<div class="col-sm-6"><h1><i class="fas fa-clipboard-list me-2 text-success"></i>Kegiatan Posyandu (5 Langkah)</h1></div>
<div class="col-sm-6 text-end"><?php if($canDel):?><a href="tambah.php" class="btn btn-success"><i class="fas fa-plus"></i> Catat Kegiatan</a><?php endif;?></div>
</div></div></section>
<section class="content"><div class="container-fluid"><div class="card"><div class="card-body table-responsive">
<table class="table table-hover datatable" style="width:100%">
<thead><tr><th>No</th><th>Tanggal</th><th>Posyandu</th><th>Kader</th><th>Sasaran</th><th>Hadir</th><th>Status</th><th>Aksi</th></tr></thead>
<tbody>
<?php foreach($data as $i=>$r):?>
<tr>
<td><?=$i+1?></td>
<td><?=formatTanggal($r['tanggal_kegiatan'])?></td>
<td><?=htmlspecialchars($r['nama_posyandu'])?></td>
<td><?=htmlspecialchars(substr($r['kader_bertugas']??'',0,40))?></td>
<td><?=$r['jumlah_sasaran']?></td>
<td><?=$r['jumlah_hadir']?></td>
<td><span class="badge bg-<?= $r['status']=='Selesai'?'success':($r['status']=='Berlangsung'?'warning':'secondary') ?>"><?=$r['status']?></span></td>
<td class="text-nowrap">
<a href="detail.php?id=<?=$r['id']?>" class="btn btn-sm btn-info" title="Detail"><i class="fas fa-eye"></i></a>
<?php if($canDel):?>
<button type="button" class="btn btn-sm btn-danger" onclick="confirmDelete('<?= APP_URL ?>/ajax/delete.php?type=kegiatan_posyandu&id=<?= (int)$r['id'] ?>','Kegiatan <?= htmlspecialchars($r['nama_posyandu'].' '.formatTanggal($r['tanggal_kegiatan']), ENT_QUOTES) ?>')" title="Hapus"><i class="fas fa-trash"></i></button>
<?php endif;?>
</td>
</tr>
<?php endforeach;?>
</tbody></table>
</div></div></div></section>
<?php include __DIR__.'/../../includes/footer.php'; ?>
