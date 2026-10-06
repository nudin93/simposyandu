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

$page_title = 'Edit Data Balita';
require_once __DIR__ . '/../../includes/header.php';
$id = (int)($_GET['id'] ?? 0);
$b = fetchOne("SELECT * FROM balita WHERE id=$id");
if (!$b) { echo '<script>window.location="index.php";</script>'; exit; }
$provinsi_list = ['Aceh','Sumatera Utara','Sumatera Barat','Riau','Jambi','Sumatera Selatan','Bengkulu','Lampung','Kepulauan Bangka Belitung','Kepulauan Riau','DKI Jakarta','Jawa Barat','Jawa Tengah','DI Yogyakarta','Jawa Timur','Banten','Bali','Nusa Tenggara Barat','Nusa Tenggara Timur','Kalimantan Barat','Kalimantan Tengah','Kalimantan Selatan','Kalimantan Timur','Kalimantan Utara','Sulawesi Utara','Sulawesi Tengah','Sulawesi Selatan','Sulawesi Tenggara','Gorontalo','Sulawesi Barat','Maluku','Maluku Utara','Papua Barat','Papua'];
?>
<section class="content-header"><div class="container-fluid"><div class="row mb-2">
  <div class="col-sm-6"><h1><i class="fas fa-edit me-2 text-warning"></i>Edit Data Balita</h1></div>
  <div class="col-sm-6"><ol class="breadcrumb float-sm-end"><li class="breadcrumb-item"><a href="../../dashboard.php">Dashboard</a></li><li class="breadcrumb-item"><a href="index.php">Balita</a></li><li class="breadcrumb-item active">Edit</li></ol></div>
</div></div></section>
<section class="content"><div class="container-fluid">
<form method="POST" action="../../ajax/update_balita.php" enctype="multipart/form-data" id="formEdit">
<input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
<input type="hidden" name="id" value="<?= $id ?>">
<div class="card">
  <div class="card-header bg-warning"><h5 class="mb-0"><i class="fas fa-baby me-2"></i>Edit: <?= htmlspecialchars($b['nama_lengkap']) ?></h5></div>
  <div class="card-body">
    <div class="row g-3">
      <div class="col-md-6"><label class="form-label fw-semibold">No. Peserta</label><input type="text" name="nomor_peserta" class="form-control" value="<?= $b['nomor_peserta'] ?>" readonly></div>
      <div class="col-md-6"><label class="form-label fw-semibold">NIK Anak</label><input type="text" name="nik_anak" class="form-control" value="<?= $b['nik_anak']??'' ?>" maxlength="16"></div>
      <div class="col-md-6"><label class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label><input type="text" name="nama_lengkap" class="form-control" value="<?= htmlspecialchars($b['nama_lengkap']) ?>" required></div>
      <div class="col-md-3"><label class="form-label fw-semibold">Jenis Kelamin</label><select name="jenis_kelamin" class="form-select select2"><option value="L" <?= $b['jenis_kelamin']=='L'?'selected':'' ?>>Laki-laki</option><option value="P" <?= $b['jenis_kelamin']=='P'?'selected':'' ?>>Perempuan</option></select></div>
      <div class="col-md-3"><label class="form-label fw-semibold">Tanggal Lahir</label><input type="date" name="tanggal_lahir" class="form-control" value="<?= $b['tanggal_lahir'] ?>"></div>
      <div class="col-md-4"><label class="form-label fw-semibold">Nama Ibu</label><input type="text" name="nama_ibu" class="form-control" value="<?= htmlspecialchars($b['nama_ibu']??'') ?>"></div>
      <div class="col-md-4"><label class="form-label fw-semibold">Nama Ayah</label><input type="text" name="nama_ayah" class="form-control" value="<?= htmlspecialchars($b['nama_ayah']??'') ?>"></div>
      <div class="col-md-4"><label class="form-label fw-semibold">No. HP</label><input type="text" name="no_hp" class="form-control" value="<?= $b['no_hp']??'' ?>"></div>
      <div class="col-md-4"><label class="form-label fw-semibold">Desa</label><input type="text" name="desa" class="form-control" value="<?= htmlspecialchars($b['desa']??'') ?>"></div>
      <div class="col-md-4"><label class="form-label fw-semibold">RT/RW</label><div class="input-group"><input type="text" name="rt" class="form-control" value="<?= $b['rt']??'' ?>" placeholder="RT"><input type="text" name="rw" class="form-control" value="<?= $b['rw']??'' ?>" placeholder="RW"></div></div>
      <div class="col-md-4"><label class="form-label fw-semibold">Status Gizi</label><select name="status_imunisasi" class="form-select select2"><option <?= $b['status_imunisasi']=='Lengkap'?'selected':'' ?>>Lengkap</option><option <?= $b['status_imunisasi']=='Belum Lengkap'?'selected':'' ?>>Belum Lengkap</option><option <?= $b['status_imunisasi']=='Dalam Proses'?'selected':'' ?>>Dalam Proses</option></select></div>
      <div class="col-md-6"><label class="form-label fw-semibold">Riwayat Alergi</label><textarea name="riwayat_alergi" class="form-control" rows="3"><?= htmlspecialchars($b['riwayat_alergi']??'') ?></textarea></div>
      <div class="col-md-6"><label class="form-label fw-semibold">Riwayat Penyakit</label><textarea name="riwayat_penyakit" class="form-control" rows="3"><?= htmlspecialchars($b['riwayat_penyakit']??'') ?></textarea></div>
      <div class="col-md-4"><label class="form-label fw-semibold">No. BPJS/KIS</label><input type="text" name="bpjs_kis" class="form-control" value="<?= $b['bpjs_kis']??'' ?>"></div>
      <div class="col-md-4"><label class="form-label fw-semibold">Status Aktif</label><div class="form-check form-switch mt-2"><input class="form-check-input" type="checkbox" name="status_aktif" value="1" <?= $b['status_aktif']?'checked':'' ?>><label class="form-check-label">Aktif</label></div></div>
      <div class="col-md-4"><label class="form-label fw-semibold">Update Foto Anak</label><input type="file" name="foto_anak" class="form-control" accept=".jpg,.jpeg,.png">
        <?php if ($b['foto_anak']): ?><small><img src="<?= APP_URL ?>/uploads/balita/<?= $b['foto_anak'] ?>" style="max-height:40px" class="mt-1 rounded"></small><?php endif; ?>
      </div>
    </div>
  </div>
  <div class="card-footer d-flex justify-content-between">
    <a href="detail.php?id=<?= $id ?>" class="btn btn-secondary btn-lg"><i class="fas fa-arrow-left me-2"></i>Kembali</a>
    <button type="submit" class="btn btn-warning btn-lg"><i class="fas fa-save me-2"></i>Simpan Perubahan</button>
  </div>
</div>
</form>
</div></section>
<script>
$('#formEdit').on('submit', function(e) {
  e.preventDefault(); showLoading('Menyimpan perubahan...');
  $.ajax({url:this.action,type:'POST',data:new FormData(this),processData:false,contentType:false,
    success:function(r){hideLoading();if(r.success){Swal.fire({icon:'success',title:'Berhasil!',text:r.message,timer:2000,showConfirmButton:false}).then(()=>window.location.href='detail.php?id=<?= $id ?>');}else{Swal.fire('Gagal!',r.message,'error');}},
    error:()=>{hideLoading();Swal.fire('Error!','Koneksi bermasalah','error');}
  });
});
</script>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
