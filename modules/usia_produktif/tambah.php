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

$page_title='Tambah Usia Produktif'; require_once __DIR__.'/../../includes/header.php';
?>
<section class="content"><div class="container-fluid"><div class="card"><div class="card-body">
<div class="mb-3"><label class="fw-semibold">Cari OpenSID</label><input id="cari" class="form-control" placeholder="NIK / Nama / KK"><div id="hasil" class="list-group mt-1" style="display:none;max-height:200px;overflow:auto"></div></div>
<form id="f" action="<?= APP_URL ?>/ajax/save_usia_produktif.php" method="post">

<input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
<input type="hidden" name="id_penduduk_opensid" id="id_os"><input type="hidden" name="status_integrasi" id="st" value="belum">
<div class="row g-3">
<div class="col-md-4"><label>NIK</label><input name="nik" id="nik" class="form-control"></div>
<div class="col-md-4"><label>No KK</label><input name="no_kk" id="no_kk" class="form-control"></div>
<div class="col-md-4"><label>JK</label><select name="jenis_kelamin" id="jk" class="form-select"><option value="L">L</option><option value="P">P</option></select></div>
<div class="col-md-6"><label>Nama *</label><input name="nama_lengkap" id="nama" class="form-control" required></div>
<div class="col-md-3"><label>Tgl Lahir *</label><input type="date" name="tanggal_lahir" id="tgl" class="form-control" required></div>
<div class="col-md-3"><label>Dusun</label><input name="dusun" id="dusun" class="form-control"></div>
<div class="col-md-2"><label>RT</label><input name="rt" id="rt" class="form-control"></div>
<div class="col-md-2"><label>RW</label><input name="rw" id="rw" class="form-control"></div>
<div class="col-md-8"><label>Alamat</label><input name="alamat_lengkap" id="alamat" class="form-control"></div>
</div>
<button class="btn btn-primary mt-3">Simpan</button> <a href="index.php" class="btn btn-secondary mt-3">Batal</a>
</form></div></div></div></section>
<script>
(function(){const c=cari,h=hasil;let t;c.oninput=()=>{clearTimeout(t);if(c.value.trim().length<2){h.style.display='none';return;}
t=setTimeout(()=>fetch('<?= APP_URL ?>/ajax/cari_penduduk.php?q='+encodeURIComponent(c.value.trim())).then(r=>r.json()).then(res=>{
if(!res.data?.length){h.innerHTML='<div class="list-group-item">Kosong</div>';h.style.display='block';return;}
h.innerHTML=res.data.map(p=>`<a href="#" class="list-group-item list-group-item-action" data-p='${JSON.stringify(p).replace(/'/g,"&#39;")}'>${p.nama} · ${p.nik||''}</a>`).join('');
h.style.display='block';h.querySelectorAll('a').forEach(a=>a.onclick=e=>{e.preventDefault();const p=JSON.parse(a.dataset.p);
id_os.value=p.id_penduduk||'';st.value=p.id_penduduk?'terhubung':'manual';nik.value=p.nik||'';no_kk.value=p.no_kk||'';
nama.value=p.nama||'';jk.value=p.jenis_kelamin||'L';tgl.value=p.tanggal_lahir||'';dusun.value=p.dusun||'';
rt.value=p.rt||'';rw.value=p.rw||'';alamat.value=p.alamat||'';h.style.display='none';c.value=p.nama;});
}));}; f.onsubmit=e=>{e.preventDefault();ajaxSubmitForm(f,{redirect:'index.php'});};
})();
</script>
<?php include __DIR__.'/../../includes/footer.php'; ?>
