-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 17, 2025 at 10:28 AM
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
-- Database: `toko1`
--

-- --------------------------------------------------------

--
-- Table structure for table `barang`
--

CREATE TABLE `barang` (
  `id_barang` varchar(10) NOT NULL,
  `nama_barang` varchar(100) NOT NULL,
  `harga` decimal(12,2) NOT NULL,
  `stok` int(11) NOT NULL
) ;

--
-- Dumping data for table `barang`
--

INSERT INTO `barang` (`id_barang`, `nama_barang`, `harga`, `stok`) VALUES
('BR01', 'ROTI PANDAN', 10000.00, 5),
('BR010', 'BISKUAT', 8000.00, 10),
('BR02', 'SUSU KENTAL MANIS', 15000.00, 5),
('BR03', 'TELUR AYAM KAMPUNG', 2500.00, 16),
('BR04', 'KOPI KAPAL API', 6000.00, 10),
('BR05', 'ROTI TAWAR', 13000.00, 5),
('BR07', 'ROMA SARI GANDUM', 13000.00, 5),
('BR08', 'WAFER TANGO', 11000.00, 15),
('BR09', 'AQUA', 5000.00, 12);

--
-- Triggers `barang`
--
DELIMITER $$
CREATE TRIGGER `uppercase_nama_barang` BEFORE INSERT ON `barang` FOR EACH ROW BEGIN
    SET NEW.nama_barang = UPPER(NEW.nama_barang);
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `detail_transaksi`
--

CREATE TABLE `detail_transaksi` (
  `id_detail` varchar(10) NOT NULL,
  `id_transaksi` varchar(10) NOT NULL,
  `id_barang` varchar(10) NOT NULL,
  `jumlah` int(11) NOT NULL,
  `total_harga` decimal(12,2) NOT NULL,
  `pembayaran` decimal(12,2) DEFAULT NULL,
  `kembalian` decimal(12,2) DEFAULT NULL,
  `tanggal` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `detail_transaksi`
--

INSERT INTO `detail_transaksi` (`id_detail`, `id_transaksi`, `id_barang`, `jumlah`, `total_harga`, `pembayaran`, `kembalian`, `tanggal`) VALUES
('TR01', 'TR01A', 'BR03', 12, 30000.00, 50000.00, 8000.00, '2025-10-17'),
('TR02', 'TR02A', 'BR02', 3, 45000.00, 70000.00, 5000.00, '2025-10-17'),
('TR03', 'TR03A', 'BR09', 13, 104000.00, 105000.00, 1000.00, '2025-10-17'),
('TR04', 'TR04A', 'BR07', 5, 65000.00, 150000.00, 15000.00, '2025-10-17'),
('TR05', 'TR05A', 'BR010', 5, 40000.00, 50000.00, 5000.00, '2025-10-17'),
('TR06', 'TR06A', 'BR010', 5, 40000.00, 60000.00, 2000.00, '2025-10-17');

-- --------------------------------------------------------

--
-- Table structure for table `pembeli`
--

CREATE TABLE `pembeli` (
  `id_pembeli` varchar(10) NOT NULL,
  `nama_pembeli` varchar(100) NOT NULL,
  `alamat` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pembeli`
--

INSERT INTO `pembeli` (`id_pembeli`, `nama_pembeli`, `alamat`) VALUES
('PB01', 'BUDI HARTONO', 'JL. MELATI NO.11'),
('PB02', 'SITI AMINAA', 'JL. MAWAR NO.50'),
('PB03', 'ANDI SATRIA', 'JL. KENANGA NO.2'),
('PB04', 'TES123', 'JL TES NO 1'),
('PB05', 'SANTIKA', 'SURABAYA'),
('PB06', 'TES111', 'SURABAYA'),
('PB07', 'TES333', 'AMBON');

-- --------------------------------------------------------

--
-- Table structure for table `transaksi`
--

CREATE TABLE `transaksi` (
  `id_transaksi` varchar(10) NOT NULL,
  `id_pembeli` varchar(10) DEFAULT NULL,
  `id_barang` varchar(10) DEFAULT NULL,
  `jumlah` int(11) NOT NULL CHECK (`jumlah` > 0),
  `total_harga` decimal(12,2) NOT NULL,
  `tanggal` date NOT NULL DEFAULT curdate()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transaksi`
--

INSERT INTO `transaksi` (`id_transaksi`, `id_pembeli`, `id_barang`, `jumlah`, `total_harga`, `tanggal`) VALUES
('TR01A', 'PB02', 'BR03', 12, 30000.00, '2025-10-17'),
('TR01B', 'PB02', 'BR04', 2, 12000.00, '2025-10-17'),
('TR02A', 'PB03', 'BR02', 3, 45000.00, '2025-10-13'),
('TR02B', 'PB03', 'BR01', 2, 20000.00, '2025-10-13'),
('TR03A', 'PB06', 'BR07', 10, 130000.00, '2025-10-01'),
('TR04A', 'PB06', 'BR07', 5, 65000.00, '2025-10-17'),
('TR04B', 'PB06', 'BR01', 7, 70000.00, '2025-10-17'),
('TR05A', 'PB03', 'BR010', 5, 40000.00, '2025-10-17'),
('TR05B', 'PB03', 'BR03', 2, 5000.00, '2025-10-17'),
('TR06A', 'PB01', 'BR010', 5, 40000.00, '2025-10-17'),
('TR06B', 'PB01', 'BR04', 3, 18000.00, '2025-10-17');

--
-- Triggers `transaksi`
--
DELIMITER $$
CREATE TRIGGER `calculate_total_harga` BEFORE INSERT ON `transaksi` FOR EACH ROW BEGIN
    DECLARE h DECIMAL(12,2);
    SELECT harga INTO h FROM barang WHERE id_barang = NEW.id_barang;
    SET NEW.total_harga = h * NEW.jumlah;
END
$$
DELIMITER ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `barang`
--
ALTER TABLE `barang`
  ADD PRIMARY KEY (`id_barang`);

--
-- Indexes for table `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  ADD PRIMARY KEY (`id_detail`),
  ADD KEY `fk_transaksi` (`id_transaksi`),
  ADD KEY `fk_barang` (`id_barang`);

--
-- Indexes for table `pembeli`
--
ALTER TABLE `pembeli`
  ADD PRIMARY KEY (`id_pembeli`);

--
-- Indexes for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`id_transaksi`),
  ADD KEY `fk_transaksi_barang` (`id_barang`),
  ADD KEY `fk_transaksi_pembeli` (`id_pembeli`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  ADD CONSTRAINT `fk_barang` FOREIGN KEY (`id_barang`) REFERENCES `barang` (`id_barang`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_transaksi` FOREIGN KEY (`id_transaksi`) REFERENCES `transaksi` (`id_transaksi`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD CONSTRAINT `fk_transaksi_barang` FOREIGN KEY (`id_barang`) REFERENCES `barang` (`id_barang`),
  ADD CONSTRAINT `fk_transaksi_pembeli` FOREIGN KEY (`id_pembeli`) REFERENCES `pembeli` (`id_pembeli`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
