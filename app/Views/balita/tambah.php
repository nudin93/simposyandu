<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<h1>Tambah Balita</h1>
<form id="f"><div class="mb-2"><input class="form-control" name="nama_lengkap" placeholder="Nama lengkap" required></div>
<div class="mb-2"><input class="form-control" name="nik" placeholder="NIK" maxlength="16"></div>
<div class="mb-2"><select class="form-control" name="jenis_kelamin"><option value="L">Laki-laki</option><option value="P">Perempuan</option></select></div>
<div class="mb-2"><input class="form-control" type="date" name="tanggal_lahir" required></div>
<button class="btn btn-primary">Simpan</button></form>
<script>
document.getElementById('f').addEventListener('submit', async (e) => {
  e.preventDefault();
  const r = await fetch('/balita/simpan', {method:'POST', body:new FormData(e.target)});
  alert(await r.text());
});
</script>
<?= $this->endSection() ?>
