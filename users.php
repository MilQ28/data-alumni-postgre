<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manajemen Pengguna — Alumni SMK</title>
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

$search = trim($_GET['search'] ?? '');
$status = trim($_GET['status'] ?? '');

$where  = ["u.role = 'user'"];
$params = [];
$paramIndex = 1;

if ($search) {
    $where[] = "(u.username ILIKE $$paramIndex OR a.nama ILIKE $" . ($paramIndex+1) . " OR a.nis ILIKE $" . ($paramIndex+2) . ")";
    $s = "%$search%";
    $params = array_merge($params, [$s, $s, $s]);
    $paramIndex += 3;
}
if ($status) {
    $where[] = "u.status = $$paramIndex";
    $params[] = $status;
    $paramIndex++;
}

$sql = "SELECT u.*, a.nama, a.nis, a.jurusan, a.angkatan, a.email as email_alumni
        FROM users u
        LEFT JOIN alumni a ON u.id_alumni = a.id_alumni
        WHERE " . implode(" AND ", $where) . "
        ORDER BY u.created_at DESC";

if ($params) {
    $res = pg_query_params($conn, $sql, $params);
} else {
    $res = pg_query($conn, $sql);
}
$users = pg_fetch_all($res) ?: [];
?>

<div class="page-wrapper">
  <div class="page-header">
    <div>
      <h1 class="page-title">Manajemen Pengguna</h1>
      <p class="page-sub">Kelola akun alumni yang terdaftar</p>
    </div>
    <div class="header-actions">
      <a href="dashboard_admin.php" class="btn-outline">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
        Kembali
      </a>
      <a href="tambah_user.php" class="btn-primary">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Tambah Pengguna
      </a>
    </div>
  </div>

  <div class="section-card">
    <form method="GET" class="search-form">
      <div class="input-wrapper">
        <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input type="text" name="search" placeholder="Cari username, nama, NIS..." value="<?= htmlspecialchars($search) ?>">
      </div>
      <div class="input-wrapper">
        <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        <select name="status">
          <option value="">Semua Status</option>
          <option value="pending"  <?= $status==='pending'  ?'selected':''?>>Pending</option>
          <option value="approved" <?= $status==='approved' ?'selected':''?>>Disetujui</option>
          <option value="rejected" <?= $status==='rejected' ?'selected':''?>>Ditolak</option>
        </select>
      </div>
      <button type="submit" class="btn-primary">Filter</button>
      <?php if ($search || $status): ?><a href="users.php" class="btn-outline">Reset</a><?php endif; ?>
    </form>
  </div>

  <div class="section-card">
    <div class="table-wrap">
      <table class="data-table">
        <thead>
          <tr>
            <th>#</th><th>Username</th><th>Nama Alumni</th><th>NIS</th>
            <th>Jurusan</th><th>Angkatan</th><th>Status</th><th>Terdaftar</th><th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($users)): ?>
          <tr><td colspan="9" style="text-align:center;color:var(--text-muted);padding:40px">Tidak ada data pengguna.</td></tr>
          <?php else: ?>
          <?php foreach ($users as $i => $u): ?>
          <tr>
            <td><?= $i + 1 ?></td>
            <td><strong><?= htmlspecialchars($u['username']) ?></strong></td>
            <td><?= htmlspecialchars($u['nama'] ?? '—') ?></td>
            <td><code><?= htmlspecialchars($u['nis'] ?? '—') ?></code></td>
            <td><?= htmlspecialchars($u['jurusan'] ?? '—') ?></td>
            <td><?= $u['angkatan'] ?? '—' ?></td>
            <td>
              <?php
              $badges = ['pending'=>'badge-amber','approved'=>'badge-green','rejected'=>'badge-red'];
              $labels = ['pending'=>'Menunggu','approved'=>'Aktif','rejected'=>'Ditolak'];
              $st = $u['status'];
              ?>
              <span class="badge <?= $badges[$st] ?? '' ?>"><?= $labels[$st] ?? $st ?></span>
            </td>
            <td><?= date('d M Y', strtotime($u['created_at'])) ?></td>
            <td>
              <div class="action-btns">
                <?php if ($u['status'] === 'pending'): ?>
                <a href="delete_user.php?action=approve&id=<?= $u['user_id'] ?>" class="btn-sm btn-success" onclick="return confirm('Setujui akun ini?')">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                  Setujui
                </a>
                <a href="delete_user.php?action=reject&id=<?= $u['user_id'] ?>" class="btn-sm btn-danger" onclick="return confirm('Tolak akun ini?')">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                  Tolak
                </a>
                <?php elseif ($u['id_alumni']): ?>
                <a href="profile.php?id=<?= $u['id_alumni'] ?>" class="btn-sm btn-edit">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                  Lihat
                </a>
                <?php endif; ?>
                <?php if (isSuperAdmin()): ?>
                <a href="delete_user.php?action=delete&id=<?= $u['user_id'] ?>" class="btn-sm btn-danger" onclick="return confirm('Hapus pengguna ini secara permanen?')">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                  Hapus
                </a>
                <?php endif; ?>
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
