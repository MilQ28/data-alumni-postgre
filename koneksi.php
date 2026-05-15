
<?php
$host     = 'localhost';
$dbname   = 'db_alumni';
$username = 'root';
$password = '';

$conn = mysqli_connect($host, $username, $password, $dbname);

if (!$conn) {
    die(json_encode(['error' => 'Koneksi database gagal: ' . mysqli_connect_error()]));
}

mysqli_set_charset($conn, 'utf8mb4');
?>
