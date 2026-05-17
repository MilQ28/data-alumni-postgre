<?php
require '../src/koneksi.php';
require '../src/auth.php';

if (!isAdmin()) {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['error' => 'Akses ditolak']);
    exit;
}

header('Content-Type: application/json');

// Ambil data pending
$res = pg_query($conn, "SELECT u.user_id, u.username, a.nama, a.nis, a.jurusan, a.angkatan FROM users u LEFT JOIN alumni a ON u.id_alumni=a.id_alumni WHERE u.status='pending' ORDER BY u.created_at DESC");
$pendingUsers = pg_fetch_all($res) ?: [];

echo json_encode([
    'count' => count($pendingUsers),
    'users' => $pendingUsers
]);
?>
