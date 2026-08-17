<?php
require_once __DIR__ . '/config/init.php';
if (isLoggedIn()) { header('Location: ' . APP_URL . '/dashboard.php'); exit; }
$pengaturan = getPengaturan();
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = escape($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if (!$username || !$password) {
        $error = 'Username dan password wajib diisi!';
    } else {
        $user = fetchOne("SELECT * FROM kader WHERE username='$username' AND status=1");
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['kader_id'] = $user['id'];
            $_SESSION['kader_nama'] = $user['nama'];
            $_SESSION['kader_username'] = $user['username'];
            $_SESSION['kader_role'] = $user['role'];
            $_SESSION['kader_foto'] = $user['foto'];
            header('Location: ' . APP_URL . '/dashboard.php');
            exit;
        } else { $error = 'Username atau password salah!'; }
    }
}
$app_name = $pengaturan['nama_aplikasi'] ?? 'SIMPOSYANDU';
$warna = $pengaturan['warna_tema'] ?? 'blue';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login | <?= $app_name ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/custom.css">
  <style>
    body { background: linear-gradient(135deg, #0d6efd 0%, #0056b3 50%, #003d82 100%); min-height: 100vh; display: flex; align-items: center; font-family: 'Inter', sans-serif; }
    .login-card { background: #fff; border-radius: 20px; box-shadow: 0 20px 60px rgba(0,0,0,.25); overflow: hidden; max-width: 440px; width: 100%; margin: auto; }
    .login-header { background: linear-gradient(135deg, #0d6efd, #0056b3); padding: 2.5rem 2rem; text-align: center; color: #fff; }
    .login-header img { width: 72px; height: 72px; border-radius: 50%; border: 3px solid rgba(255,255,255,.3); margin-bottom: 1rem; }
    .login-header h4 { font-weight: 800; font-size: 1.5rem; margin: 0; letter-spacing: -.5px; }
    .login-header p { opacity: .8; margin: 0; font-size: .9rem; }
    .login-body { padding: 2rem; }
    .form-floating label { color: #6c757d; }
    .btn-login { width: 100%; padding: 14px; font-size: 1rem; font-weight: 700; border-radius: 12px; background: linear-gradient(135deg, #0d6efd, #0056b3); border: none; position: relative; overflow: hidden; }
    .btn-login:hover { transform: translateY(-1px); box-shadow: 0 8px 20px rgba(13,110,253,.4); }
    .btn-login .spinner { display: none; }
    .loading .btn-login .spinner { display: inline-block; }
    .loading .btn-login .btn-text { display: none; }
    .password-toggle { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #6c757d; z-index: 10; background: none; border: none; }
    .alert-danger { border-radius: 10px; border-left: 4px solid #dc3545; }
    @media (max-width: 480px) { .login-card { border-radius: 0; min-height: 100vh; } body { padding: 0; } }
  </style>
</head>
<body>
<div class="container px-3">
  <div class="login-card">
    <div class="login-header">
      <img src="<?= APP_URL ?>/assets/img/logo.png" alt="Logo" onerror="this.src='data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCA4MCA4MCI+PGNpcmNsZSBjeD0iNDAiIGN5PSI0MCIgcj0iNDAiIGZpbGw9InJnYmEoMjU1LDI1NSwyNTUsMC4yKSIvPjx0ZXh0IHg9IjQwIiB5PSI1MCIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZm9udC1zaXplPSIzMCIgZmlsbD0id2hpdGUiPis8L3RleHQ+PC9zdmc+'">
      <h4><i class="fas fa-heartbeat me-2"></i><?= $app_name ?></h4>
      <p><?= htmlspecialchars($pengaturan['nama_posyandu'] ?? 'Sistem Informasi Posyandu') ?></p>
    </div>
    <div class="login-body">
      <?php if ($error): ?><div class="alert alert-danger d-flex align-items-center mb-3"><i class="fas fa-exclamation-circle me-2"></i><?= $error ?></div><?php endif; ?>
      <form method="POST" id="loginForm">
        <div class="mb-3">
          <label class="form-label fw-semibold">Username</label>
          <div class="input-group">
            <span class="input-group-text"><i class="fas fa-user"></i></span>
            <input type="text" name="username" class="form-control form-control-lg" placeholder="Masukkan username" value="<?= htmlspecialchars($_POST['username']??'') ?>" required autofocus autocomplete="username">
          </div>
        </div>
        <div class="mb-3">
          <label class="form-label fw-semibold">Password</label>
          <div class="input-group position-relative">
            <span class="input-group-text"><i class="fas fa-lock"></i></span>
            <input type="password" name="password" id="password" class="form-control form-control-lg" placeholder="Masukkan password" required autocomplete="current-password">
            <button type="button" class="password-toggle" onclick="togglePassword()"><i class="fas fa-eye" id="eyeIcon"></i></button>
          </div>
        </div>
        <div class="d-flex justify-content-between align-items-center mb-4">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" id="remember" name="remember">
            <label class="form-check-label" for="remember">Ingat saya</label>
          </div>
        </div>
        <button type="submit" class="btn btn-primary btn-login" id="loginBtn">
          <span class="spinner-border spinner-border-sm spinner me-2"></span>
          <span class="btn-text"><i class="fas fa-sign-in-alt me-2"></i>Masuk</span>
        </button>
      </form>
      <hr class="my-3">
      <div class="text-center text-muted small">
        <i class="fas fa-info-circle"></i> Default: <b>admin</b> / <b>password</b>
      </div>
    </div>
  </div>
</div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function togglePassword() {
  const p = document.getElementById('password');
  const eye = document.getElementById('eyeIcon');
  p.type = p.type === 'password' ? 'text' : 'password';
  eye.className = p.type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
}
document.getElementById('loginForm').addEventListener('submit', function() {
  this.classList.add('loading');
  document.getElementById('loginBtn').disabled = true;
});
</script>
</body>
</html>
