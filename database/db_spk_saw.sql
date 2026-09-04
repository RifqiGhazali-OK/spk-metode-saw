-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 30 Jul 2026 pada 15.18
-- Versi server: 10.4.27-MariaDB
-- Versi PHP: 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_spk_saw`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `alternatif`
--

CREATE TABLE `alternatif` (
  `id` int(11) NOT NULL,
  `periode_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT 1,
  `kode` varchar(10) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `jabatan` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `alternatif`
--

INSERT INTO `alternatif` (`id`, `periode_id`, `user_id`, `kode`, `nama`, `jabatan`, `created_at`) VALUES
(1, 1, 1, 'A1', 'KRY-01', 'Staff IT', '2026-05-07 13:31:53'),
(2, 1, 1, 'A2', 'KRY-02', 'Staff IABP', '2026-05-07 13:31:53'),
(3, 1, 1, 'A3', 'KRY-03', 'Staff IABP', '2026-05-07 13:31:53'),
(4, 1, 1, 'A4', 'KRY-04', 'Staff FATL', '2026-05-07 13:31:53'),
(5, 1, 1, 'A5', 'KRY-05', 'Staff FATL', '2026-05-07 13:31:53'),
(6, 1, 1, 'A6', 'KRY-06', 'Staff GA', '2026-05-07 13:31:53'),
(7, 1, 1, 'A7', 'KRY-07', 'Staff GA', '2026-05-07 13:31:53'),
(8, 1, 1, 'A8', 'KRY-08', 'Staff HR', '2026-05-07 13:31:53');

-- --------------------------------------------------------

--
-- Struktur dari tabel `kriteria`
--

CREATE TABLE `kriteria` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT 1,
  `kode` varchar(10) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `tipe` enum('benefit','cost') NOT NULL DEFAULT 'benefit',
  `bobot` decimal(5,4) NOT NULL DEFAULT 0.0000,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `kriteria`
--

INSERT INTO `kriteria` (`id`, `user_id`, `kode`, `nama`, `tipe`, `bobot`, `created_at`) VALUES
(9, 1, 'C2', 'Efektivitas Kerja', 'benefit', '0.2000', '2026-05-11 09:59:42'),
(10, 1, 'C3', 'Ketepatan Waktu', 'benefit', '0.2000', '2026-05-11 09:59:53'),
(11, 1, 'C4', 'Kualitas Ouput', 'benefit', '0.2000', '2026-05-11 10:00:19'),
(13, 1, 'C5', 'Konsistensi Kinerja', 'benefit', '0.1500', '2026-05-11 10:35:14'),
(15, 1, 'C1', 'Produktivitas', 'benefit', '0.2500', '2026-05-11 10:53:03');

-- --------------------------------------------------------

--
-- Struktur dari tabel `periode`
--

CREATE TABLE `periode` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `tanggal_mulai` date NOT NULL,
  `is_active` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `periode`
--

INSERT INTO `periode` (`id`, `nama`, `tanggal_mulai`, `is_active`, `created_at`) VALUES
(1, 'Januari 2026', '2026-01-01', 0, '2026-04-29 11:31:29'),
(2, 'Februari 2026', '2026-02-01', 0, '2026-04-29 11:31:29'),
(3, 'Maret 2026', '2026-03-01', 0, '2026-04-29 11:31:29'),
(4, 'April 2026', '2026-04-01', 1, '2026-04-29 11:31:29'),
(5, 'Mei 2026', '2026-05-01', 0, '2026-04-29 11:31:29'),
(6, 'Juni 2026', '2026-06-01', 0, '2026-04-29 11:31:29'),
(7, 'Juli 2026', '2026-07-01', 0, '2026-04-29 11:31:29'),
(8, 'Agustus 2026', '2026-08-01', 0, '2026-04-29 11:31:29'),
(9, 'September 2026', '2026-09-01', 0, '2026-04-29 11:31:29'),
(10, 'Oktober 2026', '2026-10-01', 0, '2026-04-29 11:31:29'),
(11, 'November 2026', '2026-11-01', 0, '2026-04-29 11:31:29'),
(12, 'Desember 2026', '2026-12-01', 0, '2026-04-29 11:31:29');

-- --------------------------------------------------------

--
-- Struktur dari tabel `saw_hasil`
--

CREATE TABLE `saw_hasil` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT 1,
  `periode_id` int(11) NOT NULL,
  `alternatif_id` int(11) NOT NULL,
  `nilai_akhir` decimal(10,6) NOT NULL,
  `ranking` int(11) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'Tidak Layak',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `saw_hasil`
--

INSERT INTO `saw_hasil` (`id`, `user_id`, `periode_id`, `alternatif_id`, `nilai_akhir`, `ranking`, `status`, `created_at`) VALUES
(130, 1, 1, 1, '0.912828', 1, 'Layak', '2026-07-29 04:31:33'),
(131, 1, 1, 2, '0.789123', 2, 'Layak', '2026-07-29 04:31:33'),
(132, 1, 1, 6, '0.786175', 3, 'Layak', '2026-07-29 04:31:33'),
(133, 1, 1, 8, '0.763097', 4, 'Layak', '2026-07-29 04:31:33'),
(134, 1, 1, 4, '0.744075', 5, 'Layak', '2026-07-29 04:31:33'),
(135, 1, 1, 5, '0.709901', 6, 'Layak', '2026-07-29 04:31:33'),
(136, 1, 1, 3, '0.560724', 7, 'Pertimbangkan', '2026-07-29 04:31:33'),
(137, 1, 1, 7, '0.539111', 8, 'Pertimbangkan', '2026-07-29 04:31:33');

-- --------------------------------------------------------

--
-- Struktur dari tabel `saw_penilaian`
--

CREATE TABLE `saw_penilaian` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT 1,
  `periode_id` int(11) NOT NULL,
  `alternatif_id` int(11) NOT NULL,
  `kriteria_id` int(11) NOT NULL,
  `nilai` decimal(10,4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `saw_penilaian`
--

INSERT INTO `saw_penilaian` (`id`, `user_id`, `periode_id`, `alternatif_id`, `kriteria_id`, `nilai`) VALUES
(75, 1, 1, 1, 9, '95.6100'),
(76, 1, 1, 1, 10, '87.2000'),
(77, 1, 1, 1, 11, '92.3400'),
(89, 1, 1, 2, 9, '94.4200'),
(90, 1, 1, 2, 10, '44.1100'),
(91, 1, 1, 2, 11, '48.6800'),
(94, 1, 1, 3, 9, '97.1200'),
(95, 1, 1, 3, 10, '58.9000'),
(96, 1, 1, 3, 11, '17.8900'),
(100, 1, 1, 4, 9, '94.7600'),
(102, 1, 1, 4, 10, '60.3600'),
(103, 1, 1, 4, 11, '32.8100'),
(105, 1, 1, 5, 9, '82.7300'),
(106, 1, 1, 5, 10, '55.0000'),
(107, 1, 1, 5, 11, '58.6900'),
(109, 1, 1, 6, 9, '99.0800'),
(110, 1, 1, 6, 10, '80.7300'),
(111, 1, 1, 6, 11, '100.0000'),
(121, 1, 1, 7, 9, '95.4500'),
(122, 1, 1, 7, 10, '43.9400'),
(123, 1, 1, 7, 11, '36.6700'),
(125, 1, 1, 8, 11, '70.0300'),
(126, 1, 1, 8, 10, '50.6100'),
(127, 1, 1, 8, 9, '92.6000'),
(132, 1, 1, 2, 15, '99.5800'),
(133, 1, 1, 3, 15, '17.4500'),
(134, 1, 1, 4, 15, '79.1600'),
(135, 1, 1, 5, 15, '59.5000'),
(136, 1, 1, 6, 15, '20.3200'),
(137, 1, 1, 7, 15, '8.8900'),
(138, 1, 1, 8, 15, '67.7300'),
(139, 1, 1, 1, 13, '100.0000'),
(140, 1, 1, 2, 13, '100.0000'),
(141, 1, 1, 3, 13, '100.0000'),
(142, 1, 1, 4, 13, '100.0000'),
(144, 1, 1, 6, 13, '100.0000'),
(145, 1, 1, 5, 13, '100.0000'),
(146, 1, 1, 7, 13, '100.0000'),
(147, 1, 1, 8, 13, '100.0000'),
(155, 1, 1, 1, 15, '73.7500');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') NOT NULL DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'HUMAN RESOURCE', 'admin@gmail.com', '0192023a7bbd73250516f069df18b500', 'admin', '2026-04-29 11:31:29');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `alternatif`
--
ALTER TABLE `alternatif`
  ADD PRIMARY KEY (`id`),
  ADD KEY `periode_id` (`periode_id`);

--
-- Indeks untuk tabel `kriteria`
--
ALTER TABLE `kriteria`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `periode`
--
ALTER TABLE `periode`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `saw_hasil`
--
ALTER TABLE `saw_hasil`
  ADD PRIMARY KEY (`id`),
  ADD KEY `alternatif_id` (`alternatif_id`),
  ADD KEY `periode_id` (`periode_id`);

--
-- Indeks untuk tabel `saw_penilaian`
--
ALTER TABLE `saw_penilaian`
  ADD PRIMARY KEY (`id`),
  ADD KEY `alternatif_id` (`alternatif_id`),
  ADD KEY `kriteria_id` (`kriteria_id`),
  ADD KEY `periode_id` (`periode_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `alternatif`
--
ALTER TABLE `alternatif`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT untuk tabel `kriteria`
--
ALTER TABLE `kriteria`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT untuk tabel `periode`
--
ALTER TABLE `periode`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT untuk tabel `saw_hasil`
--
ALTER TABLE `saw_hasil`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=138;

--
-- AUTO_INCREMENT untuk tabel `saw_penilaian`
--
ALTER TABLE `saw_penilaian`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=156;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `alternatif`
--
ALTER TABLE `alternatif`
  ADD CONSTRAINT `fk_alternatif_periode` FOREIGN KEY (`periode_id`) REFERENCES `periode` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `saw_hasil`
--
ALTER TABLE `saw_hasil`
  ADD CONSTRAINT `fk_hasil_alternatif` FOREIGN KEY (`alternatif_id`) REFERENCES `alternatif` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_hasil_periode` FOREIGN KEY (`periode_id`) REFERENCES `periode` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `saw_penilaian`
--
ALTER TABLE `saw_penilaian`
  ADD CONSTRAINT `fk_penilaian_alternatif` FOREIGN KEY (`alternatif_id`) REFERENCES `alternatif` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_penilaian_kriteria` FOREIGN KEY (`kriteria_id`) REFERENCES `kriteria` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_penilaian_periode` FOREIGN KEY (`periode_id`) REFERENCES `periode` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
