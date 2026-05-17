-- --------------------------------------------------------
-- Database: db_alumni_postgres (Untuk Supabase)
-- --------------------------------------------------------

-- --------------------------------------------------------
-- Tabel `sessions` (Wajib untuk Deployment Vercel)
-- Menyimpan sesi login pengguna di database karena Vercel bersifat Serverless
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS sessions (
  session_id   VARCHAR(255) PRIMARY KEY,
  session_data TEXT         NOT NULL,
  expires_at   TIMESTAMP    NOT NULL
);

-- Index untuk mempercepat pembersihan sesi expired
CREATE INDEX IF NOT EXISTS idx_sessions_expires ON sessions (expires_at);

-- --------------------------------------------------------
-- Tabel `alumni`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS alumni (
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

DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'user_role') THEN
        CREATE TYPE user_role AS ENUM ('user', 'admin', 'superadmin');
    END IF;
    IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'user_status') THEN
        CREATE TYPE user_status AS ENUM ('pending', 'approved', 'rejected');
    END IF;
END
$$;

-- --------------------------------------------------------
-- Tabel `users`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS users (
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

INSERT INTO users (username, password, role, status) VALUES
('admin',      '$2y$10$8TQ6R1e3mgFzSI1uG/u2Yuvkh/KBimqODiJEiNFNbIs9eqvxbQ.kG', 'admin',      'approved'),
('superadmin', '$2y$10$4yK.E.rMAoAVzbFVh5TqOukRJod8UOHOQfY.Ts2262Aa2gK9Ao6DG', 'superadmin', 'approved')
ON CONFLICT (username) DO NOTHING;

-- --------------------------------------------------------
-- Contoh data alumni
-- --------------------------------------------------------

INSERT INTO alumni (nis, nama, angkatan, jurusan, email, no_hp, pekerjaan, perusahaan, alamat) VALUES
('553241167', 'Syamil Cholid Atsani', 2024, 'Rekayasa Perangkat Lunak', 'syamil@email.com', '0812345678910', 'Software Engineer', 'Nvidia + OpenAI', 'Pringsewu, Lampung')
ON CONFLICT (email) DO NOTHING;


