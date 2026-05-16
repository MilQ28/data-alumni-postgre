<?php
// ==============================================================================
// KONEKSI DATABASE (koneksi.php)
// File ini berfungsi sebagai jembatan penghubung antara kode PHP dan database PostgreSQL (Supabase)
// ==============================================================================

$host     = 'db.fymxdzbuubvhbljowlul.supabase.co'; // Supabase Host
$port     = '5432';
$dbname   = 'postgres'; // Supabase Default Database
$username = 'postgres'; // Supabase Default User
$password = '3WHyMBpruKUSj0TL'; // Password dari user

// 1. Melakukan koneksi menggunakan fungsi pg_connect
$conn_string = "host={$host} port={$port} dbname={$dbname} user={$username} password={$password}";
$conn = pg_connect($conn_string);

// 2. Mengecek apakah koneksi berhasil atau gagal
if (!$conn) {
    die('Koneksi database gagal: ' . pg_last_error());
}

// 3. Menggunakan database untuk menyimpan sesi Vercel Serverless
require_once __DIR__ . '/session_handler.php';
?>
