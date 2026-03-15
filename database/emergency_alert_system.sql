-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Mar 15, 2026 at 07:50 PM
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
  `severity` enum('Low','Medium','High') DEFAULT 'Medium',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `alerts`
--

INSERT INTO `alerts` (`id`, `alert_type_id`, `title`, `description`, `latitude`, `longitude`, `status`, `severity`, `created_by`, `created_at`) VALUES
(15, 9, 'Industrial Fire - Westgate Warehouse', 'Massive smoke detected in the chemical storage section. Fire department is on-site. Potential hazmat risk.', -1.26460000, 36.80450000, 'verified', 'Medium', 6, '2026-03-12 11:16:56'),
(16, 13, 'Multi-Vehicle Collision - Highway A1', 'Three-car pileup near the junction. 4 casualties reported. Emergency medical services and ambulances required immediately.', -1.28330000, 36.81670000, 'resolved', 'Medium', 5, '2026-03-12 11:21:13'),
(17, 11, 'Armed Robbery - Downtown Bank', 'Silent alarm triggered. Three suspects seen entering the premises. Police units are cordoning off the area.', -1.28610000, 36.82190000, 'pending', 'Medium', 6, '2026-03-12 11:24:43'),
(18, 12, 'Flash Flood Warning - River Basin Area', 'Heavy rainfall has caused the river to burst its banks. Residents in low-lying Zone B are advised to evacuate.', -1.30320000, 36.84110000, 'broadcasted', 'High', 6, '2026-03-12 11:26:04'),
(19, 9, 'Residential Fire - Parkland Estate', 'Kitchen fire spread to the roof of a two-story building. All residents evacuated. Containment in progress.', -1.25890000, 36.81220000, 'verified', 'High', 6, '2026-03-12 11:28:22'),
(20, 10, 'Test medical', 'This a medical test feature for the site', -12.45600000, 21.45600000, 'verified', 'Low', 5, '2026-03-12 12:05:14'),
(21, 9, 'Demo', 'Demo description', -12.40000000, 24.00000000, 'pending', 'Low', 6, '2026-03-14 14:11:46'),
(22, 10, 'Test title', 'This a test description of the form input', -12.21450000, 23.98000000, 'pending', 'Low', 12, '2026-03-15 14:45:14');

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
(2, 16, 5, 'On site checking situation', 'in_progress', '2026-03-12 12:09:39'),
(3, 20, 5, 'More CRP specialist needed on site, and please create roads', 'in_progress', '2026-03-14 11:18:48'),
(4, 19, 12, 'More water can, mobile clinics and more health persons needed on site to assist.', 'accepted', '2026-03-14 13:38:04');

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
(9, 'Fire', 'Fire emergency and smoke sightings', '2026-03-12 07:12:37'),
(10, 'Medical', 'Medical emergencies requiring ambulance', '2026-03-12 07:12:37'),
(11, 'Crime', 'Theft, assault, or suspicious activity', '2026-03-12 07:12:37'),
(12, 'Natural Disaster', 'Floods, earthquakes, or storms', '2026-03-12 07:12:37'),
(13, 'Accidents', 'Road accidents caused by vehicles, people ', '2026-03-12 11:19:24');

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
(2, 5, 'Responder responded to alert #1 (Fire): completed', '2026-02-19 05:49:39'),
(3, 5, 'Responder responded to alert #16 (Accidents): in_progress', '2026-03-12 12:09:39'),
(4, 5, 'Responder responded to alert #20 (Medical): in_progress', '2026-03-14 11:18:49'),
(5, 12, 'Responder responded to alert #19 (Fire): accepted', '2026-03-14 13:38:04');

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
  `api_token` varchar(255) DEFAULT NULL,
  `role_id` int(11) NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `phone`, `password`, `api_token`, `role_id`, `status`, `created_at`) VALUES
(5, 'Mark Taly', 'demo@test.com', '123456789', '$2y$12$uxlGHXe9RVFOvXDcUyr87OqVPsgryzrZgiMmf2WvCV910temcz/5O', NULL, 2, 'active', '2026-02-12 08:08:36'),
(6, 'Luka K. Mahlly', 'admin@test.com', '0712345678', '$2y$12$0L3PG6SyTr9LoJd64mSP5.ZTeSmgahThVFLvZT7u3Aci2ss46RKkq', NULL, 1, 'active', '2026-02-16 16:28:57'),
(7, 'Phrah Amend', 'hamed@gmail.com', '9114567890', '$2y$12$3wtCP3JjYretdDL1oRPAiudMbcE4CRQekUeqFLvRToDlbbzXmW4tq', NULL, 3, 'active', '2026-03-07 05:06:01'),
(8, 'Red Cross Angels', 'red@cress.ems', '8112873', '$2y$12$5bkoLggR0Ru.b2vNc5esJuF0bqTPy3p5vOgDgO.iCy7sNDC1KXDG6', NULL, 2, 'active', '2026-03-07 05:12:54'),
(10, 'Bola Tinubu', 'tinubu@mail.com', '12131323', '$2y$12$lUZ4WvRp01/aGiNbKPgcAO0TcV310CB3K6max285NM0IrNWAWV/mi', NULL, 2, 'active', '2026-03-09 13:30:52'),
(11, 'Kenya One', 'kenyaone@mail.com', '9114567890', '$2y$12$nR87NXvMjx0xjPKsIXKhe.pb3oIjiOVXCGM7eIVwI2e6kJcyQLVg.', NULL, 2, 'active', '2026-03-09 13:44:53'),
(12, 'Kioko Musau', 'musau@responda.com', '0712345678', '$2y$12$OF9kaYUo3mtrVQW3E67lmueLUsJs60SPXC/.mB1vbPIkOx0eEi0QW', NULL, 2, 'active', '2026-03-14 13:36:12'),
(13, 'Mohamed Hussen', 'hussen@gmail.com', '0712345678', '$2y$12$5NmtVwNidu.7z9JP/l4KrOJRKhqMAA7aoQbv3LTsCiwca2LYJ2wJ.', NULL, 2, 'active', '2026-03-15 14:51:55');

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
  ADD UNIQUE KEY `api_token` (`api_token`),
  ADD KEY `role_id` (`role_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `alerts`
--
ALTER TABLE `alerts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `alert_broadcasts`
--
ALTER TABLE `alert_broadcasts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `alert_responses`
--
ALTER TABLE `alert_responses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `alert_types`
--
ALTER TABLE `alert_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

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
