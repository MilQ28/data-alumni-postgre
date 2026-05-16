<?php
// ==============================================================================
// KONEKSI DATABASE (koneksi.php)
// Menggunakan Supabase Connection Pooler agar kompatibel dengan Vercel Serverless
// ob_start() menahan semua output ke browser sampai session siap (mencegah error headers)
// ==============================================================================

// Wajib dipanggil PERTAMA sebelum ada output apapun ke browser
if (!ob_get_level()) {
    ob_start();
}

$host     = 'aws-1-ap-southeast-1.pooler.supabase.com'; // Pooler Host (IPv4 compatible)
$port     = '6543';                                       // Pooler Port
$dbname   = 'postgres';
$username = 'postgres.fymxdzbuubvhbljowlul';             // Pooler Username
$password = '3WHyMBpruKUSj0TL';

// 1. Melakukan koneksi via Connection Pooler (PgBouncer)
$conn_string = "host={$host} port={$port} dbname={$dbname} user={$username} password={$password} sslmode=require";
$conn = pg_connect($conn_string);

// 2. Mengecek apakah koneksi berhasil atau gagal
if (!$conn) {
    die('Koneksi database gagal: ' . pg_last_error());
}

// 3. Menggunakan database untuk menyimpan sesi Vercel Serverless
require_once __DIR__ . '/session_handler.php';
?>
