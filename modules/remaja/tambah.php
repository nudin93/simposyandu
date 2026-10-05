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

$page_title = 'Tambah Remaja';
require_once __DIR__ . '/../../includes/header.php';
?>
<section class="content-header"><div class="container-fluid"><h1>Tambah Anak Sekolah / Remaja</h1></div></section>
<section class="content"><div class="container-fluid"><div class="card"><div class="card-body">
<div class="mb-3"><label class="form-label fw-semibold">Cari dari OpenSID (NIK/Nama/KK)</label>
<input type="text" id="cari" class="form-control" placeholder="Ketik nama atau NIK..."><div id="hasil" class="list-group mt-1" style="display:none;max-height:200px;overflow:auto"></div></div>
<form id="f" method="post" action="<?= APP_URL ?>/ajax/save_remaja.php">

<input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
<input type="hidden" name="id_penduduk_opensid" id="id_os"><input type="hidden" name="status_integrasi" id="st_int" value="belum">
<div class="row g-3">
<div class="col-md-4"><label>NIK</label><input name="nik" id="nik" class="form-control" maxlength="16"></div>
<div class="col-md-4"><label>No KK</label><input name="no_kk" id="no_kk" class="form-control" maxlength="16"></div>
<div class="col-md-4"><label>Kategori</label><select name="kategori" class="form-select"><option>Remaja</option><option>Anak Sekolah</option></select></div>
<div class="col-md-6"><label>Nama *</label><input name="nama_lengkap" id="nama" class="form-control" required></div>
<div class="col-md-3"><label>JK *</label><select name="jenis_kelamin" id="jk" class="form-select"><option value="L">L</option><option value="P">P</option></select></div>
<div class="col-md-3"><label>Tgl Lahir *</label><input type="date" name="tanggal_lahir" id="tgl" class="form-control" required></div>
<div class="col-md-4"><label>Sekolah</label><input name="sekolah" class="form-control"></div>
<div class="col-md-2"><label>Kelas</label><input name="kelas" class="form-control"></div>
<div class="col-md-3"><label>Dusun</label><input name="dusun" id="dusun" class="form-control"></div>
<div class="col-md-1"><label>RT</label><input name="rt" id="rt" class="form-control"></div>
<div class="col-md-1"><label>RW</label><input name="rw" id="rw" class="form-control"></div>
<div class="col-md-6"><label>Alamat</label><input name="alamat_lengkap" id="alamat" class="form-control"></div>
</div>
<div class="mt-3"><button class="btn btn-warning">Simpan</button> <a href="index.php" class="btn btn-secondary">Batal</a></div>
</form>
</div></div></div></section>
<script>
(function(){
 const c=document.getElementById('cari'),h=document.getElementById('hasil'); let t;
 c.oninput=()=>{clearTimeout(t);const q=c.value.trim();if(q.length<2){h.style.display='none';return;}
 t=setTimeout(()=>fetch('<?= APP_URL ?>/ajax/cari_penduduk.php?q='+encodeURIComponent(q)).then(r=>r.json()).then(res=>{
  if(!res.data?.length){h.innerHTML='<div class="list-group-item">Tidak ada</div>';h.style.display='block';return;}
  h.innerHTML=res.data.map(p=>`<a href="#" class="list-group-item list-group-item-action" data-p='${JSON.stringify(p).replace(/'/g,"&#39;")}'>${p.nama} · ${p.nik||''}</a>`).join('');
  h.style.display='block';
  h.querySelectorAll('a').forEach(a=>a.onclick=e=>{e.preventDefault();const p=JSON.parse(a.dataset.p);
   id_os.value=p.id_penduduk||'';st_int.value=p.id_penduduk?'terhubung':'manual';
   nik.value=p.nik||'';no_kk.value=p.no_kk||'';nama.value=p.nama||'';jk.value=p.jenis_kelamin||'L';
   tgl.value=p.tanggal_lahir||'';dusun.value=p.dusun||'';rt.value=p.rt||'';rw.value=p.rw||'';alamat.value=p.alamat||'';
   h.style.display='none';c.value=p.nama;});
 }));};
 document.getElementById('f').onsubmit=e=>{e.preventDefault();ajaxSubmitForm(f,{redirect:'index.php'});};
})();
</script>
<?php include __DIR__.'/../../includes/footer.php'; ?>
