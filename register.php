<?php require 'koneksi.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Daftar Akun — Portal Alumni SMK Telkom Lampung</title>
<link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style/index.css">
</head>
<body>
<?php
if (isset($_SESSION['user_id'])) {
    header('Location: index.php'); exit;
}

$error   = '';
$success = '';

$jurusan_list = [
  'Rekayasa Perangkat Lunak',
  'Teknik Komputer dan Jaringan',
  'Teknik Jaringan Akses dan Telekomunikasi',
  'Animasi',
];
// ==============================================================================
// PROSES PENDAFTARAN ALUMNI
// ==============================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Ambil data dasar akun
    $jenis_daftar = $_POST['jenis_daftar'] ?? 'baru'; // Cek apakah dia alumni baru atau sudah ada datanya
    $username   = trim($_POST['username']   ?? '');
    $password   = trim($_POST['password']   ?? '');
    $confirm_pw = trim($_POST['confirm_pw'] ?? '');

    // 2. Validasi Input Dasar
    if (!$username || !$password) {
        $error = 'Username dan password wajib diisi.';
    } elseif ($password !== $confirm_pw) {
        $error = 'Password dan konfirmasi password tidak cocok.';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter.';
    } else {
        // 3. Cek apakah username sudah ada yang pakai
        $sql = "SELECT user_id FROM users WHERE username = $1";
        $res = pg_query_params($conn, $sql, array($username));

        if (pg_fetch_assoc($res)) {
            $error = 'Username sudah digunakan.';
        } else {
            // 4. Proses berdasarkan jenis pendaftaran
            if ($jenis_daftar === 'lama') {
                // JIKA ALUMNI LAMA (Datanya sudah diinputkan oleh Admin sebelumnya)
                $id_alumni_pilihan = $_POST['id_alumni_pilihan'] ?? '';
                if (!$id_alumni_pilihan) {
                    $error = 'Silakan pilih nama Anda dari daftar alumni.';
                } else {
                    // Pastikan nama yang dipilih benar-benar valid dan belum punya akun
                    $sqlCek = "SELECT id_alumni FROM alumni WHERE id_alumni = $1 AND id_alumni NOT IN (SELECT id_alumni FROM users WHERE id_alumni IS NOT NULL)";
                    $resCek = pg_query_params($conn, $sqlCek, array($id_alumni_pilihan));
                    if (!pg_fetch_assoc($resCek)) {
                        $error = 'Data alumni tidak valid atau sudah memiliki akun.';
                    }

                    // Jika lolos pengecekan, buatkan akun user-nya
                    if (!$error) {
                        $hashed = password_hash($password, PASSWORD_DEFAULT); // Enkripsi password
                        $sql2 = "INSERT INTO users (username, password, role, id_alumni, status) VALUES ($1,$2,'user',$3,'pending')";
                        if (pg_query_params($conn, $sql2, array($username, $hashed, $id_alumni_pilihan))) {
                            $success = true; // Berhasil!
                        } else {
                            $error = 'Terjadi kesalahan saat membuat akun. Silakan coba lagi.';
                        }
                    }
                }
            } else {
                // JIKA ALUMNI BARU (Datanya belum ada di sistem sama sekali)
                // Ambil semua data biodata dari form
                $nis        = trim($_POST['nis']        ?? '');
                $nama       = trim($_POST['nama']       ?? '');
                $angkatan   = trim($_POST['angkatan']   ?? '');
                $jurusan    = trim($_POST['jurusan']    ?? '');
                $email      = trim($_POST['email']      ?? '');
                $no_hp      = trim($_POST['no_hp']      ?? '');
                $pekerjaan  = trim($_POST['pekerjaan']  ?? '');
                $perusahaan = trim($_POST['perusahaan'] ?? '');
                $alamat     = trim($_POST['alamat']     ?? '');

                // Validasi data biodata
                if (!$nis || !$nama || !$angkatan || !$jurusan || !$email || !$no_hp) {
                    $error = 'Harap lengkapi semua field alumni yang wajib diisi.';
                } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $error = 'Format email tidak valid.';
                } elseif ($angkatan < 2000 || $angkatan > date('Y')) {
                    $error = 'Tahun angkatan tidak valid.';
                } else {
                    // Cek apakah email sudah pernah didaftarkan
                    $sqlEmail = "SELECT id_alumni FROM alumni WHERE email = $1";
                    $res = pg_query_params($conn, $sqlEmail, array($email));

                    if (pg_fetch_assoc($res)) {
                        $error = 'Email ini sudah terdaftar di data alumni.';
                    } else {
                        // Jika aman, masukkan data biodata ke tabel `alumni`
                        $sqlInsert = "INSERT INTO alumni (nis, nama, angkatan, jurusan, email, no_hp, pekerjaan, perusahaan, alamat) VALUES ($1,$2,$3,$4,$5,$6,$7,$8,$9) RETURNING id_alumni";
                        $resInsert = pg_query_params($conn, $sqlInsert, array($nis, $nama, $angkatan, $jurusan, $email, $no_hp, $pekerjaan, $perusahaan, $alamat));

                        if ($resInsert) {
                            // Ambil ID alumni yang baru saja dibuat
                            $row = pg_fetch_assoc($resInsert);
                            $id_alumni = $row['id_alumni'];

                            // Lalu buatkan akun user-nya di tabel `users` (terhubung dengan id_alumni tadi)
                            $hashed = password_hash($password, PASSWORD_DEFAULT);
                            $sql2 = "INSERT INTO users (username, password, role, id_alumni, status) VALUES ($1,$2,'user',$3,'pending')";

                            if (pg_query_params($conn, $sql2, array($username, $hashed, $id_alumni))) {
                                $success = true; // Pendaftaran selesai!
                            } else {
                                $error = 'Terjadi kesalahan saat membuat akun.';
                            }
                        } else {
                            $error = 'Terjadi kesalahan. Silakan coba lagi.';
                        }
                    }
                }
            }
        }
    }
}

// Ambil data alumni yang belum memiliki akun untuk pilihan dropdown
$resAlumni = pg_query($conn, "SELECT id_alumni, nis, nama FROM alumni WHERE id_alumni NOT IN (SELECT id_alumni FROM users WHERE id_alumni IS NOT NULL) ORDER BY nama ASC");
$alumniTanpaAkun = pg_fetch_all($resAlumni) ?: [];
?>
<div class="auth-bg register-bg">
  <div class="auth-left">
    <div class="top-content">
      <div class="brand-block">
        <!-- Logo sekolah. Ubah src jika ingin ganti gambar logo -->
        <div class="brand-logo">
          <img src="assets/logo.png" alt="Logo SMK Telkom Lampung">
        </div>
        <h1 class="brand-name">SMK Telkom Lampung</h1>
        <p class="brand-tagline">Daftarkan akun alumni Anda di portal resmi sekolah</p>
      </div>
      
      <div class="reg-steps" style="margin-top: 32px;">
        <div class="step active"><div class="step-num">1</div><div class="step-text">Data Akun</div></div>
        <div class="step-line"></div>
        <div class="step active"><div class="step-num">2</div><div class="step-text">Data Alumni</div></div>
        <div class="step-line"></div>
        <div class="step"><div class="step-num">3</div><div class="step-text">Verifikasi Admin</div></div>
      </div>
    </div>
    <div class="auth-decoration">
      <div class="deco-circle c1"></div>
      <div class="deco-circle c2"></div>
      <div class="deco-circle c3"></div>
    </div>
    <div class="auth-quote"><p>"Bersama alumni, membangun nama SMK Telkom Lampung di tingkat nasional."</p></div>
  </div>

  <div class="auth-right">
    <div class="auth-card auth-card-wide">
      <?php if ($success): ?>
      <div class="success-screen">
        <div class="success-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
          </svg>
        </div>
        <h2>Pendaftaran Berhasil!</h2>
        <p>Data alumni Anda telah berhasil didaftarkan. Akun Anda sedang <strong>menunggu verifikasi</strong> dari administrator sekolah.</p>
        <p class="success-note">Anda akan mendapat konfirmasi setelah akun diverifikasi. Harap bersabar.</p>
        <a href="login.php" class="btn-primary">Kembali ke Login</a>
      </div>

      <?php else: ?>
      <div style="margin-bottom: 20px;">
        <a href="login.php" style="display:inline-flex; align-items:center; gap:6px; color:var(--text-muted); text-decoration:none; font-size:14px; font-weight:500; transition:color .2s;">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px; height:16px;"><polyline points="15 18 9 12 15 6"/></svg>
          Kembali ke Login
        </a>
      </div>
      <div class="auth-card-header">
        <h2>Daftar sebagai Alumni</h2>
        <p>Isi data lengkap Anda untuk verifikasi keanggotaan</p>
      </div>

      <?php if ($error): ?>
      <div class="alert alert-error">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
        <?= htmlspecialchars($error) ?>
      </div>
      <?php endif; ?>

      <form method="POST" class="auth-form" id="registerForm">

        <div class="form-section-title" style="border-bottom:none; margin-bottom:4px;">
          Pilih Jenis Pendaftaran
        </div>
        <div class="type-selector-grid">
          <label class="type-card">
            <input type="radio" name="jenis_daftar" value="baru" checked onchange="toggleForm()">
            <div class="type-content">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
              <span class="type-title">Alumni Baru</span>
              <span class="type-desc">Belum terdaftar. Isi form data alumni lengkap.</span>
            </div>
          </label>
          <label class="type-card" id="cardLama">
            <input type="radio" name="jenis_daftar" value="lama" onchange="toggleForm()" <?= (isset($_POST['jenis_daftar']) && $_POST['jenis_daftar'] == 'lama') ? 'checked' : '' ?>>
            <div class="type-content">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><polyline points="16 11 18 13 22 9"/></svg>
              <span class="type-title">Sudah Terdaftar</span>
              <span class="type-desc">Cukup tautkan nama Anda dan buat kredensial.</span>
            </div>
          </label>
        </div>

        <div class="form-section-title">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
          </svg>
          Informasi Akun
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Username <span class="req">*</span></label>
            <div class="input-wrapper">
              <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
              <input type="text" name="username" placeholder="username unik Anda" required value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
            </div>
          </div>
          <div class="form-group">
            <label>Password <span class="req">*</span></label>
            <div class="input-wrapper">
              <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
              <input type="password" name="password" placeholder="Min. 6 karakter" required>
            </div>
          </div>
          <div class="form-group">
            <label>Konfirmasi Password <span class="req">*</span></label>
            <div class="input-wrapper">
              <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4m6 2a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/></svg>
              <input type="password" name="confirm_pw" placeholder="Ulangi password" required>
            </div>
          </div>
        </div>

        <!-- Section: Alumni Sudah Terdaftar -->
        <div id="sectionAlumniLama" class="form-section-animated" style="display:none;">
          <div class="form-section-title">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><polyline points="16 11 18 13 22 9"/></svg>
            Pilih Data Alumni Anda
          </div>
          <div class="form-group">
            <label>Nama Alumni <span class="req">*</span></label>
            <div class="input-wrapper">
              <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
              <select name="id_alumni_pilihan" id="id_alumni_pilihan">
                <option value="">-- Cari Nama Anda --</option>
                <?php foreach ($alumniTanpaAkun as $a): ?>
                <option value="<?= $a['id_alumni'] ?>" <?= (isset($_POST['id_alumni_pilihan']) && $_POST['id_alumni_pilihan'] == $a['id_alumni']) ? 'selected' : '' ?>><?= htmlspecialchars($a['nama']) ?> (<?= htmlspecialchars($a['nis']) ?>)</option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
        </div>

        <!-- Section: Alumni Baru -->
        <div id="sectionAlumniBaru" class="form-section-animated">
          <div class="form-section-title">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2z"/></svg>
            Data Alumni (untuk Verifikasi)
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>NIS / No. Induk <span class="req">*</span></label>
              <div class="input-wrapper">
                <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                <input type="text" name="nis" placeholder="Nomor Induk Siswa" required value="<?= htmlspecialchars($_POST['nis'] ?? '') ?>">
              </div>
            </div>
            <div class="form-group">
              <label>Nama Lengkap <span class="req">*</span></label>
              <div class="input-wrapper">
                <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                <input type="text" name="nama" placeholder="Nama sesuai ijazah" required value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>">
              </div>
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Angkatan / Tahun Lulus <span class="req">*</span></label>
              <div class="input-wrapper">
                <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                <input type="number" name="angkatan" placeholder="Contoh: 2022" min="2000" max="<?= date('Y') ?>" required value="<?= htmlspecialchars($_POST['angkatan'] ?? '') ?>">
              </div>
            </div>
            <div class="form-group">
              <label>Jurusan <span class="req">*</span></label>
              <div class="input-wrapper">
                <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
                <select name="jurusan" required>
                  <option value="">-- Pilih Jurusan --</option>
                  <?php foreach ($jurusan_list as $j): ?>
                  <option value="<?= htmlspecialchars($j) ?>" <?= (($_POST['jurusan'] ?? '') === $j) ? 'selected' : '' ?>><?= htmlspecialchars($j) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Email Aktif <span class="req">*</span></label>
              <div class="input-wrapper">
                <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                <input type="email" name="email" placeholder="email@aktif.com" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
              </div>
            </div>
            <div class="form-group">
              <label>No. HP / WhatsApp <span class="req">*</span></label>
              <div class="input-wrapper">
                <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.18 2 2 0 0 1 3.59 1h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.73a16 16 0 0 0 6 6l.91-.91a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                <input type="text" name="no_hp" placeholder="08xxxxxxxxxx" required value="<?= htmlspecialchars($_POST['no_hp'] ?? '') ?>">
              </div>
            </div>
          </div>
          <div class="form-section-title">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
            Informasi Karir (Opsional)
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Pekerjaan Saat Ini</label>
              <div class="input-wrapper">
                <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                <input type="text" name="pekerjaan" placeholder="Profesi / jabatan Anda" value="<?= htmlspecialchars($_POST['pekerjaan'] ?? '') ?>">
              </div>
            </div>
            <div class="form-group">
              <label>Perusahaan / Instansi</label>
              <div class="input-wrapper">
                <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                <input type="text" name="perusahaan" placeholder="Nama perusahaan / instansi" value="<?= htmlspecialchars($_POST['perusahaan'] ?? '') ?>">
              </div>
            </div>
          </div>
          <div class="form-group">
            <label>Alamat Sekarang</label>
            <div class="input-wrapper">
              <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="top:14px"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
              <textarea name="alamat" placeholder="Jl. ..." rows="3"><?= htmlspecialchars($_POST['alamat'] ?? '') ?></textarea>
            </div>
          </div>
        </div> <!-- End sectionAlumniBaru -->


        <div class="info-box">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          <p>Pendaftaran Anda akan diverifikasi oleh admin sekolah. Akun baru dapat digunakan setelah disetujui.</p>
        </div>

        <button type="submit" class="btn-primary btn-full">
          Kirim Pendaftaran
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
        </button>
      </form>

      <div class="auth-footer">
        <p>Sudah punya akun? <a href="login.php">Masuk di sini</a></p>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>
<script>
function toggleForm() {
    const jenis = document.querySelector('input[name="jenis_daftar"]:checked').value;
    const sBaru = document.getElementById('sectionAlumniBaru');
    const sLama = document.getElementById('sectionAlumniLama');
    
    const baruInputs = sBaru.querySelectorAll('input, select, textarea');
    const lamaSelect = document.getElementById('id_alumni_pilihan');

    if (jenis === 'lama') {
        sBaru.style.display = 'none';
        sLama.style.display = 'block';
        baruInputs.forEach(el => {
            if(el.name !== 'pekerjaan' && el.name !== 'perusahaan' && el.name !== 'alamat') {
               el.removeAttribute('required');
            }
        });
        lamaSelect.setAttribute('required', 'required');
    } else {
        sBaru.style.display = 'block';
        sLama.style.display = 'none';
        baruInputs.forEach(el => {
            if(el.name !== 'pekerjaan' && el.name !== 'perusahaan' && el.name !== 'alamat') {
               el.setAttribute('required', 'required');
            }
        });
        lamaSelect.removeAttribute('required');
    }
}
// Initialize form state
toggleForm();
</script>
<script src="assets/bg-slideshow.js"></script>
</body>
</html>
