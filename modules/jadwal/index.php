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

$page_title = 'Jadwal Posyandu';
require_once __DIR__ . '/../../includes/header.php';
$data = fetchAll("SELECT * FROM jadwal ORDER BY tanggal_kegiatan DESC");
?>
<section class="content-header"><div class="container-fluid"><div class="row mb-2">
  <div class="col-sm-6"><h1><i class="fas fa-calendar-alt me-2 text-primary"></i>Jadwal Posyandu</h1></div>
  <div class="col-sm-6"><ol class="breadcrumb float-sm-end"><li class="breadcrumb-item"><a href="../../dashboard.php">Dashboard</a></li><li class="breadcrumb-item active">Jadwal</li></ol></div>
</div></div></section>
<section class="content"><div class="container-fluid">
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="mb-0"><i class="fas fa-list me-2"></i>Daftar Jadwal</h5>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambah"><i class="fas fa-plus me-1"></i>Tambah Jadwal</button>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-hover datatable">
        <thead><tr><th>No</th><th>Kegiatan</th><th>Tanggal</th><th>Jam</th><th>Lokasi</th><th>PJ</th><th>Jenis</th><th>Status</th><th>Aksi</th></tr></thead>
        <tbody>
        <?php foreach ($data as $i => $j): ?>
        <tr>
          <td><?= $i+1 ?></td>
          <td><div class="fw-semibold"><?= htmlspecialchars($j['nama_kegiatan']) ?></div></td>
          <td><?= formatTanggal($j['tanggal_kegiatan']) ?></td>
          <td><?= date('H:i', strtotime($j['jam'])) ?></td>
          <td><?= htmlspecialchars($j['lokasi']??'-') ?></td>
          <td><?= htmlspecialchars($j['penanggung_jawab']??'-') ?></td>
          <td><span class="badge bg-info"><?= $j['jenis_kegiatan'] ?></span></td>
          <td>
            <?php $sc = ['Terjadwal'=>'secondary','Berlangsung'=>'success','Selesai'=>'primary','Dibatalkan'=>'danger']; ?>
            <span class="badge bg-<?= $sc[$j['status']] ?>"><?= $j['status'] ?></span>
          </td>
          <td>
            <button class="btn btn-sm btn-warning" onclick="editJadwal(<?= htmlspecialchars(json_encode($j)) ?>)"><i class="fas fa-edit"></i></button>
            <button class="btn btn-sm btn-danger" onclick="confirmDelete('<?= APP_URL ?>/ajax/delete.php?type=jadwal&id=<?= $j['id'] ?>','<?= htmlspecialchars($j['nama_kegiatan']) ?>')"><i class="fas fa-trash"></i></button>
          </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
</div></section>

<!-- Modal Tambah/Edit -->
<div class="modal fade" id="modalTambah" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title"><i class="fas fa-calendar-plus me-2"></i><span id="modalTitle">Tambah Jadwal</span></h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form id="formJadwal" method="POST" action="../../ajax/save_jadwal.php">
        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
        <input type="hidden" name="id" id="jadwal_id">
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-12"><label class="form-label fw-semibold">Nama Kegiatan <span class="text-danger">*</span></label><input type="text" name="nama_kegiatan" id="nama_kegiatan" class="form-control" required></div>
            <div class="col-md-4"><label class="form-label fw-semibold">Tanggal <span class="text-danger">*</span></label><input type="date" name="tanggal_kegiatan" id="tanggal_kegiatan" class="form-control" required></div>
            <div class="col-md-4"><label class="form-label fw-semibold">Jam <span class="text-danger">*</span></label><input type="time" name="jam" id="jam" class="form-control" required></div>
            <div class="col-md-4"><label class="form-label fw-semibold">Jenis Kegiatan</label>
              <select name="jenis_kegiatan" id="jenis_kegiatan" class="form-select select2">
                <?php foreach(['Penimbangan Balita','Pemeriksaan Ibu Hamil','Pemeriksaan Lansia','Imunisasi','Penyuluhan','Lainnya'] as $j): ?><option><?= $j ?></option><?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-8"><label class="form-label fw-semibold">Lokasi</label><input type="text" name="lokasi" id="lokasi" class="form-control" placeholder="Nama tempat kegiatan"></div>
            <div class="col-md-4"><label class="form-label fw-semibold">Status</label><select name="status" id="status" class="form-select select2"><option>Terjadwal</option><option>Berlangsung</option><option>Selesai</option><option>Dibatalkan</option></select></div>
            <div class="col-md-6"><label class="form-label fw-semibold">Penanggung Jawab</label><input type="text" name="penanggung_jawab" id="penanggung_jawab" class="form-control"></div>
            <div class="col-12"><label class="form-label fw-semibold">Keterangan</label><textarea name="keterangan" id="keterangan" class="form-control" rows="3"></textarea></div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save me-2"></i>Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>
<script>
function editJadwal(data) {
  $('#modalTitle').text('Edit Jadwal');
  $('#jadwal_id').val(data.id); $('#nama_kegiatan').val(data.nama_kegiatan); $('#tanggal_kegiatan').val(data.tanggal_kegiatan);
  $('#jam').val(data.jam.substring(0,5)); $('#lokasi').val(data.lokasi); $('#penanggung_jawab').val(data.penanggung_jawab);
  $('#keterangan').val(data.keterangan); $('#jenis_kegiatan').val(data.jenis_kegiatan).trigger('change');
  $('#status').val(data.status).trigger('change');
  new bootstrap.Modal(document.getElementById('modalTambah')).show();
}
$('#formJadwal').on('submit', function(e) {
  e.preventDefault(); showLoading('Menyimpan jadwal...');
  $.ajax({url:this.action,type:'POST',data:new FormData(this),processData:false,contentType:false,
    success:function(r){hideLoading();if(r.success){showToast(r.message,'success');bootstrap.Modal.getInstance(document.getElementById('modalTambah')).hide();setTimeout(()=>location.reload(),800);}else{Swal.fire('Gagal!',r.message,'error');}},
    error:()=>{hideLoading();Swal.fire('Error!','Koneksi bermasalah','error');}
  });
});
</script>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
