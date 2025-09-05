-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 20, 2025 at 06:36 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `cuci_motor`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`, `nama_lengkap`, `email`, `no_hp`, `created_at`, `updated_at`) VALUES
(2, 'yahay', '$2y$10$G6n54rt/.vdW7J0./5m/duPFd1XjzeIYG/bDMgqJc0GShRGT2Jka.', 'gilang', 'itemlu26@gmail.com', '085693093550', '2025-06-20 15:59:47', '2025-06-20 15:59:47');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `no_hp` varchar(20) NOT NULL,
  `alamat` text NOT NULL,
  `paket_id` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `waktu` time NOT NULL,
  `catatan` text DEFAULT NULL,
  `status` enum('pending','completed','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `nama`, `email`, `no_hp`, `alamat`, `paket_id`, `tanggal`, `waktu`, `catatan`, `status`, `created_at`) VALUES
(1, 'gilang', 'vidmuz26@gmail.com', '085693093550', 'jawa', 1, '2025-06-12', '23:59:00', 'keren', 'completed', '2025-06-12 16:59:30'),
(2, 'cylona', 'cylona@gmail.com', '08121212', 'pml', 2, '2025-06-21', '15:00:00', 'keren', 'completed', '2025-06-20 16:30:29');

-- --------------------------------------------------------

--
-- Table structure for table `packages`
--

CREATE TABLE `packages` (
  `id` int(11) NOT NULL,
  `nama_paket` varchar(100) NOT NULL,
  `harga` decimal(10,2) NOT NULL,
  `deskripsi` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `packages`
--

INSERT INTO `packages` (`id`, `nama_paket`, `harga`, `deskripsi`, `created_at`) VALUES
(1, 'Paket Basic', 25000.00, 'Cuci motor standar\nPembersihan body motor\nPembersihan jok\nPembersihan roda', '2025-06-12 16:40:05'),
(2, 'Paket Premium', 35000.00, 'Cuci motor standar\nPembersihan body motor\nPembersihan jok\nPembersihan roda\nPembersihan mesin\nPembersihan rantai\nSemir ban', '2025-06-12 16:40:05'),
(3, 'Paket VIP', 50000.00, 'Cuci motor standar\nPembersihan body motor\nPembersihan jok\nPembersihan roda\nPembersihan mesin\nPembersihan rantai\nSemir ban\nPoles body\nPembersihan karburator\nPembersihan filter udara', '2025-06-12 16:40:05');

-- --------------------------------------------------------

--
-- Table structure for table `paket`
--

CREATE TABLE `paket` (
  `id` int(11) NOT NULL,
  `nama_paket` varchar(100) NOT NULL,
  `deskripsi` text NOT NULL,
  `harga` decimal(10,2) NOT NULL,
  `durasi` int(11) NOT NULL COMMENT 'Durasi dalam menit',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `paket`
--

INSERT INTO `paket` (`id`, `nama_paket`, `deskripsi`, `harga`, `durasi`, `created_at`) VALUES
(1, 'Cuci Biasa', 'Cuci motor standar dengan air dan sabun', 15000.00, 30, '2025-06-12 23:40:00'),
(2, 'Cuci Express', 'Cuci cepat dengan air dan sabun', 20000.00, 20, '2025-06-12 23:40:00'),
(3, 'Cuci Premium', 'Cuci lengkap dengan semir ban dan pelindung cat', 30000.00, 45, '2025-06-12 23:40:00'),
(4, 'Cuci VIP', 'Cuci lengkap + wax + semir ban + pelindung cat + vacuum', 50000.00, 60, '2025-06-12 23:40:00');

-- --------------------------------------------------------

--
-- Table structure for table `pesanan`
--

CREATE TABLE `pesanan` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `alamat` text NOT NULL,
  `hp` varchar(20) NOT NULL,
  `waktu_pemesanan` datetime NOT NULL,
  `paket_id` int(11) NOT NULL,
  `status` enum('Baru','Diproses','Selesai') DEFAULT 'Baru',
  `tanggal_pesan` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `paket_id` (`paket_id`);

--
-- Indexes for table `packages`
--
ALTER TABLE `packages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `paket`
--
ALTER TABLE `paket`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pesanan`
--
ALTER TABLE `pesanan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `paket_id` (`paket_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `packages`
--
ALTER TABLE `packages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `paket`
--
ALTER TABLE `paket`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `pesanan`
--
ALTER TABLE `pesanan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`paket_id`) REFERENCES `packages` (`id`);

--
-- Constraints for table `pesanan`
--
ALTER TABLE `pesanan`
  ADD CONSTRAINT `pesanan_ibfk_1` FOREIGN KEY (`paket_id`) REFERENCES `paket` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
