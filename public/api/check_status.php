<?php
require '../src/koneksi.php';

header('Content-Type: application/json');

$username = trim($_GET['username'] ?? '');

if (!$username) {
    echo json_encode(['error' => 'Username required']);
    exit;
}

$sql = "SELECT status FROM users WHERE username = $1";
$res = pg_query_params($conn, $sql, array($username));
$user = pg_fetch_assoc($res);

if (!$user) {
    echo json_encode(['error' => 'User not found']);
    exit;
}

echo json_encode(['status' => $user['status']]);
?>
