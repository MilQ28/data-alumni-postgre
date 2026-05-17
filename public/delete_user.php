<?php
require 'src/auth.php';
require __DIR__ . '/src/koneksi.php';
requireAdmin();

$action = $_GET['action'] ?? '';
$id     = (int)($_GET['id'] ?? 0);

switch ($action) {
    case 'approve':
        pg_query_params($conn, "UPDATE users SET status='approved' WHERE user_id=$1", array($id));
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'dashboard_admin.php'));
        break;

    case 'reject':
        pg_query_params($conn, "UPDATE users SET status='rejected' WHERE user_id=$1", array($id));
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'users.php'));
        break;

    case 'delete':
        if (!isSuperAdmin()) { header('Location: users.php'); exit; }
        // Cari id_alumni terhubung
        $res = pg_query_params($conn, "SELECT id_alumni FROM users WHERE user_id=$1", array($id));
        $u = pg_fetch_assoc($res);

        pg_query_params($conn, "DELETE FROM users WHERE user_id=$1", array($id));
        // Jangan hapus alumni datanya, hanya putus relasi
        header('Location: users.php');
        break;

    default:
        header('Location: dashboard_admin.php');
}
exit;
?>
