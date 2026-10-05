<?php
$pengaturan = $pengaturan ?? [];
$error = session()->getFlashdata('error') ?? ($error ?? '');
$app_name = $pengaturan['nama_aplikasi'] ?? 'SIMPOSYANDU';
$nama_posyandu = $pengaturan['nama_posyandu'] ?? 'Sistem Informasi Posyandu';
$nama_desa = $pengaturan['nama_desa'] ?? '';
$kecamatan = $pengaturan['kecamatan'] ?? '';
$kabupaten = $pengaturan['kabupaten'] ?? '';
$logo_file = trim($pengaturan['logo'] ?? '');
$favicon_file = trim($pengaturan['favicon'] ?? '');
$logo_url = base_url('assets/img/logo.png');
$favicon_url = base_url('assets/img/favicon.ico');
$use_recaptcha = !empty($siteKey);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
  <title>Login | <?= htmlspecialchars($app_name) ?></title>
  <link rel="icon" href="<?= htmlspecialchars($favicon_url) ?>">
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
  <?php if ($use_recaptcha): ?>
  <script src="https://www.google.com/recaptcha/api.js" async defer></script>
  <?php endif; ?>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      min-height: 100vh;
      font-family: 'Roboto', system-ui, sans-serif;
      background: #0a0e27 url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100"><circle cx="10" cy="10" r="1" fill="%23ffffff22"/><circle cx="50" cy="30" r="0.8" fill="%23ffffff18"/><circle cx="80" cy="70" r="1.2" fill="%23ffffff15"/></svg>') repeat;
      background-color: #0b1220;
      background-image:
        radial-gradient(ellipse at 20% 20%, rgba(30,60,120,.45) 0%, transparent 50%),
        radial-gradient(ellipse at 80% 80%, rgba(20,40,90,.5) 0%, transparent 50%),
        linear-gradient(160deg, #0a0e27 0%, #111b33 40%, #0d1528 100%);
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 16px;
    }
    .login-wrap {
      width: 100%;
      max-width: 400px;
    }
    .login-card {
      background: #f7f8fa;
      border-radius: 16px;
      box-shadow: 0 12px 40px rgba(0,0,0,.45);
      padding: 28px 24px 20px;
      text-align: center;
    }
    .logo-box {
      margin: 0 auto 12px;
      width: 96px;
      height: 96px;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .logo-box img {
      max-width: 96px;
      max-height: 96px;
      object-fit: contain;
      border-radius: 8px;
    }
    .logo-fallback {
      width: 88px; height: 88px;
      border-radius: 50%;
      background: linear-gradient(135deg, #1a5fb4, #0d3b7a);
      color: #fff;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 36px;
      font-weight: 700;
    }
    .title-main {
      font-size: 1.45rem;
      font-weight: 700;
      letter-spacing: 1px;
      color: #1a4a8a;
      text-shadow: 0 1px 2px rgba(0,0,0,.08);
      margin-bottom: 4px;
      text-transform: uppercase;
    }
    .title-sub {
      font-size: .78rem;
      color: #5a6577;
      line-height: 1.45;
      margin-bottom: 20px;
      font-weight: 400;
    }
    .form-group { margin-bottom: 12px; text-align: left; }
    .form-control-login {
      width: 100%;
      padding: 12px 16px;
      border: 1px solid #d0d5dd;
      border-radius: 24px;
      font-size: .95rem;
      background: #fff;
      outline: none;
      transition: border-color .2s, box-shadow .2s;
    }
    .form-control-login:focus {
      border-color: #3c8dbc;
      box-shadow: 0 0 0 3px rgba(60,141,188,.15);
    }
    .form-control-login::placeholder { color: #9aa3b2; }
    .captcha-box {
      background: #fff;
      border: 1px solid #d0d5dd;
      border-radius: 8px;
      padding: 10px 12px;
      margin: 14px 0 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 74px;
    }
    .captcha-box .g-recaptcha { transform-origin: center; }
    .opts-row {
      display: flex;
      align-items: center;
      justify-content: space-between;
      font-size: .82rem;
      color: #5a6577;
      margin: 8px 0 16px;
      flex-wrap: wrap;
      gap: 6px;
    }
    .opts-row label {
      display: flex;
      align-items: center;
      gap: 6px;
      cursor: pointer;
      user-select: none;
    }
    .opts-row a {
      color: #3c8dbc;
      text-decoration: none;
    }
    .opts-row a:hover { text-decoration: underline; }
    .btn-masuk {
      width: 100%;
      padding: 12px 20px;
      border: none;
      border-radius: 28px;
      background: #28a745;
      color: #fff;
      font-size: 1.05rem;
      font-weight: 600;
      cursor: pointer;
      transition: background .2s, transform .15s, box-shadow .2s;
      box-shadow: 0 4px 12px rgba(40,167,69,.35);
    }
    .btn-masuk:hover {
      background: #218838;
      transform: translateY(-1px);
      box-shadow: 0 6px 16px rgba(40,167,69,.4);
    }
    .btn-masuk:disabled {
      opacity: .7;
      cursor: not-allowed;
      transform: none;
    }
    .alert-login {
      background: #fde8e8;
      color: #9b1c1c;
      border: 1px solid #f5c2c2;
      border-radius: 10px;
      padding: 10px 12px;
      font-size: .85rem;
      margin-bottom: 14px;
      text-align: left;
    }
    .footer-ver {
      margin-top: 16px;
      font-size: .75rem;
      color: #8a93a3;
    }
    @media (max-width: 420px) {
      .login-card { padding: 24px 18px 16px; }
      .title-main { font-size: 1.25rem; }
      .captcha-box .g-recaptcha { transform: scale(0.92); }
    }
  </style>
</head>
<body>
  <div class="login-wrap">
    <div class="login-card">
      <div class="logo-box">
        <?php if ($logo_url): ?>
          <img src="<?= htmlspecialchars($logo_url) ?>" alt="Logo" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
          <div class="logo-fallback" style="display:none">P</div>
        <?php else: ?>
          <div class="logo-fallback">P</div>
        <?php endif; ?>
      </div>

      <div class="title-main"><?= htmlspecialchars(strtoupper($nama_desa ?: $app_name)) ?></div>
      <div class="title-sub">
        <?php if ($alamat_lines): ?>
          <?= implode('<br>', array_map('htmlspecialchars', $alamat_lines)) ?>
        <?php else: ?>
          <?= htmlspecialchars($nama_posyandu) ?>
        <?php endif; ?>
      </div>

      <?php if (session()->getFlashdata('error') ?? ($error ?? '')): ?>
        <div class="alert-login"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <?= csrf_field() ?>
<form method="POST" id="loginForm" autocomplete="on">
        <div class="form-group">
          <input type="text" name="username" class="form-control-login" placeholder="Nama pengguna"
                 value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required autofocus autocomplete="username">
        </div>
        <div class="form-group">
          <input type="password" name="password" id="password" class="form-control-login" placeholder="Kata sandi"
                 required autocomplete="current-password">
        </div>

        <?php if ($use_recaptcha): ?>
        <div class="captcha-box">
          <div class="g-recaptcha" data-sitekey="<?= htmlspecialchars($recaptcha_site_key) ?>"></div>
        </div>
        <?php endif; ?>

        <div class="opts-row">
          <label>
            <input type="checkbox" id="showPass" onchange="togglePass(this)">
            Tampilkan kata sandi
          </label>
          <a href="javascript:void(0)" onclick="alert('Hubungi administrator untuk reset kata sandi.')">Lupa kata sandi?</a>
        </div>

        <button type="submit" class="btn-masuk" id="loginBtn">Masuk</button>
      </form>

      <div class="footer-ver"><?= htmlspecialchars($app_name) ?> · <?= date('Y') ?></div>
    </div>
  </div>

  <script>
  function togglePass(cb) {
    document.getElementById('password').type = cb.checked ? 'text' : 'password';
  }
  document.getElementById('loginForm').addEventListener('submit', function() {
    var btn = document.getElementById('loginBtn');
    btn.disabled = true;
    btn.textContent = 'Memproses...';
  });
  </script>
</body>
</html>
