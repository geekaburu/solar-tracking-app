-- phpMyAdmin SQL Dump
-- version 4.5.2
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Jul 29, 2018 at 06:14 PM
-- Server version: 5.7.9
-- PHP Version: 7.2.0RC6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `genwin`
--

-- --------------------------------------------------------

--
-- Table structure for table `counties`
--
--
-- Dumping data for table `counties`
--

INSERT INTO `counties` (`id`, `name`, `created_at`, `updated_at`) VALUES
(1, 'Nairobi', '2018-01-07 12:40:32', '2018-01-07 12:40:32'),
(2, 'Mombasa', '2018-01-07 12:40:32', '2018-01-07 12:40:32'),
(3, 'Nakuru', '2018-02-06 20:50:34', '2018-02-06 20:50:34'),
(4, 'Kisumu', '2018-02-06 20:50:50', '2018-02-06 20:50:50'),
(5, 'Kericho', '2018-02-06 20:51:24', '2018-02-06 20:51:24'),
(6, 'Nandi', '2018-02-06 20:51:37', '2018-02-06 20:51:37'),
(7, 'Taita Taveta', '2018-02-06 20:51:53', '2018-02-06 20:51:53'),
(8, 'Kiambu', '2018-02-06 20:52:05', '2018-02-06 20:52:05'),
(9, 'Bomet', '2018-02-06 20:52:18', '2018-02-06 20:52:18'),
(10, 'Kilifi', '2018-02-06 20:52:32', '2018-02-06 20:52:32'),
(11, 'Lamu', '2018-02-06 20:52:54', '2018-02-06 20:52:54'),
(12, 'Turkana', '2018-02-06 20:53:06', '2018-02-06 20:53:06'),
(13, 'Isiolo', '2018-02-06 20:53:19', '2018-02-06 20:53:19'),
(14, 'Marsabit', '2018-02-06 20:53:33', '2018-02-06 20:53:33'),
(15, 'Garissa', '2018-02-06 20:54:23', '2018-02-06 20:54:23'),
(16, 'Wajir', '2018-02-06 20:54:36', '2018-02-06 20:54:36'),
(17, 'Uasin-Ngishu', '2018-02-06 20:55:09', '2018-02-06 20:55:09'),
(18, 'Baringo', '2018-02-06 20:55:30', '2018-02-06 20:55:30'),
(19, 'Elgeyo Marakwet', '2018-02-06 20:55:52', '2018-02-06 20:55:52'),
(20, 'Narok', '2018-02-06 20:56:07', '2018-02-06 20:56:07'),
(21, 'Kajiado', '2018-02-06 20:56:21', '2018-02-06 20:56:21'),
(22, 'Meru', '2018-02-06 20:56:32', '2018-02-06 20:56:32'),
(23, 'Embu', '2018-02-06 20:56:47', '2018-02-06 20:56:47'),
(24, 'Tharaka Nithi', '2018-02-06 20:57:09', '2018-02-06 20:57:09'),
(25, 'Kakamega', '2018-02-06 20:57:27', '2018-02-06 20:57:27'),
(26, 'Busia', '2018-02-06 20:57:38', '2018-02-06 20:57:38'),
(27, 'Vihiga', '2018-02-06 20:57:50', '2018-02-06 20:57:50'),
(29, 'Trans-nzoia', '2018-02-06 14:58:00', '2018-02-06 20:59:07'),
(30, 'Migori', '2018-02-06 20:59:42', '2018-02-06 20:59:42'),
(31, 'Kisii', '2018-02-06 21:00:02', '2018-02-06 21:00:02'),
(32, 'Nyamira', '2018-02-06 21:00:15', '2018-02-06 21:00:15'),
(33, 'Nyandarua', '2018-02-06 21:00:28', '2018-02-06 21:00:28'),
(34, 'Nyeri', '2018-02-06 21:00:39', '2018-02-06 21:00:39'),
(35, 'Kirinyaga', '2018-02-06 21:00:53', '2018-02-06 21:00:53'),
(36, 'Muranga', '2018-02-06 21:01:04', '2018-02-06 21:01:04'),
(37, 'Laikipia', '2018-02-06 21:01:47', '2018-02-06 21:01:47'),
(38, 'Samburu', '2018-02-06 21:02:40', '2018-02-06 21:02:40'),
(39, 'Siaya', '2018-02-06 21:03:01', '2018-02-06 21:03:01'),
(40, 'Tana River', '2018-02-06 21:09:04', '2018-02-06 21:09:04'),
(41, 'West Pokot', '2018-02-06 21:09:18', '2018-02-06 21:09:18'),
(42, 'Mandera', '2018-02-06 21:09:56', '2018-02-06 21:09:56'),
(43, 'Makueni', '2018-02-06 21:10:08', '2018-02-06 21:10:08'),
(44, 'Machakos', '2018-02-06 21:10:22', '2018-02-06 21:10:22'),
(45, 'Kitui', '2018-02-06 21:10:36', '2018-02-06 21:10:36'),
(46, 'Kwale', '2018-02-06 21:11:21', '2018-02-06 21:11:21'),
(47, 'Homa Bay', '2018-02-06 21:11:39', '2018-02-06 21:11:39'),
(48, 'Bungoma', '2018-02-07 02:24:44', '2018-02-07 02:24:44');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
