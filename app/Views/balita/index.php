<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<h1>Data Balita (<?= (int)($total??0) ?>)</h1>
<a class="btn btn-primary mb-2" href="/balita/tambah">Tambah</a>
<table class="table table-bordered table-striped">
<thead><tr><th>Nama</th><th>NIK</th><th>L/P</th><th>Lahir</th><th>Umur</th><th>Status</th></tr></thead>
<tbody>
<?php foreach (($rows??[]) as $r): ?>
<tr><td><?= esc($r['nama']??'') ?></td><td><?= esc($r['nik']??'') ?></td><td><?= esc($r['jenis_kelamin']??'') ?></td><td><?= esc($r['tanggal_lahir']??'') ?></td><td><?= esc(formatUmurBalita($r['tanggal_lahir']??null, $r['umur_bulan']??null)) ?></td><td><?= !empty($r['terdaftar']) ? 'Terdaftar' : 'Belum' ?></td></tr>
<?php endforeach; ?>
</tbody></table>
<?= $this->endSection() ?>
