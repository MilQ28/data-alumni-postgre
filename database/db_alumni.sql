-- --------------------------------------------------------
-- Database: db_alumni
-- --------------------------------------------------------

CREATE DATABASE IF NOT EXISTS `db_alumni` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `db_alumni`;

-- --------------------------------------------------------
-- Tabel `alumni`
-- --------------------------------------------------------

CREATE TABLE `alumni` (
  `id_alumni`   INT(11)       NOT NULL AUTO_INCREMENT,
  `nis`         VARCHAR(20)   NOT NULL,
  `nama`        VARCHAR(100)  NOT NULL,
  `angkatan`    YEAR          NOT NULL,
  `jurusan`     VARCHAR(100)  NOT NULL,
  `email`       VARCHAR(100)  NOT NULL UNIQUE,
  `no_hp`       VARCHAR(20)   NOT NULL,
  `pekerjaan`   VARCHAR(100)  DEFAULT NULL,
  `perusahaan`  VARCHAR(150)  DEFAULT NULL,
  `alamat`      TEXT          DEFAULT NULL,
  `foto_profil` VARCHAR(255)  DEFAULT NULL,
  `created_at`  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_alumni`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Tabel `users`
-- --------------------------------------------------------

CREATE TABLE `users` (
  `user_id`    INT(11)       NOT NULL AUTO_INCREMENT,
  `username`   VARCHAR(50)   NOT NULL UNIQUE,
  `password`   VARCHAR(255)  NOT NULL,
  `role`       ENUM('user','admin','superadmin') NOT NULL DEFAULT 'user',
  `id_alumni`  INT(11)       DEFAULT NULL,
  `status`     ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `created_at` TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  FOREIGN KEY (`id_alumni`) REFERENCES `alumni`(`id_alumni`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Data awal `users` (admin & superadmin)
-- --------------------------------------------------------

INSERT INTO `users` (`user_id`, `username`, `password`, `role`, `status`) VALUES
(1, 'admin',      '$2y$10$8TQ6R1e3mgFzSI1uG/u2Yuvkh/KBimqODiJEiNFNbIs9eqvxbQ.kG', 'admin',      'approved'),
(2, 'superadmin', '$2y$10$4yK.E.rMAoAVzbFVh5TqOukRJod8UOHOQfY.Ts2262Aa2gK9Ao6DG', 'superadmin', 'approved');

-- --------------------------------------------------------
-- Contoh data alumni
-- --------------------------------------------------------

INSERT INTO `alumni` (`nis`, `nama`, `angkatan`, `jurusan`, `email`, `no_hp`, `pekerjaan`, `perusahaan`, `alamat`) VALUES
('553241167', 'Syamil Cholid Atsani', 2024, 'Rekayasa Perangkat Lunak', 'syamil@email.com', '0812345678910', 'Software Engineer', 'Nvidia + OpenAI', 'Pringsewu, Lampung');

-- 3WHyMBpruKUSj0TL