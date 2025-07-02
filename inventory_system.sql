-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 02, 2025 at 06:59 PM
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
-- Database: `inventory_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `audit_deleted_users`
--

CREATE TABLE `audit_deleted_users` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `reason` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `audit_deleted_users`
--

INSERT INTO `audit_deleted_users` (`id`, `user_id`, `username`, `deleted_at`, `reason`) VALUES
(1, 5, 'Staff3', '2025-06-27 00:23:26', 'Deleted after 1 minute of being disabled'),
(2, 7, 'Kurtflorence', '2025-06-27 00:24:39', 'Deleted after 1 minute of being disabled'),
(3, 9, 'Staff', '2025-06-27 15:18:02', 'Deleted after 12 hours of being disabled'),
(4, 3, 'Staff1', '2025-07-03 00:54:56', 'Deleted after 3 days of inactivity');

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `quantity` int(11) NOT NULL,
  `category` varchar(50) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`id`, `name`, `quantity`, `category`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'Jordan 1', 20, 'Shoes', 1, '2025-06-22 23:53:14', '2025-06-22 23:53:14'),
(2, 'Computer Mouse', 20, 'Electronics', 1, '2025-06-27 00:51:41', '2025-06-27 00:51:41');

-- --------------------------------------------------------

--
-- Table structure for table `role_change_logs`
--

CREATE TABLE `role_change_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `old_role` enum('admin','staff') NOT NULL,
  `new_role` enum('admin','staff') NOT NULL,
  `changed_by` int(11) NOT NULL,
  `changed_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `system_logs`
--

CREATE TABLE `system_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action_type` varchar(50) NOT NULL,
  `description` text NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_logs`
--

INSERT INTO `system_logs` (`id`, `user_id`, `action_type`, `description`, `ip_address`, `created_at`) VALUES
(1, 1, 'login_success', 'User logged in', '::1', '2025-06-22 23:36:38'),
(2, 1, 'inventory_add', 'Added item: Jordan 1', '::1', '2025-06-22 23:53:14'),
(3, 1, 'login_success', 'User logged in', '::1', '2025-06-23 00:21:25'),
(4, 1, '2fa_init', 'Started 2FA setup (manual entry)', '::1', '2025-06-23 01:14:49'),
(5, 1, '2fa_enabled', '2FA setup completed', '::1', '2025-06-23 01:18:12'),
(6, 1, 'logout', 'User logged out', '::1', '2025-06-23 01:20:03'),
(7, 1, 'login_failed', 'Invalid credentials', '::1', '2025-06-23 01:20:10'),
(8, 1, 'login_success', 'User logged in', '::1', '2025-06-23 01:20:46'),
(9, 1, 'user_created', 'Created user Staff with role staff', '::1', '2025-06-23 01:21:16'),
(10, 1, 'logout', 'User logged out', '::1', '2025-06-23 01:21:29'),
(15, 1, 'login_success', 'User logged in', '::1', '2025-06-23 15:06:45'),
(16, 1, 'user_created', 'Created user Staff1 with role staff', '::1', '2025-06-23 15:08:28'),
(17, 1, 'user_status', 'activated user 3', '::1', '2025-06-23 15:08:39'),
(18, 1, 'user_status', 'activated user 3', '::1', '2025-06-23 15:08:47'),
(19, 1, 'logout', 'User logged out', '::1', '2025-06-23 15:10:02'),
(20, 1, 'login_failed', 'Invalid credentials', '::1', '2025-06-23 15:23:07'),
(21, 1, 'login_success', 'User logged in', '::1', '2025-06-23 15:23:43'),
(22, 1, 'logout', 'User logged out', '::1', '2025-06-23 15:25:29'),
(26, NULL, 'login_attempt', 'Account locked due to too many failed attempts', '::1', '2025-06-23 15:25:41'),
(29, 1, 'login_success', 'User logged in', '::1', '2025-06-23 15:27:22'),
(30, 1, 'user_status', 'activated user 3', '::1', '2025-06-23 15:27:59'),
(31, 1, 'logout', 'User logged out', '::1', '2025-06-23 15:35:08'),
(33, NULL, 'login_attempt', 'Account locked due to too many failed attempts', '::1', '2025-06-23 15:35:21'),
(34, NULL, 'login_attempt', 'Account locked due to too many failed attempts', '::1', '2025-06-23 15:35:29'),
(35, 1, 'login_success', 'User logged in', '::1', '2025-06-23 15:36:12'),
(36, 1, 'user_status', 'activated user 3', '::1', '2025-06-23 15:37:23'),
(37, 1, 'user_status', 'activated user 3', '::1', '2025-06-23 15:37:38'),
(38, 1, 'user_status', 'activated user 3', '::1', '2025-06-23 15:56:43'),
(39, 1, 'user_status', 'deactivated user 3', '::1', '2025-06-23 15:56:51'),
(40, 1, 'logout', 'User logged out', '::1', '2025-06-23 16:07:34'),
(41, NULL, 'login_attempt', 'Account locked due to too many failed attempts', '::1', '2025-06-23 16:07:39'),
(42, NULL, 'login_attempt', 'Account locked due to too many failed attempts', '::1', '2025-06-23 16:08:29'),
(43, NULL, 'login_attempt', 'Account locked due to too many failed attempts', '::1', '2025-06-23 16:09:02'),
(45, 1, 'login_success', 'User logged in', '::1', '2025-06-23 16:09:40'),
(46, 1, 'user_created', 'Created user Staff2 with role staff', '::1', '2025-06-23 16:10:05'),
(47, 1, 'logout', 'User logged out', '::1', '2025-06-23 16:10:17'),
(48, NULL, 'login_success', 'User logged in', '::1', '2025-06-23 16:10:26'),
(49, NULL, 'logout', 'User logged out', '::1', '2025-06-23 16:11:12'),
(50, 1, 'login_success', 'User logged in', '::1', '2025-06-23 16:11:37'),
(51, 1, 'user_deleted', 'Deleted user 2', '::1', '2025-06-23 16:15:19'),
(52, 1, 'user_created', 'Created user Staff3 with role staff', '::1', '2025-06-23 16:18:08'),
(53, 1, 'user_status', 'deactivated user 5', '::1', '2025-06-23 16:19:03'),
(54, 1, 'login_failed', 'Invalid credentials', '::1', '2025-06-24 20:36:06'),
(55, 1, 'login_success', 'User logged in', '::1', '2025-06-24 20:36:37'),
(56, 1, 'user_status', 'activated user 3', '::1', '2025-06-24 20:53:55'),
(57, 1, 'user_status', 'activated user 5', '::1', '2025-06-24 20:57:41'),
(58, 1, 'user_status', 'deactivated user 5', '::1', '2025-06-24 20:57:44'),
(59, 1, 'user_status', 'activated user 5', '::1', '2025-06-24 20:57:49'),
(60, 1, 'user_status', 'deactivated user 5', '::1', '2025-06-24 21:25:53'),
(61, 1, 'user_status', 'activated user 5', '::1', '2025-06-24 21:27:24'),
(62, 1, 'user_status', 'deactivated user 5', '::1', '2025-06-24 21:27:27'),
(63, 1, 'user_status', 'deactivated user 4', '::1', '2025-06-24 21:30:34'),
(64, 1, 'user_status', 'activated user 5', '::1', '2025-06-24 21:39:15'),
(65, 1, 'user_status', 'activated user 4', '::1', '2025-06-24 21:39:18'),
(66, 1, 'user_status', 'deactivated user 5', '::1', '2025-06-24 21:39:21'),
(67, 1, 'user_status', 'deactivated user 4', '::1', '2025-06-24 21:39:24'),
(68, 1, 'user_updated', 'Updated user 5', '::1', '2025-06-24 22:11:51'),
(69, 1, 'user_updated', 'Updated user 4', '::1', '2025-06-24 22:11:57'),
(70, 1, 'user_updated', 'Updated user 3', '::1', '2025-06-24 22:12:01'),
(71, 1, 'user_updated', 'Updated user 1', '::1', '2025-06-24 22:14:56'),
(72, 1, 'user_updated', 'Updated user 1', '::1', '2025-06-24 22:16:28'),
(73, 1, 'logout', 'User logged out', '::1', '2025-06-24 22:27:47'),
(76, 1, 'login_failed', 'Invalid credentials', '::1', '2025-06-24 22:28:29'),
(77, 1, 'login_success', 'User logged in', '::1', '2025-06-24 22:29:06'),
(78, 1, 'logout', 'User logged out', '::1', '2025-06-24 22:33:30'),
(82, NULL, 'login_attempt', 'Account locked due to too many failed attempts', '::1', '2025-06-24 22:33:40'),
(83, NULL, 'login_attempt', 'Account locked due to too many failed attempts', '::1', '2025-06-24 22:42:21'),
(84, NULL, 'login_attempt', 'Account locked due to too many failed attempts', '::1', '2025-06-24 22:42:39'),
(85, NULL, 'login_attempt', 'Account locked due to too many failed attempts', '::1', '2025-06-24 22:42:41'),
(86, 1, 'login_failed', 'Invalid credentials', '::1', '2025-06-24 22:42:49'),
(87, 1, 'login_failed', 'Invalid credentials', '::1', '2025-06-24 22:42:53'),
(88, 1, 'login_failed', 'Invalid credentials', '::1', '2025-06-24 22:42:59'),
(89, NULL, 'login_attempt', 'Account locked due to too many failed attempts', '::1', '2025-06-24 22:43:07'),
(90, NULL, 'login_attempt', 'Account locked due to too many failed attempts', '::1', '2025-06-24 22:43:18'),
(91, NULL, 'login_attempt', 'Account locked due to too many failed attempts', '::1', '2025-06-24 22:43:28'),
(92, NULL, 'login_attempt', 'Account locked due to too many failed attempts', '::1', '2025-06-24 22:45:26'),
(93, NULL, 'login_attempt', 'Account locked due to too many failed attempts', '::1', '2025-06-24 22:45:29'),
(96, 1, 'login_success', 'User logged in', '::1', '2025-06-24 22:49:39'),
(97, 1, 'logout', 'User logged out', '::1', '2025-06-24 22:49:53'),
(124, NULL, 'login_failed', 'Invalid credentials', '::1', '2025-06-24 23:27:26'),
(125, NULL, 'login_failed', 'Invalid credentials', '::1', '2025-06-24 23:27:33'),
(126, NULL, 'login_attempt', 'Account locked due to too many failed attempts', '::1', '2025-06-24 23:27:35'),
(127, NULL, 'login_failed', 'Invalid credentials', '::1', '2025-06-24 23:28:35'),
(128, NULL, 'login_failed', 'Invalid credentials', '::1', '2025-06-24 23:28:37'),
(129, NULL, 'login_attempt', 'Account locked due to too many failed attempts', '::1', '2025-06-24 23:28:42'),
(130, NULL, 'login_failed', 'Invalid credentials', '::1', '2025-06-24 23:29:11'),
(131, NULL, 'login_failed', 'Invalid credentials', '::1', '2025-06-24 23:29:39'),
(132, NULL, 'login_failed', 'Invalid credentials', '::1', '2025-06-24 23:29:46'),
(133, NULL, 'login_failed', 'Invalid credentials', '::1', '2025-06-24 23:29:50'),
(135, 1, 'login_attempt', 'Account locked due to too many failed attempts', '::1', '2025-06-24 23:31:00'),
(146, 1, 'login_success', 'User logged in', '::1', '2025-06-25 23:02:54'),
(147, 1, 'user_status', 'deactivated user 3', '::1', '2025-06-25 23:03:13'),
(148, 1, 'user_status', 'activated user 3', '::1', '2025-06-25 23:03:17'),
(149, 1, 'user_updated', 'Updated user 3', '::1', '2025-06-25 23:03:38'),
(150, 1, 'logout', 'User logged out', '::1', '2025-06-25 23:03:41'),
(153, 1, 'login_success', 'User logged in', '::1', '2025-06-25 23:04:23'),
(154, 1, 'user_updated', 'Updated user 3', '::1', '2025-06-25 23:04:33'),
(155, 1, 'login_success', 'User logged in', '::1', '2025-06-26 19:31:48'),
(156, 1, 'user_created', 'Created user SAS with role staff', '::1', '2025-06-26 19:39:22'),
(157, 1, 'user_deleted', 'Deleted user 6', '::1', '2025-06-26 19:39:26'),
(158, 1, 'user_status', 'activated user 5', '::1', '2025-06-26 21:48:56'),
(159, 1, 'user_status', 'deactivated user 5', '::1', '2025-06-26 21:48:58'),
(160, 1, 'user_status', 'activated user 5', '::1', '2025-06-26 21:49:04'),
(161, 1, 'user_status', 'deactivated user 5', '::1', '2025-06-26 21:53:19'),
(162, 1, 'user_status', 'activated user 5', '::1', '2025-06-26 21:53:22'),
(163, 1, 'user_status', 'deactivated user 5', '::1', '2025-06-26 21:53:24'),
(164, 1, 'user_status', 'activated user 5', '::1', '2025-06-26 21:53:25'),
(165, 1, 'user_status', 'deactivated user 5', '::1', '2025-06-26 21:59:57'),
(166, 1, 'user_created', 'Created user Kurtflorence with role staff', '::1', '2025-06-27 00:18:07'),
(167, 1, 'user_status', 'deactivated user 5', '::1', '2025-06-27 00:18:34'),
(168, 1, 'user_status', 'deactivated user 5', '::1', '2025-06-27 00:18:36'),
(169, 1, 'user_status', 'deactivated user 5', '::1', '2025-06-27 00:18:37'),
(170, 1, 'user_status', 'deactivated user 5', '::1', '2025-06-27 00:18:41'),
(171, 1, 'user_status', 'deactivated user 5', '::1', '2025-06-27 00:20:20'),
(172, 1, 'user_status', 'deactivated user 7', '::1', '2025-06-27 00:23:34'),
(173, 1, 'user_created', 'Created user ASda with role staff', '::1', '2025-06-27 00:29:34'),
(174, 1, 'user_deleted', 'Deleted user 8', '::1', '2025-06-27 00:29:41'),
(175, 1, 'user_created', 'Created user Staff with role staff', '::1', '2025-06-27 00:34:29'),
(176, 1, 'user_status', 'deactivated user 9', '::1', '2025-06-27 00:34:35'),
(177, 1, 'user_status', 'activated user 9', '::1', '2025-06-27 00:34:38'),
(178, 1, 'user_status', 'deactivated user 9', '::1', '2025-06-27 00:34:51'),
(179, 1, 'user_status', 'activated user 9', '::1', '2025-06-27 00:34:53'),
(180, 1, 'user_status', 'deactivated user 9', '::1', '2025-06-27 00:36:05'),
(181, 1, 'logout', 'User logged out', '::1', '2025-06-27 00:45:55'),
(183, 1, 'login_failed', 'Failed login attempt', '::1', '2025-06-27 00:47:09'),
(184, 1, 'login_success', 'User logged in', '::1', '2025-06-27 00:47:25'),
(185, 1, 'login_success', 'User logged in', '::1', '2025-06-27 00:47:25'),
(186, 1, 'login_failed', 'Failed login attempt', '192.168.8.116', '2025-06-27 00:50:16'),
(187, 1, 'login_success', 'User logged in', '192.168.8.116', '2025-06-27 00:50:36'),
(188, 1, 'login_success', 'User logged in', '192.168.8.116', '2025-06-27 00:50:36'),
(189, 1, 'inventory_add', 'Added item: Computer Mouse', '192.168.8.116', '2025-06-27 00:51:41'),
(190, 1, 'logout', 'User logged out', '192.168.8.116', '2025-06-27 00:52:09'),
(191, 1, 'login_failed', 'Failed login attempt', '::1', '2025-06-27 15:17:30'),
(192, 1, 'login_success', 'User logged in', '::1', '2025-06-27 15:17:55'),
(193, 1, 'login_success', 'User logged in', '::1', '2025-06-27 15:17:55'),
(194, 1, 'logout', 'User logged out', '::1', '2025-06-27 15:22:18'),
(195, NULL, 'login_failed', 'Invalid credentials', '::1', '2025-06-27 15:22:26'),
(196, NULL, 'login_failed', 'Failed login attempt', '::1', '2025-06-27 15:22:26'),
(200, 1, 'login_failed', 'Failed login attempt', '::1', '2025-06-27 15:22:57'),
(201, 1, 'login_success', 'User logged in', '::1', '2025-06-27 15:23:10'),
(202, 1, 'login_success', 'User logged in', '::1', '2025-06-27 15:23:10'),
(203, 1, 'logout', 'User logged out', '::1', '2025-06-27 15:23:53'),
(207, 1, 'login_failed', 'Failed login attempt', '::1', '2025-06-27 15:28:08'),
(208, 1, 'login_failed', 'Invalid credentials', '::1', '2025-06-27 15:28:24'),
(209, 1, 'login_failed', 'Failed login attempt', '::1', '2025-06-27 15:28:24'),
(210, 1, 'login_failed', 'Failed login attempt', '::1', '2025-06-27 15:28:34'),
(211, 1, 'login_success', 'User logged in', '::1', '2025-06-27 15:28:43'),
(212, 1, 'login_success', 'User logged in', '::1', '2025-06-27 15:28:43'),
(213, 1, 'logout', 'User logged out', '::1', '2025-06-27 15:37:04'),
(214, 1, 'login_failed', 'Failed login attempt', '::1', '2025-06-27 15:40:42'),
(215, 1, 'login_success', 'User logged in', '::1', '2025-06-27 15:40:56'),
(216, 1, 'login_success', 'User logged in', '::1', '2025-06-27 15:40:56'),
(217, 1, 'user_updated', 'Updated user 3', '::1', '2025-06-27 15:48:49'),
(218, 1, 'user_updated', 'Updated user 3', '::1', '2025-06-27 15:50:02'),
(219, 1, 'logout', 'User logged out', '::1', '2025-06-27 15:53:06'),
(220, 1, 'login_failed', 'Failed login attempt', '::1', '2025-07-03 00:54:06'),
(221, 1, 'login_failed', 'Invalid credentials', '::1', '2025-07-03 00:54:26'),
(222, 1, 'login_failed', 'Failed login attempt', '::1', '2025-07-03 00:54:26'),
(223, 1, 'login_failed', 'Failed login attempt', '::1', '2025-07-03 00:54:33'),
(224, 1, 'login_success', 'User logged in', '::1', '2025-07-03 00:54:43'),
(225, 1, 'login_success', 'User logged in', '::1', '2025-07-03 00:54:43');

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text NOT NULL,
  `updated_by` int(11) NOT NULL,
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','staff') NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `login_attempts` int(11) DEFAULT 0,
  `last_failed_attempt` datetime DEFAULT NULL,
  `two_factor_secret` varchar(255) DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `disabled` tinyint(1) NOT NULL DEFAULT 0,
  `disabled_at` datetime DEFAULT NULL,
  `failed_attempts` int(11) NOT NULL DEFAULT 0,
  `lock_until` datetime DEFAULT NULL,
  `permissions` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `is_active`, `login_attempts`, `last_failed_attempt`, `two_factor_secret`, `last_login`, `created_at`, `updated_at`, `disabled`, `disabled_at`, `failed_attempts`, `lock_until`, `permissions`) VALUES
(1, 'admin', '$2y$10$skkL61a2IjeHL/byTCf7X.Kq7VmTMeyFqvxaoPuqNWHfSFAgjQTeq', 'admin', 1, 0, NULL, 'PSMGWNQYURQIJRJFBMO3PQL3UIN5GOCH', '2025-07-03 00:54:43', '2025-06-22 23:36:13', '2025-07-03 00:54:43', 0, NULL, 0, NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `audit_deleted_users`
--
ALTER TABLE `audit_deleted_users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `role_change_logs`
--
ALTER TABLE `role_change_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `changed_by` (`changed_by`);

--
-- Indexes for table `system_logs`
--
ALTER TABLE `system_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `system_logs_ibfk_1` (`user_id`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `audit_deleted_users`
--
ALTER TABLE `audit_deleted_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `role_change_logs`
--
ALTER TABLE `role_change_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `system_logs`
--
ALTER TABLE `system_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=226;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `inventory`
--
ALTER TABLE `inventory`
  ADD CONSTRAINT `inventory_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `role_change_logs`
--
ALTER TABLE `role_change_logs`
  ADD CONSTRAINT `role_change_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `role_change_logs_ibfk_2` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `system_logs`
--
ALTER TABLE `system_logs`
  ADD CONSTRAINT `system_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD CONSTRAINT `system_settings_ibfk_1` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
