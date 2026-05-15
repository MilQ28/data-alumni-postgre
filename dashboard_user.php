<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Beranda — Alumni SMK</title>
<link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style/dashboard.css">
</head>
<body>
<?php
session_start();
require 'auth.php';
require 'koneksi.php';
requireLogin();
if (isAdmin()) { header('Location: dashboard_admin.php'); exit; }
include 'navbar.php';

$id_alumni = $_SESSION['id_alumni'];
$myData = null;
if ($id_alumni) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM alumni WHERE id_alumni = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id_alumni);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $myData = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);
}

// Semua alumni (bisa dilihat user)
$search  = trim($_GET['search'] ?? '');
$jurusan = trim($_GET['jurusan'] ?? '');
$where   = [];
$params  = [];
$types   = '';

if ($search) {
    $where[] = "(nama LIKE ? OR nis LIKE ? OR pekerjaan LIKE ?)";
    $s = "%$search%";
    $params = array_merge($params, [$s, $s, $s]);
    $types .= 'sss';
}
if ($jurusan) {
    $where[] = "jurusan = ?";
    $params[] = $jurusan;
    $types .= 's';
}

$sql = "SELECT * FROM alumni" . ($where ? " WHERE " . implode(" AND ", $where) : "") . " ORDER BY created_at DESC";
$stmt = mysqli_prepare($conn, $sql);
if ($params) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$alumniList = mysqli_fetch_all($res, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

$res2 = mysqli_query($conn, "SELECT DISTINCT jurusan FROM alumni ORDER BY jurusan");
$jurusanList = [];
while ($row = mysqli_fetch_assoc($res2)) {
    $jurusanList[] = $row['jurusan'];
}
?>

<div class="page-wrapper">
  <div class="page-header">
    <div>
      <h1 class="page-title">Data Alumni SMK</h1>
      <p class="page-sub">Temukan informasi alumni sekolah kami</p>
    </div>
    <?php if ($myData): ?>
    <a href="profile.php" class="btn-primary">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
      Profil Saya
    </a>
    <?php endif; ?>
  </div>

  <?php if ($myData): ?>
  <div class="my-profile-banner">
    <div class="profile-banner-inner">
      <div class="profile-banner-avatar">
        <?php if ($myData['foto_profil'] && file_exists("uploads/foto_profil/".$myData['foto_profil'])): ?>
        <img src="uploads/foto_profil/<?= htmlspecialchars($myData['foto_profil']) ?>" alt="Foto">
        <?php else: ?>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        <?php endif; ?>
      </div>
      <div class="profile-banner-info">
        <h3><?= htmlspecialchars($myData['nama']) ?></h3>
        <p><?= htmlspecialchars($myData['jurusan']) ?> · Angkatan <?= $myData['angkatan'] ?></p>
        <?php if ($myData['pekerjaan']): ?>
        <p class="banner-job">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
          <?= htmlspecialchars($myData['pekerjaan']) ?><?= $myData['perusahaan'] ? ' di '.htmlspecialchars($myData['perusahaan']) : '' ?>
        </p>
        <?php endif; ?>
      </div>
      <a href="profile.php" class="btn-outline">Edit Profil Saya</a>
    </div>
  </div>
  <?php endif; ?>

  <!-- Search & Filter -->
  <div class="section-card">
    <form method="GET" class="search-form">
      <div class="input-wrapper">
        <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
        </svg>
        <input type="text" name="search" placeholder="Cari nama, NIS, pekerjaan..." value="<?= htmlspecialchars($search) ?>">
      </div>
      <div class="input-wrapper">
        <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/>
        </svg>
        <select name="jurusan">
          <option value="">Semua Jurusan</option>
          <?php foreach ($jurusanList as $j): ?>
          <option value="<?= htmlspecialchars($j) ?>" <?= $jurusan===$j?'selected':'' ?>><?= htmlspecialchars($j) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <button type="submit" class="btn-primary">Cari</button>
      <?php if ($search || $jurusan): ?><a href="dashboard_user.php" class="btn-outline">Reset</a><?php endif; ?>
    </form>
  </div>

  <!-- Alumni Grid -->
  <div class="alumni-grid">
    <?php if (empty($alumniList)): ?>
    <div class="empty-state">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
        <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
      </svg>
      <p>Tidak ada alumni yang ditemukan.</p>
    </div>
    <?php else: ?>
    <?php foreach ($alumniList as $a): ?>
    <div class="alumni-card <?= ($a['id_alumni'] == $id_alumni) ? 'my-card' : '' ?>">
      <div class="alumni-card-top">
        <div class="alumni-avatar">
          <?php if ($a['foto_profil'] && file_exists("uploads/foto_profil/".$a['foto_profil'])): ?>
          <img src="uploads/foto_profil/<?= htmlspecialchars($a['foto_profil']) ?>" alt="Foto">
          <?php else: ?>
          <div class="avatar-letter"><?= strtoupper(substr($a['nama'], 0, 1)) ?></div>
          <?php endif; ?>
        </div>
        <?php if ($a['id_alumni'] == $id_alumni): ?>
        <span class="my-badge">Saya</span>
        <?php endif; ?>
      </div>
      <div class="alumni-card-body">
        <h4><?= htmlspecialchars($a['nama']) ?></h4>
        <p class="alumni-nis"><code><?= htmlspecialchars($a['nis']) ?></code></p>
        <span class="tag"><?= htmlspecialchars($a['jurusan']) ?></span>
        <div class="alumni-meta">
          <span>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            <?= $a['angkatan'] ?>
          </span>
          <?php if ($a['pekerjaan']): ?>
          <span>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
            <?= htmlspecialchars($a['pekerjaan']) ?>
          </span>
          <?php endif; ?>
        </div>
        <?php if ($a['id_alumni'] == $id_alumni): ?>
        <a href="profile.php" class="btn-sm btn-edit" style="margin-top:10px;display:inline-flex">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
          Edit Data Saya
        </a>
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>
</body>
</html>
