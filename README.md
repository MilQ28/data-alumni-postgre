# Portal Alumni SMK Telkom

[![Vercel Deployment](https://img.shields.io/badge/Deploy-Vercel-black?style=for-the-badge&logo=vercel)](https://vercel.com)
[![PHP Version](https://img.shields.io/badge/PHP-8.x-777BB4?style=for-the-badge&logo=php)](https://www.php.net/)
[![Database](https://img.shields.io/badge/Database-PostgreSQL-336791?style=for-the-badge&logo=postgresql)](https://www.postgresql.org/)

Sebuah platform web modern dan aman untuk pendataan, pencarian, dan pengelolaan data alumni SMK Telkom. Project ini dirancang dengan standar produksi, mengutamakan keamanan, performa serverless, dan desain yang sepenuhnya responsif.

---

## 📌 Tentang Project

Website Portal Alumni ini dibuat untuk menjembatani komunikasi antara sekolah dan alumni serta mempermudah pendataan karir alumni setelah lulus. Project ini mengimplementasikan arsitektur database modern menggunakan **Supabase (PostgreSQL)** dan dideploy secara serverless di **Vercel**.

### Target Pengguna
- **Alumni**: Mendaftar, mengisi data diri, riwayat pekerjaan, dan mencari alumni lain.
- **Admin**: Memverifikasi pendaftaran alumni dan mengelola data.
- **Superadmin**: Memiliki akses penuh terhadap manajemen pengguna dan sistem.

---

## ✨ Fitur Utama

### Keamanan (Production-Ready)
- [x] **CSRF Protection**: Token anti-pemalsuan request di setiap form POST.
- [x] **Session Security**: Session disimpan di database (PostgreSQL) untuk mendukung arsitektur Serverless Vercel.
- [x] **Password Hashing**: Menggunakan `password_hash()` dengan algoritma BCRYPT.
- [x] **SQL Injection Prevention**: 100% menggunakan *Prepared Statements* (`pg_query_params`).
- [x] **Path Protection**: Akses langsung ke folder inti (`src/`) diblokir via konfigurasi Vercel (403 Forbidden).

### Fungsionalitas
- [x] **Autentikasi Multi-Role**: Sistem login terpisah untuk Alumni, Admin, dan Superadmin.
- [x] **Manajemen Profil**: Unggah foto profil, edit data diri, dan status pekerjaan.
- [x] **Pencarian Responsif**: Fitur pencarian alumni berdasarkan nama atau jurusan dengan query *case-insensitive* (`ILIKE`).
- [x] **Persetujuan Akun**: Sistem antrean (pending/approved/rejected) untuk pendaftar baru oleh Admin.

---

## 🛠️ Tech Stack

- **Backend**: PHP 8.x (Procedural with security best practices)
- **Database**: PostgreSQL (Hosted on Supabase)
- **Frontend**: Vanilla HTML5, CSS3 (Modern Glassmorphism UI), JavaScript
- **Hosting**: Vercel (Serverless Environment)

---

## 📁 Struktur Folder

```text
├── database/
│   ├── db_alumni.sql              # Skema database MySQL (Legacy)
│   └── db_alumni_postgres.sql     # Skema database PostgreSQL (Supabase)
├── public/                        # Root folder yang dapat diakses publik
│   ├── assets/                    # Gambar, logo, dan file JS
│   ├── style/                     # Kumpulan file CSS
│   ├── uploads/                   # Folder penyimpanan foto profil alumni
│   ├── src/                       # File inti sistem (Dilindungi)
│   │   ├── auth.php               # Logika otorisasi role
│   │   ├── koneksi.php           # Koneksi database & init session
│   │   ├── navbar.php             # Komponen navigasi
│   │   └── session_handler.php    # Custom session handler untuk DB
│   ├── index.php                  # Halaman utama / landing
│   ├── login.php                  # Halaman login
│   └── [file-page-lain].php       # Halaman fitur aplikasi
└── vercel.json                    # Konfigurasi routing & build Vercel
```

---

## 🚀 Panduan Instalasi Lokal

### Prasyarat
- PHP 8.x terinstall di komputer Anda.
- Ekstensi `pgsql` dan `pdo_pgsql` aktif di `php.ini`.
- Git terinstall.

### Langkah-langkah

1. **Clone Repository**
   ```bash
   git clone https://github.com/MilQ28/data-alumni-postgre.git
   cd data-alumni-postgre
   ```

2. **Setup Database**
   - Buat project baru di [Supabase](https://supabase.com).
   - Salin isi dari `database/db_alumni_postgres.sql`.
   - Jalankan script tersebut di SQL Editor Supabase untuk membuat tabel dan tipe data.

3. **Setup Environment Variables**
   Buat file `.env` di root project (atau set di environment OS Anda) dengan isi:
   ```env
   DB_HOST=your-supabase-pooler-host.supabase.com
   DB_PORT=6543
   DB_NAME=postgres
   DB_USER=postgres.your-project-id
   DB_PASS=your-database-password
   ```

4. **Jalankan Server Lokal**
   Karena struktur project menggunakan folder `public` sebagai root, jalankan PHP built-in server dengan mengarah ke folder tersebut:
   ```bash
   php -S localhost:8000 -t public
   ```
   Akses aplikasi di browser melalui `http://localhost:8000`.

---

## ☁️ Deployment ke Vercel

Project ini sudah siap dideploy ke Vercel dengan konfigurasi `vercel.json` yang tersedia.

1. Install Vercel CLI atau hubungkan repository ini langsung ke akun Vercel Anda.
2. Pastikan Anda menambahkan **Environment Variables** di dashboard Vercel sesuai dengan daftar di atas.
3. Vercel akan otomatis membaca file `vercel.json` dan mengarahkan root folder ke `public/`.

---

## 🗺️ Roadmap Masa Depan

- [ ] **Email Verification**: Verifikasi email saat pendaftaran alumni.
- [ ] **Export Data**: Fitur export daftar alumni ke format PDF/Excel untuk Admin.
- [ ] **Dark Mode**: Opsi tema gelap untuk kenyamanan pengguna.
- [ ] **RESTful API**: Menyediakan endpoint API untuk integrasi dengan sistem sekolah lain.

---

## 🤝 Kontribusi

Kontribusi selalu terbuka! Jika Anda ingin meningkatkan project ini:
1. Fork repository ini.
2. Buat branch fitur baru (`git checkout -b fitur-keren`).
3. Commit perubahan Anda (`git commit -m 'Menambahkan fitur keren'`).
4. Push ke branch tersebut (`git push origin fitur-keren`).
5. Buat Pull Request.

---

## 📄 Lisensi

Project ini dilisensikan di bawah **MIT License**. Lihat file `LICENSE` untuk informasi lebih lanjut.

---

## 👤 Author

**MilQ28**
- GitHub: [@MilQ28](https://github.com/MilQ28)
- Email: syamilcholidatsani@gmail.com

---
*Project ini dikembangkan dengan ❤️ untuk kemajuan komunikasi Alumni SMK Telkom.*
