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

$page_title = 'Tambah Bayi';
require_once __DIR__ . '/../../includes/header.php';
if (function_exists('canInput') && !canInput()) { echo '<div class="alert alert-danger m-3">Akses ditolak.</div>'; include __DIR__.'/../../includes/footer.php'; exit; }
?>
<section class="content-header"><div class="container-fluid"><div class="row mb-2">
  <div class="col-sm-6"><h1><i class="fas fa-baby me-2 text-info"></i>Tambah Data Bayi</h1></div>
  <div class="col-sm-6"><ol class="breadcrumb float-sm-end"><li class="breadcrumb-item"><a href="index.php">Bayi</a></li><li class="breadcrumb-item active">Tambah</li></ol></div>
</div></div></section>
<section class="content"><div class="container-fluid">
<div class="card">
  <div class="card-header bg-info text-white"><h5 class="mb-0">Form Data Bayi (cari dari OpenSID)</h5></div>
  <div class="card-body">
    <div class="mb-3">
      <label class="form-label fw-semibold">Cari Penduduk (NIK / Nama / No KK)</label>
      <input type="text" id="cari_penduduk" class="form-control form-control-lg" placeholder="Ketik minimal 2 karakter..." autocomplete="off">
      <div id="hasil_cari" class="list-group mt-1 shadow-sm" style="max-height:240px;overflow:auto;display:none;position:absolute;z-index:50;width:calc(100% - 2rem);"></div>
    </div>
    <form id="formBayi" method="post" action="<?= APP_URL ?>/ajax/save_bayi.php">

<input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
      <input type="hidden" name="id_penduduk_opensid" id="id_penduduk_opensid">
      <input type="hidden" name="status_integrasi" id="status_integrasi" value="belum">
      <div class="row g-3">
        <div class="col-md-4"><label class="form-label">NIK Bayi</label><input type="text" name="nik_bayi" id="nik_bayi" class="form-control" maxlength="16"></div>
        <div class="col-md-4"><label class="form-label">NIK Ibu</label><input type="text" name="nik_ibu" id="nik_ibu" class="form-control" maxlength="16"></div>
        <div class="col-md-4"><label class="form-label">No. KK</label><input type="text" name="no_kk" id="no_kk" class="form-control" maxlength="16"></div>
        <div class="col-md-6"><label class="form-label">Nama Lengkap <span class="text-danger">*</span></label><input type="text" name="nama_lengkap" id="nama_lengkap" class="form-control" required></div>
        <div class="col-md-3"><label class="form-label">Jenis Kelamin <span class="text-danger">*</span></label>
          <select name="jenis_kelamin" id="jenis_kelamin" class="form-select" required><option value="L">Laki-laki</option><option value="P">Perempuan</option></select></div>
        <div class="col-md-3"><label class="form-label">Tanggal Lahir <span class="text-danger">*</span></label><input type="date" name="tanggal_lahir" id="tanggal_lahir" class="form-control" required></div>
        <div class="col-md-3"><label class="form-label">Berat Lahir (kg)</label><input type="number" step="0.01" name="berat_lahir" class="form-control"></div>
        <div class="col-md-3"><label class="form-label">Panjang Lahir (cm)</label><input type="number" step="0.1" name="panjang_lahir" class="form-control"></div>
        <div class="col-md-3"><label class="form-label">Lingkar Kepala Lahir</label><input type="number" step="0.1" name="lingkar_kepala_lahir" class="form-control"></div>
        <div class="col-md-3"><label class="form-label">ASI Eksklusif</label>
          <select name="asi_eksklusif" class="form-select"><option value="Ya">Ya</option><option value="Tidak">Tidak</option><option value="Sebagian">Sebagian</option></select></div>
        <div class="col-md-4"><label class="form-label">Nama Ibu</label><input type="text" name="nama_ibu" id="nama_ibu" class="form-control"></div>
        <div class="col-md-4"><label class="form-label">Nama Ayah</label><input type="text" name="nama_ayah" id="nama_ayah" class="form-control"></div>
        <div class="col-md-4"><label class="form-label">Dusun</label><input type="text" name="dusun" id="dusun" class="form-control"></div>
        <div class="col-md-2"><label class="form-label">RT</label><input type="text" name="rt" id="rt" class="form-control"></div>
        <div class="col-md-2"><label class="form-label">RW</label><input type="text" name="rw" id="rw" class="form-control"></div>
        <div class="col-md-8"><label class="form-label">Alamat</label><input type="text" name="alamat_lengkap" id="alamat_lengkap" class="form-control"></div>
        <div class="col-12"><label class="form-label">Catatan</label><textarea name="catatan" class="form-control" rows="2"></textarea></div>
      </div>
      <div class="mt-4 d-flex gap-2">
        <button type="submit" class="btn btn-info"><i class="fas fa-save me-1"></i>Simpan</button>
        <a href="index.php" class="btn btn-secondary">Batal</a>
      </div>
    </form>
  </div>
</div>
</div></section>
<script>
(function(){
  const input = document.getElementById('cari_penduduk');
  const hasil = document.getElementById('hasil_cari');
  let t;
  input.addEventListener('input', function(){
    clearTimeout(t);
    const q = this.value.trim();
    if (q.length < 2) { hasil.style.display='none'; return; }
    t = setTimeout(()=>{
      fetch('<?= APP_URL ?>/ajax/cari_penduduk.php?q='+encodeURIComponent(q))
        .then(r=>r.json()).then(res=>{
          if (!res.success || !res.data.length) { hasil.innerHTML='<div class="list-group-item text-muted">Tidak ditemukan</div>'; hasil.style.display='block'; return; }
          hasil.innerHTML = res.data.map(p=>`<a href="#" class="list-group-item list-group-item-action" data-p='${JSON.stringify(p).replace(/'/g,"&#39;")}'>
            <strong>${p.nama||''}</strong> <small class="text-muted">${p.nik||''} · ${p.no_kk||''}</small><br>
            <small>${p.dusun||''} RT ${p.rt||'-'}/${p.rw||'-'} · Umur ${p.umur||'-'} th · ${p.jenis_kelamin||''}</small>
          </a>`).join('');
          hasil.style.display='block';
          hasil.querySelectorAll('a').forEach(a=>a.addEventListener('click', function(e){
            e.preventDefault();
            const p = JSON.parse(this.getAttribute('data-p'));
            document.getElementById('id_penduduk_opensid').value = p.id_penduduk||'';
            document.getElementById('status_integrasi').value = p.id_penduduk ? 'terhubung' : 'manual';
            document.getElementById('nik_bayi').value = p.nik||'';
            document.getElementById('no_kk').value = p.no_kk||'';
            document.getElementById('nama_lengkap').value = p.nama||'';
            document.getElementById('jenis_kelamin').value = p.jenis_kelamin||'L';
            document.getElementById('tanggal_lahir').value = p.tanggal_lahir||'';
            document.getElementById('dusun').value = p.dusun||'';
            document.getElementById('rt').value = p.rt||'';
            document.getElementById('rw').value = p.rw||'';
            document.getElementById('alamat_lengkap').value = p.alamat||'';
            if (p.nama_ibu) document.getElementById('nama_ibu').value = p.nama_ibu;
            if (p.nik_ibu) document.getElementById('nik_ibu').value = p.nik_ibu;
            hasil.style.display='none'; input.value = p.nama||'';
          }));
        });
    }, 300);
  });
  document.addEventListener('click', e=>{ if (!hasil.contains(e.target) && e.target!==input) hasil.style.display='none'; });
  document.getElementById('formBayi').addEventListener('submit', function(e){
    e.preventDefault();
    ajaxSubmitForm(this, { redirect: 'index.php' });
  });
})();
</script>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
