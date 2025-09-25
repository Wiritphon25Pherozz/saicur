-- phpMyAdmin SQL Dump
-- version 5.2.1deb3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Sep 25, 2025 at 06:12 AM
-- Server version: 10.11.13-MariaDB-0ubuntu0.24.04.1
-- PHP Version: 8.3.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `if0_39302480_nano_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `cards`
--

CREATE TABLE `cards` (
  `uid` varchar(32) NOT NULL,
  `balance` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cards`
--

INSERT INTO `cards` (`uid`, `balance`) VALUES
('1AF09919', 0),
('A514B601', 1000000),
('BC643D02', 100000);

-- --------------------------------------------------------

--
-- Table structure for table `logs`
--

CREATE TABLE `logs` (
  `id` int(11) NOT NULL,
  `uid` varchar(32) DEFAULT NULL,
  `action` varchar(16) DEFAULT NULL,
  `delta` int(11) DEFAULT NULL,
  `balance_after` int(11) DEFAULT NULL,
  `applied` varchar(32) DEFAULT NULL,
  `ts` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `logs`
--

INSERT INTO `logs` (`id`, `uid`, `action`, `delta`, `balance_after`, `applied`, `ts`) VALUES
(1, 'BC643D02', 'topup', 100000, 100000, '3FFF6C2AA6F167319A84DCF854769E95', '2025-09-25 05:41:58'),
(2, 'A514B601', 'topup', 1000000, 1000000, '56C64E62C7ACF1F3488DD02196D884FE', '2025-09-25 05:54:33');

-- --------------------------------------------------------

--
-- Table structure for table `nfc_cards`
--

CREATE TABLE `nfc_cards` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `card_uid` varchar(64) NOT NULL,
  `nickname` varchar(50) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `version` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `bound_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `nfc_cards`
--

INSERT INTO `nfc_cards` (`id`, `user_id`, `card_uid`, `nickname`, `is_active`, `version`, `created_at`, `bound_at`) VALUES
(2, 3, 'BC643D2E', 'ซัน', 1, 1, '2025-08-22 12:50:22', NULL),
(3, 4, 'F2B38D6C', 'แมว', 1, 1, '2025-08-24 09:00:36', NULL),
(4, 5, 'BC643D02', 'Admin__', 1, 1, '2025-09-25 04:36:11', '2025-09-25 04:50:43'),
(5, 6, 'A514B601', NULL, 1, 1, '2025-09-25 05:53:46', '2025-09-25 05:53:46');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `transaction_id` varchar(255) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price_per_unit` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `transaction_id`, `product_id`, `quantity`, `price_per_unit`, `total_price`, `created_at`) VALUES
(1, 'TXNSEEDBUY1', 1, 1, 20.00, 20.00, '2025-08-22 12:47:14'),
(2, 'TXNT1HQGL2E4229', 1, 5, 20.00, 100.00, '2025-08-24 09:02:45'),
(3, 'TXNT1HQGZ4AB5DD', 1, 100, 20.00, 2000.00, '2025-08-24 09:02:59'),
(4, 'TXNT1HQHBD4C4E8', 1, 5, 20.00, 100.00, '2025-08-24 09:03:11'),
(5, 'TXNT1HS0KF02931', 1, 1, 20.00, 20.00, '2025-08-24 09:36:20'),
(6, 'TXNT1HS0P3DF3A9', 1, 1, 20.00, 20.00, '2025-08-24 09:36:25'),
(7, 'TXNT1L6LA68D735', 1, 10, 20.00, 200.00, '2025-08-26 05:43:58'),
(8, 'TXNT1L6MACD64CD', 3, 1, 60.00, 60.00, '2025-08-26 05:44:34'),
(9, 'TXNT1LI6347BD1E', 2, 10, 15.00, 150.00, '2025-08-26 09:54:03');

-- --------------------------------------------------------

--
-- Table structure for table `pending_writes`
--

CREATE TABLE `pending_writes` (
  `id` int(11) NOT NULL,
  `uid` varchar(32) DEFAULT NULL,
  `newBlock4` varchar(32) DEFAULT NULL,
  `request_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `claimed_at` timestamp NULL DEFAULT NULL,
  `claimed_by` varchar(128) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pending_writes`
--

INSERT INTO `pending_writes` (`id`, `uid`, `newBlock4`, `request_id`, `created_at`, `claimed_at`, `claimed_by`) VALUES
(1, 'BC643D02', '3FFF6C2AA6F167319A84DCF854769E95', 1, '2025-09-25 05:41:58', NULL, NULL),
(25, 'A514B601', '56C64E62C7ACF1F3488DD02196D884FE', 2, '2025-09-25 05:54:33', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `product_name`, `price`, `stock`, `created_at`) VALUES
(1, 'น้ำ', 20.00, 78, '2025-08-22 12:47:14'),
(2, 'ขนม', 15.00, 110, '2025-08-22 12:47:14'),
(3, 'ข้าวกล่อง', 60.00, 49, '2025-08-22 12:47:14');

-- --------------------------------------------------------

--
-- Table structure for table `requests`
--

CREATE TABLE `requests` (
  `id` int(11) NOT NULL,
  `uid` varchar(32) NOT NULL,
  `req_by` int(11) DEFAULT NULL,
  `amount` int(11) NOT NULL,
  `type` enum('topup','manual_adjust') NOT NULL DEFAULT 'topup',
  `status` enum('pending','approved','rejected','applied') NOT NULL DEFAULT 'pending',
  `admin_id` int(11) DEFAULT NULL,
  `admin_note` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `requests`
--

INSERT INTO `requests` (`id`, `uid`, `req_by`, `amount`, `type`, `status`, `admin_id`, `admin_note`, `created_at`, `updated_at`) VALUES
(1, 'BC643D02', 5, 100000, 'topup', 'applied', NULL, 'fastest', '2025-09-25 04:40:16', '2025-09-25 05:41:58'),
(2, 'A514B601', 6, 1000000, 'topup', 'applied', NULL, '', '2025-09-25 05:54:24', '2025-09-25 05:54:33');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `transaction_id` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `transaction_date` datetime DEFAULT current_timestamp(),
  `status` varchar(50) DEFAULT 'completed',
  `customer_name` varchar(255) DEFAULT NULL,
  `type` varchar(50) DEFAULT 'buy',
  `is_paid` tinyint(1) DEFAULT 0,
  `is_confirmed` tinyint(1) DEFAULT 0,
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `transaction_id`, `amount`, `transaction_date`, `status`, `customer_name`, `type`, `is_paid`, `is_confirmed`, `user_id`) VALUES
(1, 'TXNSEEDBUY1', 20.00, '2025-08-22 12:47:14', 'completed', 'ผู้ใช้ตัวอย่าง', 'buy', 1, 1, 2),
(2, 'TXNSEEDTOP1', 50.00, '2025-08-22 12:47:14', 'completed', 'ผู้ใช้ตัวอย่าง', 'topup', 1, 1, 2),
(3, 'TXNT1EBOA95325C', 179.00, '2025-08-22 19:50:34', 'completed', 'post', 'topup', 1, 1, 3),
(4, 'TXNT1HQDN868C37', 500.00, '2025-08-24 16:00:59', 'completed', 'ธนสรรค์', 'topup', 1, 1, 4),
(5, 'TXNT1HQGL2E4229', 100.00, '2025-08-24 16:02:45', 'completed', 'ธนสรรค์', 'buy', 1, 1, 4),
(6, 'TXNT1HQGZ4AB5DD', 2000.00, '2025-08-24 16:02:59', 'completed', 'ธนสรรค์', 'buy', 1, 1, 4),
(7, 'TXNT1HQHBD4C4E8', 100.00, '2025-08-24 16:03:11', 'completed', 'ธนสรรค์', 'buy', 1, 1, 4),
(8, 'TXNT1HQKZ5416B0', 2000.00, '2025-08-24 16:05:23', 'completed', 'ธนสรรค์', 'topup', 1, 1, 4),
(9, 'TXNT1HS0C9F62B9', 179.00, '2025-08-24 16:36:12', 'completed', 'ธนสรรค์', 'topup', 1, 1, 4),
(10, 'TXNT1HS0KF02931', 20.00, '2025-08-24 16:36:20', 'completed', 'ธนสรรค์', 'buy', 1, 1, 4),
(11, 'TXNT1HS0P3DF3A9', 20.00, '2025-08-24 16:36:25', 'completed', 'ธนสรรค์', 'buy', 1, 1, 4),
(12, 'TXNT1L6JX061BBC', 200.00, '2025-08-25 22:43:09', 'completed', 'ธนสรรค์', 'topup', 1, 1, 4),
(13, 'TXNT1L6LA68D735', 200.00, '2025-08-25 22:43:58', 'completed', 'ธนสรรค์', 'buy', 1, 1, 4),
(14, 'TXNT1L6MACD64CD', 60.00, '2025-08-25 22:44:34', 'completed', 'ธนสรรค์', 'buy', 1, 1, 4),
(15, 'TXNT1LI5NAC1607', 150.00, '2025-08-26 02:53:47', 'completed', 'ธนสรรค์', 'topup', 1, 1, 4),
(16, 'TXNT1LI6347BD1E', 150.00, '2025-08-26 02:54:03', 'completed', 'ธนสรรค์', 'buy', 1, 1, 4),
(17, 'TXNT1Y57D4A5643', 142.00, '2025-09-01 22:42:49', 'completed', 'ธนสรรค์', 'topup', 1, 1, 4);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('admin','user') NOT NULL DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `name`, `phone`, `email`, `role`, `created_at`) VALUES
(1, 'pluem', '$2y$10$HFv1tW/N6CGgwlzYx0i51urZr/3V5zB3S5PytO/Y2Tz4eH.YIZjKq', 'ผู้ดูแลระบบ', NULL, 'pluem@example.com', 'admin', '2025-08-22 12:47:14'),
(2, 'demo', '$2y$10$0Jk0eN6Z2Tn3T4yq7r7cOe3e6t9M3u4Xo0b6mM0s3x5Q3m8oQnE3W', 'ผู้ใช้ตัวอย่าง', '0812345678', 'demo@example.com', 'user', '2025-08-22 12:47:14'),
(3, 'obtai_7ma', '$2y$10$sfvoaosj4glcde7LPDNKnO3YUVHIdJ0dcnYWWh32YEjPiirbBBVka', 'post', '0789245614', 'user_1755866991_1241@nano.com', 'user', '2025-08-22 12:49:51'),
(4, 'มีสา', '$2y$10$TTPGqTBV/rVMTjMlvYVD9.Dkm0F4tkTct6CC0ItVis20oEAUmKdm.', 'ธนสรรค์', '0813524711', 'user_1756025930_3135@nano.com', 'user', '2025-08-24 08:58:50'),
(5, 'Admin__', '$2y$10$J25dkBCWDEdqI59thud.e.pK1cDSQT6Cld5v/uNJWNoH0nf87gSqO', 'Wiritphon Charoensub', '0642413021', 'user_1758622670_8177@nano.com', 'admin', '2025-09-23 10:17:50'),
(6, 'test55', '$2y$10$kANuXqjCOTKTNkYQ/qcXU.vaXuqqT9n4fdmltqnYXtYvqoVb.xSsG', 'testTest', '0000000000', 'user_1758779372_7495@nano.com', 'user', '2025-09-25 05:49:32');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cards`
--
ALTER TABLE `cards`
  ADD PRIMARY KEY (`uid`);

--
-- Indexes for table `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_logs_uid_ts` (`uid`,`ts`);

--
-- Indexes for table `nfc_cards`
--
ALTER TABLE `nfc_cards`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_card_uid` (`card_uid`),
  ADD UNIQUE KEY `uniq_user_one_card` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_oi_txn_id` (`transaction_id`),
  ADD KEY `idx_oi_product_id` (`product_id`);

--
-- Indexes for table `pending_writes`
--
ALTER TABLE `pending_writes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_pending_writes_uid` (`uid`),
  ADD KEY `request_id` (`request_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `requests`
--
ALTER TABLE `requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_requests_uid` (`uid`),
  ADD KEY `idx_requests_status` (`status`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_transaction_id` (`transaction_id`),
  ADD KEY `idx_txn_user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_username` (`username`),
  ADD UNIQUE KEY `uniq_email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `logs`
--
ALTER TABLE `logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `nfc_cards`
--
ALTER TABLE `nfc_cards`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `pending_writes`
--
ALTER TABLE `pending_writes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `requests`
--
ALTER TABLE `requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `nfc_cards`
--
ALTER TABLE `nfc_cards`
  ADD CONSTRAINT `fk_nfc_cards_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`transaction_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `fk_txn_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
