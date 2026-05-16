-- --------------------------------------------------------
-- Database: db_alumni_postgres (Untuk Supabase)
-- --------------------------------------------------------

-- --------------------------------------------------------
-- Tabel `alumni`
-- --------------------------------------------------------

CREATE TABLE alumni (
  id_alumni   SERIAL PRIMARY KEY,
  nis         VARCHAR(20)   NOT NULL,
  nama        VARCHAR(100)  NOT NULL,
  angkatan    INTEGER       NOT NULL,
  jurusan     VARCHAR(100)  NOT NULL,
  email       VARCHAR(100)  NOT NULL UNIQUE,
  no_hp       VARCHAR(20)   NOT NULL,
  pekerjaan   VARCHAR(100)  DEFAULT NULL,
  perusahaan  VARCHAR(100)  DEFAULT NULL,
  alamat      TEXT          DEFAULT NULL,
  foto_profil VARCHAR(255)  DEFAULT NULL,
  created_at  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- --------------------------------------------------------
-- Tipe Data Custom (ENUM) untuk PostgreSQL
-- --------------------------------------------------------

CREATE TYPE user_role AS ENUM ('user', 'admin', 'superadmin');
CREATE TYPE user_status AS ENUM ('pending', 'approved', 'rejected');

-- --------------------------------------------------------
-- Tabel `users`
-- --------------------------------------------------------

CREATE TABLE users (
  user_id     SERIAL PRIMARY KEY,
  username    VARCHAR(50)   NOT NULL UNIQUE,
  password    VARCHAR(255)  NOT NULL,
  role        user_role     NOT NULL DEFAULT 'user',
  id_alumni   INTEGER       DEFAULT NULL REFERENCES alumni(id_alumni) ON DELETE SET NULL,
  status      user_status   NOT NULL DEFAULT 'pending',
  created_at  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- --------------------------------------------------------
-- Data awal `users` (admin & superadmin)
-- --------------------------------------------------------

INSERT INTO users (user_id, username, password, role, status) VALUES
(1, 'admin',      '$2y$10$8TQ6R1e3mgFzSI1uG/u2Yuvkh/KBimqODiJEiNFNbIs9eqvxbQ.kG', 'admin',      'approved'),
(2, 'superadmin', '$2y$10$4yK.E.rMAoAVzbFVh5TqOukRJod8UOHOQfY.Ts2262Aa2gK9Ao6DG', 'superadmin', 'approved');

-- --------------------------------------------------------
-- Contoh data alumni
-- --------------------------------------------------------

INSERT INTO alumni (nis, nama, angkatan, jurusan, email, no_hp, pekerjaan, perusahaan, alamat) VALUES
('553241167', 'Syamil Cholid Atsani', 2024, 'Rekayasa Perangkat Lunak', 'syamil@email.com', '0812345678910', 'Software Engineer', 'Nvidia + OpenAI', 'Pringsewu, Lampung');

-- Sesuaikan urutan sequence jika memasukkan data dengan ID manual (Optional)
SELECT setval('users_user_id_seq', (SELECT MAX(user_id) FROM users));
