<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<h1>Dashboard</h1>
<div class="row">
  <div class="col-md-3"><div class="small-box bg-info"><div class="inner"><h3><?= (int)($total_balita??0) ?></h3><p>Balita</p></div></div></div>
  <div class="col-md-3"><div class="small-box bg-success"><div class="inner"><h3><?= (int)($total_bayi??0) ?></h3><p>Bayi</p></div></div></div>
  <div class="col-md-3"><div class="small-box bg-warning"><div class="inner"><h3><?= (int)($total_bumil??0) ?></h3><p>Ibu Hamil</p></div></div></div>
  <div class="col-md-3"><div class="small-box bg-danger"><div class="inner"><h3><?= (int)($total_lansia??0) ?></h3><p>Lansia</p></div></div></div>
</div>
<?= $this->endSection() ?>
