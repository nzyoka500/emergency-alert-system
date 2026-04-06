-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Apr 06, 2026 at 10:26 AM
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
-- Table structure for table `AlertBroadcasts`
--

CREATE TABLE `AlertBroadcasts` (
  `AlertBroadcasts_id` int(11) NOT NULL,
  `AlertBroadcasts_Alerts_id` int(11) NOT NULL,
  `AlertBroadcasts_sent_count` int(11) DEFAULT 0,
  `AlertBroadcasts_created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `AlertResponses`
--

CREATE TABLE `AlertResponses` (
  `AlertResponses_id` int(11) NOT NULL,
  `AlertResponses_Alerts_id` int(11) NOT NULL,
  `AlertResponses_Users_id` int(11) NOT NULL,
  `AlertResponses_note` text DEFAULT NULL,
  `AlertResponses_status` enum('accepted','in_progress','completed') DEFAULT 'accepted',
  `AlertResponses_responded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `AlertResponses`
--

INSERT INTO `AlertResponses` (`AlertResponses_id`, `AlertResponses_Alerts_id`, `AlertResponses_Users_id`, `AlertResponses_note`, `AlertResponses_status`, `AlertResponses_responded_at`) VALUES
(2, 16, 5, 'On site checking situation', 'in_progress', '2026-03-12 12:09:39'),
(5, 24, 12, 'All situations under control', 'completed', '2026-04-06 07:58:11'),
(6, 15, 12, 'Done investingation and all victicm atke to hsopital', 'completed', '2026-04-06 08:21:39');

-- --------------------------------------------------------

--
-- Table structure for table `Alerts`
--

CREATE TABLE `Alerts` (
  `Alerts_id` int(11) NOT NULL,
  `Alerts_AlertTypes_id` int(11) NOT NULL,
  `Alerts_title` varchar(150) NOT NULL,
  `Alerts_desc` text NOT NULL,
  `Alerts_latitude` decimal(10,8) NOT NULL,
  `Alerts_longitude` decimal(11,8) NOT NULL,
  `Alerts_status` enum('pending','verified','broadcasted','resolved') DEFAULT 'pending',
  `Alerts_severity` enum('Low','Medium','High') DEFAULT 'Medium',
  `Alerts_Users_id` int(11) DEFAULT NULL,
  `Alerts_AssignedTo_id` int(11) DEFAULT NULL,
  `Alerts_created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Alerts`
--

INSERT INTO `Alerts` (`Alerts_id`, `Alerts_AlertTypes_id`, `Alerts_title`, `Alerts_desc`, `Alerts_latitude`, `Alerts_longitude`, `Alerts_status`, `Alerts_severity`, `Alerts_Users_id`, `Alerts_AssignedTo_id`, `Alerts_created_at`) VALUES
(15, 9, 'Industrial Fire - Westgate Warehouse', 'Massive smoke detected...', -1.26460000, 36.80450000, 'verified', 'Medium', 6, NULL, '2026-03-12 11:16:56'),
(16, 13, 'Multi-Vehicle Collision - Highway A1', 'Three-car pileup...', -1.28330000, 36.81670000, 'resolved', 'Medium', 5, NULL, '2026-03-12 11:21:13'),
(24, 11, 'House Break-in - Karen Estate', 'Homeowner reported suspicious activity and a broken window. Local security firm is responding.', -1.34100000, 36.71200000, 'resolved', 'High', 12, NULL, '2026-04-06 07:48:49'),
(25, 10, 'Suspected Heart Attack - Train Station', 'Passenger collapsed on the platform. Bystanders started CPR. Ambulance dispatched.', -1.28400000, 36.82900000, 'verified', 'High', 5, NULL, '2026-04-06 07:48:49'),
(26, 13, 'Public Bus Collision - Ngong Road', 'A matatu and a private car collided near the junction. Traffic is heavily affected.', -1.29800000, 36.79100000, 'broadcasted', 'Medium', 6, NULL, '2026-04-06 07:48:49'),
(27, 9, 'Trash Compactor Fire - Industrial Area', 'Small electrical fire in a waste management facility. Fire extinguishers used by staff.', -1.31200000, 36.85200000, 'resolved', 'Low', 5, NULL, '2026-04-06 07:48:49'),
(28, 12, 'Strong Winds - Roof Damage', 'Extreme wind gusts have ripped roof tiles off several houses in the North District.', -1.22100000, 36.89200000, 'verified', 'Medium', 12, NULL, '2026-04-06 07:48:49'),
(29, 11, 'Pickpocketing Report - Market Street', 'Multiple reports of petty theft in the crowded market area. Increased police patrol requested.', -1.28100000, 36.82400000, 'resolved', 'Low', 6, NULL, '2026-04-06 07:48:49'),
(30, 10, 'Allergic Reaction - University Cafeteria', 'Student experiencing severe anaphylactic shock. EpiPen administered, waiting for paramedics.', -1.27300000, 36.81100000, 'verified', 'High', 12, NULL, '2026-04-06 07:48:49'),
(31, 13, 'Minor Rear-end Collision - Highway', 'Two vehicles collided at low speed. No injuries reported. Traffic police cleared the scene within 30 minutes.', -1.28050000, 36.81020000, 'resolved', 'Low', 5, NULL, '2026-03-05 06:15:00'),
(32, 10, 'Fainting Incident - Central Library', 'Student fainted due to exhaustion. First aid was administered by the campus nurse. Patient recovered fully.', -1.27120000, 36.80450000, 'resolved', 'Medium', 12, NULL, '2026-03-10 11:30:00'),
(33, 9, 'Small Rubbish Fire - Alleyway', 'Dumpster fire reported behind the market. Fire department extinguished it before it could spread to nearby structures.', -1.29440000, 36.82110000, 'resolved', 'High', 6, NULL, '2026-03-15 19:10:00'),
(34, 11, 'Shoplifting Apprehension - Electronics Store', 'Suspect caught by store security with stolen goods. Police arrived and processed the individual on site.', -1.26100000, 36.83330000, 'resolved', 'Low', 12, NULL, '2026-03-20 08:45:00'),
(35, 12, 'Storm Drain Overflow - Main Ave', 'Localized flooding caused by a blocked drain during a heavy downpour. City council maintenance cleared the blockage.', -1.30220000, 36.84550000, 'resolved', 'Medium', 5, NULL, '2026-03-25 13:20:00');

-- --------------------------------------------------------

--
-- Table structure for table `AlertTypes`
--

CREATE TABLE `AlertTypes` (
  `AlertTypes_id` int(11) NOT NULL,
  `AlertTypes_name` varchar(100) NOT NULL,
  `AlertTypes_desc` text DEFAULT NULL,
  `AlertTypes_created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `AlertTypes`
--

INSERT INTO `AlertTypes` (`AlertTypes_id`, `AlertTypes_name`, `AlertTypes_desc`, `AlertTypes_created_at`) VALUES
(9, 'Fire', 'Fire emergency and smoke sightings', '2026-03-12 07:12:37'),
(10, 'Medical', 'Medical emergencies requiring ambulance', '2026-03-12 07:12:37'),
(11, 'Crime', 'Theft, assault, or suspicious activity', '2026-03-12 07:12:37'),
(12, 'Natural Disaster', 'Floods, earthquakes, or storms', '2026-03-12 07:12:37'),
(13, 'Accidents', 'Road accidents caused by vehicles, people ', '2026-03-12 11:19:24');

-- --------------------------------------------------------

--
-- Table structure for table `ApiTokens`
--

CREATE TABLE `ApiTokens` (
  `ApiTokens_id` int(11) NOT NULL,
  `ApiTokens_Users_id` int(11) NOT NULL,
  `ApiTokens_token` varchar(255) NOT NULL,
  `ApiTokens_expires_at` datetime DEFAULT NULL,
  `ApiTokens_created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `CommunityUsers`
--

CREATE TABLE `CommunityUsers` (
  `CommunityUsers_id` int(11) NOT NULL,
  `CommunityUsers_full_name` varchar(100) NOT NULL,
  `CommunityUsers_phone` varchar(20) DEFAULT NULL,
  `CommunityUsers_device_token` text DEFAULT NULL,
  `CommunityUsers_latitude` decimal(10,8) DEFAULT NULL,
  `CommunityUsers_longitude` decimal(11,8) DEFAULT NULL,
  `CommunityUsers_created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `Roles`
--

CREATE TABLE `Roles` (
  `Roles_id` int(11) NOT NULL,
  `Roles_name` varchar(50) NOT NULL,
  `Roles_created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Roles`
--

INSERT INTO `Roles` (`Roles_id`, `Roles_name`, `Roles_created_at`) VALUES
(1, 'Admin', '2026-02-11 09:56:59'),
(2, 'Responder', '2026-02-11 09:56:59'),
(3, 'Community', '2026-03-07 05:04:28');

-- --------------------------------------------------------

--
-- Table structure for table `SystemLogs`
--

CREATE TABLE `SystemLogs` (
  `SystemLogs_id` int(11) NOT NULL,
  `SystemLogs_Users_id` int(11) DEFAULT NULL,
  `SystemLogs_action` varchar(255) DEFAULT NULL,
  `SystemLogs_created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `SystemLogs`
--

INSERT INTO `SystemLogs` (`SystemLogs_id`, `SystemLogs_Users_id`, `SystemLogs_action`, `SystemLogs_created_at`) VALUES
(1, 5, 'Responder responded to alert #1 (Fire): accepted', '2026-02-19 05:47:23'),
(6, 12, 'Responder responded to alert #24 (Crime): in_progress', '2026-04-06 07:53:52'),
(7, 12, 'Responder responded to alert #24 (Crime): completed', '2026-04-06 07:58:11'),
(8, 12, 'Responder responded to alert #15 (Fire): completed', '2026-04-06 08:21:39');

-- --------------------------------------------------------

--
-- Table structure for table `Users`
--

CREATE TABLE `Users` (
  `Users_id` int(11) NOT NULL,
  `Users_full_name` varchar(100) NOT NULL,
  `Users_email` varchar(100) NOT NULL,
  `Users_phone` varchar(20) DEFAULT NULL,
  `Users_password` varchar(255) NOT NULL,
  `Users_api_token` varchar(255) DEFAULT NULL,
  `Users_Roles_id` int(11) NOT NULL,
  `Users_status` enum('active','inactive') DEFAULT 'active',
  `Users_created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Users`
--

INSERT INTO `Users` (`Users_id`, `Users_full_name`, `Users_email`, `Users_phone`, `Users_password`, `Users_api_token`, `Users_Roles_id`, `Users_status`, `Users_created_at`) VALUES
(5, 'Mark Taly', 'demo@test.com', '123456789', '$2y$12$uxlGHXe9RVFOvXDcUyr87OqVPsgryzrZgiMmf2WvCV910temcz/5O', 'fbfc0da0d3849128207d31f8dd843c24763dc42b365f2d0cd75f4dc0adc5534a', 2, 'active', '2026-02-12 08:08:36'),
(6, 'Luka K. Mahlly', 'admin@test.com', '0712345678', '$2y$12$0L3PG6SyTr9LoJd64mSP5.ZTeSmgahThVFLvZT7u3Aci2ss46RKkq', '829f1daff032e1e16888c32a1f317d3319a1d5ebab03f3cc47456fca8c09595c', 1, 'active', '2026-02-16 16:28:57'),
(12, 'Kioko Musau', 'musau@responda.com', '0712345678', '$2y$12$OF9kaYUo3mtrVQW3E67lmueLUsJs60SPXC/.mB1vbPIkOx0eEi0QW', NULL, 2, 'active', '2026-03-14 13:36:12');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `AlertBroadcasts`
--
ALTER TABLE `AlertBroadcasts`
  ADD PRIMARY KEY (`AlertBroadcasts_id`),
  ADD KEY `AlertBroadcasts_Alerts_id` (`AlertBroadcasts_Alerts_id`);

--
-- Indexes for table `AlertResponses`
--
ALTER TABLE `AlertResponses`
  ADD PRIMARY KEY (`AlertResponses_id`),
  ADD KEY `AlertResponses_Alerts_id` (`AlertResponses_Alerts_id`),
  ADD KEY `AlertResponses_Users_id` (`AlertResponses_Users_id`);

--
-- Indexes for table `Alerts`
--
ALTER TABLE `Alerts`
  ADD PRIMARY KEY (`Alerts_id`),
  ADD KEY `Alerts_AlertTypes_id` (`Alerts_AlertTypes_id`),
  ADD KEY `Alerts_Users_id` (`Alerts_Users_id`),
  ADD KEY `Alerts_AssignedTo_id` (`Alerts_AssignedTo_id`);

--
-- Indexes for table `AlertTypes`
--
ALTER TABLE `AlertTypes`
  ADD PRIMARY KEY (`AlertTypes_id`),
  ADD UNIQUE KEY `AlertTypes_name` (`AlertTypes_name`);

--
-- Indexes for table `ApiTokens`
--
ALTER TABLE `ApiTokens`
  ADD PRIMARY KEY (`ApiTokens_id`),
  ADD UNIQUE KEY `ApiTokens_token` (`ApiTokens_token`),
  ADD KEY `ApiTokens_Users_id` (`ApiTokens_Users_id`);

--
-- Indexes for table `CommunityUsers`
--
ALTER TABLE `CommunityUsers`
  ADD PRIMARY KEY (`CommunityUsers_id`),
  ADD UNIQUE KEY `CommunityUsers_phone` (`CommunityUsers_phone`);

--
-- Indexes for table `Roles`
--
ALTER TABLE `Roles`
  ADD PRIMARY KEY (`Roles_id`),
  ADD UNIQUE KEY `Roles_name` (`Roles_name`);

--
-- Indexes for table `SystemLogs`
--
ALTER TABLE `SystemLogs`
  ADD PRIMARY KEY (`SystemLogs_id`),
  ADD KEY `SystemLogs_Users_id` (`SystemLogs_Users_id`);

--
-- Indexes for table `Users`
--
ALTER TABLE `Users`
  ADD PRIMARY KEY (`Users_id`),
  ADD UNIQUE KEY `Users_email` (`Users_email`),
  ADD UNIQUE KEY `Users_api_token` (`Users_api_token`),
  ADD KEY `Users_Roles_id` (`Users_Roles_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `AlertBroadcasts`
--
ALTER TABLE `AlertBroadcasts`
  MODIFY `AlertBroadcasts_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `AlertResponses`
--
ALTER TABLE `AlertResponses`
  MODIFY `AlertResponses_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `Alerts`
--
ALTER TABLE `Alerts`
  MODIFY `Alerts_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `AlertTypes`
--
ALTER TABLE `AlertTypes`
  MODIFY `AlertTypes_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `ApiTokens`
--
ALTER TABLE `ApiTokens`
  MODIFY `ApiTokens_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `CommunityUsers`
--
ALTER TABLE `CommunityUsers`
  MODIFY `CommunityUsers_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Roles`
--
ALTER TABLE `Roles`
  MODIFY `Roles_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `SystemLogs`
--
ALTER TABLE `SystemLogs`
  MODIFY `SystemLogs_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `Users`
--
ALTER TABLE `Users`
  MODIFY `Users_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `AlertBroadcasts`
--
ALTER TABLE `AlertBroadcasts`
  ADD CONSTRAINT `Broadcasts_FK_Alerts` FOREIGN KEY (`AlertBroadcasts_Alerts_id`) REFERENCES `Alerts` (`Alerts_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `AlertResponses`
--
ALTER TABLE `AlertResponses`
  ADD CONSTRAINT `Responses_FK_Alerts` FOREIGN KEY (`AlertResponses_Alerts_id`) REFERENCES `Alerts` (`Alerts_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `Responses_FK_Users` FOREIGN KEY (`AlertResponses_Users_id`) REFERENCES `Users` (`Users_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `Alerts`
--
ALTER TABLE `Alerts`
  ADD CONSTRAINT `Alerts_FK_AssignedTo` FOREIGN KEY (`Alerts_AssignedTo_id`) REFERENCES `Users` (`Users_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `Alerts_FK_Types` FOREIGN KEY (`Alerts_AlertTypes_id`) REFERENCES `AlertTypes` (`AlertTypes_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `Alerts_FK_Users` FOREIGN KEY (`Alerts_Users_id`) REFERENCES `Users` (`Users_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `ApiTokens`
--
ALTER TABLE `ApiTokens`
  ADD CONSTRAINT `Tokens_FK_Users` FOREIGN KEY (`ApiTokens_Users_id`) REFERENCES `Users` (`Users_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `SystemLogs`
--
ALTER TABLE `SystemLogs`
  ADD CONSTRAINT `Logs_FK_Users` FOREIGN KEY (`SystemLogs_Users_id`) REFERENCES `Users` (`Users_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `Users`
--
ALTER TABLE `Users`
  ADD CONSTRAINT `Users_FK_Roles` FOREIGN KEY (`Users_Roles_id`) REFERENCES `Roles` (`Roles_id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
