-- phpMyAdmin SQL Dump
-- version 5.1.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3308
-- Generation Time: Mar 02, 2026 at 12:24 AM
-- Server version: 5.7.24
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sbims_pro`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_log`
--

CREATE TABLE `activity_log` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `log_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `event` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject_id` bigint(20) UNSIGNED DEFAULT NULL,
  `causer_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `causer_id` bigint(20) UNSIGNED DEFAULT NULL,
  `properties` json DEFAULT NULL,
  `batch_uuid` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `action` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES
(1, 1, 'login', 'User logged into the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-08 20:44:03', '2026-02-08 20:44:03'),
(2, 1, 'update', 'Updated barangay information', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-08 20:44:25', '2026-02-08 20:44:25'),
(3, 1, 'login', 'User logged into the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-08 21:24:14', '2026-02-08 21:24:14'),
(4, 1, 'login', 'User logged into the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-08 21:27:27', '2026-02-08 21:27:27'),
(5, 1, 'login', 'User logged into the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-08 21:34:38', '2026-02-08 21:34:38'),
(6, 1, 'logout', 'User logged out of the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-08 21:38:38', '2026-02-08 21:38:38'),
(7, 1, 'login', 'User logged into the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-08 21:38:45', '2026-02-08 21:38:45'),
(8, 1, 'logout', 'User logged out of the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-08 21:39:50', '2026-02-08 21:39:50'),
(9, 1, 'login', 'User logged into the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-08 21:39:59', '2026-02-08 21:39:59'),
(10, 1, 'logout', 'User logged out of the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-08 21:40:07', '2026-02-08 21:40:07'),
(11, 3, 'login', 'User logged into the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-08 21:40:11', '2026-02-08 21:40:11'),
(12, 3, 'logout', 'User logged out of the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-08 21:40:15', '2026-02-08 21:40:15'),
(13, 2, 'login', 'User logged into the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-08 21:40:21', '2026-02-08 21:40:21'),
(14, 2, 'logout', 'User logged out of the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-08 21:40:25', '2026-02-08 21:40:25'),
(15, 1, 'login', 'User logged into the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-08 21:40:34', '2026-02-08 21:40:34'),
(16, 1, 'login', 'User logged into the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-08 21:50:57', '2026-02-08 21:50:57'),
(17, 1, 'login', 'User logged into the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-08 22:04:00', '2026-02-08 22:04:00'),
(18, 1, 'login', 'User logged into the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-08 22:05:45', '2026-02-08 22:05:45'),
(19, 1, 'logout', 'User logged out of the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-08 22:11:19', '2026-02-08 22:11:19'),
(20, 3, 'login', 'User logged into the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-08 22:11:29', '2026-02-08 22:11:29'),
(21, 3, 'logout', 'User logged out of the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-08 22:11:34', '2026-02-08 22:11:34'),
(22, 2, 'login', 'User logged into the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-08 22:11:43', '2026-02-08 22:11:43'),
(23, 2, 'logout', 'User logged out of the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-08 22:12:05', '2026-02-08 22:12:05'),
(24, 1, 'login', 'User logged into the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-08 22:12:12', '2026-02-08 22:12:12'),
(25, 1, 'login', 'User logged into the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-11 19:00:46', '2026-02-11 19:00:46'),
(26, 1, 'logout', 'User logged out of the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-11 19:01:03', '2026-02-11 19:01:03'),
(27, 3, 'login', 'User logged into the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-11 19:01:09', '2026-02-11 19:01:09'),
(28, 3, 'logout', 'User logged out of the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-11 19:08:23', '2026-02-11 19:08:23'),
(29, 2, 'login', 'User logged into the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-11 19:08:48', '2026-02-11 19:08:48'),
(30, 1, 'login', 'User logged into the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-13 23:24:29', '2026-02-13 23:24:29'),
(31, 1, 'login', 'User logged into the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-14 00:46:37', '2026-02-14 00:46:37'),
(32, 1, 'logout', 'User logged out of the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-14 00:58:00', '2026-02-14 00:58:00'),
(33, 3, 'login', 'User logged into the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-14 00:58:14', '2026-02-14 00:58:14'),
(34, 3, 'logout', 'User logged out of the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-14 01:17:33', '2026-02-14 01:17:33'),
(35, 3, 'login', 'User logged into the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-14 01:17:39', '2026-02-14 01:17:39'),
(36, 3, 'logout', 'User logged out of the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-14 01:28:20', '2026-02-14 01:28:20'),
(37, 1, 'login', 'User logged into the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-14 01:28:26', '2026-02-14 01:28:26'),
(38, 1, 'logout', 'User logged out of the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-14 01:29:30', '2026-02-14 01:29:30'),
(39, 2, 'login', 'User logged into the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-14 01:29:35', '2026-02-14 01:29:35'),
(40, 3, 'login', 'User logged into the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-14 01:32:03', '2026-02-14 01:32:03'),
(41, 3, 'logout', 'User logged out of the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-14 03:46:47', '2026-02-14 03:46:47'),
(42, 3, 'login', 'User logged into the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-14 03:46:56', '2026-02-14 03:46:56'),
(43, 3, 'login', 'User logged into the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-14 04:02:35', '2026-02-14 04:02:35'),
(44, 3, 'login', 'User logged into the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-14 04:23:37', '2026-02-14 04:23:37'),
(45, 3, 'login', 'User logged into the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-14 04:26:53', '2026-02-14 04:26:53'),
(46, 3, 'logout', 'User logged out of the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-14 04:31:20', '2026-02-14 04:31:20'),
(47, 2, 'login', 'User logged into the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-14 04:31:25', '2026-02-14 04:31:25'),
(48, 3, 'login', 'User logged into the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-14 04:31:51', '2026-02-14 04:31:51'),
(49, 3, 'DELETE_RESIDENT', 'Deleted resident: sdasda a (ID: RES-202602-0004)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-14 04:36:47', '2026-02-14 04:36:47'),
(50, 3, 'logout', 'User logged out of the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-14 05:08:46', '2026-02-14 05:08:46'),
(51, 1, 'login', 'User logged into the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-14 05:08:52', '2026-02-14 05:08:52'),
(52, 1, 'logout', 'User logged out of the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-14 06:14:40', '2026-02-14 06:14:40'),
(53, 3, 'login', 'User logged into the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-14 06:14:46', '2026-02-14 06:14:46'),
(54, 3, 'GENERATE_REPORT', 'Generated Residents Report', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-14 06:16:01', '2026-02-14 06:16:01'),
(55, 3, 'EXPORT_REPORT', 'Exported Residents Report as PDF', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-14 06:16:21', '2026-02-14 06:16:21'),
(56, 3, 'GENERATE_REPORT', 'Generated Residents Report', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-14 06:16:21', '2026-02-14 06:16:21'),
(57, 3, 'EXPORT_REPORT', 'Exported Residents Report as PDF', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-14 06:16:24', '2026-02-14 06:16:24'),
(58, 3, 'GENERATE_REPORT', 'Generated Residents Report', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-14 06:16:25', '2026-02-14 06:16:25'),
(59, 3, 'logout', 'User logged out of the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-14 06:18:32', '2026-02-14 06:18:32'),
(60, 2, 'login', 'User logged into the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-14 06:18:38', '2026-02-14 06:18:38'),
(61, 2, 'login', 'User logged into the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-14 06:27:00', '2026-02-14 06:27:00'),
(62, 2, 'login', 'User logged into the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-14 06:32:22', '2026-02-14 06:32:22'),
(63, 2, 'login', 'User logged into the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-14 06:34:26', '2026-02-14 06:34:26'),
(64, 2, 'login', 'User logged into the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-14 06:52:16', '2026-02-14 06:52:16'),
(65, 2, 'login', 'User logged into the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-14 06:53:46', '2026-02-14 06:53:46'),
(66, 2, 'logout', 'User logged out of the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-14 06:56:02', '2026-02-14 06:56:02'),
(67, 3, 'login', 'User logged into the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-14 06:56:04', '2026-02-14 06:56:04'),
(68, 3, 'logout', 'User logged out of the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-14 06:56:13', '2026-02-14 06:56:13'),
(69, 1, 'login', 'User logged into the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-14 06:56:16', '2026-02-14 06:56:16'),
(70, 3, 'login', 'User logged into the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-14 06:58:35', '2026-02-14 06:58:35'),
(71, 3, 'logout', 'User logged out of the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-14 06:58:39', '2026-02-14 06:58:39'),
(72, 1, 'login', 'User logged into the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-14 06:58:42', '2026-02-14 06:58:42'),
(73, 1, 'logout', 'User logged out of the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-14 07:00:55', '2026-02-14 07:00:55'),
(74, 1, 'login', 'User logged into the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-15 00:57:00', '2026-02-15 00:57:00'),
(75, 1, 'login', 'User logged into the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-15 00:57:34', '2026-02-15 00:57:34'),
(76, 1, 'logout', 'User logged out of the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-15 00:58:11', '2026-02-15 00:58:11'),
(77, 3, 'login', 'User logged into the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-15 00:58:16', '2026-02-15 00:58:16'),
(78, 1, 'login', 'User logged into the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-18 00:23:28', '2026-02-18 00:23:28'),
(79, 1, 'logout', 'User logged out of the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-18 00:23:40', '2026-02-18 00:23:40'),
(80, 2, 'login', 'User logged into the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-18 00:23:47', '2026-02-18 00:23:47'),
(81, 2, 'logout', 'User logged out of the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-18 00:24:06', '2026-02-18 00:24:06'),
(82, 3, 'login', 'User logged into the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-18 00:24:17', '2026-02-18 00:24:17'),
(83, 1, 'login', 'User logged into the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-18 00:28:47', '2026-02-18 00:28:47'),
(84, 1, 'logout', 'User logged out of the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-18 00:28:51', '2026-02-18 00:28:51'),
(85, 3, 'login', 'User logged into the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-18 00:28:54', '2026-02-18 00:28:54'),
(86, 3, 'CREATE_BLOTTER', 'Filed new blotter case: Theft - Complainant: Jason Degorio, Respondent: asdasdad q (Case ID: BL-202602-0001)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-18 02:04:37', '2026-02-18 02:04:37'),
(87, 3, 'logout', 'User logged out of the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-18 02:09:42', '2026-02-18 02:09:42'),
(88, 1, 'login', 'User logged into the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-18 02:09:45', '2026-02-18 02:09:45'),
(89, 1, 'login', 'User logged into the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-18 02:16:35', '2026-02-18 02:16:35'),
(90, 1, 'logout', 'User logged out of the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-18 02:53:21', '2026-02-18 02:53:21'),
(91, 3, 'login', 'User logged into the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-18 02:53:28', '2026-02-18 02:53:28'),
(92, 3, 'CREATE_BLOTTER', 'Filed new blotter case: Verbal Argument - Complainant: asdasdad q, Respondent: Jason Degorio (Case ID: BL-202602-0002)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-18 02:54:20', '2026-02-18 02:54:20'),
(93, 3, 'CREATE_RESIDENT', 'Added new resident: some one (ID: RES-2026-0001)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-18 02:55:11', '2026-02-18 02:55:11'),
(94, 3, 'logout', 'User logged out of the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-18 02:55:49', '2026-02-18 02:55:49'),
(95, 1, 'login', 'User logged into the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-18 02:55:50', '2026-02-18 02:55:50'),
(96, 1, 'logout', 'User logged out of the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-18 03:28:30', '2026-02-18 03:28:30'),
(97, 3, 'login', 'User logged into the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-18 03:28:34', '2026-02-18 03:28:34'),
(98, 3, 'CREATE_RESIDENT', 'Added new resident: Album Buster (ID: RES-2026-0002)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-18 03:29:23', '2026-02-18 03:29:23'),
(99, 3, 'logout', 'User logged out of the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-18 03:29:30', '2026-02-18 03:29:30'),
(100, 1, 'login', 'User logged into the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-18 03:29:32', '2026-02-18 03:29:32'),
(101, 1, 'logout', 'User logged out of the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-18 03:52:52', '2026-02-18 03:52:52'),
(102, 3, 'login', 'User logged into the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-18 03:52:55', '2026-02-18 03:52:55'),
(103, 3, 'login', 'User logged into the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-18 03:58:00', '2026-02-18 03:58:00'),
(104, 3, 'logout', 'User logged out of the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-18 03:58:04', '2026-02-18 03:58:04'),
(105, 1, 'login', 'User logged into the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-18 03:58:07', '2026-02-18 03:58:07'),
(106, 1, 'logout', 'User logged out of the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-18 03:58:17', '2026-02-18 03:58:17'),
(107, 3, 'login', 'User logged into the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-18 03:58:20', '2026-02-18 03:58:20'),
(108, 3, 'CREATE_RESIDENT', 'Added new resident: Captain America (ID: RES-2026-0003)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-18 03:59:23', '2026-02-18 03:59:23'),
(109, 3, 'logout', 'User logged out of the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-18 03:59:27', '2026-02-18 03:59:27'),
(110, 1, 'login', 'User logged into the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-18 03:59:30', '2026-02-18 03:59:30'),
(111, 1, 'logout', 'User logged out of the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-18 04:05:19', '2026-02-18 04:05:19'),
(112, 3, 'login', 'User logged into the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-18 04:05:21', '2026-02-18 04:05:21'),
(113, 3, 'DELETE_RESIDENT', 'Deleted resident: asdasdad q (ID: RES-202602-0005)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-18 04:05:33', '2026-02-18 04:05:33'),
(114, 3, 'DELETE_RESIDENT', 'Deleted resident: asdadswfds d (ID: RES-202602-0006)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-18 04:05:50', '2026-02-18 04:05:50'),
(115, 3, 'DELETE_RESIDENT', 'Deleted resident: dsadads sadasdads (ID: RES-202602-0007)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-18 04:05:55', '2026-02-18 04:05:55'),
(116, 3, 'DELETE_RESIDENT', 'Deleted resident: asdasdad asdwasdf (ID: RES-202602-0008)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-18 04:05:59', '2026-02-18 04:05:59'),
(117, 3, 'CREATE_RESIDENT', 'Added new resident: Ham Burger (ID: RES-2026-0004)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-18 04:06:35', '2026-02-18 04:06:35'),
(118, 3, 'logout', 'User logged out of the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-18 04:06:38', '2026-02-18 04:06:38'),
(119, 1, 'login', 'User logged into the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-18 04:06:41', '2026-02-18 04:06:41'),
(120, 1, 'logout', 'User logged out of the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-18 04:56:11', '2026-02-18 04:56:11'),
(121, 3, 'login', 'User logged into the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-18 04:56:14', '2026-02-18 04:56:14'),
(122, 3, 'logout', 'User logged out of the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-18 05:59:33', '2026-02-18 05:59:33'),
(123, 1, 'login', 'User logged into the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-18 05:59:35', '2026-02-18 05:59:35'),
(124, 1, 'logout', 'User logged out of the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-18 05:59:42', '2026-02-18 05:59:42'),
(125, 3, 'login', 'User logged into the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-18 05:59:45', '2026-02-18 05:59:45'),
(126, 3, 'logout', 'User logged out of the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-18 06:00:47', '2026-02-18 06:00:47'),
(127, 1, 'login', 'User logged into the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-18 06:00:49', '2026-02-18 06:00:49'),
(128, 1, 'logout', 'User logged out of the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-21 06:14:42', '2026-02-21 06:14:42'),
(129, 3, 'login', 'User logged into the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-21 06:14:45', '2026-02-21 06:14:45'),
(130, 3, 'CREATE_CERTIFICATE', 'Created Clearance certificate for Jason Degorio (Certificate #: CERT-202602-0001)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-21 06:15:04', '2026-02-21 06:15:04'),
(131, 3, 'logout', 'User logged out of the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-21 06:15:09', '2026-02-21 06:15:09'),
(132, 1, 'login', 'User logged into the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-21 06:15:13', '2026-02-21 06:15:13'),
(133, 1, 'login', 'User logged into the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-24 06:34:45', '2026-02-24 06:34:45'),
(134, 1, 'logout', 'User logged out of the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-24 06:34:53', '2026-02-24 06:34:53'),
(135, 3, 'login', 'User logged into the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-24 06:34:57', '2026-02-24 06:34:57'),
(136, 3, 'logout', 'User logged out of the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-24 06:36:56', '2026-02-24 06:36:56'),
(137, 3, 'login', 'User logged into the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-24 06:43:22', '2026-02-24 06:43:22'),
(138, 3, 'logout', 'User logged out of the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-24 06:56:38', '2026-02-24 06:56:38'),
(139, 2, 'login', 'User logged into the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-24 06:56:51', '2026-02-24 06:56:51'),
(140, 2, 'logout', 'User logged out of the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-24 06:57:18', '2026-02-24 06:57:18'),
(141, 1, 'login', 'User logged into the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-24 06:57:24', '2026-02-24 06:57:24'),
(142, 3, 'login', 'User logged into the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-25 08:32:15', '2026-02-25 08:32:15'),
(143, 3, 'logout', 'User logged out of the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-25 08:32:47', '2026-02-25 08:32:47'),
(144, 1, 'login', 'User logged into the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-25 08:32:52', '2026-02-25 08:32:52'),
(145, 1, 'create', 'Created user: Clerk (Jhon Bubble)', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-25 08:35:32', '2026-02-25 08:35:32'),
(146, 1, 'logout', 'User logged out of the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-25 08:46:21', '2026-02-25 08:46:21'),
(147, 4, 'login', 'User logged into the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-25 08:46:27', '2026-02-25 08:46:27'),
(148, 4, 'logout', 'User logged out of the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-25 08:47:12', '2026-02-25 08:47:12'),
(149, 1, 'login', 'User logged into the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-25 08:47:18', '2026-02-25 08:47:18'),
(150, 1, 'logout', 'User logged out of the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-25 08:49:13', '2026-02-25 08:49:13');

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `posted_by` bigint(20) UNSIGNED NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `backups`
--

CREATE TABLE `backups` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `filename` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `size` bigint(20) NOT NULL DEFAULT '0',
  `type` enum('database','full') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'database',
  `tables_backed_up` json DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `backups`
--

INSERT INTO `backups` (`id`, `filename`, `path`, `size`, `type`, `tables_backed_up`, `created_at`) VALUES
(14, 'backup_2026-02-21_141313.json', 'backups/backup_2026-02-21_141313.json', 75918, 'database', '\"[\\\"activity_log\\\",\\\"activity_logs\\\",\\\"announcements\\\",\\\"backups\\\",\\\"barangay_info\\\",\\\"blotters\\\",\\\"cache\\\",\\\"cache_locks\\\",\\\"certificate_transactions\\\",\\\"certificates\\\",\\\"failed_jobs\\\",\\\"job_batches\\\",\\\"jobs\\\",\\\"migrations\\\",\\\"notifications\\\",\\\"password_resets\\\",\\\"residents\\\",\\\"users\\\"]\"', '2026-02-21 06:13:13');

-- --------------------------------------------------------

--
-- Table structure for table `barangay_info`
--

CREATE TABLE `barangay_info` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `barangay_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `barangay_captain` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `barangay_secretary` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `contact_number` varchar(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logo_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `barangay_info`
--

INSERT INTO `barangay_info` (`id`, `barangay_name`, `barangay_captain`, `barangay_secretary`, `address`, `contact_number`, `email`, `logo_path`, `created_at`, `updated_at`) VALUES
(1, 'Libertad', 'Justin Cabarubias', 'Zheny Morre', 'Libertad, Isabel, Leyte', '09123456789', 'brgylibertad@gmai.com', 'barangay-logos/hJGMHTXmps7mCzowO3mb6ycoqYweyUF7IEZgRyYv.png', '2026-02-08 20:19:51', '2026-02-08 20:44:25');

-- --------------------------------------------------------

--
-- Table structure for table `blotters`
--

CREATE TABLE `blotters` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `case_id` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `complainant_id` bigint(20) UNSIGNED NOT NULL,
  `respondent_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `respondent_address` text COLLATE utf8mb4_unicode_ci,
  `incident_type` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `incident_date` datetime NOT NULL,
  `incident_location` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('Pending','Ongoing','Settled','Referred') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Pending',
  `resolution` text COLLATE utf8mb4_unicode_ci,
  `resolved_date` datetime DEFAULT NULL,
  `handled_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `blotters`
--

INSERT INTO `blotters` (`id`, `case_id`, `complainant_id`, `respondent_name`, `respondent_address`, `incident_type`, `incident_date`, `incident_location`, `description`, `status`, `resolution`, `resolved_date`, `handled_by`, `created_at`, `updated_at`) VALUES
(1, 'BL-202602-0001', 4, 'asdasdad q', 'Balugo, Purok 3', 'Theft', '2026-02-17 10:03:00', 'amo balay', 'kawatan', 'Pending', NULL, NULL, 3, '2026-02-18 02:04:37', '2026-02-18 02:04:37');

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `certificates`
--

CREATE TABLE `certificates` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `certificate_id` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `resident_id` bigint(20) UNSIGNED NOT NULL,
  `certificate_type` enum('Clearance','Indigency','Residency') COLLATE utf8mb4_unicode_ci NOT NULL,
  `purpose` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('Pending','Approved','Released','Rejected','Archived') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Pending',
  `rejection_reason` text COLLATE utf8mb4_unicode_ci,
  `rejected_at` datetime DEFAULT NULL,
  `released_at` datetime DEFAULT NULL,
  `issued_by` bigint(20) UNSIGNED DEFAULT NULL,
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `issued_date` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `certificates`
--

INSERT INTO `certificates` (`id`, `certificate_id`, `resident_id`, `certificate_type`, `purpose`, `status`, `rejection_reason`, `rejected_at`, `released_at`, `issued_by`, `approved_by`, `approved_at`, `issued_date`, `created_at`, `updated_at`) VALUES
(1, 'CERT-202602-0001', 4, 'Clearance', 'OJT', 'Pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-21 06:15:04', '2026-02-21 06:15:04');

-- --------------------------------------------------------

--
-- Table structure for table `certificate_transactions`
--

CREATE TABLE `certificate_transactions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `certificate_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `action` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `action_details` text COLLATE utf8mb4_unicode_ci,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2026_02_07_062722_create_residents_table', 1),
(2, 'create_activity_logs_table', 2),
(3, 'announcements', 3),
(4, 'barangay_info', 4),
(5, 'blotters', 5),
(6, 'certificates', 6),
(7, 'notifications', 7),
(8, 'transactions', 8),
(9, 'activity_logs', 9),
(10, '2014_10_12_100000_create_password_resets_table', 10),
(11, '2026_02_07_074957_create_activity_log_table', 10),
(12, '2026_02_07_074958_add_event_column_to_activity_log_table', 10),
(13, '2026_02_07_074959_add_batch_uuid_column_to_activity_log_table', 10),
(14, 'roles', 11),
(15, 'backups', 12);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('info','warning','success','danger') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'info',
  `link` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `residents`
--

CREATE TABLE `residents` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `resident_id` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `first_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `middle_name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `birthdate` date NOT NULL,
  `gender` enum('Male','Female') COLLATE utf8mb4_unicode_ci NOT NULL,
  `civil_status` enum('Single','Married','Widowed','Divorced') COLLATE utf8mb4_unicode_ci NOT NULL,
  `contact_number` varchar(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `purok` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `household_number` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_voter` tinyint(1) NOT NULL DEFAULT '0',
  `is_4ps` tinyint(1) NOT NULL DEFAULT '0',
  `is_senior` tinyint(1) NOT NULL DEFAULT '0',
  `is_pwd` tinyint(1) NOT NULL DEFAULT '0',
  `pwd_id` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `disability_type` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `residents`
--

INSERT INTO `residents` (`id`, `resident_id`, `first_name`, `middle_name`, `last_name`, `birthdate`, `gender`, `civil_status`, `contact_number`, `email`, `address`, `purok`, `household_number`, `is_voter`, `is_4ps`, `is_senior`, `is_pwd`, `pwd_id`, `disability_type`, `created_at`, `updated_at`) VALUES
(3, 'RES-202602-0002', 'jhon', 's.', 'doe', '2026-03-05', 'Male', 'Married', '09128972938', NULL, 'Balugo', '2', 'HH-07', 1, 1, 0, 0, NULL, NULL, '2026-02-14 03:18:11', '2026-02-14 03:18:11'),
(4, 'RES-202602-0003', 'Jason', NULL, 'Degorio', '2025-11-27', 'Male', 'Single', '09128972938', 'degoriojason2004@gmail.com', 'Balugo', '2', 'HH-06', 1, 1, 0, 0, NULL, NULL, '2026-02-14 03:19:44', '2026-02-14 03:19:44'),
(10, 'RES-202602-0009', 'some', 'i', 'one', '2021-11-18', 'Male', 'Divorced', '09128972923', NULL, 'Balugo', '6', NULL, 1, 1, 0, 0, NULL, NULL, '2026-02-14 04:01:23', '2026-02-14 04:01:23'),
(11, 'RES-202602-0010', 'Light', 's', 'Fury', '2023-10-27', 'Male', 'Single', '09123456781', 'lightfury889@gmail.com', 'libertad', '1', NULL, 1, 1, 0, 0, NULL, NULL, '2026-02-14 04:29:01', '2026-02-14 04:29:01'),
(12, 'RES-2026-0001', 'some', NULL, 'one', '2004-07-21', 'Male', 'Married', '098238973342', NULL, 'Balugo', 'purok 1', NULL, 1, 1, 0, 0, NULL, NULL, '2026-02-18 02:55:11', '2026-02-18 02:55:11'),
(13, 'RES-2026-0002', 'Album', NULL, 'Buster', '2003-03-24', 'Female', 'Divorced', '09823897323', NULL, 'Balugo', 'purok 2', NULL, 1, 1, 1, 0, NULL, NULL, '2026-02-18 03:29:23', '2026-02-18 03:29:23'),
(14, 'RES-2026-0003', 'Captain', NULL, 'America', '1001-03-04', 'Male', 'Married', '09823897312', NULL, 'Balugo', 'purok 3', NULL, 1, 1, 1, 0, NULL, NULL, '2026-02-18 03:59:23', '2026-02-18 03:59:23'),
(15, 'RES-2026-0004', 'Ham', NULL, 'Burger', '2003-03-12', 'Male', 'Widowed', '098238973313', NULL, 'Balugo', 'purok 4', NULL, 1, 1, 1, 0, NULL, NULL, '2026-02-18 04:06:35', '2026-02-18 04:06:35'),
(16, 'RES-202602-0011', 'Mac', NULL, 'Donalds', '1993-03-12', 'Female', 'Widowed', '09823897313', NULL, 'Balugo', 'purok 1', NULL, 1, 0, 0, 0, NULL, NULL, '2026-02-18 06:00:37', '2026-02-18 06:00:37');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `username` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `full_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role_id` bigint(20) UNSIGNED DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `name`, `full_name`, `password`, `role_id`, `is_active`, `email_verified_at`, `remember_token`, `last_login`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin@barangaylibertad.com', 'System Administrator', 'System Administrator', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 1, '2026-02-07 05:51:02', '8VU2TfkX4RiB2lbfz4hmeMDeTpsT12RqlurXciEGzX9ujE84GQ5LM7z17Sxt', '2026-02-25 08:47:18', '2025-12-01 01:18:58', '2026-02-25 08:47:18'),
(2, 'captain', 'captain@barangaylibertad.com', 'Barangay Captain Juan Dela Cruz', 'Barangay Captain Juan Dela Cruz', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2, 1, '2026-02-07 05:51:02', NULL, '2026-02-24 06:56:51', '2025-12-01 01:18:58', '2026-02-24 06:56:51'),
(3, 'secretary', 'secretary@barangaylibertad.com', 'Barangay Secretary Maria Santos', 'Barangay Secretary Maria Santos', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3, 1, '2026-02-07 05:51:02', NULL, '2026-02-25 08:32:14', '2025-12-01 01:18:58', '2026-02-25 08:32:14'),
(4, 'Clerk', 'clerk@gmail.com', 'Jhon Bubble', 'Jhon Bubble', '$2y$12$2qWS8iiV287I.YIUpxfXr.RlXMM5ZekiWDfjYHWOKHurTYHTZWqtS', 3, 1, NULL, NULL, '2026-02-25 08:46:27', '2026-02-25 08:35:32', '2026-02-25 08:46:27');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subject` (`subject_type`,`subject_id`),
  ADD KEY `causer` (`causer_type`,`causer_id`),
  ADD KEY `activity_log_log_name_index` (`log_name`);

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `activity_logs_user_id_foreign` (`user_id`);

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `announcements_posted_by_foreign` (`posted_by`);

--
-- Indexes for table `backups`
--
ALTER TABLE `backups`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `barangay_info`
--
ALTER TABLE `barangay_info`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `blotters`
--
ALTER TABLE `blotters`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `blotters_case_id_unique` (`case_id`),
  ADD KEY `blotters_complainant_id_foreign` (`complainant_id`),
  ADD KEY `blotters_handled_by_foreign` (`handled_by`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indexes for table `certificates`
--
ALTER TABLE `certificates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `certificates_certificate_id_unique` (`certificate_id`),
  ADD KEY `certificates_resident_id_foreign` (`resident_id`),
  ADD KEY `certificates_issued_by_foreign` (`issued_by`),
  ADD KEY `certificates_approved_by_foreign` (`approved_by`);

--
-- Indexes for table `certificate_transactions`
--
ALTER TABLE `certificate_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `certificate_transactions_user_id_foreign` (`user_id`),
  ADD KEY `certificate_transactions_certificate_id_created_at_index` (`certificate_id`,`created_at`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notifications_user_id_is_read_created_at_index` (`user_id`,`is_read`,`created_at`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD KEY `password_resets_email_index` (`email`);

--
-- Indexes for table `residents`
--
ALTER TABLE `residents`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `residents_resident_id_unique` (`resident_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_username_unique` (`username`),
  ADD KEY `users_username_is_active_index` (`username`,`is_active`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_log`
--
ALTER TABLE `activity_log`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=151;

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `backups`
--
ALTER TABLE `backups`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `barangay_info`
--
ALTER TABLE `barangay_info`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `blotters`
--
ALTER TABLE `blotters`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `certificates`
--
ALTER TABLE `certificates`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `certificate_transactions`
--
ALTER TABLE `certificate_transactions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `residents`
--
ALTER TABLE `residents`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `announcements`
--
ALTER TABLE `announcements`
  ADD CONSTRAINT `announcements_posted_by_foreign` FOREIGN KEY (`posted_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `blotters`
--
ALTER TABLE `blotters`
  ADD CONSTRAINT `blotters_complainant_id_foreign` FOREIGN KEY (`complainant_id`) REFERENCES `residents` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `blotters_handled_by_foreign` FOREIGN KEY (`handled_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `certificates`
--
ALTER TABLE `certificates`
  ADD CONSTRAINT `certificates_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `certificates_issued_by_foreign` FOREIGN KEY (`issued_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `certificates_resident_id_foreign` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `certificate_transactions`
--
ALTER TABLE `certificate_transactions`
  ADD CONSTRAINT `certificate_transactions_certificate_id_foreign` FOREIGN KEY (`certificate_id`) REFERENCES `certificates` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `certificate_transactions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
