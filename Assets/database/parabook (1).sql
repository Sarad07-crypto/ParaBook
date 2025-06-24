-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Generation Time: Jun 23, 2025 at 08:05 AM
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
-- Database: `parabook`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `booking_id` int(11) NOT NULL,
  `booking_no` varchar(50) NOT NULL,
  `user_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `pickup` varchar(255) NOT NULL,
  `flight_type` varchar(100) NOT NULL,
  `weight` decimal(5,2) DEFAULT NULL,
  `age` int(11) NOT NULL,
  `medical_condition` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` varchar(20) DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`booking_id`, `booking_no`, `user_id`, `date`, `pickup`, `flight_type`, `weight`, `age`, `medical_condition`, `created_at`, `updated_at`, `status`) VALUES
(1, 'BK-UDTI6YH8', 20, '2025-06-23', 'Lakeside,Pokhara', 'Normal Tandem', 70.00, 26, 'Nothing', '2025-06-22 13:08:38', '2025-06-22 14:07:24', 'confirmed'),
(2, 'BK-01LZIBJK', 21, '2025-06-24', 'Birauta,Pokhara', 'Michelle Wilson', 70.00, 25, 'aass', '2025-06-22 14:10:07', '2025-06-22 14:10:07', 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `company_services`
--

CREATE TABLE `company_services` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `company_name` varchar(255) NOT NULL,
  `service_title` varchar(255) NOT NULL,
  `address` text NOT NULL,
  `contact` varchar(20) NOT NULL,
  `pan_number` varchar(20) NOT NULL,
  `service_description` text NOT NULL,
  `thumbnail_path` varchar(500) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `company_services`
--

INSERT INTO `company_services` (`id`, `user_id`, `company_name`, `service_title`, `address`, `contact`, `pan_number`, `service_description`, `thumbnail_path`, `created_at`, `updated_at`) VALUES
(1, 18, 'Paragliders', 'par', 'Pokhara', '9826134758', '456854322', 'Hello bro whats up g', 'Assets/uploads/thumbnails/thumb_685812c778355.jpg', '2025-06-19 13:32:42', '2025-06-23 04:20:17'),
(3, 18, 'Walsh and Aguilar Plc', 'Soluta numquam fuga', 'Aut et dolor qui omn', 'Quam cum aut elit a', '737', 'Numquam esse quo ut molestiae molestias cupiditate', 'Assets/uploads/thumbnails/thumb_68552c135560f.jpg', '2025-06-20 09:38:27', '2025-06-20 09:38:27');

-- --------------------------------------------------------

--
-- Table structure for table `service_flight_types`
--

CREATE TABLE `service_flight_types` (
  `id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `flight_type_name` varchar(255) NOT NULL,
  `price` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `service_flight_types`
--

INSERT INTO `service_flight_types` (`id`, `service_id`, `flight_type_name`, `price`) VALUES
(4, 3, 'Michelle Wilson', '688'),
(6, 1, 'Normal Tandem', '5000'),
(7, 1, 'Premium', '10000000');

-- --------------------------------------------------------

--
-- Table structure for table `service_office_photos`
--

CREATE TABLE `service_office_photos` (
  `id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `photo_path` varchar(500) NOT NULL,
  `photo_order` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `service_office_photos`
--

INSERT INTO `service_office_photos` (`id`, `service_id`, `photo_path`, `photo_order`) VALUES
(1, 1, 'Assets/uploads/company_images/photo_0_6854117a192c3.jpg', 1),
(2, 1, 'Assets/uploads/company_images/photo_1_6854117a197b4.jpg', 2),
(3, 1, 'Assets/uploads/company_images/photo_2_6854117a19c0a.jpg', 3),
(4, 1, 'Assets/uploads/company_images/photo_3_6854117a1c71d.jpg', 4),
(9, 3, 'Assets/uploads/company_images/photo_3_0_68552c135ed66.jpg', 1),
(10, 3, 'Assets/uploads/company_images/photo_3_1_68552c135f6c8.jpg', 2),
(11, 3, 'Assets/uploads/company_images/photo_3_2_68552c135fd7f.jpg', 3),
(12, 3, 'Assets/uploads/company_images/photo_3_3_68552c1364bdf.jpg', 4);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `google_id` varchar(255) DEFAULT NULL,
  `facebook_id` varchar(255) DEFAULT NULL,
  `sign_with` enum('form','google','facebook') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `google_id`, `facebook_id`, `sign_with`) VALUES
(18, 'sabinpandey2061@gmail.com', '$2y$10$AUhB.TTlkXKDvhOkT1326eXfVcvZHPKjXkUJwvo8hxuGj.b5D5iwO', NULL, NULL, 'form'),
(20, 'sabinpandey2018@gmail.com', '$2y$10$Zsw5ASXEMfJ/OsVcXjWe7eMiUSLA/J0FWn.Y3MvBotOcch8y3cOyS', NULL, NULL, 'form'),
(21, 'sabinpandey2004@gmail.com', NULL, '106669582702814582034', NULL, 'google');

-- --------------------------------------------------------

--
-- Table structure for table `users_info`
--

CREATE TABLE `users_info` (
  `user_id` int(11) NOT NULL,
  `acc_type` varchar(50) DEFAULT NULL,
  `firstName` varchar(100) NOT NULL,
  `lastName` varchar(100) NOT NULL,
  `gender` varchar(20) DEFAULT NULL,
  `contact` varchar(10) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `avatar` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users_info`
--

INSERT INTO `users_info` (`user_id`, `acc_type`, `firstName`, `lastName`, `gender`, `contact`, `dob`, `country`, `avatar`) VALUES
(18, 'company', 'Sabin', 'Pandey', 'Male', '9826134758', '2004-05-10', 'Nepal', NULL),
(20, 'passenger', 'Sabin', 'Pandey', 'Male', '9826134758', '2004-05-10', 'Nepal', NULL),
(21, 'passenger', 'Sabin', 'Pandey', '', '', NULL, '', 'https://lh3.googleusercontent.com/a/ACg8ocKwl4kg_Hgz0Mww-4zQfCn3lTtNLb7ZIfOY7-h5RdorGk1j8A=s96-c');

-- --------------------------------------------------------

--
-- Table structure for table `users_sessions`
--

CREATE TABLE `users_sessions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `session_token` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `expires_at` datetime DEFAULT (current_timestamp() + interval 1 day)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users_sessions`
--

INSERT INTO `users_sessions` (`id`, `user_id`, `session_token`, `created_at`, `expires_at`) VALUES
(15, 18, '4ecf631bd5763e59f5c1a10b8cf74e0f', '2025-06-23 04:05:55', '2025-06-24 09:50:55'),
(19, 20, '2fd94474d19ae6799d468b1f6613dacc', '2025-06-23 03:52:18', '2025-06-24 09:37:18'),
(48, 21, 'b170eabe04efa6dbc42702a00b8e0e02', '2025-06-22 14:08:47', '2025-06-21 16:13:10');

-- --------------------------------------------------------

--
-- Table structure for table `users_verify`
--

CREATE TABLE `users_verify` (
  `user_id` int(11) NOT NULL,
  `verify_token` varchar(255) DEFAULT NULL,
  `is_verified` tinyint(1) NOT NULL DEFAULT 0,
  `otp` varchar(5) DEFAULT NULL,
  `otp_expiry` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users_verify`
--

INSERT INTO `users_verify` (`user_id`, `verify_token`, `is_verified`, `otp`, `otp_expiry`) VALUES
(18, '5529adeefdfcc9c5b8381bc295670263', 1, '27675', '2025-06-18 03:55:45'),
(20, '1669ae80e944336d876b091c3ccea2f2', 1, '43429', '2025-06-18 12:51:15'),
(21, NULL, 1, NULL, '2025-06-20 10:28:10');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`booking_id`),
  ADD UNIQUE KEY `booking_no` (`booking_no`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `company_services`
--
ALTER TABLE `company_services`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `service_flight_types`
--
ALTER TABLE `service_flight_types`
  ADD PRIMARY KEY (`id`),
  ADD KEY `service_id` (`service_id`);

--
-- Indexes for table `service_office_photos`
--
ALTER TABLE `service_office_photos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `service_id` (`service_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `users_info`
--
ALTER TABLE `users_info`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `users_sessions`
--
ALTER TABLE `users_sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `users_verify`
--
ALTER TABLE `users_verify`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `booking_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `company_services`
--
ALTER TABLE `company_services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `service_flight_types`
--
ALTER TABLE `service_flight_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `service_office_photos`
--
ALTER TABLE `service_office_photos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `users_info`
--
ALTER TABLE `users_info`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `users_sessions`
--
ALTER TABLE `users_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=80;

--
-- AUTO_INCREMENT for table `users_verify`
--
ALTER TABLE `users_verify`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `company_services`
--
ALTER TABLE `company_services`
  ADD CONSTRAINT `company_services_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users_info` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `service_flight_types`
--
ALTER TABLE `service_flight_types`
  ADD CONSTRAINT `service_flight_types_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `company_services` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `service_office_photos`
--
ALTER TABLE `service_office_photos`
  ADD CONSTRAINT `service_office_photos_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `company_services` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users_info`
--
ALTER TABLE `users_info`
  ADD CONSTRAINT `users_info_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users_sessions`
--
ALTER TABLE `users_sessions`
  ADD CONSTRAINT `users_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users_verify`
--
ALTER TABLE `users_verify`
  ADD CONSTRAINT `users_verify_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
