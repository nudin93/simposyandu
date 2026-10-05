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

$page_title='Kunjungan Rumah'; require_once __DIR__.'/../../includes/header.php';
$data=[]; try{$data=fetchAll("SELECT kr.*, k.nama as nama_kader FROM kunjungan_rumah kr LEFT JOIN kader k ON k.id=kr.kader_id ORDER BY kr.tanggal_kunjungan DESC");}catch(Throwable $e){}
$canDel = function_exists('canInput') ? canInput() : true;
?>
<section class="content-header"><div class="container-fluid"><div class="row mb-2">
<div class="col-sm-6"><h1><i class="fas fa-house-user me-2 text-danger"></i>Kunjungan Rumah Kader</h1></div>
<div class="col-sm-6 text-end"><?php if($canDel):?><a href="tambah.php" class="btn btn-danger"><i class="fas fa-plus"></i> Catat Kunjungan</a><?php endif;?></div>
</div></div></section>
<section class="content"><div class="container-fluid">
<div class="alert alert-light border"><strong>Prioritas:</strong> Balita Stunting · Ibu Hamil Risiko Tinggi · Lansia Sakit · Warga Tidak Hadir Posyandu</div>
<div class="card"><div class="card-body table-responsive">
<table class="table table-hover datatable" style="width:100%">
<thead><tr><th>No</th><th>Tanggal</th><th>Keluarga</th><th>Prioritas</th><th>Masalah</th><th>Kader</th><th>Status</th><th>Aksi</th></tr></thead>
<tbody>
<?php foreach($data as $i=>$r):?>
<tr>
<td><?=$i+1?></td>
<td><?=formatTanggal($r['tanggal_kunjungan'])?></td>
<td><?=htmlspecialchars($r['nama_keluarga'])?><br><small class="text-muted"><?=htmlspecialchars($r['dusun']??'')?> RT<?=$r['rt']??''?></small></td>
<td><span class="badge bg-danger"><?=htmlspecialchars($r['prioritas'])?></span></td>
<td><?=htmlspecialchars(substr($r['masalah_ditemukan']??'',0,60))?></td>
<td><?=htmlspecialchars($r['nama_kader']??'-')?></td>
<td><?=$r['status_tindak_lanjut']?></td>
<td class="text-nowrap">
<?php if($canDel):?>
<button type="button" class="btn btn-sm btn-danger" onclick="confirmDelete('<?= APP_URL ?>/ajax/delete.php?type=kunjungan_rumah&id=<?= (int)$r['id'] ?>','Kunjungan <?= htmlspecialchars($r['nama_keluarga'], ENT_QUOTES) ?>')" title="Hapus"><i class="fas fa-trash"></i></button>
<?php endif;?>
</td>
</tr>
<?php endforeach;?>
</tbody></table>
</div></div></div></section>
<?php include __DIR__.'/../../includes/footer.php'; ?>
