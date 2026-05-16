<?php
require ("koneksi.php");

echo password_hash('syamil', PASSWORD_DEFAULT);
echo "\n";

echo password_hash('admin', PASSWORD_DEFAULT);
echo "\n";

echo password_hash('superadmin', PASSWORD_DEFAULT);
