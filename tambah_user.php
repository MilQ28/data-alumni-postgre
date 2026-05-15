<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tambah Pengguna — Alumni SMK</title>
<link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style/dashboard.css">
</head>
<body>
<?php
session_start();
require 'auth.php';
require 'koneksi.php';
requireAdmin();
include 'navbar.php';

$error = $success = '';
$jurusan_list = [
    'Rekayasa Perangkat Lunak','Teknik Komputer dan Jaringan','Akuntansi',
    'Administrasi Perkantoran','Teknik Otomotif','Teknik Elektronika',
    'Multimedia','Bisnis Daring dan Pemasaran','Teknik Instalasi Tenaga Listrik','Kuliner',
];

// Ambil alumni tanpa user
$res = mysqli_query($conn, "SELECT a.id_alumni, a.nama, a.nis FROM alumni a WHERE NOT EXISTS (SELECT 1 FROM users u WHERE u.id_alumni=a.id_alumni) ORDER BY a.nama");
$alumniTanpaUser = mysqli_fetch_all($res, MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username  = trim($_POST['username']  ?? '');
    $password  = trim($_POST['password']  ?? '');
    $role      = trim($_POST['role']      ?? 'user');
    $id_alumni = trim($_POST['id_alumni'] ?? '');

    $allowedRoles = isSuperAdmin() ? ['user','admin'] : ['user'];
    if (!in_array($role, $allowedRoles)) $role = 'user';

    if (!$username || !$password) {
        $error = 'Username dan password wajib diisi.';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter.';
    } else {
        $s = mysqli_prepare($conn, "SELECT user_id FROM users WHERE username=?");
        mysqli_stmt_bind_param($s, 's', $username);
        mysqli_stmt_execute($s);
        $sres = mysqli_stmt_get_result($s);
        mysqli_stmt_close($s);

        if (mysqli_fetch_assoc($sres)) {
            $error = 'Username sudah digunakan.';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $alId   = ($id_alumni !== '') ? (int)$id_alumni : null;

            $stmt = mysqli_prepare($conn, "INSERT INTO users (username,password,role,id_alumni,status) VALUES (?,?,?,?,'approved')");
            mysqli_stmt_bind_param($stmt, 'ssss', $username, $hashed, $role, $alId);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            $success = 'Pengguna berhasil ditambahkan.';
        }
    }
}
?>

<div class="page-wrapper">
  <div class="page-header">
    <div>
      <h1 class="page-title">Tambah Pengguna</h1>
      <p class="page-sub">Buat akun pengguna baru</p>
    </div>
    <a href="users.php" class="btn-outline">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
      Kembali
    </a>
  </div>

  <?php if ($error): ?><div class="alert alert-error"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg><?= htmlspecialchars($error) ?></div><?php endif; ?>
  <?php if ($success): ?><div class="alert alert-success"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg><?= htmlspecialchars($success) ?></div><?php endif; ?>

  <div class="section-card">
    <form method="POST" class="auth-form">
      <div class="form-row">
        <div class="form-group">
          <label>Username <span class="req">*</span></label>
          <div class="input-wrapper">
            <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            <input type="text" name="username" required value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
          </div>
        </div>
        <div class="form-group">
          <label>Password <span class="req">*</span></label>
          <div class="input-wrapper">
            <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            <input type="text" name="password" required>
          </div>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Role</label>
          <div class="input-wrapper">
            <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            <select name="role">
              <option value="user">User (Alumni)</option>
              <?php if (isSuperAdmin()): ?>
              <option value="admin">Admin</option>
              <?php endif; ?>
            </select>
          </div>
        </div>
        <div class="form-group">
          <label>Hubungkan ke Alumni</label>
          <div class="input-wrapper">
            <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
            <select name="id_alumni">
              <option value="">-- Tidak dihubungkan --</option>
              <?php foreach ($alumniTanpaUser as $a): ?>
              <option value="<?= $a['id_alumni'] ?>"><?= htmlspecialchars($a['nama']) ?> (<?= htmlspecialchars($a['nis']) ?>)</option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
      </div>

      <button type="submit" class="btn-primary">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Buat Pengguna
      </button>
    </form>
  </div>
</div>
</body>
</html>
