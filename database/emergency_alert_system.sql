-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Mar 12, 2026 at 07:57 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `emergency_alert_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `alerts`
--

CREATE TABLE `alerts` (
  `id` int(11) NOT NULL,
  `alert_type_id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `description` text NOT NULL,
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `status` enum('pending','verified','broadcasted','resolved') DEFAULT 'pending',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `alerts`
--

INSERT INTO `alerts` (`id`, `alert_type_id`, `title`, `description`, `latitude`, `longitude`, `status`, `created_by`, `created_at`) VALUES
(1, 1, 'Fire at market', 'Fire at market place', 57.00000000, 112.00000000, 'pending', 5, '2026-02-19 05:30:58'),
(2, 3, 'Airport road accident', 'Coalition of several vehicles along Main land road to airport road, on the the Eastern bypass.', 57.80000000, 143.00000000, 'verified', 6, '2026-02-19 05:44:19'),
(3, 1, 'Test data 2', 'This is a test alert to test the featire', 67.00000000, 112.00000000, 'resolved', 6, '2026-03-06 16:37:19'),
(4, 2, 'Floods due to heavy rains', 'Most parts of the country has floods due to the rains, most affected areas are the regions on mountains', 12.88792500, 30.17099200, 'broadcasted', 6, '2026-03-12 06:06:49');

-- --------------------------------------------------------

--
-- Table structure for table `alert_broadcasts`
--

CREATE TABLE `alert_broadcasts` (
  `id` int(11) NOT NULL,
  `alert_id` int(11) NOT NULL,
  `sent_count` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `alert_responses`
--

CREATE TABLE `alert_responses` (
  `id` int(11) NOT NULL,
  `alert_id` int(11) NOT NULL,
  `responder_id` int(11) NOT NULL,
  `note` text DEFAULT NULL,
  `status` enum('accepted','in_progress','completed') DEFAULT 'accepted',
  `responded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `alert_responses`
--

INSERT INTO `alert_responses` (`id`, `alert_id`, `responder_id`, `note`, `status`, `responded_at`) VALUES
(1, 1, 5, 'Arrived 10 minutes after alert was created, and 10 people where affected with minor burns, 50 shops burned, no deaths records', 'completed', '2026-02-19 05:49:39');

-- --------------------------------------------------------

--
-- Table structure for table `alert_types`
--

CREATE TABLE `alert_types` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `alert_types`
--

INSERT INTO `alert_types` (`id`, `name`, `description`, `created_at`) VALUES
(1, 'Fire', 'Fire outbreak', '2026-02-11 09:56:59'),
(2, 'Flood', 'Flood disaster', '2026-02-11 09:56:59'),
(3, 'Accident', 'Road accident', '2026-02-11 09:56:59'),
(4, 'Security Threat', 'Man-made disaster', '2026-02-11 09:56:59');

-- --------------------------------------------------------

--
-- Table structure for table `api_tokens`
--

CREATE TABLE `api_tokens` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `community_users`
--

CREATE TABLE `community_users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `device_token` text DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `created_at`) VALUES
(1, 'Admin', '2026-02-11 09:56:59'),
(2, 'Responder', '2026-02-11 09:56:59'),
(3, 'Community', '2026-03-07 05:04:28');

-- --------------------------------------------------------

--
-- Table structure for table `system_logs`
--

CREATE TABLE `system_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_logs`
--

INSERT INTO `system_logs` (`id`, `user_id`, `action`, `created_at`) VALUES
(1, 5, 'Responder responded to alert #1 (Fire): accepted', '2026-02-19 05:47:23'),
(2, 5, 'Responder responded to alert #1 (Fire): completed', '2026-02-19 05:49:39');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role_id` int(11) NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `phone`, `password`, `role_id`, `status`, `created_at`) VALUES
(5, 'Demo Account', 'demo@test.com', '123456789', '$2y$12$uxlGHXe9RVFOvXDcUyr87OqVPsgryzrZgiMmf2WvCV910temcz/5O', 2, 'active', '2026-02-12 08:08:36'),
(6, 'Admin Tesst', 'admin@test.com', '0712345678', '$2y$12$0L3PG6SyTr9LoJd64mSP5.ZTeSmgahThVFLvZT7u3Aci2ss46RKkq', 1, 'active', '2026-02-16 16:28:57'),
(7, 'Phrah Amend', 'hamed@gmail.com', '9114567890', '$2y$12$3wtCP3JjYretdDL1oRPAiudMbcE4CRQekUeqFLvRToDlbbzXmW4tq', 3, 'active', '2026-03-07 05:06:01'),
(8, 'Red Cross Angels', 'red@cress.ems', '8112873', '$2y$12$5bkoLggR0Ru.b2vNc5esJuF0bqTPy3p5vOgDgO.iCy7sNDC1KXDG6', 2, 'active', '2026-03-07 05:12:54'),
(9, 'Test Account', 'test@mail.com', '9114567890', '$2y$12$pmTmnwhgoi4CGTu6ys2cze/bJhVcqQ5YEYJaXIhtnqgK0oPKg2KdO', 3, 'active', '2026-03-09 13:22:55'),
(10, 'Bola Tinubu', 'tinubu@mail.com', '12131323', '$2y$12$lUZ4WvRp01/aGiNbKPgcAO0TcV310CB3K6max285NM0IrNWAWV/mi', 2, 'active', '2026-03-09 13:30:52'),
(11, 'Kenya One', 'kenyaone@mail.com', '9114567890', '$2y$12$nR87NXvMjx0xjPKsIXKhe.pb3oIjiOVXCGM7eIVwI2e6kJcyQLVg.', 2, 'active', '2026-03-09 13:44:53');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `alerts`
--
ALTER TABLE `alerts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `alert_type_id` (`alert_type_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `alert_broadcasts`
--
ALTER TABLE `alert_broadcasts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `alert_id` (`alert_id`);

--
-- Indexes for table `alert_responses`
--
ALTER TABLE `alert_responses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `alert_id` (`alert_id`),
  ADD KEY `responder_id` (`responder_id`);

--
-- Indexes for table `alert_types`
--
ALTER TABLE `alert_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `api_tokens`
--
ALTER TABLE `api_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `community_users`
--
ALTER TABLE `community_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `phone` (`phone`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `system_logs`
--
ALTER TABLE `system_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `role_id` (`role_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `alerts`
--
ALTER TABLE `alerts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `alert_broadcasts`
--
ALTER TABLE `alert_broadcasts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `alert_responses`
--
ALTER TABLE `alert_responses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `alert_types`
--
ALTER TABLE `alert_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `api_tokens`
--
ALTER TABLE `api_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `community_users`
--
ALTER TABLE `community_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `system_logs`
--
ALTER TABLE `system_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `alerts`
--
ALTER TABLE `alerts`
  ADD CONSTRAINT `alerts_ibfk_1` FOREIGN KEY (`alert_type_id`) REFERENCES `alert_types` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `alerts_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `alert_broadcasts`
--
ALTER TABLE `alert_broadcasts`
  ADD CONSTRAINT `alert_broadcasts_ibfk_1` FOREIGN KEY (`alert_id`) REFERENCES `alerts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `alert_responses`
--
ALTER TABLE `alert_responses`
  ADD CONSTRAINT `alert_responses_ibfk_1` FOREIGN KEY (`alert_id`) REFERENCES `alerts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `alert_responses_ibfk_2` FOREIGN KEY (`responder_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `api_tokens`
--
ALTER TABLE `api_tokens`
  ADD CONSTRAINT `api_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `system_logs`
--
ALTER TABLE `system_logs`
  ADD CONSTRAINT `system_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
