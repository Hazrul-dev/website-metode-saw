-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 23, 2024 at 04:20 PM
-- Server version: 10.4.27-MariaDB
-- PHP Version: 8.0.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `spk_karyawan`
--

-- --------------------------------------------------------

--
-- Table structure for table `karyawan`
--

CREATE TABLE `karyawan` (
  `id` int(11) NOT NULL,
  `nip` varchar(20) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `tempat_lahir` varchar(100) DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `jenis_kelamin` enum('L','P') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `karyawan`
--

INSERT INTO `karyawan` (`id`, `nip`, `nama`, `tempat_lahir`, `tanggal_lahir`, `jenis_kelamin`) VALUES
(1, '2145693254', 'Viza Fandhana', 'Medan', '1985-12-10', 'P'),
(2, '2145693255', 'Amir Hasan', 'Deli Serdang', '1979-10-10', 'L'),
(3, '2145693256', 'Safruddin', 'Aceh', '1977-05-02', 'L'),
(4, '2145693257', 'Alwi Darmansyah', 'Batam', '1985-12-03', 'L'),
(5, '2145693258', 'Nuraini Nasution', 'Medan', '1979-02-05', 'P'),
(6, '2145693259', 'Rosna Dewi Laila', 'Medan', '1989-12-07', 'P'),
(7, '2145693260', 'Sulastri Marlina Siahaan', 'Binjai', '1992-10-22', 'P'),
(8, '2145693261', 'Arwan Efendi Batubara', 'Medan', '1985-08-12', 'L'),
(9, '2145693262', 'Khrisna Natassia Pasaribu', 'Samosir', '1991-01-01', 'P'),
(10, '2145693263', 'Yunita Hairani Sitanggang', 'Sibolga', '1980-06-02', 'P'),
(11, '2145693264', 'Jepri H Simanjuntak', 'Rantau Prapat', '1981-02-05', 'L'),
(12, '2145693265', 'Junius Bona Siahaan', 'Sidikalang', '1988-10-16', 'L'),
(13, '2145693266', 'Syahrizal Situmorang', 'Sipispis', '1980-08-25', 'L'),
(14, '2145693267', 'Widya Lestari Sinaga', 'Medan', '1986-05-05', 'P'),
(15, '2145693268', 'Handri Mawandri Ritonga', 'Simalungun', '1990-06-23', 'L'),
(16, '2145693269', 'Novita Sari', 'Medan', '1991-11-25', 'P'),
(17, '2145693270', 'Dedek Kurniawan', 'Langsa', '1992-04-18', 'L');

-- --------------------------------------------------------

--
-- Table structure for table `kriteria`
--

CREATE TABLE `kriteria` (
  `id` int(11) NOT NULL,
  `kode` varchar(10) NOT NULL,
  `nama` varchar(50) NOT NULL,
  `bobot` float NOT NULL,
  `tipe` enum('benefit','cost') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kriteria`
--

INSERT INTO `kriteria` (`id`, `kode`, `nama`, `bobot`, `tipe`) VALUES
(3, '', 'Kualitas Pekerjaan (C4)', 30, 'benefit'),
(4, '', 'Kerjasama (C3)', 20, 'benefit'),
(5, '', 'Pengetahuan Pekerjaan (C2)', 20, 'benefit'),
(6, '', 'Tanggung Jawab (C1)', 30, 'benefit');

-- --------------------------------------------------------

--
-- Table structure for table `penilaian`
--

CREATE TABLE `penilaian` (
  `id` int(11) NOT NULL,
  `karyawan_id` int(11) NOT NULL,
  `kriteria_id` int(11) NOT NULL,
  `nilai` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `penilaian`
--

INSERT INTO `penilaian` (`id`, `karyawan_id`, `kriteria_id`, `nilai`) VALUES
(10, 1, 3, 80),
(11, 1, 4, 85),
(12, 1, 5, 86),
(13, 1, 6, 90),
(15, 2, 3, 79),
(16, 2, 4, 84),
(17, 2, 5, 90),
(18, 2, 6, 89),
(19, 3, 3, 84),
(20, 3, 4, 78),
(21, 3, 5, 90),
(22, 3, 6, 70),
(23, 4, 3, 79),
(24, 4, 4, 94),
(25, 4, 5, 78),
(26, 4, 6, 80),
(27, 5, 3, 80),
(28, 5, 4, 80),
(29, 5, 5, 80),
(30, 5, 6, 80),
(31, 6, 3, 78),
(32, 6, 4, 79),
(33, 6, 5, 85),
(34, 6, 6, 75),
(35, 7, 3, 81),
(36, 7, 4, 84),
(37, 7, 5, 90),
(38, 7, 6, 74),
(39, 8, 3, 85),
(40, 8, 4, 91),
(41, 8, 5, 75),
(42, 8, 6, 90),
(43, 9, 3, 78),
(44, 9, 4, 82),
(45, 9, 5, 90),
(46, 9, 6, 75),
(47, 10, 3, 79),
(48, 10, 4, 80),
(49, 10, 5, 85),
(50, 10, 6, 88),
(51, 11, 3, 79),
(52, 11, 4, 80),
(53, 11, 5, 78),
(54, 11, 6, 70),
(55, 12, 3, 87),
(56, 12, 4, 76),
(57, 12, 6, 80),
(58, 12, 5, 78),
(59, 13, 3, 87),
(60, 13, 4, 80),
(61, 13, 5, 74),
(62, 13, 6, 70),
(63, 14, 3, 80),
(64, 14, 4, 80),
(65, 14, 5, 80),
(66, 14, 6, 75),
(67, 15, 3, 75),
(68, 15, 4, 78),
(69, 15, 5, 79),
(70, 15, 6, 76),
(71, 16, 3, 80),
(72, 16, 4, 74),
(73, 16, 5, 81),
(74, 16, 6, 82),
(75, 17, 3, 78),
(76, 17, 4, 78),
(77, 17, 5, 78),
(78, 17, 6, 75);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `nama_lengkap`, `role`, `created_at`) VALUES
(1, 'admin', '$2y$10$45sIgX09gqQqEaCMClupIexOGTN7AfTEDPpTONAaulI8D2WFimi/O', 'Administrator', 'user', '2024-10-22 15:55:19');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `karyawan`
--
ALTER TABLE `karyawan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nip` (`nip`);

--
-- Indexes for table `kriteria`
--
ALTER TABLE `kriteria`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `penilaian`
--
ALTER TABLE `penilaian`
  ADD PRIMARY KEY (`id`),
  ADD KEY `karyawan_id` (`karyawan_id`),
  ADD KEY `kriteria_id` (`kriteria_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `karyawan`
--
ALTER TABLE `karyawan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `kriteria`
--
ALTER TABLE `kriteria`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `penilaian`
--
ALTER TABLE `penilaian`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=79;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `penilaian`
--
ALTER TABLE `penilaian`
  ADD CONSTRAINT `penilaian_ibfk_1` FOREIGN KEY (`karyawan_id`) REFERENCES `karyawan` (`id`),
  ADD CONSTRAINT `penilaian_ibfk_2` FOREIGN KEY (`kriteria_id`) REFERENCES `kriteria` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
