<?php
require '../src/koneksi.php';
require '../src/auth.php';

// Wajib login
if (!isLoggedIn()) {
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode(['error' => 'Belum login']);
    exit;
}

header('Content-Type: application/json');

// Ambil data alumni yang sudah diapprove
$sql = "SELECT a.* FROM alumni a JOIN users u ON a.id_alumni = u.id_alumni WHERE u.status = 'approved' ORDER BY a.created_at DESC";
$res = pg_query($conn, $sql);
$alumniList = pg_fetch_all($res) ?: [];

// Bersihkan data sensitif jika ada (email, no_hp mungkin boleh tampil di dashboard user?)
// Di dashboard_user.php yang asli, email dan no_hp tidak ditampilkan di grid, tapi ada di detail atau query.
// Mari kita tetap kirim apa yang dibutuhkan saja untuk meminimalkan ukuran data.
$cleanedList = [];
foreach ($alumniList as $a) {
    $cleanedList[] = [
        'id_alumni' => $a['id_alumni'],
        'nis' => $a['nis'],
        'nama' => $a['nama'],
        'jurusan' => $a['jurusan'],
        'angkatan' => $a['angkatan'],
        'pekerjaan' => $a['pekerjaan'],
        'perusahaan' => $a['perusahaan'],
        'foto_profil' => $a['foto_profil']
    ];
}

echo json_encode($cleanedList);
?>
