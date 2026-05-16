<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Pengguna — Alumni SMK</title>
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

$id = (int)($_GET['id'] ?? 0);

$sql = "SELECT u.*, a.nama FROM users u LEFT JOIN alumni a ON u.id_alumni=a.id_alumni WHERE u.user_id=$1";
$res = pg_query_params($conn, $sql, array($id));
$user = pg_fetch_assoc($res);

if (!$user) {
    echo '<div class="page-wrapper"><div class="alert alert-error">Pengguna tidak ditemukan.</div></div>';
    exit;
}

$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_pw = trim($_POST['new_password'] ?? '');
    $status = trim($_POST['status'] ?? '');
    $role   = trim($_POST['role']   ?? '');

    $allowedRoles = isSuperAdmin() ? ['user','admin','superadmin'] : ['user','admin'];
    if (!in_array($role, $allowedRoles)) $role = $user['role'];

    $sql = "UPDATE users SET role=$1, status=$2 WHERE user_id=$3";
    pg_query_params($conn, $sql, array($role, $status, $id));

    if ($new_pw) {
        if (strlen($new_pw) < 6) {
            $error = 'Password minimal 6 karakter.';
        } else {
            $hashed = password_hash($new_pw, PASSWORD_DEFAULT);
            $sql = "UPDATE users SET password=$1 WHERE user_id=$2";
            pg_query_params($conn, $sql, array($hashed, $id));
        }
    }

    if (!$error) {
        $success = 'Data pengguna diperbarui.';
        // Refresh data user
        $res = pg_query_params($conn, "SELECT u.*, a.nama FROM users u LEFT JOIN alumni a ON u.id_alumni=a.id_alumni WHERE u.user_id=$1", array($id));
        $user = pg_fetch_assoc($res);
    }
}
?>

<div class="page-wrapper">
  <div class="page-header">
    <div><h1 class="page-title">Edit Pengguna</h1><p class="page-sub"><?= htmlspecialchars($user['username']) ?></p></div>
    <a href="users.php" class="btn-outline"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>Kembali</a>
  </div>

  <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
  <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>

  <div class="section-card">
    <form method="POST" class="auth-form">
      <div class="form-row">
        <div class="form-group">
          <label>Username</label>
          <div class="input-wrapper"><input type="text" value="<?= htmlspecialchars($user['username']) ?>" disabled></div>
        </div>
        <div class="form-group">
          <label>Alumni Terhubung</label>
          <div class="input-wrapper"><input type="text" value="<?= htmlspecialchars($user['nama'] ?? 'Tidak ada') ?>" disabled></div>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>Role</label>
          <div class="input-wrapper">
            <select name="role">
              <?php
              $roles = isSuperAdmin() ? ['user','admin','superadmin'] : ['user','admin'];
              foreach ($roles as $r): ?>
              <option value="<?= $r ?>" <?= $user['role']===$r?'selected':'' ?>><?= ucfirst($r) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="form-group">
          <label>Status Akun</label>
          <div class="input-wrapper">
            <select name="status">
              <option value="pending"  <?= $user['status']==='pending' ?'selected':''?>>Pending</option>
              <option value="approved" <?= $user['status']==='approved'?'selected':''?>>Approved</option>
              <option value="rejected" <?= $user['status']==='rejected'?'selected':''?>>Rejected</option>
            </select>
          </div>
        </div>
      </div>
      <div class="form-group">
        <label>Reset Password (kosongkan jika tidak ingin ubah)</label>
        <div class="input-wrapper">
          <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
          <input type="text" name="new_password" placeholder="Password baru">
        </div>
      </div>
      <button type="submit" class="btn-primary">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/></svg>
        Simpan
      </button>
    </form>
  </div>
</div>
</body>
</html>
