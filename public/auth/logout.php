<?php
require __DIR__ . '/../src/koneksi.php'; // Koneksi sekaligus memulai sesi database
session_destroy();
header('Location: login.php');
exit;
?>
