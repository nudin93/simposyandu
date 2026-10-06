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

$page_title = 'Tambah Anak TK';
require_once __DIR__ . '/../../includes/header.php';
?>
<section class="content-header"><div class="container-fluid"><h1>Tambah Anak TK</h1><p class="text-muted mb-0">Sasaran usia 3-7 tahun (umur tidak ditampilkan). Cari nama anak, lalu guru wajib mengisi <strong>Kelas</strong>.</p></div></section>
<section class="content"><div class="container-fluid"><div class="card"><div class="card-body">
<div class="mb-3"><label class="form-label fw-semibold">Cari dari OpenSID / Penduduk (NIK/Nama/KK)</label>
<input type="text" id="cari" class="form-control" placeholder="Ketik nama anak atau NIK..."><div id="hasil" class="list-group mt-1" style="display:none;max-height:200px;overflow:auto"></div></div>
<form id="f" method="post" action="<?= APP_URL ?>/ajax/save_anak_tk.php">
<input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
<input type="hidden" name="id_penduduk_opensid" id="id_os"><input type="hidden" name="status_integrasi" id="st_int" value="belum">
<input type="hidden" name="kategori" value="Anak TK">
<div class="row g-3">
<div class="col-md-4"><label>NIK</label><input name="nik" id="nik" class="form-control" maxlength="16"></div>
<div class="col-md-4"><label>No KK</label><input name="no_kk" id="no_kk" class="form-control" maxlength="16"></div>
<div class="col-md-4"><label>Kelas *</label><select name="kelas" class="form-select" required><option value="">- Pilih Kelas -</option><option>TK A</option><option>TK B</option></select></div>
<div class="col-md-6"><label>Nama Lengkap *</label><input name="nama_lengkap" id="nama" class="form-control" required></div>
<div class="col-md-3"><label>Jenis Kelamin *</label><select name="jenis_kelamin" id="jk" class="form-select"><option value="L">Laki-laki</option><option value="P">Perempuan</option></select></div>
<div class="col-md-3"><label>Tgl Lahir * (3-7 thn)</label><input type="date" name="tanggal_lahir" id="tgl" class="form-control" required></div>
<div class="col-md-6"><label>Sekolah / TK</label><input name="sekolah" class="form-control" placeholder="Nama TK"></div>
<div class="col-md-3"><label>Dusun</label><input name="dusun" id="dusun" class="form-control"></div>
<div class="col-md-1"><label>RT</label><input name="rt" id="rt" class="form-control"></div>
<div class="col-md-1"><label>RW</label><input name="rw" id="rw" class="form-control"></div>
<div class="col-md-6"><label>Alamat Lengkap</label><input name="alamat_lengkap" id="alamat" class="form-control"></div>
</div>
<div class="mt-3"><button class="btn btn-warning">Simpan</button> <a href="index.php" class="btn btn-secondary">Batal</a></div>
</form>
</div></div></div></section>
<script>
(function(){
 var c=document.getElementById("cari"),h=document.getElementById("hasil"),t=null;
 c.oninput=function(){clearTimeout(t);var q=c.value.trim();if(q.length<2){h.style.display="none";return;}
 t=setTimeout(function(){fetch("<?= APP_URL ?>/ajax/cari_penduduk.php?q="+encodeURIComponent(q)).then(function(r){return r.json();}).then(function(res){
  if(!res.data||!res.data.length){h.innerHTML="<div class='list-group-item'>Tidak ada</div>";h.style.display="block";return;}
  window._lastCari=res.data;
  h.innerHTML=res.data.map(function(p,i){return "<a href='#' class='list-group-item list-group-item-action' data-i='"+i+"'>"+p.nama+" - "+(p.nik||"")+"</a>";}).join("");
  h.style.display="block";
  var links=h.querySelectorAll("a");
  for(var k=0;k<links.length;k++){links[k].onclick=function(e){e.preventDefault();var p=window._lastCari[+this.getAttribute("data-i")];
   document.getElementById("id_os").value=p.id_penduduk||"";document.getElementById("st_int").value=p.id_penduduk?"terhubung":"manual";
   document.getElementById("nik").value=p.nik||"";document.getElementById("no_kk").value=p.no_kk||"";document.getElementById("nama").value=p.nama||"";document.getElementById("jk").value=p.jenis_kelamin||"L";
   document.getElementById("tgl").value=p.tanggal_lahir||"";document.getElementById("dusun").value=p.dusun||"";document.getElementById("rt").value=p.rt||"";document.getElementById("rw").value=p.rw||"";document.getElementById("alamat").value=p.alamat||"";
   h.style.display="none";c.value=p.nama;};}
 });});};
 document.getElementById("f").onsubmit=function(e){e.preventDefault();ajaxSubmitForm(f,{redirect:"index.php"});};
})();
</script>
<?php include __DIR__.'/../../includes/footer.php'; ?>
