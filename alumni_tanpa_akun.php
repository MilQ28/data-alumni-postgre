<?php require 'koneksi.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Alumni Belum Terdaftar — Alumni SMK</title>
<link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style/dashboard.css">
</head>
<body>
<?php
require 'auth.php';
requireAdmin();
include 'navbar.php';

$search = trim($_GET['search'] ?? '');

$sql = "SELECT a.id_alumni, a.nis, a.nama, a.jurusan, a.angkatan, a.email 
        FROM alumni a 
        LEFT JOIN users u ON a.id_alumni = u.id_alumni 
        WHERE u.id_alumni IS NULL";

$params = [];

if ($search) {
    $sql .= " AND (a.nama ILIKE $1 OR a.nis ILIKE $2 OR a.jurusan ILIKE $3)";
    $s = "%$search%";
    $params = [$s, $s, $s];
}

$sql .= " ORDER BY a.nama ASC";

if ($params) {
    $res = pg_query_params($conn, $sql, $params);
} else {
    $res = pg_query($conn, $sql);
}
$alumni = pg_fetch_all($res) ?: [];
?>

<div class="page-wrapper">
  <div class="page-header">
    <div>
      <h1 class="page-title">Alumni Belum Terdaftar</h1>
      <p class="page-sub">Daftar keseluruhan alumni yang belum memiliki akun pengguna</p>
    </div>
    <a href="dashboard_admin.php" class="btn-outline">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
      Kembali
    </a>
  </div>

  <div class="section-card">
    <form method="GET" class="search-form">
      <div class="input-wrapper">
        <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input type="text" name="search" placeholder="Cari nama, NIS, jurusan..." value="<?= htmlspecialchars($search) ?>">
      </div>
      <button type="submit" class="btn-primary">Cari</button>
      <?php if ($search): ?><a href="alumni_tanpa_akun.php" class="btn-outline">Reset</a><?php endif; ?>
    </form>
  </div>

  <div class="section-card">
    <div class="table-wrap">
      <table class="data-table">
        <thead>
          <tr>
            <th>#</th><th>NIS</th><th>Nama Alumni</th><th>Jurusan</th><th>Angkatan</th><th>Status</th><th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($alumni)): ?>
          <tr><td colspan="7" style="text-align:center;color:var(--text-muted);padding:40px">Tidak ada data alumni belum terdaftar.</td></tr>
          <?php else: ?>
          <?php foreach ($alumni as $i => $a): ?>
          <tr>
            <td><?= $i + 1 ?></td>
            <td><code><?= htmlspecialchars($a['nis'] ?? '—') ?></code></td>
            <td><strong><?= htmlspecialchars($a['nama'] ?? '—') ?></strong></td>
            <td><span class="tag"><?= htmlspecialchars($a['jurusan'] ?? '—') ?></span></td>
            <td><?= $a['angkatan'] ?? '—' ?></td>
            <td><span class="badge badge-amber">Belum Terdaftar</span></td>
            <td>
              <div class="action-btns">
                <a href="tambah_user.php?id_alumni=<?= $a['id_alumni'] ?>" class="btn-sm btn-primary">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
                  Buatkan Akun
                </a>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
</body>
</html>
