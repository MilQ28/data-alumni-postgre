<?php 
require 'src/koneksi.php'; 
require 'src/auth.php';
requireAdmin(); 
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard Admin — Portal Alumni SMK Telkom Lampung</title>
<link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style/dashboard.css">
</head>
<body>
<?php
include 'src/navbar.php';

// ==============================================================================
// MENGAMBIL DATA STATISTIK UNTUK DASHBOARD
// ==============================================================================

// 1. Hitung total semua alumni yang ada di database
$res = pg_query($conn, "SELECT COUNT(*) FROM alumni");
$totalAlumni = pg_fetch_row($res)[0]; // Ambil angka hasil hitungan

// 2. Hitung total akun dengan role 'user' (alumni biasa)
$res = pg_query($conn, "SELECT COUNT(*) FROM users WHERE role='user'");
$totalUsers = pg_fetch_row($res)[0];

// 3. Hitung akun yang statusnya masih 'pending' (menunggu persetujuan admin)
$res = pg_query($conn, "SELECT COUNT(*) FROM users WHERE status='pending'");
$pending = pg_fetch_row($res)[0];

// 4. Ambil statistik 5 jurusan dengan alumni terbanyak
// GROUP BY jurusan = kelompokkan berdasarkan jurusan
// ORDER BY total DESC = urutkan dari yang terbanyak
$res = pg_query($conn, "SELECT jurusan, COUNT(*) as total FROM alumni GROUP BY jurusan ORDER BY total DESC LIMIT 5");
$jurusanStat = pg_fetch_all($res) ?: [];

// 5. Ambil statistik alumni per angkatan (6 angkatan terakhir)
$res = pg_query($conn, "SELECT angkatan, COUNT(*) as total FROM alumni GROUP BY angkatan ORDER BY angkatan DESC LIMIT 6");
$angkatanStat = pg_fetch_all($res) ?: [];

// 6. Ambil 8 data alumni yang paling baru ditambahkan
$res = pg_query($conn, "SELECT * FROM alumni ORDER BY created_at DESC LIMIT 8");
$recentAlumni = pg_fetch_all($res) ?: [];

// 7. Ambil data akun yang masih pending untuk ditampilkan di tabel approval
// LEFT JOIN menghubungkan tabel users dan alumni berdasarkan id_alumni
$res = pg_query($conn, "SELECT u.*, a.nama, a.nis, a.jurusan, a.angkatan FROM users u LEFT JOIN alumni a ON u.id_alumni=a.id_alumni WHERE u.status='pending' ORDER BY u.created_at DESC");
$pendingUsers = pg_fetch_all($res) ?: [];

// 8. Hitung berapa banyak data alumni yang BELUM punya akun
// Kondisi u.id_alumni IS NULL berarti data alumni tersebut tidak ditemukan di tabel users
$resBelum = pg_query($conn, "SELECT COUNT(*) FROM alumni a LEFT JOIN users u ON a.id_alumni = u.id_alumni WHERE u.id_alumni IS NULL");
$totalBelumTerdaftar = pg_fetch_row($resBelum)[0];

// 9. Ambil 5 data alumni yang belum punya akun
$resDataBelum = pg_query($conn, "SELECT a.id_alumni, a.nis, a.nama, a.jurusan, a.angkatan FROM alumni a LEFT JOIN users u ON a.id_alumni = u.id_alumni WHERE u.id_alumni IS NULL ORDER BY a.nama ASC LIMIT 5");
$alumniBelumTerdaftar = pg_fetch_all($resDataBelum) ?: [];
?>

<div class="page-wrapper">
  <div class="page-header">
    <div>
      <h1 class="page-title">Dashboard</h1>
      <p class="page-sub">Selamat datang, <?= htmlspecialchars($_SESSION['username']) ?> — <?= ucfirst($_SESSION['role']) ?></p>
    </div>
    <div class="header-actions">
      <a href="tambah.php" class="btn-primary">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Tambah Alumni
      </a>
    </div>
  </div>

  <!-- Stats -->
  <div class="stats-grid">
    <div class="stat-card stat-blue">
      <div class="stat-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>
          <path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
        </svg>
      </div>
      <div class="stat-info">
        <span class="stat-value" id="total-alumni"><?= number_format($totalAlumni) ?></span>
        <span class="stat-label">Total Alumni</span>
      </div>
    </div>
    <div class="stat-card stat-green">
      <div class="stat-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
        </svg>
      </div>
      <div class="stat-info">
        <span class="stat-value" id="total-users"><?= number_format($totalUsers) ?></span>
        <span class="stat-label">Pengguna Aktif</span>
      </div>
    </div>
    <div class="stat-card stat-amber <?= $pending > 0 ? 'stat-pulse' : '' ?>">
      <div class="stat-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
        </svg>
      </div>
      <div class="stat-info">
        <span class="stat-value" id="total-pending"><?= number_format($pending) ?></span>
        <span class="stat-label">Menunggu Verifikasi</span>
      </div>
    </div>
    <div class="stat-card stat-purple">
      <div class="stat-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/>
        </svg>
      </div>
      <div class="stat-info">
        <span class="stat-value"><?= count($jurusanStat) ?></span>
        <span class="stat-label">Jurusan Terdaftar</span>
      </div>
    </div>
  </div>

  <?php if ($pending > 0): ?>
  <!-- Pending Verifikasi -->
  <div class="section-card">
    <div class="section-head">
      <h2>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
        </svg>
        Menunggu Verifikasi
        <span class="badge badge-amber" id="badge-pending"><?= $pending ?></span>
      </h2>
    </div>
    <div class="table-wrap">
      <table class="data-table">
        <thead>
          <tr>
            <th>NIS</th><th>Nama</th><th>Jurusan</th><th>Angkatan</th><th>Username</th><th>Aksi</th>
          </tr>
        </thead>
        <tbody id="pending-users-table">
          <?php foreach ($pendingUsers as $pu): ?>
          <tr>
            <td><code><?= htmlspecialchars($pu['nis'] ?? '-') ?></code></td>
            <td><?= htmlspecialchars($pu['nama'] ?? '-') ?></td>
            <td><?= htmlspecialchars($pu['jurusan'] ?? '-') ?></td>
            <td><?= htmlspecialchars($pu['angkatan'] ?? '-') ?></td>
            <td><?= htmlspecialchars($pu['username']) ?></td>
            <td>
              <div class="action-btns">
                <a href="delete_user.php?action=approve&id=<?= $pu['user_id'] ?>" class="btn-sm btn-success" onclick="return confirm('Setujui pendaftaran ini?')">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                  Setujui
                </a>
                <a href="delete_user.php?action=reject&id=<?= $pu['user_id'] ?>" class="btn-sm btn-danger" onclick="return confirm('Tolak pendaftaran ini?')">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                  Tolak
                </a>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php endif; ?>

  <?php if ($totalBelumTerdaftar > 0): ?>
  <!-- Alumni Belum Punya Akun -->
  <div class="section-card">
    <div class="section-head">
      <h2>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/>
        </svg>
        Alumni Belum Memiliki Akun
        <span class="badge badge-amber"><?= $totalBelumTerdaftar ?></span>
      </h2>
      <a href="alumni_tanpa_akun.php" class="btn-outline-sm">Lihat Semua</a>
    </div>
    <div class="table-wrap">
      <table class="data-table">
        <thead>
          <tr>
            <th>NIS</th><th>Nama</th><th>Jurusan</th><th>Angkatan</th><th>Status</th><th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($alumniBelumTerdaftar as $abt): ?>
          <tr>
            <td><code><?= htmlspecialchars($abt['nis'] ?? '-') ?></code></td>
            <td><?= htmlspecialchars($abt['nama'] ?? '-') ?></td>
            <td><span class="tag"><?= htmlspecialchars($abt['jurusan'] ?? '-') ?></span></td>
            <td><?= htmlspecialchars($abt['angkatan'] ?? '-') ?></td>
            <td><span class="badge badge-amber">Belum Terdaftar</span></td>
            <td>
              <div class="action-btns">
                <a href="tambah_user.php?id_alumni=<?= $abt['id_alumni'] ?>" class="btn-sm btn-primary">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
                  Buatkan Akun
                </a>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php endif; ?>

  <div class="two-col-grid">
    <!-- Jurusan Stats -->
    <div class="section-card">
      <div class="section-head">
        <h2>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/>
          </svg>
          Alumni per Jurusan
        </h2>
      </div>
      <div class="bar-chart">
        <?php
        $maxVal = max(array_column($jurusanStat, 'total') ?: [1]);
        foreach ($jurusanStat as $js):
          $pct = round(($js['total'] / $maxVal) * 100);
        ?>
        <div class="bar-item">
          <div class="bar-label"><?= htmlspecialchars($js['jurusan']) ?></div>
          <div class="bar-track">
            <div class="bar-fill" style="width:<?= $pct ?>%"></div>
          </div>
          <div class="bar-val"><?= $js['total'] ?></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Angkatan Stats -->
    <div class="section-card">
      <div class="section-head">
        <h2>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
          </svg>
          Alumni per Angkatan
        </h2>
      </div>
      <div class="angkatan-grid">
        <?php foreach ($angkatanStat as $as): ?>
        <div class="angkatan-card">
          <span class="angkatan-year"><?= $as['angkatan'] ?></span>
          <span class="angkatan-count"><?= $as['total'] ?> alumni</span>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- Recent Alumni -->
  <div class="section-card">
    <div class="section-head">
      <h2>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
        </svg>
        Alumni Terbaru
      </h2>
      <a href="users.php" class="btn-outline-sm">Lihat Semua</a>
    </div>
    <div class="table-wrap">
      <table class="data-table">
        <thead>
          <tr><th>NIS</th><th>Nama</th><th>Jurusan</th><th>Angkatan</th><th>Pekerjaan</th><th>Aksi</th></tr>
        </thead>
        <tbody>
          <?php foreach ($recentAlumni as $a): ?>
          <tr>
            <td><code><?= htmlspecialchars($a['nis']) ?></code></td>
            <td><?= htmlspecialchars($a['nama']) ?></td>
            <td><span class="tag"><?= htmlspecialchars($a['jurusan']) ?></span></td>
            <td><?= $a['angkatan'] ?></td>
            <td><?= htmlspecialchars($a['pekerjaan'] ?: '—') ?></td>
            <td>
              <div class="action-btns">
                <a href="edit.php?id=<?= $a['id_alumni'] ?>" class="btn-sm btn-edit">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                  Edit
                </a>
                <a href="delete.php?id=<?= $a['id_alumni'] ?>" class="btn-sm btn-danger" onclick="return confirm('Hapus data alumni ini?')">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                  Hapus
                </a>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<script src="js/bg-slideshow-dashboard.js"></script>
<script src="js/realtime-admin.js"></script>
</body>
</html>
