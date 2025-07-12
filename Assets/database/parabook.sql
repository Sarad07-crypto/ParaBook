-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Generation Time: Jul 12, 2025 at 04:47 AM
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
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `contact` varchar(15) NOT NULL,
  `password` varchar(255) NOT NULL,
  `date_of_birth` date NOT NULL,
  `gender` enum('Male','Female') NOT NULL,
  `role` enum('main_admin','sub_admin') NOT NULL DEFAULT 'sub_admin',
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `first_name`, `last_name`, `email`, `contact`, `password`, `date_of_birth`, `gender`, `role`, `status`, `created_at`, `updated_at`, `approved_by`, `approved_at`) VALUES
(1, 'Karun', 'Sunuwar', 'karunkarun9797@gmail.com', '9826197849', '$2y$10$4xMlDVldSAdGo4DKaQUmZeGtBEokA6ZezkSJxZrtZdEZs4/rilWV2', '1111-11-11', 'Male', 'main_admin', 'approved', '2025-07-07 06:25:43', '2025-07-07 06:25:43', NULL, '2025-07-07 02:40:43'),
(10, 'Dustin', 'Shields', 'nukyv@mailinator.com', '9800000000', '$2y$10$fYNRbEGBxlBjW7w8gfYMPOlD.2k/wqcAdEHmaVg.szIj025vHCEy.', '1971-05-15', 'Male', 'sub_admin', 'pending', '2025-07-07 06:40:44', '2025-07-07 06:40:44', NULL, NULL),
(11, 'Abdul', 'Glenn', 'felegugi@mailinator.com', '9800000000', '$2y$10$r18RtPEjMKYkSi8s5n845ODKvH05xejV4d2rPdgl.e80Nk2KVZNpi', '2008-07-07', 'Male', 'sub_admin', 'pending', '2025-07-07 06:42:00', '2025-07-07 06:42:00', NULL, NULL),
(12, 'Regan', 'Mason', 'lyfyxa@mailinator.com', '9846303212', '$2y$10$CNGr1H46A00EnLOe/DDCYOoE.K3yvPmzFzttOa4QchOAxPiER01Zq', '1989-09-09', 'Female', 'sub_admin', 'approved', '2025-07-07 06:42:50', '2025-07-07 13:10:38', 1, '2025-07-07 09:25:38');

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `booking_id` int(11) NOT NULL,
  `booking_no` varchar(50) NOT NULL,
  `user_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `pickup` varchar(255) NOT NULL,
  `flight_type` varchar(100) NOT NULL,
  `weight` decimal(5,2) DEFAULT NULL,
  `age` int(11) NOT NULL,
  `medical_condition` text DEFAULT NULL,
  `status` varchar(20) DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`booking_id`, `booking_no`, `user_id`, `service_id`, `date`, `pickup`, `flight_type`, `weight`, `age`, `medical_condition`, `status`, `created_at`, `updated_at`, `total_amount`) VALUES
(2, 'BK-L8BKR2HO', 43, 23, '2025-07-25', 'Jarebar', 'Josiah Sweeney', 70.00, 25, 'Excited', 'completed', '2025-07-10 09:18:32', '2025-07-11 03:39:24', 514.00),
(3, 'BK-BRAP7DKU', 43, 19, '2025-07-25', 'Nareshwor', 'Pro Tandem', 70.00, 25, 'afsdasfas', 'completed', '2025-07-11 16:03:37', '2025-07-11 16:05:58', 12000.00),
(4, 'BK-SK7UJX19', 23, 19, '2025-07-25', 'Lakeside', 'Pro Tandem', 70.00, 25, 'asdfasfsa', 'completed', '2025-07-11 16:09:43', '2025-07-11 16:10:22', 12000.00);

-- --------------------------------------------------------

--
-- Table structure for table `chat_conversations`
--

CREATE TABLE `chat_conversations` (
  `id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `passenger_user_id` int(11) NOT NULL,
  `company_user_id` int(11) NOT NULL,
  `last_message_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `passenger_unread_count` int(11) DEFAULT 0,
  `company_unread_count` int(11) DEFAULT 0,
  `status` enum('active','closed') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chat_conversations`
--

INSERT INTO `chat_conversations` (`id`, `service_id`, `passenger_user_id`, `company_user_id`, `last_message_at`, `passenger_unread_count`, `company_unread_count`, `status`, `created_at`, `updated_at`) VALUES
(1, 22, 43, 41, '2025-07-01 17:38:51', 0, 0, 'active', '2025-06-29 07:13:36', '2025-07-11 09:28:47'),
(2, 20, 43, 41, '2025-07-05 02:41:52', 0, 0, 'active', '2025-06-29 12:59:39', '2025-07-11 09:28:13'),
(3, 22, 23, 41, '2025-06-30 06:11:47', 0, 0, 'active', '2025-06-30 06:11:36', '2025-07-06 09:07:15'),
(4, 19, 43, 41, '2025-07-02 05:06:07', 0, 0, 'active', '2025-07-02 05:06:07', '2025-07-06 07:47:42'),
(5, 23, 23, 41, '2025-07-11 15:10:48', 0, 0, 'active', '2025-07-11 15:10:48', '2025-07-11 15:10:48');

-- --------------------------------------------------------

--
-- Table structure for table `chat_messages`
--

CREATE TABLE `chat_messages` (
  `id` int(11) NOT NULL,
  `conversation_id` int(11) NOT NULL,
  `sender_user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `message_type` enum('text','image','file') DEFAULT 'text',
  `file_path` varchar(500) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chat_messages`
--

INSERT INTO `chat_messages` (`id`, `conversation_id`, `sender_user_id`, `message`, `message_type`, `file_path`, `is_read`, `created_at`, `updated_at`) VALUES
(1, 1, 43, 'Hello', 'text', NULL, 1, '2025-06-29 09:46:50', '2025-07-02 06:11:49'),
(2, 1, 43, 'Karun', 'text', NULL, 1, '2025-06-29 09:47:36', '2025-07-02 06:11:49'),
(3, 1, 43, 'Hello', 'text', NULL, 1, '2025-06-29 09:54:27', '2025-07-02 06:11:49'),
(4, 1, 43, 'New Message', 'text', NULL, 1, '2025-06-29 10:15:17', '2025-07-02 06:11:49'),
(5, 1, 43, 'Hi', 'text', NULL, 1, '2025-06-29 10:20:12', '2025-07-02 06:11:49'),
(6, 1, 43, 'fasdfasfa', 'text', NULL, 1, '2025-06-29 10:25:15', '2025-07-02 06:11:49'),
(7, 1, 43, 'asfsafd', 'text', NULL, 1, '2025-06-29 10:35:52', '2025-07-02 06:11:49'),
(8, 1, 43, 'fasdfasfasfasdfas', 'text', NULL, 1, '2025-06-29 10:49:32', '2025-07-02 06:11:49'),
(9, 1, 43, 'asfassaf', 'text', NULL, 1, '2025-06-29 10:54:06', '2025-07-02 06:11:49'),
(10, 1, 43, 'asfasdfas', 'text', NULL, 1, '2025-06-29 10:58:45', '2025-07-02 06:11:49'),
(11, 2, 43, 'Hello I\'m Karun', 'text', NULL, 1, '2025-06-29 13:00:02', '2025-07-02 06:07:52'),
(12, 2, 43, 'are you online ?', 'text', NULL, 1, '2025-06-29 13:08:41', '2025-07-02 06:07:52'),
(13, 2, 43, 'hi', 'text', NULL, 1, '2025-06-29 13:18:46', '2025-07-02 06:07:52'),
(14, 2, 43, 'bye', 'text', NULL, 1, '2025-06-29 13:26:37', '2025-07-02 06:07:52'),
(15, 2, 43, 'New', 'text', NULL, 1, '2025-06-29 13:32:04', '2025-07-02 06:07:52'),
(16, 2, 43, 'Did you received this message ?', 'text', NULL, 1, '2025-06-29 13:32:19', '2025-07-02 06:07:52'),
(17, 2, 43, 'Hello Hello', 'text', NULL, 1, '2025-06-29 15:07:43', '2025-07-02 06:07:52'),
(18, 3, 23, 'Hello', 'text', NULL, 1, '2025-06-30 06:11:42', '2025-07-02 06:11:40'),
(19, 3, 23, 'Are you there', 'text', NULL, 1, '2025-06-30 06:11:47', '2025-07-02 06:11:40'),
(20, 1, 43, 'New Message', 'text', NULL, 1, '2025-07-01 17:38:51', '2025-07-02 06:11:49'),
(21, 2, 43, 'This is a new message', 'text', NULL, 1, '2025-07-02 05:06:26', '2025-07-02 06:07:52'),
(22, 2, 41, 'How can I help you', 'text', NULL, 1, '2025-07-02 15:57:01', '2025-07-04 17:15:59'),
(23, 2, 43, 'I\'m wondering what if I\'ve to cancel the flight, will you refund me', 'text', NULL, 1, '2025-07-02 15:59:00', '2025-07-02 16:00:53'),
(24, 2, 41, 'Okay so to refund you\'ve to visit our office remotely', 'text', NULL, 1, '2025-07-02 16:00:14', '2025-07-04 17:15:59'),
(25, 2, 43, 'Thanks for the quick response', 'text', NULL, 1, '2025-07-02 16:00:30', '2025-07-02 16:00:53'),
(26, 2, 43, 'Sure you can leave messages if you need to query something else', 'text', NULL, 1, '2025-07-05 02:41:26', '2025-07-05 09:54:48'),
(27, 2, 41, 'for sure', 'text', NULL, 1, '2025-07-05 02:41:52', '2025-07-05 02:43:02');

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
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `company_services`
--

INSERT INTO `company_services` (`id`, `user_id`, `company_name`, `service_title`, `address`, `contact`, `pan_number`, `service_description`, `thumbnail_path`, `status`, `created_at`, `updated_at`) VALUES
(19, 41, 'Flying Monkeys', 'Smooth Surfing', 'Lakeside, Gaurighat', '9812345678', '354', 'Eveniet sit nisi maiores quasi est temporibus sed Eum eos voluptate ad veritatis vero Temporibus est modi voluptas dolorum qui Dolor dolores qui perferendis adipisci fugiat in velit veniam nostrum sint aut quasi quas error Aut quis eos exercitationem laborum Voluptates non commodi nulla eius velit praesentium officiis ducimus aliquam cum deserunt fugit elit Voluptas est nihil deleniti quae dicta eligendi praesentium laborum', 'Assets/uploads/thumbnails/thumb_6856bb0fd716a.jpg', 'approved', '2025-06-21 03:05:42', '2025-07-05 15:46:34'),
(20, 41, 'Blue Sky Paragliding', 'Professional Flight Service', 'Lakeside', '9826197940', '123456789', 'Welcome to Blue Sky Paragliding, where your dream of flying becomes reality. Nestled in the heart of scenic valleys and towering peaks, we offer more than just a ride—we offer an unforgettable journey through the open skies. Feel the rush as you take off, leaving the ground behind and soaring into a canvas of endless blue. Below you, rivers wind like silver threads and villages dot the landscape like stories waiting to be told.\r\n\r\nOur team of experienced pilots ensures every flight is safe, smooth, and tailored to your comfort—whether you\'re an adventure junkie or a first-time flyer. From gentle scenic glides to thrilling cross-country flights, there\'s something magical for everyone.\r\n\r\nLet the sky be your playground. With Blue Sky Paragliding, every takeoff is the beginning of a new story.', 'Assets/uploads/thumbnails/thumb_685ab71e2fa93.jpg', 'pending', '2025-06-21 03:26:50', '2025-07-06 07:24:24'),
(21, 23, 'Mayer and Stuart Co', 'Dolor dolorem quia e', 'Excepteur officia au', '9826197849', '123456789', 'Exercitationem delectus voluptas tempora qui asperiores libero animi velit in sed voluptatem', 'Assets/uploads/thumbnails/thumb_685792ff91bee.jpg', 'approved', '2025-06-22 05:22:07', '2025-07-05 15:55:08'),
(22, 41, 'Dragon Flies', 'Voluptas dolore even', 'Commodi dolore eos ', '9823451678', '123456789', 'Sequi porro incididunt cillum accusamus qui', 'Assets/uploads/thumbnails/thumb_685ad07fa1f82.jpg', 'approved', '2025-06-24 16:21:19', '2025-07-06 03:07:44'),
(23, 41, 'Fulton Poole Plc', 'Aut veniam minim eo', 'Laudantium cupidata', '9826197849', '331123456', 'Odit iusto minim officia itaque voluptatem illum at et deleniti magni rerum in nulla', 'Assets/uploads/thumbnails/thumb_686904a1cf0c8.jpg', 'approved', '2025-07-05 10:55:29', '2025-07-06 06:49:23');

-- --------------------------------------------------------

--
-- Table structure for table `esewainfo`
--

CREATE TABLE `esewainfo` (
  `esewaid` int(11) NOT NULL,
  `booking_no` varchar(10) NOT NULL,
  `amount` float NOT NULL,
  `transaction_code` varchar(200) NOT NULL,
  `transaction_date` date NOT NULL,
  `status` varchar(100) NOT NULL,
  `pickup` varchar(200) NOT NULL,
  `flight_type` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `esewainfo`
--

INSERT INTO `esewainfo` (`esewaid`, `booking_no`, `amount`, `transaction_code`, `transaction_date`, `status`, `pickup`, `flight_type`) VALUES
(43, 'BK-FYMACSK', 1500, '000B3UZ', '2025-07-01', 'completed', 'Dolorum omnis expedi', 'Xena Marsh'),
(43, 'BK-ZWFKRO4', 533, '000B6OV', '2025-07-10', 'completed', 'Lakeside', 'Mary Patel'),
(43, 'BK-YGLM96E', 533, '000B6V5', '2025-07-10', 'completed', 'Jarebar', 'Mary Patel'),
(43, 'BK-L8BKR2H', 514, '000B6VP', '2025-07-10', 'completed', 'Jarebar', 'Josiah Sweeney'),
(43, 'BK-BRAP7DK', 12000, '000B7DO', '2025-07-11', 'completed', 'Nareshwor', 'Pro Tandem'),
(23, 'BK-SK7UJX1', 12000, '000B7DP', '2025-07-11', 'completed', 'Lakeside', 'Pro Tandem');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `recipient_id` int(11) NOT NULL,
  `recipient_type` enum('user','company','admin') NOT NULL DEFAULT 'user',
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` varchar(50) NOT NULL DEFAULT 'general',
  `icon` varchar(100) DEFAULT 'fas fa-info-circle',
  `booking_id` int(11) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `recipient_id`, `recipient_type`, `title`, `message`, `type`, `icon`, `booking_id`, `is_read`, `created_at`, `updated_at`) VALUES
(23, 43, '', 'Booking Confirmed Successfully', 'Your booking #BK-ZWFKRO4P has been confirmed and payment of Rs. 533.00 has been processed successfully.', 'booking_confirmed', 'fas fa-plane', 2, 1, '2025-07-10 02:11:55', '2025-07-10 02:16:30'),
(24, 41, 'company', 'New Booking Received', 'New booking received from Mira Sunuwar. Amount: Rs. 533.00', 'new_booking', 'fas fa-plane', 2, 1, '2025-07-10 02:11:55', '2025-07-11 09:26:06'),
(25, 43, '', 'Flight Completed Successfully', 'Your flight booking BK-ZWFKRO4P has been completed successfully. We hope you enjoyed your experience! Please consider leaving a review.', 'completed', 'fas fa-check-circle', 2, 1, '2025-07-10 02:17:42', '2025-07-10 02:28:38'),
(26, 41, 'company', 'Flight Completed', 'Flight booking for Mira Sunuwar has been completed successfully.', 'completed', 'fas fa-check-circle', 2, 1, '2025-07-10 02:17:42', '2025-07-11 09:26:03'),
(29, 43, '', 'Flight Completed Successfully', 'Your flight booking BK-ZWFKRO4P has been completed successfully. We hope you enjoyed your experience! Please consider leaving a review.', 'completed', 'fas fa-check-circle', 2, 1, '2025-07-10 02:24:19', '2025-07-10 02:28:38'),
(30, 41, 'company', 'Flight Completed', 'Flight booking for Mira Sunuwar has been completed successfully.', 'completed', 'fas fa-check-circle', 2, 1, '2025-07-10 02:24:19', '2025-07-11 09:26:04'),
(31, 43, '', 'Flight Completed Successfully', 'Your flight booking BK-ZWFKRO4P has been completed successfully. We hope you enjoyed your experience! Please consider leaving a review.', 'completed', 'fas fa-check-circle', 2, 1, '2025-07-10 02:28:07', '2025-07-10 04:38:03'),
(32, 41, 'company', 'Flight Completed', 'Flight booking for Mira Sunuwar has been completed successfully.', 'completed', 'fas fa-check-circle', 2, 1, '2025-07-10 02:28:07', '2025-07-11 09:26:05'),
(33, 43, '', 'Flight Completed Successfully', 'Your flight booking BK-ZWFKRO4P has been completed successfully. We hope you enjoyed your experience! Please consider leaving a review.', 'completed', 'fas fa-check-circle', 2, 1, '2025-07-10 04:39:39', '2025-07-10 04:42:45'),
(34, 41, 'company', 'Flight Completed', 'Flight booking for Mira Sunuwar has been completed successfully.', 'completed', 'fas fa-check-circle', 2, 1, '2025-07-10 04:39:39', '2025-07-11 03:40:27'),
(35, 43, '', 'Flight Completed Successfully', 'Your flight booking BK-ZWFKRO4P has been completed successfully. We hope you enjoyed your experience! Please consider leaving a review.', 'completed', 'fas fa-check-circle', 2, 1, '2025-07-10 08:22:35', '2025-07-10 08:23:04'),
(36, 41, 'company', 'Flight Completed', 'Flight booking for Mira Sunuwar has been completed successfully.', 'completed', 'fas fa-check-circle', 2, 1, '2025-07-10 08:22:35', '2025-07-11 03:40:26'),
(37, 43, '', 'Booking Confirmed Successfully', 'Your booking #BK-YGLM96E7 has been confirmed and payment of Rs. 533.00 has been processed successfully.', 'booking_confirmed', 'fas fa-plane', 1, 1, '2025-07-10 08:37:49', '2025-07-10 08:38:38'),
(38, 41, 'company', 'New Booking Received', 'New booking received from Mira Sunuwar. Amount: Rs. 533.00', 'new_booking', 'fas fa-plane', 1, 1, '2025-07-10 08:37:49', '2025-07-11 03:40:25'),
(39, 43, '', 'Booking Confirmed Successfully', 'Your booking #BK-L8BKR2HO has been confirmed and payment of Rs. 514.00 has been processed successfully.', 'booking_confirmed', 'fas fa-plane', 2, 1, '2025-07-10 09:18:32', '2025-07-11 02:17:10'),
(40, 41, 'company', 'New Booking Received', 'New booking received from Mira Sunuwar. Amount: Rs. 514.00', 'new_booking', 'fas fa-plane', 2, 1, '2025-07-10 09:18:32', '2025-07-11 03:40:19'),
(41, 43, '', 'Flight Completed Successfully', 'Your flight booking BK-L8BKR2HO has been completed successfully. We hope you enjoyed your experience! Please consider leaving a review.', 'completed', 'fas fa-check-circle', 2, 1, '2025-07-10 09:20:24', '2025-07-11 02:17:11'),
(42, 41, 'company', 'Flight Completed', 'Flight booking for Mira Sunuwar has been completed successfully.', 'completed', 'fas fa-check-circle', 2, 1, '2025-07-10 09:20:24', '2025-07-11 03:40:21'),
(43, 43, '', 'Flight Completed Successfully', 'Your flight booking BK-L8BKR2HO has been completed successfully. We hope you enjoyed your experience! Please consider leaving a review.', 'completed', 'fas fa-check-circle', 2, 1, '2025-07-10 15:11:52', '2025-07-11 02:19:20'),
(44, 41, 'company', 'Flight Completed', 'Flight booking for Mira Sunuwar has been completed successfully.', 'completed', 'fas fa-check-circle', 2, 1, '2025-07-10 15:11:52', '2025-07-11 03:40:17'),
(45, 43, '', 'Flight Completed Successfully', 'Your flight for booking BK-L8BKR2HO has been completed successfully. We hope you enjoyed your experience! Please consider leaving a review.', 'completed', 'fas fa-check-circle', 2, 1, '2025-07-11 03:39:24', '2025-07-11 03:39:40'),
(46, 41, 'company', 'Flight Completed', 'Flight booking for Mira Sunuwar has been completed successfully.', 'completed', 'fas fa-check-circle', 2, 1, '2025-07-11 03:39:24', '2025-07-11 03:39:26'),
(47, 43, '', 'Booking Confirmed Successfully', 'Your booking #BK-BRAP7DKU has been confirmed and payment of Rs. 12,000.00 has been processed successfully.', 'booking_confirmed', 'fas fa-plane', 3, 1, '2025-07-11 16:03:37', '2025-07-11 16:06:43'),
(48, 41, 'company', 'New Booking Received', 'New booking received from Mira Sunuwar. Amount: Rs. 12,000.00', 'new_booking', 'fas fa-plane', 3, 1, '2025-07-11 16:03:37', '2025-07-11 16:04:22'),
(49, 43, '', 'Flight Completed Successfully', 'Your flight for booking BK-BRAP7DKU has been completed successfully. We hope you enjoyed your experience! Please consider leaving a review.', 'completed', 'fas fa-check-circle', 3, 1, '2025-07-11 16:04:44', '2025-07-11 16:06:43'),
(50, 41, 'company', 'Flight Completed', 'Flight booking for Mira Sunuwar has been completed successfully.', 'completed', 'fas fa-check-circle', 3, 1, '2025-07-11 16:04:44', '2025-07-11 16:04:48'),
(51, 23, '', 'Booking Confirmed Successfully', 'Your booking #BK-SK7UJX19 has been confirmed and payment of Rs. 12,000.00 has been processed successfully.', 'booking_confirmed', 'fas fa-plane', 4, 1, '2025-07-11 16:09:43', '2025-07-11 16:10:02'),
(52, 41, 'company', 'New Booking Received', 'New booking received from Karun Sunuwar. Amount: Rs. 12,000.00', 'new_booking', 'fas fa-plane', 4, 1, '2025-07-11 16:09:43', '2025-07-11 16:11:09');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `review_text` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `user_id`, `service_id`, `rating`, `review_text`, `created_at`, `updated_at`) VALUES
(1, 43, 23, 3, 'Not bad flight', '2025-07-11 09:20:34', '2025-07-11 15:40:59'),
(5, 23, 19, 4, 'Great experience! Friendly pilot and smooth ride.', '2025-07-11 15:43:13', '2025-07-11 16:08:18'),
(6, 43, 19, 5, 'Such a pleasant flight trip definitely a go-to company for paragliding experience.', '2025-07-12 02:24:04', '2025-07-12 02:24:04');

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
(44, 19, 'Pro Tandem', '12000'),
(51, 21, 'Normal Tandem', '12000'),
(52, 20, 'Normal Tandem', '10000'),
(53, 20, 'Cloud Surfing', '15000'),
(56, 22, 'Mary Patel', '533'),
(57, 22, 'Xena Marsh', '1500'),
(58, 23, 'Josiah Sweeney', '514');

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
(77, 20, 'Assets/uploads/company_images/photo_20_0_68567128d051d.jpg', 1),
(78, 20, 'Assets/uploads/company_images/photo_20_1_68567128d082e.jpg', 2),
(79, 20, 'Assets/uploads/company_images/photo_20_2_68567128d0a7a.jpg', 3),
(80, 20, 'Assets/uploads/company_images/photo_20_3_68567128d0c9a.jpg', 4),
(81, 19, 'Assets/uploads/company_images/photo_19_0_6856bb0fd859d.jpg', 1),
(82, 19, 'Assets/uploads/company_images/photo_19_1_6856bb0fd89d2.jpg', 2),
(83, 19, 'Assets/uploads/company_images/photo_19_2_6856bb0fdea59.jpg', 3),
(84, 19, 'Assets/uploads/company_images/photo_19_3_6856bb0fdecb9.jpg', 4),
(93, 21, 'Assets/uploads/company_images/photo_21_0_685792ff92b80.jpg', 1),
(94, 21, 'Assets/uploads/company_images/photo_21_1_685792ff93008.jpg', 2),
(95, 21, 'Assets/uploads/company_images/photo_21_2_685792ff932a5.jpg', 3),
(96, 21, 'Assets/uploads/company_images/photo_21_3_685792ff934f8.jpg', 4),
(97, 22, 'Assets/uploads/company_images/photo_22_0_685ad07fa2c98.jpg', 1),
(98, 22, 'Assets/uploads/company_images/photo_22_1_685ad07fa2e68.jpg', 2),
(99, 22, 'Assets/uploads/company_images/photo_22_2_685ad07fa307c.jpg', 3),
(100, 22, 'Assets/uploads/company_images/photo_22_3_685ad07fa3212.jpg', 4),
(101, 23, 'Assets/uploads/company_images/photo_23_0_686904a1cfc54.jpg', 1),
(102, 23, 'Assets/uploads/company_images/photo_23_1_686904a1cfef3.jpg', 2),
(103, 23, 'Assets/uploads/company_images/photo_23_2_686904a1d00e2.jpg', 3),
(104, 23, 'Assets/uploads/company_images/photo_23_3_686904a1d02c2.jpg', 4);

-- --------------------------------------------------------

--
-- Table structure for table `service_status_logs`
--

CREATE TABLE `service_status_logs` (
  `id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `old_status` varchar(50) NOT NULL,
  `new_status` varchar(50) NOT NULL,
  `reason` text DEFAULT NULL,
  `changed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `service_status_logs`
--

INSERT INTO `service_status_logs` (`id`, `service_id`, `old_status`, `new_status`, `reason`, `changed_at`, `created_at`, `updated_at`) VALUES
(1, 23, 'pending', 'rejected', 'asfasfasd', '2025-07-05 15:43:38', '2025-07-05 15:43:38', '2025-07-05 15:43:38'),
(2, 22, 'pending', 'rejected', 'not full details', '2025-07-05 15:54:04', '2025-07-05 15:54:04', '2025-07-05 15:54:04'),
(3, 22, 'pending', 'rejected', 'abcd', '2025-07-06 02:24:20', '2025-07-06 02:24:20', '2025-07-06 02:24:20'),
(4, 20, 'pending', 'rejected', 'No images', '2025-07-06 03:08:03', '2025-07-06 03:08:03', '2025-07-06 03:08:03');

-- --------------------------------------------------------

--
-- Table structure for table `temp_bookings`
--

CREATE TABLE `temp_bookings` (
  `temp_id` int(11) NOT NULL,
  `booking_no` varchar(20) NOT NULL,
  `user_id` int(11) NOT NULL,
  `service_id` int(11) DEFAULT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `nationality` varchar(50) NOT NULL,
  `date` date NOT NULL,
  `pickup` varchar(200) NOT NULL,
  `flight_type_id` int(11) NOT NULL,
  `flight_type_name` varchar(100) NOT NULL,
  `weight` decimal(5,2) DEFAULT NULL,
  `age` int(11) NOT NULL,
  `gender` enum('Male','Female','Other') NOT NULL,
  `notes` text DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `payment_status` enum('pending','paid','expired') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(23, 'karun97@gmail.com', '$2y$10$JV0Kp68NGYfaqj8eeXND6eKg1b9lb6woPUcyQAWEtSktpKflEWKh6', NULL, NULL, 'form'),
(41, 'karunkarun9797@gmail.com', NULL, NULL, '697975666519088', 'facebook'),
(43, 'sunuwarmira12@gmail.com', NULL, '108019909049708825911', NULL, 'google');

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
(23, 'passenger', 'Karun', 'Sunuwar', 'Male', '9826197849', '1997-09-07', 'Nepal', 'avatar_23_1750851564.jpg'),
(41, 'company', 'Karun', 'Sunuwar', 'Male', '9826197849', '1997-09-07', 'Nepal', 'https://platform-lookaside.fbsbx.com/platform/profilepic/?asid=697975666519088&height=200&width=200&ext=1753040482&hash=AT_dETUmybBGOCfyaA3I0Gkc'),
(43, 'passenger', 'Mira', 'Sunuwar', 'Female', '9846303212', '1997-09-07', 'Nepal', 'https://lh3.googleusercontent.com/a/ACg8ocLoOIvJrlUGdPOp61-z5FAtdGL_Gb9sQu-95efGEVpO078izXA=s96-c');

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
(27, 23, '50e8e56ab6b741a5dfbabac511df85f1', '2025-07-12 02:24:36', '2025-07-13 08:09:36'),
(56, 41, '518911c70f83e59e904c301604817410', '2025-07-11 16:11:06', '2025-06-22 01:26:25'),
(65, 43, '2756066200ce95ec5edfadb31aa617dc', '2025-07-12 02:25:21', '2025-06-22 09:30:49');

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
(23, 'dc890ee679a8dc3ff0f050720a4cb30a', 1, '57562', '2025-06-20 09:16:00'),
(41, NULL, 1, NULL, '2025-06-20 19:41:25'),
(43, NULL, 1, NULL, '2025-06-21 03:45:49');

-- --------------------------------------------------------

--
-- Table structure for table `user_favorites`
--

CREATE TABLE `user_favorites` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_favorites`
--

INSERT INTO `user_favorites` (`id`, `user_id`, `service_id`, `created_at`) VALUES
(49, 43, 22, '2025-07-11 09:45:51');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `approved_by` (`approved_by`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`booking_id`),
  ADD UNIQUE KEY `booking_no` (`booking_no`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `service_id` (`service_id`);

--
-- Indexes for table `chat_conversations`
--
ALTER TABLE `chat_conversations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_conversation` (`service_id`,`passenger_user_id`,`company_user_id`),
  ADD KEY `idx_passenger_user_id` (`passenger_user_id`),
  ADD KEY `idx_company_user_id` (`company_user_id`),
  ADD KEY `idx_service_id` (`service_id`),
  ADD KEY `idx_last_message` (`last_message_at`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_conversation_id` (`conversation_id`),
  ADD KEY `idx_sender_user_id` (`sender_user_id`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_is_read` (`is_read`);

--
-- Indexes for table `company_services`
--
ALTER TABLE `company_services`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `esewainfo`
--
ALTER TABLE `esewainfo`
  ADD KEY `bookingf` (`booking_no`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_recipient` (`recipient_id`),
  ADD KEY `idx_booking` (`booking_id`),
  ADD KEY `idx_read_status` (`is_read`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_service` (`user_id`,`service_id`),
  ADD KEY `fk_reviews_service_id` (`service_id`);

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
-- Indexes for table `service_status_logs`
--
ALTER TABLE `service_status_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_service_id` (`service_id`),
  ADD KEY `idx_changed_at` (`changed_at`),
  ADD KEY `idx_status_change` (`old_status`,`new_status`);

--
-- Indexes for table `temp_bookings`
--
ALTER TABLE `temp_bookings`
  ADD PRIMARY KEY (`temp_id`),
  ADD UNIQUE KEY `booking_no` (`booking_no`),
  ADD KEY `idx_booking_no` (`booking_no`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_expires_at` (`expires_at`);

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
-- Indexes for table `user_favorites`
--
ALTER TABLE `user_favorites`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_favorite` (`user_id`,`service_id`),
  ADD KEY `service_id` (`service_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `booking_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `chat_conversations`
--
ALTER TABLE `chat_conversations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `chat_messages`
--
ALTER TABLE `chat_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `company_services`
--
ALTER TABLE `company_services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `service_flight_types`
--
ALTER TABLE `service_flight_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- AUTO_INCREMENT for table `service_office_photos`
--
ALTER TABLE `service_office_photos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=105;

--
-- AUTO_INCREMENT for table `service_status_logs`
--
ALTER TABLE `service_status_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `temp_bookings`
--
ALTER TABLE `temp_bookings`
  MODIFY `temp_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `users_info`
--
ALTER TABLE `users_info`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `users_sessions`
--
ALTER TABLE `users_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=209;

--
-- AUTO_INCREMENT for table `users_verify`
--
ALTER TABLE `users_verify`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `user_favorites`
--
ALTER TABLE `user_favorites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admins`
--
ALTER TABLE `admins`
  ADD CONSTRAINT `admins_ibfk_1` FOREIGN KEY (`approved_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `company_services` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `chat_conversations`
--
ALTER TABLE `chat_conversations`
  ADD CONSTRAINT `chat_conversations_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `company_services` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `chat_conversations_ibfk_2` FOREIGN KEY (`passenger_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `chat_conversations_ibfk_3` FOREIGN KEY (`company_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD CONSTRAINT `chat_messages_ibfk_1` FOREIGN KEY (`conversation_id`) REFERENCES `chat_conversations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `chat_messages_ibfk_2` FOREIGN KEY (`sender_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `company_services`
--
ALTER TABLE `company_services`
  ADD CONSTRAINT `company_services_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users_info` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`booking_id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `fk_reviews_service_id` FOREIGN KEY (`service_id`) REFERENCES `company_services` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users_info` (`user_id`) ON DELETE CASCADE;

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
-- Constraints for table `service_status_logs`
--
ALTER TABLE `service_status_logs`
  ADD CONSTRAINT `service_status_logs_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `company_services` (`id`) ON DELETE CASCADE;

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

--
-- Constraints for table `user_favorites`
--
ALTER TABLE `user_favorites`
  ADD CONSTRAINT `user_favorites_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users_info` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_favorites_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `company_services` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
