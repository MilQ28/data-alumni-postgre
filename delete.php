<?php
require 'auth.php';
require 'koneksi.php';
requireAdmin();

$id = (int)($_GET['id'] ?? 0);
if ($id) {
    // Hapus foto jika ada
    $res = pg_query_params($conn, "SELECT foto_profil FROM alumni WHERE id_alumni=$1", array($id));
    $a = pg_fetch_assoc($res);

    if ($a && $a['foto_profil'] && file_exists("uploads/foto_profil/".$a['foto_profil'])) {
        unlink("uploads/foto_profil/".$a['foto_profil']);
    }

    // Set id_alumni di users menjadi null
    pg_query_params($conn, "UPDATE users SET id_alumni=NULL WHERE id_alumni=$1", array($id));

    pg_query_params($conn, "DELETE FROM alumni WHERE id_alumni=$1", array($id));
}
header('Location: dashboard_admin.php');
exit;
?>
