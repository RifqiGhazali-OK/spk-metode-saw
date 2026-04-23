-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 20 Apr 2026 pada 11.43
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
  `user_id` int(11) NOT NULL DEFAULT 0,
  `kode` varchar(10) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `jabatan` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `alternatif`
--

INSERT INTO `alternatif` (`id`, `user_id`, `kode`, `nama`, `jabatan`, `created_at`) VALUES
(2, 1, 'A2', 'Karyawan C', 'Staff HR', '2026-04-18 10:46:55'),
(3, 1, 'A3', 'Karyawan C', 'Staff Finance', '2026-04-18 10:46:55'),
(4, 1, 'A4', 'Karyawan D', 'Staff Bisnis Proses', '2026-04-18 10:46:55'),
(6, 1, 'A2', 'Kayla', 'Supervisor it', '2026-04-20 07:41:36');

-- --------------------------------------------------------

--
-- Struktur dari tabel `kriteria`
--

CREATE TABLE `kriteria` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `kode` varchar(10) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `tipe` enum('benefit','cost') NOT NULL DEFAULT 'benefit',
  `bobot` decimal(5,4) NOT NULL DEFAULT 0.0000 COMMENT 'Total semua bobot harus = 1',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `kriteria`
--

INSERT INTO `kriteria` (`id`, `user_id`, `kode`, `nama`, `tipe`, `bobot`, `created_at`) VALUES
(1, 1, 'K1', 'Produktivitas', 'benefit', '0.3000', '2026-04-18 10:46:56'),
(2, 1, 'K2', 'Kualitas Kerja', 'benefit', '0.2500', '2026-04-18 10:46:56'),
(3, 1, 'K3', 'Konsistensi Kinerja', 'benefit', '0.2000', '2026-04-18 10:46:56'),
(4, 1, 'K4', 'Ketepatan Waktu', 'benefit', '0.1500', '2026-04-18 10:46:56'),
(5, 1, 'K5', 'Hasil Kerja', 'benefit', '0.1000', '2026-04-18 10:46:56');

-- --------------------------------------------------------

--
-- Struktur dari tabel `saw_hasil`
--

CREATE TABLE `saw_hasil` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `alternatif_id` int(11) NOT NULL,
  `nilai_akhir` decimal(10,6) NOT NULL,
  `ranking` int(11) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'Tidak Layak',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `saw_hasil`
--

INSERT INTO `saw_hasil` (`id`, `user_id`, `alternatif_id`, `nilai_akhir`, `ranking`, `status`, `created_at`) VALUES
(1, 1, 4, '0.941200', 1, 'Layak', '2026-04-18 10:46:56'),
(3, 1, 3, '0.891300', 3, 'Layak', '2026-04-18 10:46:56'),
(4, 1, 2, '0.812400', 4, 'Pertimbangkan', '2026-04-18 10:46:56');

-- --------------------------------------------------------

--
-- Struktur dari tabel `saw_penilaian`
--

CREATE TABLE `saw_penilaian` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `alternatif_id` int(11) NOT NULL,
  `kriteria_id` int(11) NOT NULL,
  `nilai` decimal(10,4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `saw_penilaian`
--

INSERT INTO `saw_penilaian` (`id`, `user_id`, `alternatif_id`, `kriteria_id`, `nilai`) VALUES
(6, 1, 2, 1, '80.0000'),
(7, 1, 2, 2, '82.0000'),
(8, 1, 2, 3, '70.0000'),
(9, 1, 2, 4, '61.0000'),
(10, 1, 2, 5, '80.0000'),
(11, 1, 3, 1, '90.0000'),
(12, 1, 3, 2, '85.0000'),
(13, 1, 3, 3, '70.0000'),
(14, 1, 3, 4, '62.0000'),
(15, 1, 3, 5, '54.9900'),
(16, 1, 4, 1, '90.0000'),
(17, 1, 4, 2, '91.0000'),
(18, 1, 4, 3, '71.0000'),
(19, 1, 4, 4, '53.0000'),
(20, 1, 4, 5, '89.0000');

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
(1, 'Manager HRD', 'admin@gmail.com', '0192023a7bbd73250516f069df18b500', 'admin', '2026-04-18 10:46:55'),
(2, 'rifqi', 'rifqi@gmail.com', '6ad14ba9986e3615423dfca256d04e3f', 'user', '2026-04-20 09:43:11');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `alternatif`
--
ALTER TABLE `alternatif`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `kriteria`
--
ALTER TABLE `kriteria`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `saw_hasil`
--
ALTER TABLE `saw_hasil`
  ADD PRIMARY KEY (`id`),
  ADD KEY `alternatif_id` (`alternatif_id`);

--
-- Indeks untuk tabel `saw_penilaian`
--
ALTER TABLE `saw_penilaian`
  ADD PRIMARY KEY (`id`),
  ADD KEY `alternatif_id` (`alternatif_id`),
  ADD KEY `kriteria_id` (`kriteria_id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `kriteria`
--
ALTER TABLE `kriteria`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `saw_hasil`
--
ALTER TABLE `saw_hasil`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `saw_penilaian`
--
ALTER TABLE `saw_penilaian`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `saw_hasil`
--
ALTER TABLE `saw_hasil`
  ADD CONSTRAINT `fk_hasil_alternatif` FOREIGN KEY (`alternatif_id`) REFERENCES `alternatif` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `saw_penilaian`
--
ALTER TABLE `saw_penilaian`
  ADD CONSTRAINT `fk_penilaian_alternatif` FOREIGN KEY (`alternatif_id`) REFERENCES `alternatif` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_penilaian_kriteria` FOREIGN KEY (`kriteria_id`) REFERENCES `kriteria` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
