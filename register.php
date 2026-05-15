<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Daftar Alumni — Alumni SMK</title>
<link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style/index.css">
</head>
<body>
<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: index.php'); exit;
}
require 'koneksi.php';

$error   = '';
$success = '';

$jurusan_list = [
  'Rekayasa Perangkat Lunak',
  'Teknik Komputer dan Jaringan',
  'Teknik Jaringan Akses dan Telekomunikasi',
  'Animasi',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username   = trim($_POST['username']   ?? '');
    $password   = trim($_POST['password']   ?? '');
    $confirm_pw = trim($_POST['confirm_pw'] ?? '');
    $nis        = trim($_POST['nis']        ?? '');
    $nama       = trim($_POST['nama']       ?? '');
    $angkatan   = trim($_POST['angkatan']   ?? '');
    $jurusan    = trim($_POST['jurusan']    ?? '');
    $email      = trim($_POST['email']      ?? '');
    $no_hp      = trim($_POST['no_hp']      ?? '');
    $pekerjaan  = trim($_POST['pekerjaan']  ?? '');
    $perusahaan = trim($_POST['perusahaan'] ?? '');
    $alamat     = trim($_POST['alamat']     ?? '');

    if (!$username || !$password || !$nis || !$nama || !$angkatan || !$jurusan || !$email || !$no_hp) {
        $error = 'Harap lengkapi semua field yang wajib diisi.';
    } elseif ($password !== $confirm_pw) {
        $error = 'Password dan konfirmasi password tidak cocok.';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid.';
    } elseif ($angkatan < 2000 || $angkatan > date('Y')) {
        $error = 'Tahun angkatan tidak valid.';
    } else {
        // Cek username sudah dipakai
        $stmt = mysqli_prepare($conn, "SELECT user_id FROM users WHERE username = ?");
        mysqli_stmt_bind_param($stmt, 's', $username);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        mysqli_stmt_close($stmt);

        if (mysqli_fetch_assoc($res)) {
            $error = 'Username sudah digunakan.';
        } else {
            // Cek email sudah terdaftar di alumni
            $stmt = mysqli_prepare($conn, "SELECT id_alumni FROM alumni WHERE email = ?");
            mysqli_stmt_bind_param($stmt, 's', $email);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            mysqli_stmt_close($stmt);

            if (mysqli_fetch_assoc($res)) {
                $error = 'Email ini sudah terdaftar di data alumni.';
            } else {
                // Insert alumni
                $stmt = mysqli_prepare($conn, "INSERT INTO alumni (nis, nama, angkatan, jurusan, email, no_hp, pekerjaan, perusahaan, alamat) VALUES (?,?,?,?,?,?,?,?,?)");
                mysqli_stmt_bind_param($stmt, 'ssissssss', $nis, $nama, $angkatan, $jurusan, $email, $no_hp, $pekerjaan, $perusahaan, $alamat);

                if (mysqli_stmt_execute($stmt)) {
                    $id_alumni = mysqli_insert_id($conn);
                    mysqli_stmt_close($stmt);

                    $hashed = password_hash($password, PASSWORD_DEFAULT);
                    $stmt2 = mysqli_prepare($conn, "INSERT INTO users (username, password, role, id_alumni, status) VALUES (?,?,'user',?,'pending')");
                    mysqli_stmt_bind_param($stmt2, 'ssi', $username, $hashed, $id_alumni);

                    if (mysqli_stmt_execute($stmt2)) {
                        $success = true;
                    } else {
                        $error = 'Terjadi kesalahan saat membuat akun. Silakan coba lagi.';
                    }
                    mysqli_stmt_close($stmt2);
                } else {
                    $error = 'Terjadi kesalahan. Silakan coba lagi.';
                    mysqli_stmt_close($stmt);
                }
            }
        }
    }
}
?>
<div class="auth-bg register-bg">
  <div class="auth-left">
    <div class="brand-block">
      <div class="brand-logo">
        <svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
          <rect width="48" height="48" rx="14" fill="white" fill-opacity="0.15"/>
          <path d="M24 10L34 16V28L24 34L14 28V16L24 10Z" stroke="white" stroke-width="2" fill="none"/>
          <circle cx="24" cy="22" r="5" fill="white" fill-opacity="0.9"/>
          <path d="M15 30C17 27 20 25 24 25C28 25 31 27 33 30" stroke="white" stroke-width="1.5" stroke-linecap="round"/>
        </svg>
      </div>
      <h1 class="brand-name">Alumni SMK</h1>
      <p class="brand-tagline">Bergabunglah dengan komunitas alumni kami</p>
    </div>
    <div class="auth-decoration">
      <div class="deco-circle c1"></div>
      <div class="deco-circle c2"></div>
      <div class="deco-circle c3"></div>
    </div>
    <div class="reg-steps">
      <div class="step active"><div class="step-num">1</div><div class="step-text">Data Akun</div></div>
      <div class="step-line"></div>
      <div class="step active"><div class="step-num">2</div><div class="step-text">Data Alumni</div></div>
      <div class="step-line"></div>
      <div class="step"><div class="step-num">3</div><div class="step-text">Verifikasi Admin</div></div>
    </div>
    <div class="auth-quote"><p>"Daftar sekarang dan terhubung dengan sesama alumni."</p></div>
  </div>

  <div class="auth-right">
    <div class="auth-card auth-card-wide">
      <?php if ($success): ?>
      <div class="success-screen">
        <div class="success-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
          </svg>
        </div>
        <h2>Pendaftaran Berhasil!</h2>
        <p>Data alumni Anda telah berhasil didaftarkan. Akun Anda sedang <strong>menunggu verifikasi</strong> dari administrator sekolah.</p>
        <p class="success-note">Anda akan mendapat konfirmasi setelah akun diverifikasi. Harap bersabar.</p>
        <a href="login.php" class="btn-primary">Kembali ke Login</a>
      </div>

      <?php else: ?>
      <div class="auth-card-header">
        <h2>Daftar sebagai Alumni</h2>
        <p>Isi data lengkap Anda untuk verifikasi keanggotaan</p>
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
        <div class="form-section-title">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
          </svg>
          Informasi Akun
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Username <span class="req">*</span></label>
            <div class="input-wrapper">
              <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
              <input type="text" name="username" placeholder="username unik Anda" required value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
            </div>
          </div>
          <div class="form-group">
            <label>Password <span class="req">*</span></label>
            <div class="input-wrapper">
              <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
              <input type="password" name="password" placeholder="Min. 6 karakter" required>
            </div>
          </div>
          <div class="form-group">
            <label>Konfirmasi Password <span class="req">*</span></label>
            <div class="input-wrapper">
              <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4m6 2a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/></svg>
              <input type="password" name="confirm_pw" placeholder="Ulangi password" required>
            </div>
          </div>
        </div>

        <div class="form-section-title">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
          Data Alumni (untuk Verifikasi)
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>NIS / No. Induk <span class="req">*</span></label>
            <div class="input-wrapper">
              <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
              <input type="text" name="nis" placeholder="Nomor Induk Siswa" required value="<?= htmlspecialchars($_POST['nis'] ?? '') ?>">
            </div>
          </div>
          <div class="form-group">
            <label>Nama Lengkap <span class="req">*</span></label>
            <div class="input-wrapper">
              <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
              <input type="text" name="nama" placeholder="Nama sesuai ijazah" required value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>">
            </div>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Angkatan / Tahun Lulus <span class="req">*</span></label>
            <div class="input-wrapper">
              <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
              <input type="number" name="angkatan" placeholder="Contoh: 2022" min="2000" max="<?= date('Y') ?>" required value="<?= htmlspecialchars($_POST['angkatan'] ?? '') ?>">
            </div>
          </div>
          <div class="form-group">
            <label>Jurusan <span class="req">*</span></label>
            <div class="input-wrapper">
              <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
              <select name="jurusan" required>
                <option value="">-- Pilih Jurusan --</option>
                <?php foreach ($jurusan_list as $j): ?>
                <option value="<?= htmlspecialchars($j) ?>" <?= (($_POST['jurusan'] ?? '') === $j) ? 'selected' : '' ?>><?= htmlspecialchars($j) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Email Aktif <span class="req">*</span></label>
            <div class="input-wrapper">
              <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
              <input type="email" name="email" placeholder="email@aktif.com" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
          </div>
          <div class="form-group">
            <label>No. HP / WhatsApp <span class="req">*</span></label>
            <div class="input-wrapper">
              <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.18 2 2 0 0 1 3.59 1h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.73a16 16 0 0 0 6 6l.91-.91a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
              <input type="text" name="no_hp" placeholder="08xxxxxxxxxx" required value="<?= htmlspecialchars($_POST['no_hp'] ?? '') ?>">
            </div>
          </div>
        </div>

        <div class="form-section-title">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
          Informasi Karir (Opsional)
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Pekerjaan Saat Ini</label>
            <div class="input-wrapper">
              <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
              <input type="text" name="pekerjaan" placeholder="Profesi / jabatan Anda" value="<?= htmlspecialchars($_POST['pekerjaan'] ?? '') ?>">
            </div>
          </div>
          <div class="form-group">
            <label>Perusahaan / Instansi</label>
            <div class="input-wrapper">
              <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
              <input type="text" name="perusahaan" placeholder="Nama perusahaan / instansi" value="<?= htmlspecialchars($_POST['perusahaan'] ?? '') ?>">
            </div>
          </div>
        </div>
        <div class="form-group">
          <label>Alamat Sekarang</label>
          <div class="input-wrapper">
            <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="top:14px"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
            <textarea name="alamat" placeholder="Jl. ..." rows="3"><?= htmlspecialchars($_POST['alamat'] ?? '') ?></textarea>
          </div>
        </div>

        <div class="info-box">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          <p>Pendaftaran Anda akan diverifikasi oleh admin sekolah. Akun baru dapat digunakan setelah disetujui.</p>
        </div>

        <button type="submit" class="btn-primary btn-full">
          Kirim Pendaftaran
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
        </button>
      </form>

      <div class="auth-footer">
        <p>Sudah punya akun? <a href="login.php">Masuk di sini</a></p>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>
</body>
</html>
