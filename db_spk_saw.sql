DROP DATABASE IF EXISTS `db_spk_saw`;

CREATE DATABASE `db_spk_saw` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

USE `db_spk_saw`;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";

START TRANSACTION;

CREATE TABLE `periode` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `nama` varchar(100) NOT NULL,
    `tanggal_mulai` date NOT NULL,
    `is_active` tinyint(1) DEFAULT 0,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

INSERT INTO
    `periode` (
        `nama`,
        `tanggal_mulai`,
        `is_active`
    )
VALUES (
        'Januari 2026',
        '2026-01-01',
        0
    ),
    (
        'Februari 2026',
        '2026-02-01',
        0
    ),
    ('Maret 2026', '2026-03-01', 0),
    ('April 2026', '2026-04-01', 1),
    ('Mei 2026', '2026-05-01', 0),
    ('Juni 2026', '2026-06-01', 0),
    ('Juli 2026', '2026-07-01', 0),
    (
        'Agustus 2026',
        '2026-08-01',
        0
    ),
    (
        'September 2026',
        '2026-09-01',
        0
    ),
    (
        'Oktober 2026',
        '2026-10-01',
        0
    ),
    (
        'November 2026',
        '2026-11-01',
        0
    ),
    (
        'Desember 2026',
        '2026-12-01',
        0
    );

CREATE TABLE `users` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `username` varchar(50) NOT NULL,
    `email` varchar(100) NOT NULL,
    `password` varchar(255) NOT NULL,
    `role` enum('admin', 'user') NOT NULL DEFAULT 'user',
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    UNIQUE KEY `email` (`email`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

INSERT INTO
    `users` (
        `username`,
        `email`,
        `password`,
        `role`
    )
VALUES (
        'Manager HRD',
        'admin@gmail.com',
        MD5('admin123'),
        'admin'
    );

CREATE TABLE `kriteria` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL DEFAULT 1,
    `kode` varchar(10) NOT NULL,
    `nama` varchar(100) NOT NULL,
    `tipe` enum('benefit', 'cost') NOT NULL DEFAULT 'benefit',
    `bobot` decimal(5, 4) NOT NULL DEFAULT 0.0000,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

INSERT INTO
    `kriteria` (
        `user_id`,
        `kode`,
        `nama`,
        `tipe`,
        `bobot`
    )
VALUES (
        1,
        'K1',
        'Produktivitas',
        'benefit',
        0.3000
    ),
    (
        1,
        'K2',
        'Kualitas Kerja',
        'benefit',
        0.2500
    ),
    (
        1,
        'K3',
        'Konsistensi Kinerja',
        'benefit',
        0.2000
    ),
    (
        1,
        'K4',
        'Ketepatan Waktu',
        'benefit',
        0.1500
    ),
    (
        1,
        'K5',
        'Hasil Kerja',
        'benefit',
        0.1000
    );

CREATE TABLE `alternatif` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `periode_id` int(11) NOT NULL,
    `user_id` int(11) NOT NULL DEFAULT 1,
    `kode` varchar(10) NOT NULL,
    `nama` varchar(100) NOT NULL,
    `jabatan` varchar(100) DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `periode_id` (`periode_id`),
    CONSTRAINT `fk_alternatif_periode` FOREIGN KEY (`periode_id`) REFERENCES `periode` (`id`) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

INSERT INTO
    `alternatif` (
        `periode_id`,
        `user_id`,
        `kode`,
        `nama`,
        `jabatan`
    )
VALUES (
        1,
        1,
        'A1',
        'Budi Santoso',
        'Staff IT'
    ),
    (
        1,
        1,
        'A2',
        'Siti Aminah',
        'Staff HR'
    ),
    (
        1,
        1,
        'A3',
        'Agus Wijaya',
        'Staff Finance'
    ),
    (
        1,
        1,
        'A4',
        'Dewi Kartika',
        'Staff Marketing'
    ),
    (
        1,
        1,
        'A5',
        'Eko Prasetyo',
        'Staff Operasional'
    ),
    (
        2,
        1,
        'A1',
        'Ahmad Fauzi',
        'Staff IT'
    ),
    (
        2,
        1,
        'A2',
        'Nurul Hidayah',
        'Staff HR'
    ),
    (
        2,
        1,
        'A3',
        'Dwi Cahyono',
        'Staff Finance'
    ),
    (
        2,
        1,
        'A4',
        'Sri Wahyuni',
        'Staff Marketing'
    ),
    (
        2,
        1,
        'A5',
        'Mulyono',
        'Staff Operasional'
    ),
    (
        3,
        1,
        'A1',
        'Hendra Gunawan',
        'Staff IT'
    ),
    (
        3,
        1,
        'A2',
        'Rina Andriani',
        'Staff HR'
    ),
    (
        3,
        1,
        'A3',
        'Teguh Purnomo',
        'Staff Finance'
    ),
    (
        3,
        1,
        'A4',
        'Lestari Mulyani',
        'Staff Marketing'
    ),
    (
        3,
        1,
        'A5',
        'Yanto',
        'Staff Operasional'
    ),
    (
        4,
        1,
        'A1',
        'Rizki Anugrah',
        'Staff IT'
    ),
    (
        4,
        1,
        'A2',
        'Dian Sastro',
        'Staff HR'
    ),
    (
        4,
        1,
        'A3',
        'Wahyu Kurniawan',
        'Staff Finance'
    ),
    (
        4,
        1,
        'A4',
        'Citra Kirana',
        'Staff Marketing'
    ),
    (
        4,
        1,
        'A5',
        'Bagus Wicaksono',
        'Staff Operasional'
    ),
    (
        5,
        1,
        'A1',
        'Irwan Hakim',
        'Staff IT'
    ),
    (
        5,
        1,
        'A2',
        'Farida Nurjanah',
        'Staff HR'
    ),
    (
        5,
        1,
        'A3',
        'Haryanto',
        'Staff Finance'
    ),
    (
        5,
        1,
        'A4',
        'Umi Kalsum',
        'Staff Marketing'
    ),
    (
        5,
        1,
        'A5',
        'Slamet Riyadi',
        'Staff Operasional'
    ),
    (
        6,
        1,
        'A1',
        'Gunawan',
        'Staff IT'
    ),
    (
        6,
        1,
        'A2',
        'Maya Sari',
        'Staff HR'
    ),
    (
        6,
        1,
        'A3',
        'Bambang Sutrisno',
        'Staff Finance'
    ),
    (
        6,
        1,
        'A4',
        'Nadia Putri',
        'Staff Marketing'
    ),
    (
        6,
        1,
        'A5',
        'Rudi Hartono',
        'Staff Operasional'
    ),
    (
        7,
        1,
        'A1',
        'Suharto',
        'Staff IT'
    ),
    (
        7,
        1,
        'A2',
        'Rahmawati',
        'Staff HR'
    ),
    (
        7,
        1,
        'A3',
        'Edi Susanto',
        'Staff Finance'
    ),
    (
        7,
        1,
        'A4',
        'Nur Azizah',
        'Staff Marketing'
    ),
    (
        7,
        1,
        'A5',
        'Joko Prasetyo',
        'Staff Operasional'
    ),
    (
        8,
        1,
        'A1',
        'Aditya Permana',
        'Staff IT'
    ),
    (
        8,
        1,
        'A2',
        'Ratna Dewi',
        'Staff HR'
    ),
    (
        8,
        1,
        'A3',
        'Hendro',
        'Staff Finance'
    ),
    (
        8,
        1,
        'A4',
        'Dewi Sartika',
        'Staff Marketing'
    ),
    (
        8,
        1,
        'A5',
        'Purnomo',
        'Staff Operasional'
    ),
    (
        9,
        1,
        'A1',
        'Cahyo',
        'Staff IT'
    ),
    (
        9,
        1,
        'A2',
        'Indah Lestari',
        'Staff HR'
    ),
    (
        9,
        1,
        'A3',
        'Doni Setiawan',
        'Staff Finance'
    ),
    (
        9,
        1,
        'A4',
        'Lilis',
        'Staff Marketing'
    ),
    (
        9,
        1,
        'A5',
        'Aris Munandar',
        'Staff Operasional'
    ),
    (
        10,
        1,
        'A1',
        'Fajar Nugroho',
        'Staff IT'
    ),
    (
        10,
        1,
        'A2',
        'Lina Marlina',
        'Staff HR'
    ),
    (
        10,
        1,
        'A3',
        'Haryanto',
        'Staff Finance'
    ),
    (
        10,
        1,
        'A4',
        'Rina Melati',
        'Staff Marketing'
    ),
    (
        10,
        1,
        'A5',
        'Sigit Purnomo',
        'Staff Operasional'
    ),
    (
        11,
        1,
        'A1',
        'Andi Wijaya',
        'Staff IT'
    ),
    (
        11,
        1,
        'A2',
        'Fitri Handayani',
        'Staff HR'
    ),
    (
        11,
        1,
        'A3',
        'Tono',
        'Staff Finance'
    ),
    (
        11,
        1,
        'A4',
        'Tri Mulyani',
        'Staff Marketing'
    ),
    (
        11,
        1,
        'A5',
        'Rudi Hartono',
        'Staff Operasional'
    ),
    (
        12,
        1,
        'A1',
        'Slamet',
        'Staff IT'
    ),
    (
        12,
        1,
        'A2',
        'Karina',
        'Staff HR'
    ),
    (
        12,
        1,
        'A3',
        'Iwan',
        'Staff Finance'
    ),
    (
        12,
        1,
        'A4',
        'Winda',
        'Staff Marketing'
    ),
    (
        12,
        1,
        'A5',
        'Heri',
        'Staff Operasional'
    );

CREATE TABLE `saw_penilaian` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL DEFAULT 1,
    `periode_id` int(11) NOT NULL,
    `alternatif_id` int(11) NOT NULL,
    `kriteria_id` int(11) NOT NULL,
    `nilai` decimal(10, 4) NOT NULL,
    PRIMARY KEY (`id`),
    KEY `alternatif_id` (`alternatif_id`),
    KEY `kriteria_id` (`kriteria_id`),
    KEY `periode_id` (`periode_id`),
    CONSTRAINT `fk_penilaian_alternatif` FOREIGN KEY (`alternatif_id`) REFERENCES `alternatif` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_penilaian_kriteria` FOREIGN KEY (`kriteria_id`) REFERENCES `kriteria` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_penilaian_periode` FOREIGN KEY (`periode_id`) REFERENCES `periode` (`id`) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE `saw_hasil` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL DEFAULT 1,
    `periode_id` int(11) NOT NULL,
    `alternatif_id` int(11) NOT NULL,
    `nilai_akhir` decimal(10, 6) NOT NULL,
    `ranking` int(11) NOT NULL,
    `status` varchar(20) NOT NULL DEFAULT 'Tidak Layak',
    `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `alternatif_id` (`alternatif_id`),
    KEY `periode_id` (`periode_id`),
    CONSTRAINT `fk_hasil_alternatif` FOREIGN KEY (`alternatif_id`) REFERENCES `alternatif` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_hasil_periode` FOREIGN KEY (`periode_id`) REFERENCES `periode` (`id`) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

COMMIT;