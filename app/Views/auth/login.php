<!DOCTYPE html>
<html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Login | SIMPOSYANDU</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"></head>
<body class="bg-light d-flex align-items-center justify-content-center" style="min-height:100vh">
<div class="card shadow" style="width:380px"><div class="card-body">
<h4 class="text-center">SIMPOSYANDU</h4>
<?php if (session()->getFlashdata('error')): ?><div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div><?php endif; ?>
<form method="post" action="/login"><?= csrf_field() ?>
<div class="mb-2"><input class="form-control" name="username" placeholder="Nama pengguna" required></div>
<div class="mb-3"><input class="form-control" type="password" name="password" placeholder="Kata sandi" required></div>
<button class="btn btn-primary w-100">Masuk</button></form>
</div></div></body></html>
