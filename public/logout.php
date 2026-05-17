<?php
require 'src/koneksi.php'; // Koneksi sekaligus memulai sesi database
session_destroy();
header('Location: login.php');
exit;
?>
