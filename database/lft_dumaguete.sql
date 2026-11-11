-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 12, 2026 at 12:08 AM
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
-- Database: `lft_dumaguete`
--

-- --------------------------------------------------------

--
-- Table structure for table `amenities`
--

CREATE TABLE `amenities` (
  `id` int(10) UNSIGNED NOT NULL,
  `icon` varchar(120) NOT NULL,
  `name` varchar(160) NOT NULL,
  `description` text NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `site_id` tinyint(3) UNSIGNED NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `amenities`
--

INSERT INTO `amenities` (`id`, `icon`, `name`, `description`, `active`, `created_at`, `site_id`) VALUES
(1, 'fa-solid fa-wifi', 'Fast Wi-Fi', 'Reliable connectivity for calls, collaboration, and deep work.', 1, '2026-09-05 09:10:25', 1),
(2, 'fa-solid fa-mug-hot', 'Coffee corner', 'Good coffee and easy refills to keep your ideas in motion.', 1, '2026-09-05 09:10:25', 1),
(3, 'fa-solid fa-door-open', 'Meeting rooms', 'Private, professional spaces when your work needs a little more focus.', 1, '2026-09-05 09:10:25', 1),
(4, 'fa-solid fa-calendar-days', 'Community events', 'Workshops and gatherings that turn useful connections into momentum.', 1, '2026-09-05 09:10:25', 1),
(5, 'fa-solid fa-print', 'Work essentials', 'Printing, power, comfortable seating, and the tools you reach for every day.', 1, '2026-09-05 09:10:25', 1),
(6, 'fa-solid fa-location-dot', 'Central Dumaguete', 'Find us on Hibard St., close to the city energy and everyday essentials.', 1, '2026-09-05 09:10:25', 1);

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `booking_type` enum('booking','walk-in') NOT NULL,
  `space` varchar(160) NOT NULL,
  `space_id` int(10) UNSIGNED DEFAULT NULL,
  `visit_date` date NOT NULL,
  `visit_time` time NOT NULL,
  `duration_minutes` int(10) UNSIGNED NOT NULL DEFAULT 60,
  `guest_count` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `notes` text DEFAULT NULL,
  `status` varchar(40) NOT NULL DEFAULT 'Pending',
  `checked_in_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payment_status` enum('Unpaid','Pending','Paid','Failed','Refunded') NOT NULL DEFAULT 'Unpaid',
  `payment_reference` varchar(100) DEFAULT NULL,
  `payment_provider` varchar(50) DEFAULT NULL,
  `paid_at` datetime DEFAULT NULL,
  `payment_method` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `user_id`, `booking_type`, `space`, `space_id`, `visit_date`, `visit_time`, `duration_minutes`, `guest_count`, `notes`, `status`, `checked_in_at`, `created_at`, `updated_at`, `total_amount`, `payment_status`, `payment_reference`, `payment_provider`, `paid_at`, `payment_method`) VALUES
(1, 1, 'walk-in', 'Conference Room', 4, '2026-09-05', '22:13:00', 60, 1, '', 'Checked in', '2026-09-05 22:13:39', '2026-09-05 14:13:39', '2026-11-10 02:17:48', 0.00, 'Unpaid', NULL, NULL, NULL, NULL),
(2, 1, 'walk-in', 'LFT Commons', 1, '2026-09-05', '22:21:00', 480, 1, '', 'Checked in', '2026-09-05 22:21:08', '2026-09-05 14:21:08', '2026-11-10 02:17:48', 0.00, 'Unpaid', NULL, NULL, NULL, NULL),
(3, 1, 'booking', 'Focus Suite', 7, '2026-09-05', '22:21:00', 180, 1, '', 'Pending', NULL, '2026-09-05 14:21:53', '2026-11-10 02:17:48', 0.00, 'Unpaid', NULL, NULL, NULL, NULL),
(4, 1, 'booking', 'Podcast Studio', 2, '2026-09-05', '22:22:00', 240, 1, '', 'Pending', NULL, '2026-09-05 14:22:42', '2026-11-10 02:17:48', 0.00, 'Unpaid', NULL, NULL, NULL, NULL),
(5, 1, 'walk-in', 'LFT Reserve', 5, '2026-09-05', '22:35:00', 60, 1, '', 'Pending', NULL, '2026-09-05 14:35:53', '2026-11-10 02:17:48', 199.00, 'Unpaid', NULL, NULL, NULL, NULL),
(6, 1, 'walk-in', 'LFT Reserve', 5, '2026-09-05', '22:36:00', 120, 1, '', 'Cancelled', NULL, '2026-09-05 14:36:43', '2026-11-10 02:17:48', 398.00, 'Unpaid', NULL, NULL, NULL, NULL),
(7, 1, 'walk-in', 'LFT Reserve', 5, '2026-09-05', '22:39:00', 180, 1, '', 'Pending', NULL, '2026-09-05 14:39:15', '2026-11-10 02:17:48', 597.00, 'Unpaid', NULL, NULL, NULL, NULL),
(8, 1, 'walk-in', 'LFT Commons', 1, '2026-09-05', '22:56:00', 60, 1, '', 'Pending', NULL, '2026-09-05 14:56:28', '2026-11-10 02:17:48', 99.00, 'Unpaid', NULL, NULL, NULL, NULL),
(9, 1, 'walk-in', 'Conference Room', NULL, '2026-11-11', '23:32:00', 60, 1, '', 'Pending', NULL, '2026-11-11 15:32:54', '2026-11-11 15:33:07', 850.00, 'Unpaid', NULL, NULL, NULL, 'Pay at Counter');

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(10) UNSIGNED NOT NULL,
  `event_date` date NOT NULL,
  `event_time` time NOT NULL,
  `category` varchar(120) NOT NULL,
  `title` varchar(180) NOT NULL,
  `description` text NOT NULL,
  `image` varchar(255) NOT NULL DEFAULT 'conference.png',
  `location` varchar(180) NOT NULL DEFAULT 'LFT Dumaguete',
  `status` varchar(40) NOT NULL DEFAULT 'Active',
  `active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `event_registrations`
--

CREATE TABLE `event_registrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `event_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `registered_at` datetime NOT NULL DEFAULT current_timestamp(),
  `status` varchar(40) NOT NULL DEFAULT 'Registered'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `memberships`
--

CREATE TABLE `memberships` (
  `id` int(10) UNSIGNED NOT NULL,
  `label` varchar(120) NOT NULL,
  `name` varchar(160) NOT NULL,
  `price` varchar(80) NOT NULL,
  `period` varchar(80) NOT NULL,
  `description` text NOT NULL,
  `features` text NOT NULL,
  `featured` tinyint(1) NOT NULL DEFAULT 0,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `status` varchar(40) NOT NULL DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `memberships`
--

INSERT INTO `memberships` (`id`, `label`, `name`, `price`, `period`, `description`, `features`, `featured`, `active`, `status`, `created_at`) VALUES
(1, 'DAY PASS', 'Commons Day', '₱199', 'day', 'One full day of access to our shared workspace, Wi-Fi, and coffee.', 'Shared workspace\nWi-Fi\nCoffee', 0, 1, 'Active', '2026-09-05 09:10:25'),
(2, 'MOST FLEXIBLE', 'Monthly Commons', '₱3,500', 'month', 'Your regular desk away from home, plus community events and member rates.', 'Regular desk\nCommunity events\nMember rates', 1, 1, 'Active', '2026-09-05 09:10:25'),
(3, 'PRIVATE ACCESS', 'The Club', '₱25,000', 'month', 'A premium private room and full access for focused teams and professionals.', 'Private room\nFull access\nTeam workspace', 0, 1, 'Active', '2026-09-05 09:10:25');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `sender` varchar(120) NOT NULL,
  `email` varchar(190) NOT NULL,
  `subject` varchar(160) NOT NULL,
  `message` text NOT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'Unread',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `site_settings`
--

CREATE TABLE `site_settings` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `website_name` varchar(160) NOT NULL DEFAULT 'LFT Dumaguete',
  `email` varchar(190) NOT NULL DEFAULT 'lftdumaguete@gmail.com',
  `phone` varchar(40) NOT NULL DEFAULT '+63 9912345678',
  `address` varchar(255) NOT NULL DEFAULT 'Hibard St. Dumaguete City, Negros Oriental',
  `facebook` varchar(255) NOT NULL DEFAULT '',
  `instagram` varchar(255) NOT NULL DEFAULT '',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `site_settings`
--

INSERT INTO `site_settings` (`id`, `website_name`, `email`, `phone`, `address`, `facebook`, `instagram`, `updated_at`) VALUES
(1, 'LFT Dumaguete', 'lftdumaguete@gmail.com', '+63 9912345678', 'Hibard St. Dumaguete City, Negros Oriental', '', '', '2026-09-05 09:10:25');

-- --------------------------------------------------------

--
-- Table structure for table `spaces`
--

CREATE TABLE `spaces` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(160) NOT NULL,
  `category` varchar(120) NOT NULL,
  `description` text NOT NULL,
  `rates` text NOT NULL,
  `seats` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `image` varchar(255) NOT NULL DEFAULT 'common.png',
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `hourly_rate` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `spaces`
--

INSERT INTO `spaces` (`id`, `name`, `category`, `description`, `rates`, `seats`, `image`, `active`, `created_at`, `hourly_rate`) VALUES
(1, 'LFT Commons', 'Flexible desks', 'A bright, social workspace for freelancers and remote teams.', '₱199 / day', 30, 'common.png', 1, '2026-09-05 09:10:25', 99.00),
(2, 'Podcast Studio', 'Creator studio', 'A ready-to-use recording room for conversations and content.', '₱650 / hour', 4, 'podcast.png', 1, '2026-09-05 09:10:25', 650.00),
(3, 'The Club', 'Private workspace', 'A premium room with full access for teams who need room to grow.', '₱25,000 / month', 8, 'the club.png', 1, '2026-09-05 09:10:25', 0.00),
(4, 'Conference Room', 'Meetings', 'A professional setting for meetings and workshops.', '₱850 / hour', 12, 'conference.png', 1, '2026-09-05 09:10:25', 850.00),
(5, 'LFT Reserve', 'Private room', 'A private room reserved by the hour for focused work and calls.', '₱240 / hour', 4, 'common.png', 1, '2026-09-05 09:10:25', 199.00),
(6, 'Learning Studio', 'Learning and workshops', 'A welcoming room for classes, study sessions, and workshops.', '₱199 / hour\n₱548 / 3 hours', 20, 'common.png', 1, '2026-09-05 09:10:25', 199.00),
(7, 'Focus Suite', 'Deep work', 'A quiet suite for uninterrupted focus and productivity.', '₱149 / hour\n₱399 / 3 hours', 1, 'about.png', 1, '2026-09-05 09:10:25', 149.00),
(8, 'Team Suite', 'Team workspace', 'A collaborative room for meetings and growing teams.', '₱450 / hour\n₱1,600 / half day (4 hours)\n₱2,800 / day', 10, 'conference.png', 1, '2026-09-05 09:10:25', 450.00),
(9, 'Private Office', 'Private workspace', 'A private professional office for focused work and calls.', '₱1,250 / hour\n₱8,999 / day', 4, 'the club.png', 1, '2026-09-05 09:10:25', 450.00),
(10, 'Content Studio', 'Content creation', 'A ready-to-use studio for podcasts, presentations, and content.', '₱650 / hour\n₱3,999 / day', 6, 'podcast.png', 1, '2026-09-05 09:10:25', 650.00);

-- --------------------------------------------------------

--
-- Table structure for table `space_amenities`
--

CREATE TABLE `space_amenities` (
  `space_id` int(10) UNSIGNED NOT NULL,
  `amenity_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(120) NOT NULL,
  `email` varchar(190) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('customer','staff','admin') NOT NULL DEFAULT 'customer',
  `phone` varchar(40) NOT NULL DEFAULT '',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `profile_image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password_hash`, `role`, `phone`, `created_at`, `updated_at`, `profile_image`) VALUES
(1, 'Montes Angelina', 'test45@gmail.com', '$2y$10$ab1tad9Wo8XjUio9gK77quzTI7XgcQrLX0.GK6ifAgRGGGq6tTXEi', 'customer', '', '2026-09-05 09:11:09', '2026-09-05 17:35:31', 'user_1_9fa52b3f932c9097a50883d81cbca59a.jpg'),
(2, 'LFT Staff', 'staff@lftdumaguete.com', '$2y$10$ic7NH04F0su3aXubgA9QJudQVAOHw2w/XkHDhVzwxjriyjYI4zLHm', 'staff', '', '2026-09-05 15:42:15', '2026-09-05 15:42:15', NULL),
(3, 'LFT Administrator', 'admin@lftdumaguete.com', '$2y$10$slSuEBDHfqhmNB2xg6mF7erCSielT6ncQoiOm6ycP5bAJlph5YeOy', 'admin', '', '2026-09-05 16:34:03', '2026-09-05 16:34:03', NULL),
(4, 'ang', 'angelinamontes45@gmail.com', '$2y$10$2nEhQv6e9CenTQHVlvI81OKwLWva7FdXd7oAVHKtcOrgdx9EgfjzW', 'customer', '', '2026-09-14 11:01:28', '2026-09-14 11:01:28', NULL),
(5, 'Mark Anthony Quibot', 'quibotmarkanthony91@gmail.com', '$2y$10$iYdz9DXa7qiez456bjIk1eXD4rrSN/6HmTnh2rqDxayVtWBVIpdjO', 'customer', '', '2026-11-11 15:24:10', '2026-11-11 15:24:10', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_memberships`
--

CREATE TABLE `user_memberships` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `membership_id` int(10) UNSIGNED NOT NULL,
  `started_at` datetime NOT NULL,
  `ends_at` datetime DEFAULT NULL,
  `status` varchar(40) NOT NULL DEFAULT 'Active',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `amenities`
--
ALTER TABLE `amenities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_amenities_site` (`site_id`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_bookings_schedule` (`space`,`visit_date`,`visit_time`,`status`),
  ADD KEY `idx_bookings_user` (`user_id`,`visit_date`),
  ADD KEY `fk_bookings_space` (`space_id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `event_registrations`
--
ALTER TABLE `event_registrations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_event_user` (`event_id`,`user_id`),
  ADD KEY `idx_er_user` (`user_id`);

--
-- Indexes for table `memberships`
--
ALTER TABLE `memberships`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_messages_user` (`user_id`);

--
-- Indexes for table `site_settings`
--
ALTER TABLE `site_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `spaces`
--
ALTER TABLE `spaces`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_spaces_name` (`name`);

--
-- Indexes for table `space_amenities`
--
ALTER TABLE `space_amenities`
  ADD PRIMARY KEY (`space_id`,`amenity_id`),
  ADD KEY `idx_sa_amenity` (`amenity_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_users_email` (`email`);

--
-- Indexes for table `user_memberships`
--
ALTER TABLE `user_memberships`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_um_user` (`user_id`),
  ADD KEY `idx_um_membership` (`membership_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `amenities`
--
ALTER TABLE `amenities`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `event_registrations`
--
ALTER TABLE `event_registrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `memberships`
--
ALTER TABLE `memberships`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `spaces`
--
ALTER TABLE `spaces`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `user_memberships`
--
ALTER TABLE `user_memberships`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `amenities`
--
ALTER TABLE `amenities`
  ADD CONSTRAINT `fk_amenities_site` FOREIGN KEY (`site_id`) REFERENCES `site_settings` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `fk_bookings_space` FOREIGN KEY (`space_id`) REFERENCES `spaces` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_bookings_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `event_registrations`
--
ALTER TABLE `event_registrations`
  ADD CONSTRAINT `fk_er_event` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_er_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `fk_messages_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `space_amenities`
--
ALTER TABLE `space_amenities`
  ADD CONSTRAINT `fk_sa_amenity` FOREIGN KEY (`amenity_id`) REFERENCES `amenities` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_sa_space` FOREIGN KEY (`space_id`) REFERENCES `spaces` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_memberships`
--
ALTER TABLE `user_memberships`
  ADD CONSTRAINT `fk_um_membership` FOREIGN KEY (`membership_id`) REFERENCES `memberships` (`id`),
  ADD CONSTRAINT `fk_um_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
