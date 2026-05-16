<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login — Portal Alumni SMK Telkom Lampung</title>
<link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style/index.css">
</head>
<body>
<?php
session_start();
if (isset($_SESSION['user_id'])) {
    $role = $_SESSION['role'];
    header('Location: ' . ($role === 'user' ? 'dashboard_user.php' : 'dashboard_admin.php'));
    exit;
}

require 'koneksi.php';
$conn = mysqli_connect($host, $username, $password, $dbname);
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username && $password) {
        $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE username = ?");
        mysqli_stmt_bind_param($stmt, 's', $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        if ($user && password_verify($password, $user['password'])) {
        // if ($password == ($user['password'])) {
            if ($user['status'] === 'pending') {
                $error = 'Akun Anda sedang menunggu verifikasi admin.';
            } elseif ($user['status'] === 'rejected') {
                $error = 'Akun Anda ditolak. Hubungi administrator.';
            } else {
                $_SESSION['user_id']  = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role']     = $user['role'];
                $_SESSION['id_alumni']= $user['id_alumni'];
                header('Location: ' . ($user['role'] === 'user' ? 'dashboard_user.php' : 'dashboard_admin.php'));
                exit;
            }
        } else {
            $error = 'Username atau password salah.';
        }
    } else {
        $error = 'Harap isi semua kolom.';
    }
}
?>

<div class="auth-bg">
  <div class="auth-left">
    <div class="brand-block">
      <!-- Logo sekolah. Ubah src jika ingin ganti gambar logo -->
      <div class="brand-logo">
        <img src="assets/logo.png" alt="Logo SMK Telkom Lampung">
      </div>
      <h1 class="brand-name">SMK Telkom Lampung</h1>
      <p class="brand-tagline">Portal Resmi Data Alumni · Sekolah Menengah Kejuruan Telkom Lampung</p>
    </div>
    <div class="auth-decoration">
      <div class="deco-circle c1"></div>
      <div class="deco-circle c2"></div>
      <div class="deco-circle c3"></div>
    </div>
    <div class="auth-quote">
      <p>"Membangun generasi unggul di bidang telekomunikasi dan teknologi."</p>
    </div>
  </div>

  <div class="auth-right">
    <div class="auth-card">
      <div class="auth-card-header">
        <h2>Selamat Datang</h2>
        <p>Masuk ke akun Anda untuk melanjutkan</p>
      </div>

      <?php if ($error): ?>
      <div class="alert alert-error">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
        <?= htmlspecialchars($error) ?>
      </div>
      <?php endif; ?>

      <form method="POST" class="auth-form">
        <div class="form-group">
          <label for="username">Username</label>
          <div class="input-wrapper">
            <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
            </svg>
            <input type="text" id="username" name="username" placeholder="Masukkan username" required value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
          </div>
        </div>

        <div class="form-group">
          <label for="password">Password</label>
          <div class="input-wrapper">
            <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
            </svg>
            <input type="password" id="password" name="password" placeholder="Masukkan password" required>
            <button type="button" class="toggle-pw" onclick="togglePassword()">
              <svg id="eye-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
              </svg>
            </button>
          </div>
        </div>

        <button type="submit" class="btn-primary btn-full">
          Masuk
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>
          </svg>
        </button>
      </form>

      <div class="auth-footer">
        <p>Belum punya akun? <a href="register.php">Daftar sebagai Alumni</a></p>
      </div>
    </div>
  </div>
</div>

<script>
function togglePassword() {
  const pw = document.getElementById('password');
  pw.type = pw.type === 'password' ? 'text' : 'password';
}
</script>
</body>
</html>
