-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 20, 2025 at 09:23 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `menejemen_booking_room`
--

-- --------------------------------------------------------

--
-- Table structure for table `tb_booking`
--

CREATE TABLE `tb_booking` (
  `id_booking` int(11) NOT NULL,
  `code_booking` varchar(225) NOT NULL,
  `name` varchar(40) NOT NULL,
  `email` varchar(40) NOT NULL,
  `date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `room_id` int(11) NOT NULL,
  `ip_address` varchar(40) NOT NULL,
  `token` text NOT NULL,
  `status` varchar(40) NOT NULL DEFAULT 'upcoming',
  `sisa_update` int(1) NOT NULL DEFAULT 3,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_booking`
--

INSERT INTO `tb_booking` (`id_booking`, `code_booking`, `name`, `email`, `date`, `start_time`, `end_time`, `room_id`, `ip_address`, `token`, `status`, `sisa_update`, `created_at`) VALUES
(1, '$2y$10$hHiYNEWNTCNniYEK3cjgzOoazMSttOrtktQ57EWeuYyj63liNgaYW', 'Miftah Khaiulah W', 'miftaharul80@outlook.com', '2025-10-20', '13:00:00', '17:00:00', 2001, '192.168.101.05', '458ebd488cb7eaa5ebf0b8d41abe8eb6', 'upcoming', 3, '2025-10-20 09:42:18'),
(2, '$2y$10$KMytGAmVWSjg0qqqzQnqgOCTCj/GIom3/GgNNqUUD5bR2OxpDa3Eu', 'Syansa', 'miftaharul80@outlook.com', '2025-10-21', '09:00:00', '12:00:00', 2002, '192.168.101.05', '2a7816e76fbb7d7f21851a87e6fe9b7b', 'upcoming', 2, '2025-10-20 10:30:13');

-- --------------------------------------------------------

--
-- Table structure for table `tb_departemen`
--

CREATE TABLE `tb_departemen` (
  `id_departemen` int(11) NOT NULL,
  `departement_name` varchar(40) NOT NULL,
  `password` varchar(225) NOT NULL,
  `role` varchar(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_departemen`
--

INSERT INTO `tb_departemen` (`id_departemen`, `departement_name`, `password`, `role`) VALUES
(1, 'HRGA-ICT', '$2y$10$OQQvynirjG7j3py8GByIeeHqtdohvF60iGRQuRBb98jWCOWqyYFEK', 'HRGA-ICT'),
(2, 'FINANCE-ACCOUNTING', '$2y$10$./ebTwILdnl4LhR1bATUKub2hU4D34z3nqVI8gDfCPEG3X1oesLOi', 'FINANCE-ACCOUNTING'),
(3, 'PURCHASING/WH', '$2y$10$CRBz0/kodIxlpwdK.Y5EjuLE.IptrqKzizMr0HgaD67s5muBYasMC', 'PURCHASING/WH'),
(4, 'MAINTENANCE', '$2y$10$HIClpOB2vzSeBJ6EBdKXfup9cNRYU1CBzShetB0LdJ1PkygMiXUQS', 'MAINTENANCE'),
(5, 'PPIC/REPRO', '$2y$10$odfJQD3IF4UdlTb4FxteZexV.46gcwGrSNsBxaj2xD52orZoGcoQi', 'PPIC/REPRO'),
(6, 'QA/MR', '$2y$10$5CzOyovtUm8Sypy4qPTtyuRsZxn4kDBABLCZdBPViFnx8eEFBRzW2', 'QA/MR'),
(7, 'QC', '$2y$10$tLp1gRu0GehXdErlAIt/9OXx41JB/dAz9nOH9z0YvBpB14EULOm2W', 'QC'),
(8, 'TECHNICAL', '$2y$10$izKVKdWyXRg8KDCwlCBRC.44h31.qsdLu7XL3XZlsL3z44sbL4qy.', 'TECHNICAL'),
(9, 'SALES', '$2y$10$.V1lVCwfvST4vceLsfvM1e6ImtNJZz5Da5J/44qVwyzHfqbFq8a8u', 'SALES'),
(10, 'PRODUCTION (FAC. 1)', '$2y$10$kMC0j.MzAcMURoNdziFp5uHEzOeC2F3nWdn.dOjMi6TJF0sso2TIe', 'PRODUCTION (FAC. 1)'),
(11, 'PRODUCTION (FAC. 2)', '$2y$10$kWKYTgV.51XGU0VBUw27x.1511WD5gNI9h1scMSbjG4KAHPzRg2rO', 'PRODUCTION (FAC. 2)');

-- --------------------------------------------------------

--
-- Table structure for table `tb_ruangan`
--

CREATE TABLE `tb_ruangan` (
  `id_ruangan` int(11) NOT NULL,
  `nama_ruangan` varchar(40) NOT NULL,
  `foto_ruangan` text NOT NULL,
  `kapasitas` int(11) NOT NULL,
  `fasilitas` text NOT NULL,
  `ip_address` varchar(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_ruangan`
--

INSERT INTO `tb_ruangan` (`id_ruangan`, `nama_ruangan`, `foto_ruangan`, `kapasitas`, `fasilitas`, `ip_address`) VALUES
(2001, 'Guest Room 1', '1.jpg', 8, 'Meja, Kursi, Proyektor, Papan Tulis/Whiteboard, Wi-Fi, Sistem Audio, Alat Tulis dan Kertas', '192.168.101.05'),
(2002, 'Guest Room 2', '2.jpg', 8, 'Meja, Kursi, Proyektor, Papan Tulis/Whiteboard, Wi-Fi, Sistem Audio, Alat Tulis dan Kertas', '192.168.10.100'),
(2003, 'Guest Room 3', '3.jpg', 12, 'Meja, Kursi, Proyektor, Papan Tulis/Whiteboard, Wi-Fi, Sistem Audio, Alat Tulis dan Kertas', '192.168.10.130');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tb_booking`
--
ALTER TABLE `tb_booking`
  ADD PRIMARY KEY (`id_booking`);

--
-- Indexes for table `tb_departemen`
--
ALTER TABLE `tb_departemen`
  ADD PRIMARY KEY (`id_departemen`);

--
-- Indexes for table `tb_ruangan`
--
ALTER TABLE `tb_ruangan`
  ADD PRIMARY KEY (`id_ruangan`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tb_booking`
--
ALTER TABLE `tb_booking`
  MODIFY `id_booking` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tb_departemen`
--
ALTER TABLE `tb_departemen`
  MODIFY `id_departemen` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `tb_ruangan`
--
ALTER TABLE `tb_ruangan`
  MODIFY `id_ruangan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2004;

DELIMITER $$
--
-- Events
--
CREATE DEFINER=`root`@`localhost` EVENT `update_status_segera_datang` ON SCHEDULE EVERY 2 MINUTE STARTS '2025-10-02 08:00:00' ON COMPLETION NOT PRESERVE ENABLE DO UPDATE tb_booking
  SET status = 'upcoming'
  WHERE NOW() < CONCAT(date, ' ', start_time)$$

CREATE DEFINER=`root`@`localhost` EVENT `update_status_berlangsung` ON SCHEDULE EVERY 1 MINUTE STARTS '2025-10-02 08:00:00' ON COMPLETION NOT PRESERVE ENABLE DO UPDATE tb_booking SET status = 'ongoing' WHERE NOW() BETWEEN CONCAT(date, ' ', start_time) AND CONCAT(date, ' ', end_time)$$

CREATE DEFINER=`root`@`localhost` EVENT `update_status_selesai` ON SCHEDULE EVERY 1 MINUTE STARTS '2025-10-02 08:00:00' ON COMPLETION NOT PRESERVE ENABLE DO UPDATE tb_booking
  SET status = 'finished'
  WHERE NOW() > CONCAT(date, ' ', end_time)$$

DELIMITER ;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
