<?php
require '../src/koneksi.php';
require '../src/auth.php';

if (!isAdmin()) {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['error' => 'Akses ditolak']);
    exit;
}

header('Content-Type: application/json');

// Hitung total alumni
$res = pg_query($conn, "SELECT COUNT(*) FROM alumni");
$totalAlumni = pg_fetch_row($res)[0];

// Hitung total user
$res = pg_query($conn, "SELECT COUNT(*) FROM users WHERE role='user'");
$totalUsers = pg_fetch_row($res)[0];

// Hitung pending
$res = pg_query($conn, "SELECT COUNT(*) FROM users WHERE status='pending'");
$pending = pg_fetch_row($res)[0];

echo json_encode([
    'totalAlumni' => (int)$totalAlumni,
    'totalUsers' => (int)$totalUsers,
    'pending' => (int)$pending
]);
?>
