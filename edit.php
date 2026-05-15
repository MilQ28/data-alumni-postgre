<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Data Alumni — Alumni SMK</title>
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
$stmt = mysqli_prepare($conn, "SELECT * FROM alumni WHERE id_alumni=?");
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$alumni = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

if (!$alumni) {
    echo '<div class="page-wrapper"><div class="alert alert-error">Data tidak ditemukan.</div></div>';
    exit;
}

$error = $success = '';
$jurusan_list = [
  'Rekayasa Perangkat Lunak',
  'Teknik Komputer dan Jaringan',
  'Teknik Jaringan Akses dan Telekomunikasi',
  'Animasi',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nis        = trim($_POST['nis']        ?? '');
    $nama       = trim($_POST['nama']       ?? '');
    $angkatan   = trim($_POST['angkatan']   ?? '');
    $jurusan    = trim($_POST['jurusan']    ?? '');
    $email      = trim($_POST['email']      ?? '');
    $no_hp      = trim($_POST['no_hp']      ?? '');
    $pekerjaan  = trim($_POST['pekerjaan']  ?? '');
    $perusahaan = trim($_POST['perusahaan'] ?? '');
    $alamat     = trim($_POST['alamat']     ?? '');

    if (!$nis || !$nama || !$angkatan || !$jurusan || !$email || !$no_hp) {
        $error = 'Field wajib tidak boleh kosong.';
    } else {
        $s = mysqli_prepare($conn, "SELECT id_alumni FROM alumni WHERE email=? AND id_alumni!=?");
        mysqli_stmt_bind_param($s, 'si', $email, $id);
        mysqli_stmt_execute($s);
        $sres = mysqli_stmt_get_result($s);
        mysqli_stmt_close($s);

        if (mysqli_fetch_assoc($sres)) {
            $error = 'Email sudah digunakan.';
        } else {
            $stmt = mysqli_prepare($conn, "UPDATE alumni SET nis=?,nama=?,angkatan=?,jurusan=?,email=?,no_hp=?,pekerjaan=?,perusahaan=?,alamat=? WHERE id_alumni=?");
            mysqli_stmt_bind_param($stmt, 'ssissssssi', $nis, $nama, $angkatan, $jurusan, $email, $no_hp, $pekerjaan, $perusahaan, $alamat, $id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            $success = 'Data berhasil diperbarui.';

            $stmt = mysqli_prepare($conn, "SELECT * FROM alumni WHERE id_alumni=?");
            mysqli_stmt_bind_param($stmt, 'i', $id);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            $alumni = mysqli_fetch_assoc($res);
            mysqli_stmt_close($stmt);
        }
    }
}
?>

<div class="page-wrapper">
  <div class="page-header">
    <div>
      <h1 class="page-title">Edit Data Alumni</h1>
      <p class="page-sub">Perbarui data alumni: <?= htmlspecialchars($alumni['nama']) ?></p>
    </div>
    <a href="dashboard_admin.php" class="btn-outline">
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
          <label>NIS <span class="req">*</span></label>
          <div class="input-wrapper">
            <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/></svg>
            <input type="text" name="nis" required value="<?= htmlspecialchars($alumni['nis']) ?>">
          </div>
        </div>
        <div class="form-group">
          <label>Nama Lengkap <span class="req">*</span></label>
          <div class="input-wrapper">
            <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            <input type="text" name="nama" required value="<?= htmlspecialchars($alumni['nama']) ?>">
          </div>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>Angkatan <span class="req">*</span></label>
          <div class="input-wrapper">
            <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/></svg>
            <input type="number" name="angkatan" min="2000" max="<?= date('Y') ?>" required value="<?= $alumni['angkatan'] ?>">
          </div>
        </div>
        <div class="form-group">
          <label>Jurusan <span class="req">*</span></label>
          <div class="input-wrapper">
            <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/></svg>
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
            <input type="email" name="email" required value="<?= htmlspecialchars($alumni['email']) ?>">
          </div>
        </div>
        <div class="form-group">
          <label>No. HP <span class="req">*</span></label>
          <div class="input-wrapper">
            <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07"/></svg>
            <input type="text" name="no_hp" value="<?= htmlspecialchars($alumni['no_hp']) ?>">
          </div>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>Pekerjaan</label>
          <div class="input-wrapper">
            <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
            <input type="text" name="pekerjaan" value="<?= htmlspecialchars($alumni['pekerjaan'] ?? '') ?>">
          </div>
        </div>
        <div class="form-group">
          <label>Perusahaan</label>
          <div class="input-wrapper">
            <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
            <input type="text" name="perusahaan" value="<?= htmlspecialchars($alumni['perusahaan'] ?? '') ?>">
          </div>
        </div>
      </div>
      <div class="form-group">
        <label>Alamat</label>
        <div class="input-wrapper">
          <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="top:14px"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
          <textarea name="alamat" rows="3"><?= htmlspecialchars($alumni['alamat'] ?? '') ?></textarea>
        </div>
      </div>
      <button type="submit" class="btn-primary">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/></svg>
        Simpan Perubahan
      </button>
    </form>
  </div>
</div>
</body>
</html>
