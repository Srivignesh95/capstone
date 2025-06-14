-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 14, 2025 at 06:23 PM
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
-- Database: `eventjoin`
--

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `event_date` date NOT NULL,
  `event_time` time NOT NULL,
  `hall_id` int(11) NOT NULL,
  `banner_image` varchar(255) DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `rsvp_deadline` date DEFAULT NULL,
  `rsvp_limit` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_public` tinyint(1) DEFAULT 0,
  `delete_requested` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `title`, `description`, `event_date`, `event_time`, `hall_id`, `banner_image`, `created_by`, `status`, `rsvp_deadline`, `rsvp_limit`, `created_at`, `is_public`, `delete_requested`) VALUES
(1, 'Check', 'I love my event', '2025-05-31', '20:00:00', 5, 'event_1_1749761145.jpg', 2, 'approved', '2025-05-31', 50, '2025-05-08 22:47:28', 0, 0),
(2, 'Testing public event', 'Teging the public event', '2025-05-08', '23:05:00', 5, 'event_2_1749760882.png', 2, 'approved', '2025-05-08', 100, '2025-05-09 01:04:03', 1, 0),
(3, 'Checking event', 'Checking events', '2025-06-30', '15:45:00', 3, 'event_3_1749761171.jpg', 2, 'approved', '2025-06-28', 100, '2025-06-12 16:44:28', 1, 0),
(4, 'checking RSVP limit', 'Checking the RSVP Limit ', '2025-07-10', '15:37:00', 3, NULL, 2, 'approved', '2025-06-13', 2, '2025-06-12 17:38:20', 0, 1);

-- --------------------------------------------------------

--
-- Table structure for table `event_rsvps`
--

CREATE TABLE `event_rsvps` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `rsvp_status` enum('yes','no') DEFAULT 'yes',
  `rsvp_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `event_rsvps`
--

INSERT INTO `event_rsvps` (`id`, `user_id`, `event_id`, `rsvp_status`, `rsvp_at`) VALUES
(1, 2, 2, 'yes', '2025-06-12 16:22:13'),
(2, 2, 3, 'yes', '2025-06-12 17:08:41');

-- --------------------------------------------------------

--
-- Table structure for table `guests`
--

CREATE TABLE `guests` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `event_id` int(11) NOT NULL,
  `note` text DEFAULT NULL,
  `plus_one` tinyint(1) DEFAULT 0,
  `rsvp_token` varchar(64) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `rsvp_status` enum('yes','no') DEFAULT NULL,
  `rsvp_note` text DEFAULT NULL,
  `rsvp_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `guests`
--

INSERT INTO `guests` (`id`, `name`, `email`, `event_id`, `note`, `plus_one`, `rsvp_token`, `created_at`, `rsvp_status`, `rsvp_note`, `rsvp_at`) VALUES
(1, 'Srivignesh', 'test@gmail.com', 1, 'Vegetarian', 0, NULL, '2025-05-08 23:13:24', NULL, NULL, NULL),
(2, 'Jane Smith', 'jane@example.com', 1, '', 0, NULL, '2025-05-08 23:13:24', NULL, NULL, NULL),
(5, 'test', 'ramnathkavle@gmail.com', 1, 'ssfdrfrferf', 1, 'dbedb9d43028d716a06168eb49517d3a', '2025-05-08 23:36:12', 'yes', 'testingg', '2025-05-09 00:08:52'),
(6, 'Srivignesh Kavle', 'ramnathkavle@gmail.com', 1, 'Hip hop hurray!!!', 1, 'ab961450bff6add0cd83f38f28e33ac1', '2025-05-09 00:16:48', 'yes', NULL, '2025-05-09 00:17:13'),
(7, 'John Doe', 'john@example.com', 3, 'Vegetarian', 1, '4ec06b45f9db20d156222183f67b38ea', '2025-06-12 17:25:55', NULL, NULL, NULL),
(8, 'Jane Smith', 'jane@example.com', 3, '', 0, '45cf5eacef3e65816d97600a658b887e', '2025-06-12 17:25:57', NULL, NULL, NULL),
(9, 'Vignesh', 'ramnathkavle@ymail.xom', 3, 'check', 0, '378195575d5866c52692fad31bb34be3', '2025-06-12 17:25:59', NULL, NULL, NULL),
(10, 'check', 'check@gmail.com', 4, 'cewfwefw', 0, '1d265e380becc42054579fd6008d8a86', '2025-06-12 17:43:14', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `halls`
--

CREATE TABLE `halls` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `halls`
--

INSERT INTO `halls` (`id`, `name`) VALUES
(4, 'Banquet Suite'),
(3, 'Conference Room 1'),
(1, 'Grand Hall A'),
(2, 'Grand Hall B'),
(5, 'Outdoor Garden');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `profile_pic` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('venue_manager','requestor','registered_user') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `name`, `email`, `phone`, `address`, `city`, `country`, `bio`, `profile_pic`, `password`, `role`) VALUES
(1, NULL, NULL, 'Srivignesh', 'admin@example.com', NULL, NULL, NULL, NULL, NULL, NULL, '$2a$12$f4EN66j6FHz.j4.ipHVPtOfHZCnVgj258iSoMjF73FS5h9olj6Loa', 'venue_manager'),
(2, 'Srivignesh ', 'Kavle', 'Srivignesh', 'requestor@example.com', '5485772236', '317, Bankside Drive', 'Kitchener', 'Canada', 'Bla bla bla ', 'user_2_1749687209.jpeg', '$2a$12$f4EN66j6FHz.j4.ipHVPtOfHZCnVgj258iSoMjF73FS5h9olj6Loa', 'requestor');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `hall_id` (`hall_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `event_rsvps`
--
ALTER TABLE `event_rsvps`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`,`event_id`),
  ADD KEY `event_id` (`event_id`);

--
-- Indexes for table `guests`
--
ALTER TABLE `guests`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `rsvp_token` (`rsvp_token`),
  ADD KEY `event_id` (`event_id`);

--
-- Indexes for table `halls`
--
ALTER TABLE `halls`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `event_rsvps`
--
ALTER TABLE `event_rsvps`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `guests`
--
ALTER TABLE `guests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `halls`
--
ALTER TABLE `halls`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `events_ibfk_1` FOREIGN KEY (`hall_id`) REFERENCES `halls` (`id`),
  ADD CONSTRAINT `events_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `event_rsvps`
--
ALTER TABLE `event_rsvps`
  ADD CONSTRAINT `event_rsvps_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `event_rsvps_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`);

--
-- Constraints for table `guests`
--
ALTER TABLE `guests`
  ADD CONSTRAINT `guests_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
