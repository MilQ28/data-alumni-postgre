<?php
// navbar.php - included on every page
$current = basename($_SERVER['PHP_SELF']);
$prefix = $path_prefix ?? '';
?>
<nav class="navbar">
  <div class="nav-brand">
    <!-- Logo SMK Telkom Lampung -->
    <img src="<?= $prefix ?>assets/telkom-logo-removebg.png" alt="Logo SMK Telkom Lampung">
    <span>SMK Telkom Lampung</span>
  </div>

  <div class="nav-links">
    <?php if (isAdmin()): ?>
    <a href="<?= $prefix ?>admin/dashboard_admin.php" class="nav-link <?= $current === 'dashboard_admin.php' ? 'active' : '' ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
      </svg>
      <span>Dashboard</span>
    </a>
    <a href="<?= $prefix ?>admin/users.php" class="nav-link <?= $current === 'users.php' ? 'active' : '' ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
      </svg>
      <span>Pengguna</span>
    </a>
    <a href="<?= $prefix ?>admin/tambah_user.php" class="nav-link <?= $current === 'tambah_user.php' ? 'active' : '' ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/>
      </svg>
      <span>Tambah Alumni</span>
    </a>
    <a href="<?= $prefix ?>admin/tambah.php" class="nav-link <?= $current === 'tambah.php' ? 'active' : '' ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/>
      </svg>
      <span>Tambah Data</span>
    </a>
    <?php else: ?>
    <a href="<?= $prefix ?>dashboard_user.php" class="nav-link <?= $current === 'dashboard_user.php' ? 'active' : '' ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
      </svg>
      <span>Beranda</span>
    </a>
    <?php endif; ?>
  </div>

  <div class="nav-user">
    <a href="<?= $prefix ?>profile.php" class="nav-profile <?= $current === 'profile.php' ? 'active' : '' ?>">
      <?php
      $foto = '';
      if (isset($_SESSION['id_alumni'])) {
          $res = pg_query_params($conn, "SELECT foto_profil, nama FROM alumni WHERE id_alumni = $1", array($_SESSION['id_alumni']));
          $row = pg_fetch_assoc($res);
          $foto = $row['foto_profil'] ?? '';
          $namaAlumni = $row['nama'] ?? $_SESSION['username'];
      } else {
          $namaAlumni = $_SESSION['username'];
      }
      if ($foto && file_exists($prefix . "uploads/foto_profil/$foto")):
      ?>
      <img src="<?= $prefix ?>uploads/foto_profil/<?= htmlspecialchars($foto) ?>" alt="Foto Profil" class="avatar-img">
      <?php else: ?>
      <div class="avatar-placeholder">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
        </svg>
      </div>
      <?php endif; ?>
      <div class="nav-user-info">
        <span class="nav-username"><?= htmlspecialchars($namaAlumni) ?></span>
        <span class="nav-role"><?= ucfirst($_SESSION['role']) ?></span>
      </div>
    </a>
    <a href="<?= $prefix ?>auth/logout.php" class="nav-logout" title="Logout">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>
      </svg>
    </a>
  </div>
</nav>
