<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<h1>Detail Balita</h1>
<pre><?= esc(json_encode($row ?? [], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE)) ?></pre>
<?= $this->endSection() ?>
