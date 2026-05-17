<?php
require __DIR__ . '/src/koneksi.php'; // Koneksi sekaligus memulai sesi database
if (isset($_SESSION['user_id'])) {
    $role = $_SESSION['role'];
    if ($role === 'superadmin' || $role === 'admin') {
        header('Location: admin/dashboard_admin.php');
    } else {
        header('Location: dashboard_user.php');
    }
} else {
    header('Location: auth/login.php');
}
exit;
?>
