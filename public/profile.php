<?php require __DIR__ . '/src/koneksi.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Profil — Alumni SMK</title>
<link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/dashboard.css">
</head>
<body>
<?php
require 'src/auth.php';
require __DIR__ . '/src/koneksi.php';
requireLogin();
include __DIR__ . '/src/navbar.php';

$error   = '';
$success = '';
$id_alumni = $_SESSION['id_alumni'];
$isPureAdmin = false;

if (!$id_alumni && !isAdmin()) {
    echo '<div class="page-wrapper"><div class="alert alert-error">Anda tidak memiliki data alumni yang terhubung.</div></div>';
    exit;
}

// Admin bisa lihat profil alumni by ?id=
$target_id = $id_alumni;
if (isAdmin() && isset($_GET['id'])) {
    $target_id = (int)$_GET['id'];
}

// Jika ini admin murni (tidak punya id_alumni) dan tidak sedang melihat profil orang lain
if (!$target_id && isAdmin()) {
    $isPureAdmin = true;
    $res = pg_query_params($conn, "SELECT * FROM users WHERE user_id = $1", array($_SESSION['user_id']));
    $user_data = pg_fetch_assoc($res);
    $alumni = [
        'nama' => $user_data['username'], // Admin pakai username
        'nis' => '-',
        'angkatan' => '-',
        'jurusan' => '-',
        'email' => '-',
        'no_hp' => '-',
        'status_kesibukan' => '-',
        'pekerjaan' => '-',
        'perusahaan' => '-',
        'linkedin' => '-',
        'biodata' => '-',
        'foto_profil' => '',
        'alamat' => '-'
    ];
} else {
    $res = pg_query_params($conn, "SELECT * FROM alumni WHERE id_alumni = $1", array($target_id));
    $alumni = pg_fetch_assoc($res);
}

if (!$alumni && !$isPureAdmin) {
    echo '<div class="page-wrapper"><div class="alert alert-error">Data alumni tidak ditemukan.</div></div>';
    exit;
}

$canEdit = ($target_id == $id_alumni) || isAdmin();

$jurusan_list = [
  'Rekayasa Perangkat Lunak',
  'Teknik Komputer dan Jaringan',
  'Teknik Jaringan Akses dan Telekomunikasi',
  'Animasi',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $canEdit) {
    // Validasi CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = 'Token CSRF tidak valid. Silakan muat ulang halaman.';
    } else {
        // Jika admin murni, lewati semua urusan alumni
        if ($isPureAdmin) {
        // Boleh ganti username dan password
        $username = trim($_POST['username'] ?? '');
        if (!$username) {
            $error = 'Username tidak boleh kosong.';
        } else {
            // Cek username duplikat
            $sres = pg_query_params($conn, "SELECT user_id FROM users WHERE username=$1 AND user_id!=$2", array($username, $_SESSION['user_id']));
            if (pg_fetch_assoc($sres)) {
                $error = 'Username sudah digunakan.';
            } else {
                pg_query_params($conn, "UPDATE users SET username=$1 WHERE user_id=$2", array($username, $_SESSION['user_id']));
                $_SESSION['username'] = $username; // Update session!
                $success = 'Profil berhasil diperbarui.';
                $alumni['nama'] = $username; // Update local variable for display
            }
        }
        
        // Ganti password
        if (!$error && !empty($_POST['new_password'])) {
            $new_pw  = $_POST['new_password'];
            $conf_pw = $_POST['confirm_new_password'] ?? '';
            if (strlen($new_pw) < 6) {
                $error = 'Password baru minimal 6 karakter.';
            } elseif ($new_pw !== $conf_pw) {
                $error = 'Konfirmasi password tidak cocok.';
            } else {
                $hashed = password_hash($new_pw, PASSWORD_DEFAULT);
                pg_query_params($conn, "UPDATE users SET password=$1 WHERE user_id=$2", array($hashed, $_SESSION['user_id']));
                $success = 'Profil dan password berhasil diperbarui.';
            }
        }
    } else {
        // Logika asli untuk Alumni
        // Handle foto upload
        if (!empty($_FILES['foto']['name'])) {
            $allowed = ['jpg','jpeg','png','webp'];
            $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed)) {
                $error = 'Format foto tidak didukung. Gunakan JPG, PNG, atau WEBP.';
            } elseif ($_FILES['foto']['size'] > 2 * 1024 * 1024) {
                $error = 'Ukuran foto maksimal 2MB.';
            } else {
                $filename = 'foto_' . $target_id . '_' . time() . '.' . $ext;
                move_uploaded_file($_FILES['foto']['tmp_name'], "uploads/foto_profil/$filename");
                if ($alumni['foto_profil'] && file_exists("uploads/foto_profil/".$alumni['foto_profil'])) {
                    unlink("uploads/foto_profil/".$alumni['foto_profil']);
                }
                pg_query_params($conn, "UPDATE alumni SET foto_profil=$1 WHERE id_alumni=$2", array($filename, $target_id));
                $alumni['foto_profil'] = $filename;
            }
        }

        if (!$error) {
            $nama       = trim($_POST['nama']       ?? '');
            $angkatan   = trim($_POST['angkatan']   ?? '');
            $jurusan    = trim($_POST['jurusan']    ?? '');
            $email      = trim($_POST['email']      ?? '');
            $no_hp      = trim($_POST['no_hp']      ?? '');
            $pekerjaan  = trim($_POST['pekerjaan']  ?? '');
            $perusahaan = trim($_POST['perusahaan'] ?? '');
            $alamat     = trim($_POST['alamat']     ?? '');

            if (!$nama || !$angkatan || !$jurusan || !$email || !$no_hp) {
                $error = 'Field wajib tidak boleh kosong.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Format email tidak valid.';
            } else {
                // Cek email duplikat (selain dirinya)
                $sres = pg_query_params($conn, "SELECT id_alumni FROM alumni WHERE email=$1 AND id_alumni!=$2", array($email, $target_id));

                if (pg_fetch_assoc($sres)) {
                    $error = 'Email sudah digunakan alumni lain.';
                } else {
                    $sql = "UPDATE alumni SET nama=$1,angkatan=$2,jurusan=$3,email=$4,no_hp=$5,pekerjaan=$6,perusahaan=$7,alamat=$8 WHERE id_alumni=$9";
                    pg_query_params($conn, $sql, array($nama, $angkatan, $jurusan, $email, $no_hp, $pekerjaan, $perusahaan, $alamat, $target_id));
                    $success = 'Profil berhasil diperbarui.';

                    $res = pg_query_params($conn, "SELECT * FROM alumni WHERE id_alumni=$1", array($target_id));
                    $alumni = pg_fetch_assoc($res);
                }
            }
        }

        // Ganti password untuk alumni
        if (!$error && !empty($_POST['new_password'])) {
            $new_pw  = $_POST['new_password'];
            $conf_pw = $_POST['confirm_new_password'] ?? '';
            if (strlen($new_pw) < 6) {
                $error = 'Password baru minimal 6 karakter.';
            } elseif ($new_pw !== $conf_pw) {
                $error = 'Konfirmasi password tidak cocok.';
            } else {
                $hashed = password_hash($new_pw, PASSWORD_DEFAULT);
                pg_query_params($conn, "UPDATE users SET password=$1 WHERE id_alumni=$2", array($hashed, $target_id));
                $success = 'Profil dan password berhasil diperbarui.';
            }
        }
    }
    }
}
?>

<div class="page-wrapper">
  <div class="page-header">
    <div>
      <h1 class="page-title">Profil Alumni</h1>
      <p class="page-sub">Data pribadi dan informasi karir</p>
    </div>
    <?php if (isAdmin()): ?>
    <a href="<?= isAdmin() ? 'dashboard_admin.php' : 'dashboard_user.php' ?>" class="btn-outline">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
      Kembali
    </a>
    <?php endif; ?>
  </div>

  <?php if ($error): ?>
  <div class="alert alert-error">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    <?= htmlspecialchars($error) ?>
  </div>
  <?php endif; ?>
  <?php if ($success): ?>
  <div class="alert alert-success">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
    <?= htmlspecialchars($success) ?>
  </div>
  <?php endif; ?>

  <div class="profile-layout">
    <!-- Sidebar -->
    <div class="profile-sidebar">
      <div class="profile-card">
        <div class="profile-avatar-wrap">
          <?php if ($alumni['foto_profil'] && file_exists("uploads/foto_profil/".$alumni['foto_profil'])): ?>
          <img src="uploads/foto_profil/<?= htmlspecialchars($alumni['foto_profil']) ?>" alt="Foto Profil" class="profile-avatar">
          <?php else: ?>
          <div class="profile-avatar-placeholder">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
          </div>
          <?php endif; ?>
          <?php if ($canEdit): ?>
          <label for="fotoInput" class="foto-overlay">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
            Ganti Foto
          </label>
          <?php endif; ?>
        </div>
        <h3 class="profile-name"><?= htmlspecialchars($alumni['nama']) ?></h3>
        <p class="profile-jurusan"><?= htmlspecialchars($alumni['jurusan']) ?></p>
        <?php if (!$isPureAdmin): ?>
        <div class="profile-badges">
          <span class="badge badge-blue">Angkatan <?= $alumni['angkatan'] ?></span>
          <span class="badge badge-gray">NIS: <?= htmlspecialchars($alumni['nis']) ?></span>
        </div>
        <div class="profile-contact">
          <div class="contact-item">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
            <?= htmlspecialchars($alumni['email']) ?>
          </div>
          <div class="contact-item">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.18 2 2 0 0 1 3.59 1h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L7.91 8.73a16 16 0 0 0 6 6l.91-.91a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
            <?= htmlspecialchars($alumni['no_hp']) ?>
          </div>
          <?php if ($alumni['pekerjaan']): ?>
          <div class="contact-item">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
            <?= htmlspecialchars($alumni['pekerjaan']) ?><?= $alumni['perusahaan'] ? ' — '.htmlspecialchars($alumni['perusahaan']) : '' ?>
          </div>
          <?php endif; ?>
          <?php if ($alumni['alamat']): ?>
          <div class="contact-item">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
            <?= htmlspecialchars($alumni['alamat']) ?>
          </div>
          <?php endif; ?>
        </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Edit Form -->
    <?php if ($canEdit): ?>
    <div class="profile-main">
      <div class="section-card">
        <div class="section-head"><h2>Edit Profil</h2></div>
        <form method="POST" enctype="multipart/form-data" class="auth-form">
          <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
          <input type="file" id="fotoInput" name="foto" accept="image/*" style="display:none" onchange="previewFoto(this)">

          <?php if (!$isPureAdmin): ?>
          <div class="form-section-title">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            Data Pribadi
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Nama Lengkap <span class="req">*</span></label>
              <div class="input-wrapper">
                <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                <input type="text" name="nama" value="<?= htmlspecialchars($alumni['nama']) ?>" required>
              </div>
            </div>
            <div class="form-group">
              <label>NIS (tidak bisa diubah)</label>
              <div class="input-wrapper">
                <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                <input type="text" value="<?= htmlspecialchars($alumni['nis']) ?>" disabled>
              </div>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Angkatan <span class="req">*</span></label>
              <div class="input-wrapper">
                <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                <input type="number" name="angkatan" value="<?= $alumni['angkatan'] ?>" min="2000" max="<?= date('Y') ?>" required>
              </div>
            </div>
            <div class="form-group">
              <label>Jurusan <span class="req">*</span></label>
              <div class="input-wrapper">
                <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
                <select name="jurusan" required>
                  <?php foreach ($jurusan_list as $j): ?>
                  <option value="<?= htmlspecialchars($j) ?>" <?= $alumni['jurusan']===$j?'selected':'' ?>><?= htmlspecialchars($j) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Email <span class="req">*</span></label>
              <div class="input-wrapper">
                <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                <input type="email" name="email" value="<?= htmlspecialchars($alumni['email']) ?>" required>
              </div>
            </div>
            <div class="form-group">
              <label>No. HP <span class="req">*</span></label>
              <div class="input-wrapper">
                <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.18 2 2 0 0 1 3.59 1h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.73a16 16 0 0 0 6 6l.91-.91a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                <input type="text" name="no_hp" value="<?= htmlspecialchars($alumni['no_hp']) ?>" required>
              </div>
            </div>
          </div>

          <div class="form-section-title">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
            Informasi Karir
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Pekerjaan</label>
              <div class="input-wrapper">
                <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                <input type="text" name="pekerjaan" value="<?= htmlspecialchars($alumni['pekerjaan'] ?? '') ?>" placeholder="Profesi Anda">
              </div>
            </div>
            <div class="form-group">
              <label>Perusahaan</label>
              <div class="input-wrapper">
                <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                <input type="text" name="perusahaan" value="<?= htmlspecialchars($alumni['perusahaan'] ?? '') ?>" placeholder="Nama perusahaan">
              </div>
            </div>
          </div>

          <div class="form-group">
            <label>Alamat</label>
            <div class="input-wrapper">
              <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="top:14px"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
              <textarea name="alamat" rows="3" placeholder="Alamat lengkap"><?= htmlspecialchars($alumni['alamat'] ?? '') ?></textarea>
            </div>
          </div>
          <?php else: ?>
          <div class="form-section-title">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            Data Akun Admin
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Username <span class="req">*</span></label>
              <div class="input-wrapper">
                <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                <input type="text" name="username" value="<?= htmlspecialchars($alumni['nama']) ?>" required>
              </div>
            </div>
          </div>
          <?php endif; ?>

          <div class="form-section-title">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            Ganti Password (opsional)
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Password Baru</label>
              <div class="input-wrapper">
                <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                <input type="password" name="new_password" placeholder="Kosongkan jika tidak ingin ganti">
              </div>
            </div>
            <div class="form-group">
              <label>Konfirmasi Password Baru</label>
              <div class="input-wrapper">
                <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4"/></svg>
                <input type="password" name="confirm_new_password" placeholder="Ulangi password baru">
              </div>
            </div>
          </div>

          <button type="submit" class="btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
            Simpan Perubahan
          </button>
        </form>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>

<script>
function previewFoto(input) {
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = e => {
      const avatarEl = document.querySelector('.profile-avatar, .profile-avatar-placeholder');
      if (avatarEl) {
        const img = document.createElement('img');
        img.src = e.target.result;
        img.className = 'profile-avatar';
        avatarEl.replaceWith(img);
      }
    };
    reader.readAsDataURL(input.files[0]);
    input.form.submit();
  }
}
</script>
</body>
</html>
