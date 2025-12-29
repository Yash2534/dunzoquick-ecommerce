-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 10, 2025 at 07:56 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `dunzo_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `profile_photo` varchar(255) DEFAULT NULL,
  `preferences` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`preferences`)),
  `two_factor_secret` varchar(255) DEFAULT NULL,
  `two_factor_enabled` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`id`, `user_id`, `product_id`, `quantity`, `created_at`) VALUES
(46, 3, 385, 1, '2025-09-05 19:21:05');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `link_url` varchar(255) NOT NULL,
  `sort_order` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `link_url`, `sort_order`) VALUES
(1, 'Grocery', 'product/Grocery.php', 10),
(2, 'Pharmacy', 'product/Pharmacy.php', 20),
(3, 'Snacks', 'product/SnackZone.php', 30),
(4, 'Beverages', 'product/CoolSips.php', 40),
(5, 'Bakery', 'product/Bakery.php', 50),
(6, 'Personal Care', 'product/Cosmetic.php', 60),
(7, 'Electronics', 'product/Electronics.php', 70),
(8, 'Pet Care', 'product/Pet.php', 80),
(10, 'Fashion', 'product/fashion.php', 90);

-- --------------------------------------------------------

--
-- Table structure for table `coupons`
--

CREATE TABLE `coupons` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `discount_percentage` decimal(5,2) NOT NULL,
  `min_spend` decimal(10,2) NOT NULL DEFAULT 0.00,
  `expiry_date` date DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `prime_only` tinyint(1) NOT NULL DEFAULT 0,
  `user_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `max_uses_total` int(11) DEFAULT NULL COMMENT 'Max uses for this coupon overall',
  `max_uses_user` int(11) DEFAULT 1 COMMENT 'Max uses per user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `coupons`
--

INSERT INTO `coupons` (`id`, `code`, `discount_percentage`, `min_spend`, `expiry_date`, `is_active`, `prime_only`, `user_id`, `created_at`, `max_uses_total`, `max_uses_user`) VALUES
(1, 'WELCOME10-7', 10.00, 200.00, '2025-10-02', 1, 0, 7, '2025-09-01 19:40:27', NULL, 1),
(2, 'WELCOME10-6', 10.00, 200.00, '2025-10-02', 1, 0, 6, '2025-09-01 19:40:27', NULL, 1),
(3, 'WELCOME10-4', 10.00, 200.00, '2025-10-02', 1, 0, 4, '2025-09-01 19:40:27', NULL, 1),
(4, 'WELCOME10-5', 10.00, 200.00, '2025-10-02', 1, 0, 5, '2025-09-01 19:40:27', NULL, 1),
(5, 'WELCOME10-2', 10.00, 200.00, '2025-10-02', 1, 0, 2, '2025-09-01 19:40:27', NULL, 1),
(6, 'WELCOME10-3', 10.00, 200.00, '2025-10-02', 1, 0, 3, '2025-09-01 19:40:27', NULL, 1),
(7, 'WELCOME10-1', 10.00, 200.00, '2025-10-02', 1, 0, 1, '2025-09-01 19:40:27', NULL, 1),
(8, 'SPECIAL20-7', 20.00, 500.00, '2025-11-01', 1, 0, 7, '2025-09-01 19:40:27', NULL, 1),
(9, 'SPECIAL20-6', 20.00, 500.00, '2025-11-01', 1, 0, 6, '2025-09-01 19:40:27', NULL, 1),
(10, 'SPECIAL20-4', 20.00, 500.00, '2025-11-01', 1, 0, 4, '2025-09-01 19:40:27', NULL, 1),
(11, 'SPECIAL20-5', 20.00, 5000.00, '2025-11-01', 1, 0, 5, '2025-09-01 19:40:27', NULL, 1),
(12, 'SPECIAL20-2', 20.00, 500.00, '2025-11-01', 1, 0, 2, '2025-09-01 19:40:27', NULL, 1),
(13, 'SPECIAL20-3', 20.00, 500.00, '2025-11-01', 1, 0, 3, '2025-09-01 19:40:27', NULL, 1),
(14, 'SPECIAL20-1', 20.00, 50.00, '2025-11-01', 1, 0, 1, '2025-09-01 19:40:27', NULL, 1),
(15, 'DUNZO756', 25.00, 10000.00, '2025-03-05', 1, 0, 6, '2025-09-18 10:20:02', NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_number` varchar(50) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `discount_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `shipping_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `tax_amount` decimal(10,2) NOT NULL,
  `coupon_code` varchar(50) DEFAULT NULL,
  `delivery_partner_id` int(11) DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','confirmed','preparing','out_for_delivery','delivered','cancelled') DEFAULT 'pending',
  `delivery_priority` enum('standard','priority') NOT NULL DEFAULT 'standard',
  `delivery_address` text NOT NULL,
  `payment_method` varchar(50) DEFAULT 'COD',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `payment_id` varchar(255) DEFAULT NULL,
  `razorpay_order_id` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `order_number`, `subtotal`, `discount_amount`, `shipping_amount`, `tax_amount`, `coupon_code`, `delivery_partner_id`, `total_amount`, `status`, `delivery_priority`, `delivery_address`, `payment_method`, `created_at`, `updated_at`, `payment_id`, `razorpay_order_id`) VALUES
(41, 1, 'DUNZO-17571949911', 522.00, 104.40, 49.00, 33.41, 'SPECIAL20-1', NULL, 500.01, 'delivered', 'standard', '12, mavdi, rajkot, gujrat - 36004', 'Razorpay', '2025-09-06 21:43:11', '2025-09-06 21:52:44', 'pay_RET4K1AeqazTeL', 'order_RET4C72gZvrZbK'),
(42, 1, 'DUNZO-17571977481', 483.00, 0.00, 49.00, 38.64, NULL, NULL, 570.64, 'delivered', 'standard', '12, mavdi, rajkot, gujrat - 36004', 'Razorpay', '2025-09-06 22:29:08', '2025-09-06 22:30:13', 'pay_RETqiN0IYKoyyg', 'order_RETqRkqbmDpF5B'),
(43, 1, 'DUNZO-17573061311', 390.00, 78.00, 49.00, 24.96, 'SPECIAL20-1', NULL, 385.96, 'delivered', 'standard', '12, mavdi, rajkot, gujrat - 36004', 'Razorpay', '2025-09-08 04:35:31', '2025-09-08 04:37:22', 'pay_REycyd7MU8X0cP', 'order_REycPdNXJzTd4A'),
(44, 4, 'DUNZO-17573069444', 345.00, 34.50, 49.00, 24.84, 'WELCOME10-4', NULL, 384.34, 'confirmed', 'standard', 'indraprastha-2, near patel society, surendranagar, Gujarat - 363020', 'Razorpay', '2025-09-08 04:49:04', '2025-09-08 04:49:49', 'pay_REyrHOP5Q54VYW', 'order_REyqLtyVnDTvrT'),
(45, 4, 'DUNZO-17573075554', 11991.00, 1199.10, 0.00, 863.35, 'WELCOME10-4', NULL, 11655.25, 'confirmed', 'standard', 'indraprastha-2, near patel society, surendranagar, Gujarat - 363020', 'Razorpay', '2025-09-08 04:59:15', '2025-09-26 18:29:31', 'pay_REz23c0TkWVzPf', 'order_REz1u1UZ6TeXTz'),
(46, 4, 'DUNZO-17573091434', 210.00, 0.00, 0.00, 16.80, NULL, NULL, 226.80, 'confirmed', 'priority', '12, mavdi, rajkot, gujrat - 36004', 'Razorpay', '2025-09-08 05:25:43', '2025-09-08 19:21:44', 'pay_REzTDhzCruzUSM', 'order_REzSsj21HTfoj3'),
(47, 1, 'DUNZO-17573581521', 50.00, 10.00, 49.00, 3.20, 'SPECIAL20-1', NULL, 92.20, 'delivered', 'standard', '12, mavdi, rajkot, gujrat - 36004', 'Razorpay', '2025-09-08 19:02:32', '2025-09-09 09:53:03', 'pay_RFDOoGE1L7Caif', 'order_RFDOacJkklQ4uY'),
(48, 1, 'DUNZO-17574063541', 725.00, 0.00, 49.00, 58.00, NULL, NULL, 832.00, 'confirmed', 'standard', 'Rajkot West, mavdi, Rajkot West, gujrat - 36004', 'Razorpay', '2025-09-09 08:25:54', '2025-09-09 09:27:08', 'pay_RFR5RK2aRcFLmE', 'order_RFR4vyAFmH7ans'),
(49, 4, 'DUNZO-17581029154', 199.00, 0.00, 0.00, 15.92, NULL, NULL, 214.92, 'pending', 'priority', 'indraprastha-2, near patel society, surendranagar, Gujarat - 363020', 'Razorpay', '2025-09-17 09:55:15', '2025-09-17 09:55:15', 'pay_RIcsgDqgtzZHrQ', 'order_RIcsVVT6Nlr6U0'),
(50, 1, 'DUNZO-17581045561', 120.00, 24.00, 49.00, 7.68, 'SPECIAL20-1', NULL, 152.68, 'pending', 'standard', '12, mavdi, rajkot, gujrat - 360004', 'Razorpay', '2025-09-17 10:22:36', '2025-09-17 10:22:36', 'pay_RIdLakgRcjWK1Y', 'order_RIdKxrwsODDkAF'),
(51, 1, 'DUNZO-17581885621', 440.00, 44.00, 49.00, 31.68, 'WELCOME10-1', NULL, 476.68, 'delivered', 'standard', '12, mavdi, rajkot, gujrat - 360004', 'Razorpay', '2025-09-18 09:42:42', '2025-09-18 09:47:01', 'pay_RJ1CZq7QC7x4kV', 'order_RJ1CMhG9PoRRM7'),
(52, 1, 'DUNZO-17583895541', 30.00, 0.00, 49.00, 2.40, NULL, NULL, 81.40, 'pending', 'standard', '12, mavdi, rajkot, gujrat - 360004', 'Razorpay', '2025-09-20 17:32:34', '2025-09-20 17:32:34', 'pay_RJwH848U3jUaT2', 'order_RJwFd3TMVivgtd'),
(53, 1, 'DUNZO-17585319481', 432.00, 86.40, 49.00, 27.65, 'SPECIAL20-1', NULL, 422.25, 'pending', 'standard', '12, mavdi, rajkot, gujrat - 360004', 'Razorpay', '2025-09-22 09:05:48', '2025-09-22 09:05:48', 'pay_RKai107JjkWZrX', 'order_RKahn1YOGdvtSK'),
(54, 1, 'DUNZO-17586136041', 66.00, 0.00, 49.00, 5.28, NULL, NULL, 120.28, 'confirmed', 'standard', '12, mavdi, rajkot, gujrat - 360004', 'Razorpay', '2025-09-23 07:46:44', '2025-09-23 08:22:53', 'pay_RKxtWVpNzGonAA', 'order_RKxtIEm7nFhudE'),
(55, 1, 'DUNZO-17586196941', 440.00, 88.00, 49.00, 28.16, 'SPECIAL20-1', NULL, 429.16, 'delivered', 'standard', 'indraprastha-2, near patel society, surendranagar, Gujarat - 363020', 'Razorpay', '2025-09-23 09:28:14', '2025-09-23 09:29:12', 'pay_RKzcs7KVeQTpCw', 'order_RKzccclhrnfZA2'),
(56, 1, 'DUNZO-17586210481', 110.00, 22.00, 49.00, 7.04, 'SPECIAL20-1', NULL, 144.04, 'pending', 'standard', '12, mavdi, rajkot, gujrat - 360004', 'Razorpay', '2025-09-23 09:50:48', '2025-09-23 09:50:48', 'pay_RL00cj95061KPc', 'order_RL00Q61uYNOzJe'),
(57, 1, 'DUNZO-17587758711', 45.00, 0.00, 49.00, 3.60, NULL, NULL, 97.60, 'pending', 'standard', '12, mavdi, rajkot, gujrat - 360004', 'Razorpay', '2025-09-25 04:51:11', '2025-09-25 04:51:11', 'pay_RLhyJ1hd2hZsYn', 'order_RLhy6lvw1KVdz0'),
(58, 1, 'DUNZO-17587762831', 1299.00, 259.80, 25.00, 83.14, 'SPECIAL20-1', NULL, 1147.34, 'pending', 'standard', 'indraprastha-2, near patel society, surendranagar, Gujarat - 363020', 'Razorpay', '2025-09-25 04:58:03', '2025-09-26 18:28:48', 'pay_RLi5fyMzr4CWdr', 'order_RLi5VbeXj3lETb'),
(59, 1, 'DUNZO-17589152341', 280.00, 0.00, 49.00, 22.40, NULL, NULL, 351.40, 'pending', 'standard', '12, mavdi, rajkot, gujrat - 363020', 'Razorpay', '2025-09-26 19:33:54', '2025-09-26 19:33:54', 'pay_RMLXxMK0amRWU9', 'order_RMLXCc9DvrWWsz'),
(60, 1, 'DUNZO-17589163331', 45.00, 0.00, 49.00, 3.60, NULL, NULL, 97.60, 'confirmed', 'standard', '12, mavdi, rajkot, gujrat - 360004', 'Razorpay', '2025-09-26 19:52:13', '2025-09-26 20:17:20', 'pay_RMLrJICgwdVTPo', 'order_RMLr3umeUcrOvm'),
(61, 1, 'DUNZO-17589167571', 150.00, 0.00, 49.00, 12.00, NULL, NULL, 211.00, 'cancelled', 'standard', '12, mavdi, rajkot, gujrat - 360004', 'Razorpay', '2025-09-26 19:59:17', '2025-09-26 19:59:31', 'pay_RMLymeEnFrKB2O', 'order_RMLyW3d8ywsHro'),
(62, 1, 'DUNZO-17589169861', 199.00, 0.00, 0.00, 15.92, NULL, NULL, 214.92, 'cancelled', 'standard', 'indraprastha-2, near patel society, surendranagar, Gujarat - 363020', 'Razorpay', '2025-09-26 20:03:06', '2025-09-26 20:10:10', 'pay_RMM2Sa9Mik6WzX', 'order_RMM1wQ7gJaZqlt'),
(63, 1, 'DUNZO-17589173521', 270.00, 0.00, 0.00, 21.60, NULL, NULL, 291.60, 'cancelled', 'standard', '12, mavdi, rajkot, gujrat - 360004', 'Razorpay', '2025-09-26 20:09:12', '2025-09-26 20:09:41', 'pay_RMM99U5jb4B1kI', 'order_RMM8zLGRFeWiBu'),
(64, 1, 'DUNZO-17589175731', 30.00, 0.00, 0.00, 2.40, NULL, NULL, 32.40, 'cancelled', 'standard', '12, mavdi, rajkot, gujrat - 360004', 'Razorpay', '2025-09-26 20:12:53', '2025-09-26 20:14:50', 'pay_RMMD7fXgMwo6iL', 'order_RMMCY1Mu3s3FC7'),
(65, 1, 'DUNZO-17589177261', 2683.00, 268.30, 0.00, 193.18, 'WELCOME10-1', NULL, 2607.88, 'confirmed', 'standard', 'indraprastha-2, near patel society, surendranagar, Gujarat - 363020', 'Razorpay', '2025-09-26 20:15:26', '2025-09-26 20:17:25', 'pay_RMMFpOsshCGgul', 'order_RMMFh9pM6m51Z3'),
(66, 1, 'DUNZO-17589179081', 699.00, 0.00, 0.00, 55.92, NULL, NULL, 754.92, 'pending', 'standard', '12, mavdi, rajkot, gujrat - 360004', 'Razorpay', '2025-09-26 20:18:28', '2025-09-26 20:18:28', 'pay_RMMIzj5MEfZ9MY', 'order_RMMIqZuNDBC4LK'),
(67, 1, 'DUNZO-17589182151', 38.00, 0.00, 0.00, 3.04, NULL, NULL, 41.04, 'confirmed', 'standard', '12, mavdi, rajkot, gujrat - 360004', 'Razorpay', '2025-09-26 20:23:35', '2025-09-26 20:30:52', 'pay_RMMOSE9OII5sB6', 'order_RMMOG7HULiPdzg'),
(68, 1, 'DUNZO-17589184991', 399.00, 0.00, 0.00, 31.92, NULL, NULL, 430.92, 'cancelled', 'standard', 'indraprastha-2, near patel society, surendranagar, Gujarat - 363020', 'Razorpay', '2025-09-26 20:28:19', '2025-09-26 20:29:34', 'pay_RMMTRgd4s1qK8l', 'order_RMMTJ5W9hx9v1e'),
(69, 1, 'DUNZO-17589187511', 360.00, 72.00, 0.00, 23.04, 'SPECIAL20-1', NULL, 311.04, 'pending', 'standard', 'chamunda krupa om nager, mavdi chok, rajkot, gujrat - 360004', 'Razorpay', '2025-09-26 20:32:31', '2025-09-26 20:32:31', 'pay_RMMXsRqCU9Qrsg', 'order_RMMXKzoHPt5nRR'),
(70, 1, 'DUNZO-17589193731', 3100.00, 0.00, 0.00, 248.00, NULL, NULL, 3348.00, 'pending', 'standard', 'chamunda krupa om nager, mavdi chok, rajkot, gujrat - 360004', 'Razorpay', '2025-09-26 20:42:53', '2025-09-26 20:42:53', 'pay_RMMijyejyWXpY0', 'order_RMMiYol3DHzaiJ'),
(71, 1, 'DUNZO-17597281161', 62475.00, 0.00, 0.00, 4998.00, NULL, NULL, 67473.00, 'cancelled', 'standard', 'chamunda krupa om nager, mavdi chok, rajkot, gujrat - 360004', 'Razorpay', '2025-10-06 05:21:56', '2025-10-06 05:22:20', 'pay_RQ4Mmd1K3akrOK', 'order_RQ4LKCxurFM76W'),
(72, 1, 'DUNZO-17598125061', 50.00, 10.00, 0.00, 3.20, 'SPECIAL20-1', NULL, 43.20, 'cancelled', 'standard', 'chamunda krupa om nager, mavdi chok, rajkot, gujrat - 360004', 'Razorpay', '2025-10-07 04:48:26', '2025-10-07 04:48:36', 'pay_RQSKsmLqCD40Lu', 'order_RQSKgy9qyZoKRm'),
(73, 1, 'DUNZO-17598212501', 282.00, 0.00, 0.00, 22.56, NULL, NULL, 304.56, 'pending', 'standard', 'chamunda krupa om nager, mavdi chok, rajkot, gujrat - 360004', 'Razorpay', '2025-10-07 07:14:10', '2025-10-07 07:14:10', 'pay_RQUoppcXG4Hw5B', 'order_RQUocrceLC1Xxc'),
(74, 1, 'DUNZO-17598224321', 150.00, 30.00, 0.00, 9.60, 'SPECIAL20-1', NULL, 129.60, 'cancelled', 'standard', 'chamunda krupa om nager, mavdi chok, rajkot, gujrat - 360004', 'Razorpay', '2025-10-07 07:33:52', '2025-10-07 07:34:44', 'pay_RQV9ZSgfmNwxMA', 'order_RQV9K0ObiOFAov');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `product_name` varchar(200) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name`, `quantity`, `price`, `image`, `created_at`) VALUES
(1, 41, 596, 'Baby Lotion', 1, 95.00, 'Image/PHARMACY/Baby care/6.jpeg', '2025-09-06 21:43:11'),
(2, 41, 11, 'Soft Sandwich Bread (400g)', 1, 42.00, 'Bakeryyy/download (4).jpeg', '2025-09-06 21:43:11'),
(3, 41, 296, 'B Natural Apple Juice', 1, 65.00, 'Image/CoolSips/download (8).jpeg', '2025-09-06 21:43:11'),
(4, 41, 500, 'Red Bull (250ml)', 1, 110.00, 'Image/Beverages/pexels-sanket-sawale-62949595-17423270.jpg', '2025-09-06 21:43:11'),
(5, 41, 382, 'Khadi Natural Neem & Tea Tree Face Wash', 1, 160.00, 'Image/COSMETIC/download (45).jpeg', '2025-09-06 21:43:11'),
(6, 41, 332, 'Black Horse Energy Drink', 1, 50.00, 'Image/CoolSips/download (44).jpeg', '2025-09-06 21:43:11'),
(7, 42, 398, 'HDMI Cable', 1, 299.00, 'Image/Electronics/download (13).jpeg', '2025-09-06 22:29:08'),
(8, 42, 611, 'Reusable Ice Pack', 1, 99.00, 'Image/PHARMACY/Equipment/9.jpeg', '2025-09-06 22:29:08'),
(9, 42, 591, 'Johnson’s Baby Oil', 1, 85.00, 'Image/PHARMACY/Baby care/13.jpeg', '2025-09-06 22:29:08'),
(10, 43, 27, 'Italian Tiramisu Cake (500g)', 1, 390.00, 'Bakeryyy/download (20).jpeg', '2025-09-08 04:35:31'),
(11, 44, 29, 'Mocha Coffee Cake (500g)', 1, 345.00, 'Bakeryyy/download (22).jpeg', '2025-09-08 04:49:04'),
(12, 45, 633, 'Ferrero Rocher (3 pcs)', 33, 140.00, 'Image/SnackZonee/download (17).jpeg', '2025-09-08 04:59:15'),
(13, 45, 298, 'Real Pineapple Juice', 23, 67.00, 'Image/CoolSips/download (10).jpeg', '2025-09-08 04:59:15'),
(14, 45, 634, 'Cadbury Bournville 50% Cocoa', 29, 99.00, 'Image/SnackZonee/download (18).jpeg', '2025-09-08 04:59:15'),
(15, 45, 332, 'Black Horse Energy Drink', 8, 50.00, 'Image/CoolSips/download (44).jpeg', '2025-09-08 04:59:15'),
(16, 45, 419, 'Fresh Papaya', 5, 55.00, 'Image/PICTURE/Fresh Papaya.jpg', '2025-09-08 04:59:15'),
(17, 45, 518, 'Frozen Paneer Roll (2 pcs)', 4, 85.00, 'Image/Frozen/images.jpeg', '2025-09-08 04:59:15'),
(18, 45, 513, 'Frozen Momos (10 pcs)', 4, 110.00, 'Image/Frozen/download (4).jpeg', '2025-09-08 04:59:15'),
(19, 45, 401, 'USB Extension Cable', 4, 199.00, 'Image/Electronics/download (16).jpeg', '2025-09-08 04:59:15'),
(20, 45, 41, 'Peanut Butter Cookies (200g)', 4, 92.00, 'Bakeryyy/download (34).jpeg', '2025-09-08 04:59:15'),
(21, 45, 462, 'Masoor Dal (1kg)', 4, 85.00, 'Image/Grains/download.jpeg', '2025-09-08 04:59:15'),
(22, 46, 590, 'Apple Cider Vinegar', 1, 210.00, 'Image/PHARMACY/Wellness/12.jpeg', '2025-09-08 05:25:43'),
(23, 47, 451, 'Butter Croissant', 1, 50.00, 'Image/Bakery/pexels-elkady-3892469.jpg', '2025-09-08 19:02:32'),
(24, 48, 330, 'Panther Energy Drink', 1, 35.00, 'Image/CoolSips/download (42).jpeg', '2025-09-09 08:25:54'),
(25, 48, 443, 'Pure Ghee (500ml)', 1, 310.00, 'Image/Dairy/Pure Ghee.jpg', '2025-09-09 08:25:54'),
(26, 48, 30, 'New York Cheesecake (400g)', 1, 380.00, 'Bakeryyy/download (23).jpeg', '2025-09-09 08:25:54'),
(27, 49, 397, 'Type-C Charging Cable', 1, 199.00, 'Image/Electronics/download (12).jpeg', '2025-09-17 09:55:15'),
(28, 50, 381, 'Lever Ayush Natural Ayurvedic Face Wash', 1, 75.00, 'Image/COSMETIC/download (44).jpeg', '2025-09-17 10:22:36'),
(29, 50, 448, 'Brown Bread (400g)', 1, 45.00, 'Image/Bakery/istockphoto-1420937092-612x612.jpg', '2025-09-17 10:22:36'),
(30, 51, 500, 'Red Bull (250ml)', 4, 110.00, 'Image/Beverages/pexels-sanket-sawale-62949595-17423270.jpg', '2025-09-18 09:42:42'),
(31, 52, 622, 'Peri Peri Spiced Potato Chips', 1, 30.00, 'Image/SnackZonee/download (7).jpeg', '2025-09-20 17:32:34'),
(32, 53, 568, 'Crocin Advance', 1, 35.00, 'Image/PHARMACY/Medicines/2.jpeg', '2025-09-22 09:05:48'),
(33, 53, 44, 'Paneer Puff (Pack of 2)', 1, 70.00, 'Bakeryyy/download (37).jpeg', '2025-09-22 09:05:48'),
(34, 53, 425, 'Red Onions', 1, 28.00, 'Image/Vegetables/istockphoto-514833906-612x612.jpg', '2025-09-22 09:05:48'),
(35, 53, 398, 'HDMI Cable', 1, 299.00, 'Image/Electronics/download (13).jpeg', '2025-09-22 09:05:48'),
(36, 54, 53, 'Masala Puff (Pack of 2)', 1, 66.00, 'Bakeryyy/download (45).jpeg', '2025-09-23 07:46:44'),
(37, 55, 27, 'Italian Tiramisu Cake (500g)', 1, 390.00, 'Bakeryyy/download (20).jpeg', '2025-09-23 09:28:14'),
(38, 55, 332, 'Black Horse Energy Drink', 1, 50.00, 'Image/CoolSips/download (44).jpeg', '2025-09-23 09:28:14'),
(39, 56, 500, 'Red Bull (250ml)', 1, 110.00, 'Image/Beverages/pexels-sanket-sawale-62949595-17423270.jpg', '2025-09-23 09:50:48'),
(40, 57, 331, 'Gold Energy Drink', 1, 45.00, 'Image/CoolSips/download (43).jpeg', '2025-09-25 04:51:11'),
(41, 58, 409, 'Portable Power Bank', 1, 1299.00, 'Image/Electronics/download (24).jpeg', '2025-09-25 04:58:03'),
(42, 59, 20, 'Classic Vanilla Cake (500g)', 1, 280.00, 'Bakeryyy/download (13).jpeg', '2025-09-26 19:33:54'),
(43, 60, 504, 'Tender Coconut Water', 1, 45.00, 'Image/Beverages/images.jpeg', '2025-09-26 19:52:13'),
(44, 61, 416, 'Black Grapes (500g)', 2, 75.00, 'Image/PICTURE/Black Grapes.jpg', '2025-09-26 19:59:17'),
(45, 62, 543, 'Chew Rope Tug Toy for Dogs', 1, 199.00, 'Image/PET/download (24).jpeg', '2025-09-26 20:03:06'),
(46, 63, 500, 'Red Bull (250ml)', 1, 110.00, 'Image/Beverages/pexels-sanket-sawale-62949595-17423270.jpg', '2025-09-26 20:09:12'),
(47, 63, 542, 'Gnawlers Oat Bone Treat for Dogs (240g)', 1, 160.00, 'Image/PET/download (23).jpeg', '2025-09-26 20:09:12'),
(48, 64, 622, 'Peri Peri Spiced Potato Chips', 1, 30.00, 'Image/SnackZonee/download (7).jpeg', '2025-09-26 20:12:53'),
(49, 65, 477, 'Garbage Bags (30 pcs)', 1, 85.00, 'Image/Household/download (6).jpeg', '2025-09-26 20:15:26'),
(50, 65, 393, 'Gaming Headset', 1, 2499.00, 'Image/Electronics/download (8).jpeg', '2025-09-26 20:15:26'),
(51, 65, 326, 'Hell Classic Energy Drink', 1, 99.00, 'Image/CoolSips/download (38).jpeg', '2025-09-26 20:15:26'),
(52, 66, 387, 'Multi-Port USB Charger', 1, 699.00, 'Image/Electronics/download (4).jpeg', '2025-09-26 20:18:28'),
(53, 67, 305, 'Fanta Orange Drink', 1, 38.00, 'Image/CoolSips/download (17).jpeg', '2025-09-26 20:23:35'),
(54, 68, 403, 'DisplayPort Cable', 1, 399.00, 'Image/Electronics/download (18).jpeg', '2025-09-26 20:28:19'),
(55, 69, 460, 'Whole Wheat Flour (5kg)', 1, 210.00, 'Image/Grains/istockphoto-172876049-612x612.jpg', '2025-09-26 20:32:31'),
(56, 69, 33, 'Oatmeal Raisin Cookies (200g)', 1, 90.00, 'Bakeryyy/download (26).jpeg', '2025-09-26 20:32:31'),
(57, 69, 320, 'Smartwater Vapor Distilled', 1, 60.00, 'Image/CoolSips/download (32).jpeg', '2025-09-26 20:32:31'),
(58, 70, 609, 'Littmann Classic II Stethoscope', 1, 3100.00, 'Image/PHARMACY/Equipment/7.jpeg', '2025-09-26 20:42:53'),
(59, 71, 393, 'Gaming Headset', 25, 2499.00, 'Image/Electronics/download (8).jpeg', '2025-10-06 05:21:56'),
(60, 72, 451, 'Butter Croissant', 1, 50.00, 'Image/Bakery/pexels-elkady-3892469.jpg', '2025-10-07 04:48:26'),
(61, 73, 292, 'Real Guava Juice', 1, 62.00, 'Image/CoolSips/download (4).jpeg', '2025-10-07 07:14:10'),
(62, 73, 561, 'Pet Toothbrush & Toothpaste Set', 1, 220.00, 'Image/PET/download (42).jpeg', '2025-10-07 07:14:10'),
(63, 74, 415, 'Alphonso Mangoes (1kg)', 1, 150.00, 'Image/PICTURE/Alphonso Mangoes.jpg', '2025-10-07 07:33:52');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `payment_id` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` varchar(50) NOT NULL,
  `method` varchar(50) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `payment_id`, `amount`, `status`, `method`, `user_id`, `order_id`, `created_at`) VALUES
(1, 'pay_REQH0ttWEAqMkf', 140.80, 'success', NULL, 1, NULL, '2025-09-07 00:29:19'),
(2, 'pay_REQN2iCNTwQCDg', 2579.04, 'success', NULL, 1, NULL, '2025-09-07 00:34:49'),
(3, 'pay_REQVyseHU1aiD2', 308.20, 'success', NULL, 1, NULL, '2025-09-07 00:43:18'),
(4, 'pay_RERZ2YYU6laXfa', 243.40, 'success', NULL, 1, NULL, '2025-09-07 01:44:52'),
(5, 'pay_RERcjd2dnDdNvs', 92.20, 'success', NULL, 1, NULL, '2025-09-07 01:48:29');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `category` varchar(100) NOT NULL,
  `sub_category` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `image` varchar(255) NOT NULL,
  `stock` int(11) NOT NULL,
  `unit` varchar(50) NOT NULL,
  `stock_quantity` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `category`, `sub_category`, `description`, `price`, `image`, `stock`, `unit`, `stock_quantity`) VALUES
(7, 'Fresh Brown Bread (400g)', 'Bakery', 'bread', 'Bread', 45.00, 'Bakeryyy/download.jpeg', 100, '400g', 10),
(8, 'Classic White Bread (400g)', 'Bakery', 'bread', NULL, 40.00, 'Bakeryyy/download (1).jpeg', 100, '400g', 0),
(9, 'Multigrain Bread (400g)', 'Bakery', 'bread', NULL, 50.00, 'Bakeryyy/download (2).jpeg', 100, '400g', 0),
(10, 'Whole Wheat Bread (400g)', 'Bakery', 'bread', NULL, 48.00, 'Bakeryyy/download (3).jpeg', 100, '400g', 0),
(11, 'Soft Sandwich Bread (400g)', 'Bakery', 'bread', NULL, 42.00, 'Bakeryyy/download (4).jpeg', 100, '400g', 0),
(12, 'Burger Buns (Pack of 4)', 'Bakery', 'bread', NULL, 35.00, 'Bakeryyy/download (5).jpeg', 100, 'Pack of 4', 0),
(13, 'Hot Dog Buns (Pack of 4)', 'Bakery', 'bread', NULL, 40.00, 'Bakeryyy/download (6).jpeg', 100, 'Pack of 4', 0),
(14, 'Garlic Bread Loaf (250g)', 'Bakery', 'bread', NULL, 60.00, 'Bakeryyy/download (7).jpeg', 100, '250g', 0),
(15, 'French Baguette (300g)', 'Bakery', 'bread', NULL, 55.00, 'Bakeryyy/download (8).jpeg', 100, '300g', 0),
(16, 'Italian Ciabatta Bread (250g)', 'Bakery', 'bread', NULL, 65.00, 'Bakeryyy/download (9).jpeg', 100, '250g', 0),
(17, 'Fruit Bread with Nuts (350g)', 'Bakery', 'bread', NULL, 70.00, 'Bakeryyy/download (10).jpeg', 100, '350g', 0),
(18, 'Honey Oat Bread (400g)', 'Bakery', 'bread', NULL, 58.00, 'Bakeryyy/download (11).jpeg', 100, '400g', 0),
(19, 'Chocolate Truffle Cake (500g)', 'Bakery', 'cake', NULL, 299.00, 'Bakeryyy/download (12).jpeg', 50, '500g', 0),
(20, 'Classic Vanilla Cake (500g)', 'Bakery', 'cake', NULL, 280.00, 'Bakeryyy/download (13).jpeg', 50, '500g', 0),
(21, 'Black Forest Cake (500g)', 'Bakery', 'cake', NULL, 320.00, 'Bakeryyy/download (14).jpeg', 50, '500g', 0),
(22, 'Red Velvet Cake (500g)', 'Bakery', 'cake', NULL, 350.00, 'Bakeryyy/download (15).jpeg', 50, '500g', 0),
(23, 'Butterscotch Delight Cake (500g)', 'Bakery', 'cake', NULL, 310.00, 'Bakeryyy/download (16).jpeg', 50, '500g', 0),
(24, 'Fresh Strawberry Cake (500g)', 'Bakery', 'cake', NULL, 295.00, 'Bakeryyy/download (17).jpeg', 50, '500g', 0),
(25, 'Pineapple Cream Cake (500g)', 'Bakery', 'cake', NULL, 275.00, 'Bakeryyy/download (18).jpeg', 50, '500g', 0),
(26, 'Rasmalai Fusion Cake (500g)', 'Bakery', 'cake', NULL, 370.00, 'Bakeryyy/download (19).jpeg', 50, '500g', 0),
(27, 'Italian Tiramisu Cake (500g)', 'Bakery', 'cake', NULL, 390.00, 'Bakeryyy/download (20).jpeg', 50, '500g', 0),
(28, 'Mixed Fruit Cake (500g)', 'Bakery', 'cake', NULL, 310.00, 'Bakeryyy/download (21).jpeg', 50, '500g', 0),
(29, 'Mocha Coffee Cake (500g)', 'Bakery', 'cake', NULL, 345.00, 'Bakeryyy/download (22).jpeg', 50, '500g', 0),
(30, 'New York Cheesecake (400g)', 'Bakery', 'cake', NULL, 380.00, 'Bakeryyy/download (23).jpeg', 50, '400g', 0),
(31, 'Butter Cookies (200g)', 'Bakery', 'cookies', NULL, 99.00, 'Bakeryyy/download (24).jpeg', 100, '200g', 0),
(32, 'Choco Chip Cookies (150g)', 'Bakery', 'cookies', NULL, 85.00, 'Bakeryyy/download (25).jpeg', 100, '150g', 0),
(33, 'Oatmeal Raisin Cookies (200g)', 'Bakery', 'cookies', NULL, 90.00, 'Bakeryyy/download (26).jpeg', 100, '200g', 0),
(34, 'Jeera Spiced Cookies (180g)', 'Bakery', 'cookies', NULL, 75.00, 'Bakeryyy/download (27).jpeg', 100, '180g', 0),
(35, 'Traditional Nankhatai (250g)', 'Bakery', 'cookies', NULL, 110.00, 'Bakeryyy/download (28).jpeg', 100, '250g', 0),
(36, 'Almond Crunch Cookies (200g)', 'Bakery', 'cookies', NULL, 120.00, 'Bakeryyy/download (29).jpeg', 100, '200g', 0),
(37, 'Crunchy Coconut Cookies (180g)', 'Bakery', 'cookies', NULL, 88.00, 'Bakeryyy/download (30).jpeg', 100, '180g', 0),
(38, 'Cashew Delight Cookies (200g)', 'Bakery', 'cookies', NULL, 105.00, 'Bakeryyy/download (31).jpeg', 100, '200g', 0),
(39, 'Wheat Atta Cookies (250g)', 'Bakery', 'cookies', NULL, 70.00, 'Bakeryyy/download (32).jpeg', 100, '250g', 0),
(40, 'Milky Cream Cookies (180g)', 'Bakery', 'cookies', NULL, 95.00, 'Bakeryyy/download (33).jpeg', 100, '180g', 0),
(41, 'Peanut Butter Cookies (200g)', 'Bakery', 'cookies', NULL, 92.00, 'Bakeryyy/download (34).jpeg', 100, '200g', 0),
(42, 'Digestive Cookies (250g)', 'Bakery', 'cookies', NULL, 78.00, 'Bakeryyy/download (35).jpeg', 100, '250g', 0),
(43, 'Veg Puff (Pack of 2)', 'Bakery', 'puffs', NULL, 60.00, 'Bakeryyy/download (36).jpeg', 100, 'Pack of 2', 0),
(44, 'Paneer Puff (Pack of 2)', 'Bakery', 'puffs', NULL, 70.00, 'Bakeryyy/download (37).jpeg', 100, 'Pack of 2', 0),
(45, 'Cheese Puff (Pack of 2)', 'Bakery', 'puffs', NULL, 75.00, 'Bakeryyy/download (38).jpeg', 100, 'Pack of 2', 0),
(46, 'Egg Puff (Pack of 2)', 'Bakery', 'puffs', NULL, 65.00, 'Bakeryyy/download (39).jpeg', 100, 'Pack of 2', 0),
(47, 'Chicken Puff (Pack of 2)', 'Bakery', 'puffs', NULL, 80.00, 'Bakeryyy/download (40).jpeg', 100, 'Pack of 2', 0),
(48, 'Spinach & Corn Puff (Pack of 2)', 'Bakery', 'puffs', NULL, 68.00, 'Bakeryyy/download (41).jpeg', 100, 'Pack of 2', 0),
(49, 'Mushroom Puff (Pack of 2)', 'Bakery', 'puffs', NULL, 70.00, 'Bakeryyy/download (42).jpeg', 100, 'Pack of 2', 0),
(50, 'Onion Puff (Pack of 2)', 'Bakery', 'puffs', NULL, 58.00, 'Bakeryyy/download (43).jpeg', 100, 'Pack of 2', 0),
(51, 'Mixed Veg Puff (Pack of 2)', 'Bakery', 'puffs', NULL, 62.00, 'Bakeryyy/download (44).jpeg', 100, 'Pack of 2', 0),
(52, 'Pizza Puff (Pack of 2)', 'Bakery', 'puffs', NULL, 85.00, 'Bakeryyy/images.jpeg', 100, 'Pack of 2', 0),
(53, 'Masala Puff (Pack of 2)', 'Bakery', 'puffs', NULL, 66.00, 'Bakeryyy/download (45).jpeg', 100, 'Pack of 2', 0),
(54, 'Samosa Puff Style (Pack of 2)', 'Bakery', 'puffs', NULL, 59.00, 'Bakeryyy/download (46).jpeg', 100, 'Pack of 2', 0),
(287, 'Real Mango Juice', 'Beverages', 'juice', NULL, 65.00, 'Image/CoolSips/download (47).jpeg', 0, '1L', 0),
(288, 'Tropicana Orange Juice', 'Beverages', 'juice', NULL, 70.00, 'Image/CoolSips/download (3).jpeg', 0, '1L', 0),
(289, 'Paper Boat Jamun Juice', 'Beverages', 'juice', NULL, 35.00, 'Image/CoolSips/download.jpeg', 0, '250ml', 0),
(290, 'B Natural Mixed Fruit Juice', 'Beverages', 'juice', NULL, 68.00, 'Image/CoolSips/download (1).jpeg', 0, '1L', 0),
(291, 'Dabur Amla Juice', 'Beverages', 'juice', NULL, 90.00, 'Image/CoolSips/download (2).jpeg', 0, '500ml', 0),
(292, 'Real Guava Juice', 'Beverages', 'juice', NULL, 62.00, 'Image/CoolSips/download (4).jpeg', 0, '1L', 0),
(293, 'Tropicana Pomegranate Juice', 'Beverages', 'juice', NULL, 80.00, 'Image/CoolSips/download (5).jpeg', 0, '1L', 0),
(294, 'Paper Boat Aam Panna', 'Beverages', 'juice', NULL, 30.00, 'Image/CoolSips/download (6).jpeg', 0, '250ml', 0),
(295, 'Real Litchi Juice', 'Beverages', 'juice', NULL, 66.00, 'Image/CoolSips/download (7).jpeg', 0, '1L', 0),
(296, 'B Natural Apple Juice', 'Beverages', 'juice', NULL, 65.00, 'Image/CoolSips/download (8).jpeg', 0, '1L', 0),
(297, 'Minute Maid Mango Juice', 'Beverages', 'juice', NULL, 60.00, 'Image/CoolSips/download (9).jpeg', 0, '1L', 0),
(298, 'Real Pineapple Juice', 'Beverages', 'juice', NULL, 67.00, 'Image/CoolSips/download (10).jpeg', 0, '1L', 0),
(299, 'Sprite Soft Drink', 'Beverages', 'soda', NULL, 40.00, 'Image/CoolSips/download (11).jpeg', 0, '750ml', 0),
(300, 'Coca Cola', 'Beverages', 'soda', NULL, 45.00, 'Image/CoolSips/download (12).jpeg', 0, '1L', 0),
(301, 'Pepsi Soft Drink', 'Beverages', 'soda', NULL, 40.00, 'Image/CoolSips/download (13).jpeg', 0, '750ml', 0),
(302, 'Thumbs Up', 'Beverages', 'soda', NULL, 38.00, 'Image/CoolSips/download (14).jpeg', 0, '600ml', 0),
(303, '7Up Lemon Drink', 'Beverages', 'soda', NULL, 40.00, 'Image/CoolSips/download (15).jpeg', 0, '750ml', 0),
(304, 'Mountain Dew', 'Beverages', 'soda', NULL, 42.00, 'Image/CoolSips/download (16).jpeg', 0, '750ml', 0),
(305, 'Fanta Orange Drink', 'Beverages', 'soda', NULL, 38.00, 'Image/CoolSips/download (17).jpeg', 0, '600ml', 0),
(306, 'Limca Lemon Drink', 'Beverages', 'soda', NULL, 39.00, 'Image/CoolSips/download (18).jpeg', 0, '750ml', 0),
(307, 'Appy Fizz', 'Beverages', 'soda', NULL, 30.00, 'Image/CoolSips/download (19).jpeg', 0, '500ml', 0),
(308, 'Duke Soda', 'Beverages', 'soda', NULL, 25.00, 'Image/CoolSips/download (20).jpeg', 0, '500ml', 0),
(309, 'Big Cola', 'Beverages', 'soda', NULL, 50.00, 'Image/CoolSips/download (21).jpeg', 0, '1.5L', 0),
(310, 'Bisleri Club Soda', 'Beverages', 'soda', NULL, 28.00, 'Image/CoolSips/download (22).jpeg', 0, '750ml', 0),
(311, 'Bisleri Water', 'Beverages', 'water', NULL, 20.00, 'Image/CoolSips/download (23).jpeg', 0, '1L', 0),
(312, 'Aquafina Packaged Water', 'Beverages', 'water', NULL, 18.00, 'Image/CoolSips/download (24).jpeg', 0, '1L', 0),
(313, 'Kinley Mineral Water', 'Beverages', 'water', NULL, 20.00, 'Image/CoolSips/download (25).jpeg', 0, '1L', 0),
(314, 'Himalayan Natural Mineral Water', 'Beverages', 'water', NULL, 60.00, 'Image/CoolSips/download (26).jpeg', 0, '1L', 0),
(315, 'Bailley Packaged Drinking Water', 'Beverages', 'water', NULL, 18.00, 'Image/CoolSips/download (27).jpeg', 0, '1L', 0),
(316, 'Rail Neer Drinking Water', 'Beverages', 'water', NULL, 15.00, 'Image/CoolSips/download (28).jpeg', 0, '1L', 0),
(317, 'Oxyrich Oxygenated Water', 'Beverages', 'water', NULL, 22.00, 'Image/CoolSips/download (29).jpeg', 0, '1L', 0),
(318, 'Kingfisher Mineral Water', 'Beverages', 'water', NULL, 20.00, 'Image/CoolSips/download (30).jpeg', 0, '1L', 0),
(319, 'Vedica Himalayan Water', 'Beverages', 'water', NULL, 55.00, 'Image/CoolSips/download (31).jpeg', 0, '1L', 0),
(320, 'Smartwater Vapor Distilled', 'Beverages', 'water', NULL, 60.00, 'Image/CoolSips/download (32).jpeg', 0, '750ml', 0),
(321, 'Qua Natural Mineral Water', 'Beverages', 'water', NULL, 50.00, 'Image/CoolSips/download (33).jpeg', 0, '1L', 0),
(322, 'Evian Natural Spring Water', 'Beverages', 'water', NULL, 150.00, 'Image/CoolSips/download (34).jpeg', 0, '1L', 0),
(323, 'Red Bull Energy Drink', 'Beverages', 'energy', NULL, 110.00, 'Image/CoolSips/download (35).jpeg', 0, '250ml', 0),
(324, 'Monster Energy Drink', 'Beverages', 'energy', NULL, 120.00, 'Image/CoolSips/download (36).jpeg', 0, '500ml', 0),
(325, 'Sting Energy Drink', 'Beverages', 'energy', NULL, 25.00, 'Image/CoolSips/download (37).jpeg', 0, '250ml', 0),
(326, 'Hell Classic Energy Drink', 'Beverages', 'energy', NULL, 99.00, 'Image/CoolSips/download (38).jpeg', 0, '250ml', 0),
(327, 'Cloud 9 Energy Drink', 'Beverages', 'energy', NULL, 60.00, 'Image/CoolSips/download (39).jpeg', 0, '250ml', 0),
(328, 'Tzinga Energy Drink Lemon Mint', 'Beverages', 'energy', NULL, 25.00, 'Image/CoolSips/download (40).jpeg', 0, '250ml', 0),
(329, 'Rebound Energy Drink', 'Beverages', 'energy', NULL, 40.00, 'Image/CoolSips/download (41).jpeg', 0, '250ml', 0),
(330, 'Panther Energy Drink', 'Beverages', 'energy', NULL, 35.00, 'Image/CoolSips/download (42).jpeg', 0, '250ml', 0),
(331, 'Gold Energy Drink', 'Beverages', 'energy', NULL, 45.00, 'Image/CoolSips/download (43).jpeg', 0, '300ml', 0),
(332, 'Black Horse Energy Drink', 'Beverages', 'energy', NULL, 50.00, 'Image/CoolSips/download (44).jpeg', 0, '250ml', 0),
(333, 'Go+ Energy Drink', 'Beverages', 'energy', NULL, 28.00, 'Image/CoolSips/download (45).jpeg', 0, '250ml', 0),
(334, 'Charged by Thums Up', 'Beverages', 'energy', NULL, 50.00, 'Image/CoolSips/download (46).jpeg', 0, '250ml', 0),
(335, 'Dove Hair Fall Rescue Shampoo', 'Cosmetics', 'shampoo', NULL, 135.00, 'Image/COSMETIC/download.jpeg', 0, '180ml', 0),
(336, 'Clinic Plus Strong & Long Shampoo', 'Cosmetics', 'shampoo', NULL, 110.00, 'Image/COSMETIC/download (1).jpeg', 0, '200ml', 0),
(337, 'Pantene Advanced Hairfall Solution', 'Cosmetics', 'shampoo', NULL, 140.00, 'Image/COSMETIC/download (2).jpeg', 0, '180ml', 0),
(338, 'Sunsilk Stunning Black Shine Shampoo', 'Cosmetics', 'shampoo', NULL, 130.00, 'Image/COSMETIC/download (3).jpeg', 0, '180ml', 0),
(339, 'Head & Shoulders Anti Dandruff Shampoo', 'Cosmetics', 'shampoo', NULL, 160.00, 'Image/COSMETIC/download (4).jpeg', 0, '170ml', 0),
(340, 'WOW Apple Cider Vinegar Shampoo', 'Cosmetics', 'shampoo', NULL, 299.00, 'Image/COSMETIC/download (5).jpeg', 0, '250ml', 0),
(341, 'Himalaya Gentle Protein Shampoo', 'Cosmetics', 'shampoo', NULL, 120.00, 'Image/COSMETIC/download (6).jpeg', 0, '200ml', 0),
(342, 'Indulekha Bringha Ayurvedic Shampoo', 'Cosmetics', 'shampoo', NULL, 230.00, 'Image/COSMETIC/download (7).jpeg', 0, '200ml', 0),
(343, 'TRESemmé Keratin Smooth Shampoo', 'Cosmetics', 'shampoo', NULL, 190.00, 'Image/COSMETIC/download (8).jpeg', 0, '190ml', 0),
(344, 'Biotique Green Apple Shampoo', 'Cosmetics', 'shampoo', NULL, 115.00, 'Image/COSMETIC/download (9).jpeg', 0, '200ml', 0),
(345, 'Khadi Natural Amla & Bhringraj Shampoo', 'Cosmetics', 'shampoo', NULL, 180.00, 'Image/COSMETIC/download (10).jpeg', 0, '210ml', 0),
(346, 'Mamaearth Onion Hair Fall Shampoo', 'Cosmetics', 'shampoo', NULL, 349.00, 'Image/COSMETIC/download (11).jpeg', 0, '250ml', 0),
(347, 'Dettol Original Germ Protection Soap', 'Cosmetics', 'soap', NULL, 99.00, 'Image/COSMETIC/download (12).jpeg', 0, 'Pack of 3', 0),
(348, 'Lux Velvet Touch Jasmine & Almond Oil', 'Cosmetics', 'soap', NULL, 96.00, 'Image/COSMETIC/download (13).jpeg', 0, '3 x 100g', 0),
(349, 'Santoor Sandal & Turmeric Soap', 'Cosmetics', 'soap', NULL, 130.00, 'Image/COSMETIC/download (14).jpeg', 0, 'Pack of 4', 0),
(350, 'Medimix Ayurvedic Classic 18 Herbs Soap', 'Cosmetics', 'soap', NULL, 135.00, 'Image/COSMETIC/download (15).jpeg', 0, '4 x 125g', 0),
(351, 'Cinthol Original Deodorant Soap', 'Cosmetics', 'soap', NULL, 128.00, 'Image/COSMETIC/download (16).jpeg', 0, 'Pack of 4', 0),
(352, 'Hamam Neem Tulsi & Aloe Vera Soap', 'Cosmetics', 'soap', NULL, 92.00, 'Image/COSMETIC/download (17).jpeg', 0, 'Pack of 3', 0),
(353, 'Vivel Aloe Vera & Vitamin E Soap', 'Cosmetics', 'soap', NULL, 125.00, 'Image/COSMETIC/download (18).jpeg', 0, '4 x 100g', 0),
(354, 'Fiama Gel Bar Celebration Pack', 'Cosmetics', 'soap', NULL, 245.00, 'Image/COSMETIC/download (19).jpeg', 0, '5 x 125g', 0),
(355, 'Mysore Sandal Classic Soap', 'Cosmetics', 'soap', NULL, 155.00, 'Image/COSMETIC/download (20).jpeg', 0, '3 x 125g', 0),
(356, 'Himalaya Neem & Turmeric Soap', 'Cosmetics', 'soap', NULL, 115.00, 'Image/COSMETIC/download (21).jpeg', 0, '4 x 75g', 0),
(357, 'Pears Pure & Gentle Glycerin Soap', 'Cosmetics', 'soap', NULL, 132.00, 'Image/COSMETIC/download (22).jpeg', 0, 'Pack of 3', 0),
(358, 'Biotique Basil & Parsley Revitalizing Soap', 'Cosmetics', 'soap', NULL, 75.00, 'Image/COSMETIC/download (23).jpeg', 0, '150g', 0),
(359, 'Colgate Active Salt Toothpaste', 'Cosmetics', 'toothpaste', NULL, 85.00, 'Image/COSMETIC/download (24).jpeg', 0, '150g', 0),
(360, 'Colgate MaxFresh Red Gel', 'Cosmetics', 'toothpaste', NULL, 99.00, 'Image/COSMETIC/download (25).jpeg', 0, '150g', 0),
(361, 'Pepsodent Germicheck Toothpaste', 'Cosmetics', 'toothpaste', NULL, 90.00, 'Image/COSMETIC/download (26).jpeg', 0, '150g', 0),
(362, 'Sensodyne Fresh Mint Toothpaste', 'Cosmetics', 'toothpaste', NULL, 125.00, 'Image/COSMETIC/download (2).png', 0, '100g', 0),
(363, 'Closeup Red Hot Gel Toothpaste', 'Cosmetics', 'toothpaste', NULL, 88.00, 'Image/COSMETIC/download (27).jpeg', 0, '150g', 0),
(364, 'Himalaya Complete Care Toothpaste', 'Cosmetics', 'toothpaste', NULL, 75.00, 'Image/COSMETIC/download (28).jpeg', 0, '150g', 0),
(365, 'Dabur Red Ayurvedic Toothpaste', 'Cosmetics', 'toothpaste', NULL, 99.00, 'Image/COSMETIC/download (29).jpeg', 0, '200g', 0),
(366, 'Vicco Vajradanti Ayurvedic Paste', 'Cosmetics', 'toothpaste', NULL, 70.00, 'Image/COSMETIC/download (30).jpeg', 0, '100g', 0),
(367, 'Babool Herbal Toothpaste', 'Cosmetics', 'toothpaste', NULL, 60.00, 'Image/COSMETIC/download (31).jpeg', 0, '175g', 0),
(368, 'Lever Ayush Natural Clove Toothpaste', 'Cosmetics', 'toothpaste', NULL, 79.00, 'Image/COSMETIC/download (32).jpeg', 0, '150g', 0),
(369, 'Meswak Herbal Toothpaste', 'Cosmetics', 'toothpaste', NULL, 92.00, 'Image/COSMETIC/download (33).jpeg', 0, '200g', 0),
(370, 'Patanjali Dant Kanti Toothpaste', 'Cosmetics', 'toothpaste', NULL, 85.00, 'Image/COSMETIC/download (3).png', 0, '200g', 0),
(371, 'Himalaya Purifying Neem Face Wash', 'Cosmetics', 'facewash', NULL, 110.00, 'Image/COSMETIC/download (34).jpeg', 0, '100ml', 0),
(372, 'Garnier Men OilClear Face Wash', 'Cosmetics', 'facewash', NULL, 135.00, 'Image/COSMETIC/download (35).jpeg', 0, '100g', 0),
(373, 'Clean & Clear Foaming Face Wash', 'Cosmetics', 'facewash', NULL, 120.00, 'Image/COSMETIC/download (36).jpeg', 0, '100ml', 0),
(374, 'Biotique Papaya Visibly Ageless Face Wash', 'Cosmetics', 'facewash', NULL, 130.00, 'Image/COSMETIC/download (37).jpeg', 0, '100ml', 0),
(375, 'VLCC Orange Oil Pimple Control Face Wash', 'Cosmetics', 'facewash', NULL, 115.00, 'Image/COSMETIC/download (38).jpeg', 0, '100ml', 0),
(376, 'Mamaearth Ubtan Face Wash with Turmeric & Saffron', 'Cosmetics', 'facewash', NULL, 249.00, 'Image/COSMETIC/download (39).jpeg', 0, '100ml', 0),
(377, 'WOW Apple Cider Vinegar Foaming Face Wash', 'Cosmetics', 'facewash', NULL, 299.00, 'Image/COSMETIC/download (40).jpeg', 0, '100ml', 0),
(378, 'Nivea Men Oil Control Face Wash', 'Cosmetics', 'facewash', NULL, 145.00, 'Image/COSMETIC/download (41).jpeg', 0, '100g', 0),
(379, 'Lakmé Blush & Glow Strawberry Gel Face Wash', 'Cosmetics', 'facewash', NULL, 140.00, 'Image/COSMETIC/download (42).jpeg', 0, '100g', 0),
(380, 'Joy Skin Fruits Oil Control Face Wash', 'Cosmetics', 'facewash', NULL, 90.00, 'Image/COSMETIC/download (43).jpeg', 0, '150ml', 0),
(381, 'Lever Ayush Natural Ayurvedic Face Wash', 'Cosmetics', 'facewash', NULL, 75.00, 'Image/COSMETIC/download (44).jpeg', 0, '80g', 0),
(382, 'Khadi Natural Neem & Tea Tree Face Wash', 'Cosmetics', 'facewash', NULL, 160.00, 'Image/COSMETIC/download (45).jpeg', 0, '210ml', 0),
(383, 'Fast USB Wall Charger', 'Electronics', 'charger', NULL, 399.00, 'Image/Electronics/download.jpeg', 0, '1 pc', 0),
(384, 'Quick Charge 3.0 Charger', 'Electronics', 'charger', NULL, 499.00, 'Image/Electronics/download (1).jpeg', 0, '1 pc', 0),
(385, 'Wireless Charging Pad', 'Electronics', 'charger', NULL, 799.00, 'Image/Electronics/download (2).jpeg', 0, '1 pc', 0),
(386, 'Car Charger with Dual USB', 'Electronics', 'charger', NULL, 299.00, 'Image/Electronics/download (3).jpeg', 0, '1 pc', 0),
(387, 'Multi-Port USB Charger', 'Electronics', 'charger', NULL, 699.00, 'Image/Electronics/download (4).jpeg', 0, '1 pc', 0),
(388, 'Fast Car Charger', 'Electronics', 'charger', NULL, 399.00, 'Image/Electronics/download (5).jpeg', 0, '1 pc', 0),
(389, 'Travel Charger Adapter', 'Electronics', 'charger', NULL, 4399.00, 'Image/Electronics/download (6).jpeg', 0, '1 pc', 0),
(390, 'Wired Earphones', 'Electronics', 'earphones', NULL, 299.00, 'Image/Electronics/77.png', 0, '1 pc', 0),
(391, 'Bluetooth Earphones', 'Electronics', 'earphones', NULL, 799.00, 'Image/Electronics/download (7).jpeg', 0, '1 pc', 0),
(392, 'Noise-Cancelling Earphones', 'Electronics', 'earphones', NULL, 1499.00, 'Image/Electronics/99.png', 0, '1 pc', 0),
(393, 'Gaming Headset', 'Electronics', 'earphones', NULL, 2499.00, 'Image/Electronics/download (8).jpeg', 0, '1 pc', 0),
(394, 'Sports Earphones', 'Electronics', 'earphones', NULL, 899.00, 'Image/Electronics/download (9).jpeg', 0, '1 pc', 0),
(395, 'Over-Ear Headphones', 'Electronics', 'earphones', NULL, 1999.00, 'Image/Electronics/download (10).jpeg', 0, '1 pc', 0),
(396, 'In-Ear Headphones', 'Electronics', 'earphones', NULL, 599.00, 'Image/Electronics/download (11).jpeg', 0, '1 pc', 0),
(397, 'Type-C Charging Cable', 'Electronics', 'cable', NULL, 199.00, 'Image/Electronics/download (12).jpeg', 0, '1 pc', 0),
(398, 'HDMI Cable', 'Electronics', 'cable', NULL, 299.00, 'Image/Electronics/download (13).jpeg', 0, '1 pc', 0),
(399, 'Lightning Cable', 'Electronics', 'cable', NULL, 249.00, 'Image/Electronics/download (14).jpeg', 0, '1 pc', 0),
(400, 'Micro USB Cable', 'Electronics', 'cable', NULL, 149.00, 'Image/Electronics/download (15).jpeg', 0, '1 pc', 0),
(401, 'USB Extension Cable', 'Electronics', 'cable', NULL, 199.00, 'Image/Electronics/download (16).jpeg', 0, '1 pc', 0),
(402, '3.5mm Audio Cable', 'Electronics', 'cable', NULL, 99.00, 'Image/Electronics/download (17).jpeg', 0, '1 pc', 0),
(403, 'DisplayPort Cable', 'Electronics', 'cable', NULL, 399.00, 'Image/Electronics/download (18).jpeg', 0, '1 pc', 0),
(404, '10,000mAh Power Bank', 'Electronics', 'powerbank', NULL, 899.00, 'Image/Electronics/download (19).jpeg', 0, '1 pc', 0),
(405, '20,000mAh Power Bank', 'Electronics', 'powerbank', NULL, 1299.00, 'Image/Electronics/download (20).jpeg', 0, '1 pc', 0),
(406, 'Solar Power Bank', 'Electronics', 'powerbank', NULL, 1599.00, 'Image/Electronics/download (21).jpeg', 0, '1 pc', 0),
(407, 'Wireless Power Bank', 'Electronics', 'powerbank', NULL, 1799.00, 'Image/Electronics/download (22).jpeg', 0, '1 pc', 0),
(408, 'Compact Power Bank', 'Electronics', 'powerbank', NULL, 999.00, 'Image/Electronics/download (23).jpeg', 0, '1 pc', 0),
(409, 'Portable Power Bank', 'Electronics', 'powerbank', NULL, 1299.00, 'Image/Electronics/download (24).jpeg', 1, '1 pc', 0),
(410, 'Ultra-Slim Power Bank', 'Electronics', 'powerbank', NULL, 1499.00, 'Image/Electronics/download (25).jpeg', 0, '1 pc', 0),
(411, 'Fresh Apples', 'Grocery', 'Fruits', NULL, 120.00, 'Image/PICTURE/fresh-apples.jpg', 100, '1kg', 0),
(412, 'Bananas (1 dozen)', 'Grocery', 'Fruits', NULL, 50.00, 'Image/PICTURE/Bananas.jpg', 100, '1 dozen', 0),
(413, 'Juicy Oranges', 'Grocery', 'Fruits', NULL, 90.00, 'Image/PICTURE/Juicy Oranges.jpg', 100, '1kg', 0),
(414, 'Green Kiwi (Pack of 4)', 'Grocery', 'Fruits', NULL, 130.00, 'Image/PICTURE/Green Kiwi.jpg', 100, 'Pack of 4', 0),
(415, 'Alphonso Mangoes (1kg)', 'Grocery', 'Fruits', NULL, 150.00, 'Image/PICTURE/Alphonso Mangoes.jpg', 100, '1kg', 0),
(416, 'Black Grapes (500g)', 'Grocery', 'Fruits', NULL, 75.00, 'Image/PICTURE/Black Grapes.jpg', 100, '500g', 0),
(417, 'Whole Pineapple', 'Grocery', 'Fruits', NULL, 60.00, 'Image/PICTURE/Whole Pineapple.jpg', 100, '1 pc', 0),
(418, 'Pomegranate (1kg)', 'Grocery', 'Fruits', NULL, 140.00, 'Image/PICTURE/Pomegranate.jpg', 100, '1kg', 0),
(419, 'Fresh Papaya', 'Grocery', 'Fruits', NULL, 55.00, 'Image/PICTURE/Fresh Papaya.jpg', 100, '1 pc', 0),
(420, 'Green Pears (1kg)', 'Grocery', 'Fruits', NULL, 100.00, 'Image/PICTURE/Green Pears.jpg', 100, '1kg', 0),
(421, 'Fresh Blueberries (125g)', 'Grocery', 'Fruits', NULL, 180.00, 'Image/PICTURE/Fresh Blueberries.jpg', 100, '125g', 0),
(422, 'Guava (1kg)', 'Grocery', 'Fruits', NULL, 60.00, 'Image/PICTURE/Guava.jpg', 100, '1kg', 0),
(423, 'Tomatoes', 'Grocery', 'Vegetables', NULL, 30.00, 'Image/Vegetables/pexels-rauf-allahverdiyev-561368-1367243.jpg', 100, '1kg', 0),
(424, 'Potatoes', 'Grocery', 'Vegetables', NULL, 25.00, 'Image/Vegetables/pexels-victorino-2286776.jpg', 100, '1kg', 0),
(425, 'Red Onions', 'Grocery', 'Vegetables', NULL, 28.00, 'Image/Vegetables/istockphoto-514833906-612x612.jpg', 100, '1kg', 0),
(426, 'Carrots', 'Grocery', 'Vegetables', NULL, 40.00, 'Image/Vegetables/pexels-mali-143133.jpg', 100, '1kg', 0),
(427, 'Green Cabbage', 'Grocery', 'Vegetables', NULL, 25.00, 'Image/Vegetables/pexels-quang-nguyen-vinh-222549-2518893.jpg', 100, '1 pc', 0),
(428, 'Cauliflower', 'Grocery', 'Vegetables', NULL, 30.00, 'Image/Vegetables/istockphoto-182240577-612x612.jpg', 100, '1 pc', 0),
(429, 'Green Capsicum', 'Grocery', 'Vegetables', NULL, 45.00, 'Image/Vegetables/pexels-nc-farm-bureau-mark-2893635.jpg', 100, '1kg', 0),
(430, 'French Beans', 'Grocery', 'Vegetables', NULL, 60.00, 'Image/Vegetables/istockphoto-182035936-612x612.jpg', 100, '1kg', 0),
(431, 'Fresh Spinach', 'Grocery', 'Vegetables', NULL, 20.00, 'Image/Vegetables/istockphoto-1159979911-612x612.jpg', 100, 'bundle', 0),
(432, 'Beetroot', 'Grocery', 'Vegetables', NULL, 35.00, 'Image/Vegetables/istockphoto-493446908-612x612.jpg', 100, '1kg', 0),
(433, 'Brinjal (500g)', 'Grocery', 'Vegetables', NULL, 28.00, 'Image/Vegetables/istockphoto-173879887-612x612.jpg', 100, '500g', 0),
(434, 'Bitter Gourd (500g)', 'Grocery', 'Vegetables', NULL, 35.00, 'Image/Vegetables/pexels-ian-panelo-32813825.jpg', 100, '500g', 0),
(435, 'Full Cream Milk (1L)', 'Grocery', 'Dairy', NULL, 60.00, 'Image/Dairy/Full Cream Milk.jpg', 100, '1L', 0),
(436, 'Cheddar Cheese (200g)', 'Grocery', 'Dairy', NULL, 140.00, 'Image/Dairy/Cheddar Cheese.jpg', 100, '200g', 0),
(437, 'Amul Butter (500g)', 'Grocery', 'Dairy', NULL, 260.00, 'Image/Dairy/Amul Butter.jpg', 100, '500g', 0),
(438, 'Fresh Curd (500g)', 'Grocery', 'Dairy', NULL, 35.00, 'Image/Dairy/Fresh Curd.jpg', 100, '500g', 0),
(439, 'Paneer (250g)', 'Grocery', 'Dairy', NULL, 90.00, 'Image/Dairy/Paneer.jpg', 100, '250g', 0),
(440, 'Fresh Cream (200ml)', 'Grocery', 'Dairy', NULL, 55.00, 'Image/Dairy/Fresh Cream.jpg', 100, '200ml', 0),
(441, 'Chocolate Milk (200ml)', 'Grocery', 'Dairy', NULL, 25.00, 'Image/Dairy/Chocolate Milk.jpg', 100, '200ml', 0),
(442, 'Sweet Lassi (200ml)', 'Grocery', 'Dairy', NULL, 20.00, 'Image/Dairy/Lassi.jpg', 100, '200ml', 0),
(443, 'Pure Ghee (500ml)', 'Grocery', 'Dairy', NULL, 310.00, 'Image/Dairy/Pure Ghee.jpg', 100, '500ml', 0),
(444, 'Fruit Yogurt (Strawberry)', 'Grocery', 'Dairy', NULL, 30.00, 'Image/Dairy/Fruit Yogurt (Strawberry).jpg', 100, '', 0),
(445, 'Amul Fresh Cream (250ml)', 'Grocery', 'Dairy', NULL, 70.00, 'Image/Dairy/Amul Fresh Cream.jpg', 100, '250ml', 0),
(446, 'Amul Butter (100g)', 'Grocery', 'Dairy', NULL, 55.00, 'Image/Dairy/Amul Butter (2).jpg', 100, '100g', 0),
(447, 'White Bread (400g)', 'Grocery', 'Bakery', NULL, 40.00, 'Image/Bakery/istockphoto-1289368014-612x612.jpg', 100, '400g', 0),
(448, 'Brown Bread (400g)', 'Grocery', 'Bakery', NULL, 45.00, 'Image/Bakery/istockphoto-1420937092-612x612.jpg', 100, '400g', 0),
(449, 'Burger Buns (Pack of 4)', 'Grocery', 'Bakery', NULL, 30.00, 'Image/Bakery/pexels-alleksana-7497216.jpg', 100, 'Pack of 4', 0),
(450, 'Ladi Pav (Pack of 6)', 'Grocery', 'Bakery', NULL, 28.00, 'Image/Bakery/download.jpeg', 100, 'Pack of 6', 0),
(451, 'Butter Croissant', 'Grocery', 'Bakery', NULL, 50.00, 'Image/Bakery/pexels-elkady-3892469.jpg', 100, '', 0),
(452, 'Chocolate Muffin', 'Grocery', 'Bakery', NULL, 35.00, 'Image/Bakery/pexels-castorlystock-3650438.jpg', 100, '', 0),
(453, 'Glazed Donut', 'Grocery', 'Bakery', NULL, 40.00, 'Image/Bakery/download (1).jpeg', 100, '', 0),
(454, 'Vanilla Cake Slice', 'Grocery', 'Bakery', NULL, 45.00, 'Image/Bakery/pexels-foodphotography-15399403.jpg', 100, '', 0),
(455, 'Swiss Roll (Chocolate)', 'Grocery', 'Bakery', NULL, 60.00, 'Image/Bakery/pexels-istvanpszabo-10338467.jpg', 100, '', 0),
(456, 'Black Forest Pastry', 'Grocery', 'Bakery', NULL, 50.00, 'Image/Bakery/istockphoto-495192223-612x612.jpg', 100, '', 0),
(457, 'Bread Rolls (Pack of 6)', 'Grocery', 'Bakery', NULL, 35.00, 'Image/Bakery/pexels-flat-hito-294826-863014.jpg', 100, 'Pack of 6', 0),
(458, 'Eggless Marble Cake (300g)', 'Grocery', 'Bakery', NULL, 75.00, 'Image/Bakery/istockphoto-524451933-612x612.jpg', 100, '300g', 0),
(459, 'Basmati Rice (5kg)', 'Grocery', 'Grains', NULL, 400.00, 'Image/Grains/pexels-mart-production-8108170.jpg', 100, '5kg', 0),
(460, 'Whole Wheat Flour (5kg)', 'Grocery', 'Grains', NULL, 210.00, 'Image/Grains/istockphoto-172876049-612x612.jpg', 100, '5kg', 0),
(461, 'Moong Dal (1kg)', 'Grocery', 'Grains', NULL, 90.00, 'Image/Grains/pexels-sonika-agarwal-1264788-7334141.jpg', 100, '1kg', 0),
(462, 'Masoor Dal (1kg)', 'Grocery', 'Grains', NULL, 85.00, 'Image/Grains/download.jpeg', 100, '1kg', 0),
(463, 'Chana Dal (1kg)', 'Grocery', 'Grains', NULL, 95.00, 'Image/Grains/istockphoto-2160775869-612x612.jpg', 100, '1kg', 0),
(464, 'Rajma (Kidney Beans) (1kg)', 'Grocery', 'Grains', NULL, 110.00, 'Image/Grains/istockphoto-1310281043-612x612.jpg', 100, '1kg', 0),
(465, 'White Chickpeas (1kg)', 'Grocery', 'Grains', NULL, 100.00, 'Image/Grains/plateful-of-organic-white-chickpeas-and-spilled-white-chickpeas-on-wooden-background.jpg', 100, '1kg', 0),
(466, 'Flattened Rice (Poha) 500g', 'Grocery', 'Grains', NULL, 45.00, 'Image/Grains/istockphoto-1291430555-612x612.jpg', 100, '500g', 0),
(467, 'Semolina (Rava/Sooji) 1kg', 'Grocery', 'Grains', NULL, 50.00, 'Image/Grains/download (1).jpeg', 100, '1kg', 0),
(468, 'Corn Flour (500g)', 'Grocery', 'Grains', NULL, 40.00, 'Image/Grains/istockphoto-947623144-612x612.jpg', 100, '500g', 0),
(469, 'Bajra Flour (1kg)', 'Grocery', 'Grains', NULL, 48.00, 'Image/Grains/istockphoto-974890186-612x612.jpg', 100, '1kg', 0),
(470, 'Ragi Flour (1kg)', 'Grocery', 'Grains', NULL, 52.00, 'Image/Grains/istockphoto-985919390-612x612.jpg', 100, '1kg', 0),
(471, 'Surf Excel Detergent (1kg)', 'Grocery', 'Household', NULL, 210.00, 'Image/Household/download.jpeg', 100, '1kg', 0),
(472, 'Vim Liquid (500ml)', 'Grocery', 'Household', NULL, 65.00, 'Image/Household/download (1).jpeg', 100, '500ml', 0),
(473, 'Lizol Floor Cleaner (1L)', 'Grocery', 'Household', NULL, 110.00, 'Image/Household/download (2).jpeg', 100, '1L', 0),
(474, 'Harpic Toilet Cleaner (500ml)', 'Grocery', 'Household', NULL, 85.00, 'Image/Household/download (3).jpeg', 100, '500ml', 0),
(475, 'Hand Sanitizer (100ml)', 'Grocery', 'Household', NULL, 40.00, 'Image/Household/download (4).jpeg', 100, '100ml', 0),
(476, 'Spin Mop Set', 'Grocery', 'Household', NULL, 599.00, 'Image/Household/download (5).jpeg', 100, '', 0),
(477, 'Garbage Bags (30 pcs)', 'Grocery', 'Household', NULL, 85.00, 'Image/Household/download (6).jpeg', 100, '30 pcs', 0),
(478, 'Matchboxes (Pack of 10)', 'Grocery', 'Household', NULL, 20.00, 'Image/Household/images.jpeg', 100, 'Pack of 10', 0),
(479, 'Lifebuoy Soap (125g)', 'Grocery', 'Household', NULL, 35.00, 'Image/Household/download (7).jpeg', 100, '125g', 0),
(480, 'Colgate Paste (150g)', 'Grocery', 'Household', NULL, 80.00, 'Image/Household/download (8).jpeg', 100, '150g', 0),
(481, 'Floor Wiper', 'Grocery', 'Household', NULL, 120.00, 'Image/Household/download (2).png', 100, '', 0),
(482, 'Facial Tissues (100 pcs)', 'Grocery', 'Household', NULL, 55.00, 'Image/Household/download (9).jpeg', 100, '100 pcs', 0),
(483, 'Lay\'s Chips (90g)', 'Grocery', 'Snacks', NULL, 30.00, 'Image/Snacks/pexels-cmrcn-30358849.jpg', 100, '90g', 0),
(484, 'Parle-G Biscuits (250g)', 'Grocery', 'Snacks', NULL, 25.00, 'Image/Snacks/pexels-tejasvi-maheshwari-1144700-4168645.jpg', 100, '250g', 0),
(485, 'Aloo Bhujia (200g)', 'Grocery', 'Snacks', NULL, 40.00, 'Image/Snacks/download.jpeg', 100, '200g', 0),
(486, '5 Star Bar (40g)', 'Grocery', 'Snacks', NULL, 20.00, 'Image/Snacks/download (1).jpeg', 100, '40g', 0),
(487, 'Kurkure Masala (90g)', 'Grocery', 'Snacks', NULL, 20.00, 'Image/Snacks/download (2).jpeg', 100, '90g', 0),
(488, 'Ready Popcorn (100g)', 'Grocery', 'Snacks', NULL, 30.00, 'Image/Snacks/download (3).jpeg', 100, '100g', 0),
(489, 'Chocolate Cookies (Pack)', 'Grocery', 'Snacks', NULL, 60.00, 'Image/Snacks/download (4).jpeg', 100, 'Pack', 0),
(490, 'Mini Samosa (Pack of 4)', 'Grocery', 'Snacks', NULL, 35.00, 'Image/Snacks/download (5).jpeg', 100, 'Pack of 4', 0),
(491, 'Energy Bar (50g)', 'Grocery', 'Snacks', NULL, 45.00, 'Image/Snacks/download (6).jpeg', 100, '50g', 0),
(492, 'South Indian Murukku', 'Grocery', 'Snacks', NULL, 55.00, 'Image/Snacks/pexels-thrissurkaranphotography-12865863.jpg', 100, '', 0),
(493, 'Instant Bhel Mix (200g)', 'Grocery', 'Snacks', NULL, 40.00, 'Image/Snacks/download (7).jpeg', 100, '200g', 0),
(494, 'Peanut Chikki (100g)', 'Grocery', 'Snacks', NULL, 30.00, 'Image/Snacks/download (8).jpeg', 100, '100g', 0),
(495, 'Coca-Cola (1L)', 'Grocery', 'Beverages', NULL, 45.00, 'Image/Beverages/pexels-karolina-grabowska-4389659.jpg', 100, '1L', 0),
(496, 'Pepsi (750ml)', 'Grocery', 'Beverages', NULL, 40.00, 'Image/Beverages/pexels-vladimir-11659356.jpg', 100, '750ml', 0),
(497, 'Sprite (500ml)', 'Grocery', 'Beverages', NULL, 35.00, 'Image/Beverages/pexels-slytonic-31332092.jpg', 100, '500ml', 0),
(498, 'Tata Tea (250g)', 'Grocery', 'Beverages', NULL, 95.00, 'Image/Beverages/download.jpeg', 100, '250g', 0),
(499, 'Bru Instant Coffee (100g)', 'Grocery', 'Beverages', NULL, 140.00, 'Image/Beverages/download (1).jpeg', 100, '100g', 0),
(500, 'Red Bull (250ml)', 'Grocery', 'Beverages', NULL, 110.00, 'Image/Beverages/pexels-sanket-sawale-62949595-17423270.jpg', 100, '250ml', 0),
(501, 'Lemonade Juice', 'Grocery', 'Beverages', NULL, 35.00, 'Image/Beverages/pexels-charlotte-may-5947071.jpg', 100, '', 0),
(502, 'Bisleri Water (1L)', 'Grocery', 'Beverages', NULL, 20.00, 'Image/Beverages/download (2).jpeg', 100, '1L', 0),
(503, 'Mixed Fruit Juice (1L)', 'Grocery', 'Beverages', NULL, 90.00, 'Image/Beverages/download (3).jpeg', 100, '1L', 0),
(504, 'Tender Coconut Water', 'Grocery', 'Beverages', NULL, 45.00, 'Image/Beverages/images.jpeg', 100, '', 0),
(505, 'Green Tea Bags (25 pcs)', 'Grocery', 'Beverages', NULL, 90.00, 'Image/Beverages/download (4).jpeg', 100, '25 pcs', 0),
(506, 'Soy Milk (1L)', 'Grocery', 'Beverages', NULL, 70.00, 'Image/Beverages/download (5).jpeg', 100, '1L', 0),
(507, 'Frozen Green Peas (500g)', 'Grocery', 'Frozen', NULL, 65.00, 'Image/Frozen/download.jpeg', 100, '500g', 0),
(508, 'French Fries (750g)', 'Grocery', 'Frozen', NULL, 120.00, 'Image/Frozen/istockphoto-1218213212-612x612.jpg', 100, '750g', 0),
(509, 'Vanilla Ice Cream (500ml)', 'Grocery', 'Frozen', NULL, 90.00, 'Image/Frozen/download (1).jpeg', 100, '500ml', 0),
(510, 'Stuffed Paratha (4 pcs)', 'Grocery', 'Frozen', NULL, 85.00, 'Image/Frozen/pexels-dhiraj-jain-207743066-12737919.jpg', 100, '4 pcs', 0),
(511, 'Frozen Paneer (200g)', 'Grocery', 'Frozen', NULL, 95.00, 'Image/Frozen/download (9).jpeg', 100, '200g', 0),
(512, 'Frozen Sweet Corn (500g)', 'Grocery', 'Frozen', NULL, 60.00, 'Image/Frozen/download (3).jpeg', 100, '500g', 0),
(513, 'Frozen Momos (10 pcs)', 'Grocery', 'Frozen', NULL, 110.00, 'Image/Frozen/download (4).jpeg', 100, '10 pcs', 0),
(514, 'Veg Cutlets (6 pcs)', 'Grocery', 'Frozen', NULL, 95.00, 'Image/Frozen/download (5).jpeg', 100, '6 pcs', 0),
(515, 'Frozen Chapatis (Pack of 10)', 'Grocery', 'Frozen', NULL, 85.00, 'Image/Frozen/download (6).jpeg', 100, 'Pack of 10', 0),
(516, 'Veg Nuggets (500g)', 'Grocery', 'Frozen', NULL, 130.00, 'Image/Frozen/download (7).jpeg', 100, '500g', 0),
(517, 'Frozen Idli (6 pcs)', 'Grocery', 'Frozen', NULL, 65.00, 'Image/Frozen/download (8).jpeg', 100, '6 pcs', 0),
(518, 'Frozen Paneer Roll (2 pcs)', 'Grocery', 'Frozen', NULL, 85.00, 'Image/Frozen/images.jpeg', 100, '2 pcs', 0),
(519, 'Pedigree Adult Dog Food (3kg)', 'Pet Care', 'food', NULL, 699.00, 'Image/PET/download.jpeg', 0, '3kg', 0),
(520, 'Drools Chicken & Egg Dog Food (3kg)', 'Pet Care', 'food', NULL, 620.00, 'Image/PET/download (1).jpeg', 0, '3kg', 0),
(521, 'Whiskas Adult Cat Food (1.2kg)', 'Pet Care', 'food', NULL, 349.00, 'Image/PET/download (2).jpeg', 0, '1.2kg', 0),
(522, 'Royal Canin Labrador Puppy (3kg)', 'Pet Care', 'food', NULL, 2300.00, 'Image/PET/download (3).jpeg', 0, '3kg', 0),
(523, 'Purina Supercoat Adult Dog Food (3kg)', 'Pet Care', 'food', NULL, 850.00, 'Image/PET/download (4).jpeg', 0, '3kg', 0),
(524, 'Me-O Seafood Cat Food (1.2kg)', 'Pet Care', 'food', NULL, 299.00, 'Image/PET/download (5).jpeg', 0, '1.2kg', 0),
(525, 'Pedigree Chicken Chunks (Wet Food, 70g)', 'Pet Care', 'food', NULL, 45.00, 'Image/PET/download (6).jpeg', 0, '70g', 0),
(526, 'Kennel Kitchen Chicken Wet Dog Food (70g)', 'Pet Care', 'food', NULL, 50.00, 'Image/PET/download (7).jpeg', 0, '70g', 0),
(527, 'Royal Canin Persian Cat Adult (2kg)', 'Pet Care', 'food', NULL, 1950.00, 'Image/PET/download (8).jpeg', 0, '2kg', 0),
(528, 'Drools Kitten Ocean Fish Cat Food (1.2kg)', 'Pet Care', 'food', NULL, 299.00, 'Image/PET/download (9).jpeg', 0, '1.2kg', 0),
(529, 'Meat Up Chicken Dog Food (3kg)', 'Pet Care', 'food', NULL, 599.00, 'Image/PET/download (10).jpeg', 0, '3kg', 0),
(530, 'SmartHeart Puppy Food (3kg)', 'Pet Care', 'food', NULL, 735.00, 'Image/PET/download (11).jpeg', 0, '3kg', 0),
(531, 'Gnawlers Calcium Milk Bone (30 pcs)', 'Pet Care', 'treats', NULL, 270.00, 'Image/PET/download (12).jpeg', 0, '30 pcs', 0),
(532, 'Pedigree Rodeo Treats (Chicken, 123g)', 'Pet Care', 'treats', NULL, 120.00, 'Image/PET/download (13).jpeg', 0, '123g', 0),
(533, 'JerHigh Chicken Jerky (70g)', 'Pet Care', 'treats', NULL, 175.00, 'Image/PET/download (14).jpeg', 0, '70g', 0),
(534, 'Drools Power Bites Dog Treats (270g)', 'Pet Care', 'treats', NULL, 179.00, 'Image/PET/download (15).jpeg', 0, '270g', 0),
(535, 'Choostix Chicken Stix Dog Treat (450g)', 'Pet Care', 'treats', NULL, 220.00, 'Image/PET/download (16).jpeg', 0, '450g', 0),
(536, 'Temptations Cat Treats Seafood (85g)', 'Pet Care', 'treats', NULL, 155.00, 'Image/PET/download (17).jpeg', 0, '85g', 0),
(537, 'Purepet Dog Biscuit - Milk Flavour (500g)', 'Pet Care', 'treats', NULL, 130.00, 'Image/PET/download (18).jpeg', 0, '500g', 0),
(538, 'JerHigh Variety Treat Pack (200g)', 'Pet Care', 'treats', NULL, 299.00, 'Image/PET/download (19).jpeg', 0, '200g', 0),
(539, 'Goodies Energy Dog Treats - Mix Sticks (125g)', 'Pet Care', 'treats', NULL, 110.00, 'Image/PET/download (20).jpeg', 0, '125g', 0),
(540, 'Dogsee Crunch Apple Treats (70g)', 'Pet Care', 'treats', NULL, 175.00, 'Image/PET/download (21).jpeg', 0, '70g', 0),
(541, 'Fidele Dog Biscuits - Chicken (400g)', 'Pet Care', 'treats', NULL, 189.00, 'Image/PET/download (22).jpeg', 0, '400g', 0),
(542, 'Gnawlers Oat Bone Treat for Dogs (240g)', 'Pet Care', 'treats', NULL, 160.00, 'Image/PET/download (23).jpeg', 0, '240g', 0),
(543, 'Chew Rope Tug Toy for Dogs', 'Pet Care', 'toys', NULL, 199.00, 'Image/PET/download (24).jpeg', 0, '1 pc', 0),
(544, 'Catnip Rolling Ball Toy', 'Pet Care', 'toys', NULL, 120.00, 'Image/PET/download (25).jpeg', 0, '1 pc', 0),
(545, 'Squeaky Bone Toy (Rubber)', 'Pet Care', 'toys', NULL, 175.00, 'Image/PET/download (26).jpeg', 0, '1 pc', 0),
(546, 'Dog Flying Disc Frisbee (Soft)', 'Pet Care', 'toys', NULL, 149.00, 'Image/PET/download (27).jpeg', 0, '1 pc', 0),
(547, 'Cat Mouse Toy Plush (Pack of 2)', 'Pet Care', 'toys', NULL, 99.00, 'Image/PET/download (28).jpeg', 0, 'Pack of 2', 0),
(548, 'Rubber Spike Ball (Medium)', 'Pet Care', 'toys', NULL, 85.00, 'Image/PET/download (29).jpeg', 0, '1 pc', 0),
(549, 'Bone Shaped Chew Toy (Durable)', 'Pet Care', 'toys', NULL, 130.00, 'Image/PET/download (30).jpeg', 0, '1 pc', 0),
(550, 'LED Feather Teaser Cat Toy', 'Pet Care', 'toys', NULL, 175.00, 'Image/PET/download (31).jpeg', 0, '1 pc', 0),
(551, 'Cat Jingle Ball & Bell Set (6 pcs)', 'Pet Care', 'toys', NULL, 140.00, 'Image/PET/download (32).jpeg', 0, '6 pcs', 0),
(552, 'Dog Squeaky Duck Plush Toy', 'Pet Care', 'toys', NULL, 160.00, 'Image/PET/download (33).jpeg', 0, '1 pc', 0),
(553, 'Rope Chew Toys Combo (Pack of 3)', 'Pet Care', 'toys', NULL, 199.00, 'Image/PET/download (34).jpeg', 0, 'Pack of 3', 0),
(554, 'Interactive Cat Toy with Bell', 'Pet Care', 'toys', NULL, 89.00, 'Image/PET/download (35).jpeg', 0, '1 pc', 0),
(555, 'Himalaya Erina Dog Shampoo (200ml)', 'Pet Care', 'care', NULL, 165.00, 'Image/PET/download (36).jpeg', 0, '200ml', 0),
(556, 'Captain Zack Dog Shampoo (200ml)', 'Pet Care', 'care', NULL, 240.00, 'Image/PET/download (37).jpeg', 0, '200ml', 0),
(557, 'Wahl Odor Control Pet Shampoo (300ml)', 'Pet Care', 'care', NULL, 299.00, 'Image/PET/download (38).jpeg', 0, '300ml', 0),
(558, 'Petvit Paw & Nose Cream (50g)', 'Pet Care', 'care', NULL, 199.00, 'Image/PET/download (39).jpeg', 0, '50g', 0),
(559, 'Drools Skin + Coat Spray (120ml)', 'Pet Care', 'care', NULL, 180.00, 'Image/PET/download (40).jpeg', 0, '120ml', 0),
(560, 'Trixie Eye Care Wipes (15 pcs)', 'Pet Care', 'care', NULL, 135.00, 'Image/PET/download (41).jpeg', 0, '15 pcs', 0),
(561, 'Pet Toothbrush & Toothpaste Set', 'Pet Care', 'care', NULL, 220.00, 'Image/PET/download (42).jpeg', 0, '1 set', 0),
(562, 'Pet Deodorant Spray (100ml)', 'Pet Care', 'care', NULL, 180.00, 'Image/PET/download (43).jpeg', 0, '100ml', 0),
(563, 'Pet Nail Clipper with Safety Guard', 'Pet Care', 'care', NULL, 145.00, 'Image/PET/download (44).jpeg', 0, '1 pc', 0),
(564, 'Vivaldis No Tick Spray (100ml)', 'Pet Care', 'care', NULL, 190.00, 'Image/PET/download (45).jpeg', 0, '100ml', 0),
(565, 'Virbac Medicated Dog Shampoo (200ml)', 'Pet Care', 'care', NULL, 235.00, 'Image/PET/download (46).jpeg', 0, '200ml', 0),
(566, 'Pet Hair Comb with Anti-Slip Grip', 'Pet Care', 'care', NULL, 175.00, 'Image/PET/download (47).jpeg', 0, '1 pc', 0),
(567, 'Paracetamol 500mg', 'Pharmacy', 'medicines', NULL, 20.00, 'Image/PHARMACY/Medicines/1.jpg', 100, '10 tablets', 0),
(568, 'Crocin Advance', 'Pharmacy', 'medicines', NULL, 35.00, 'Image/PHARMACY/Medicines/2.jpeg', 100, '15 tablets', 0),
(569, 'Dolo 650', 'Pharmacy', 'medicines', NULL, 28.00, 'Image/PHARMACY/Medicines/3.jpeg', 100, '15 tablets', 0),
(570, 'Cetirizine 10mg', 'Pharmacy', 'medicines', NULL, 18.00, 'Image/PHARMACY/Medicines/4.jpeg', 100, '10 tablets', 0),
(571, 'Benadryl Cough Syrup', 'Pharmacy', 'medicines', NULL, 75.00, 'Image/PHARMACY/Medicines/5.jpeg', 100, '100ml', 0),
(572, 'Combiflam', 'Pharmacy', 'medicines', NULL, 42.00, 'Image/PHARMACY/Medicines/6.jpeg', 100, '20 tablets', 0),
(573, 'Digene Antacid Tabs', 'Pharmacy', 'medicines', NULL, 45.00, 'Image/PHARMACY/Medicines/7.jpeg', 100, '15s', 0),
(574, 'ORS Powder', 'Pharmacy', 'medicines', NULL, 30.00, 'Image/PHARMACY/Medicines/8.jpeg', 100, 'Pack of 5', 0),
(575, 'Volini Pain Relief Spray', 'Pharmacy', 'medicines', NULL, 110.00, 'Image/PHARMACY/Medicines/9.jpeg', 100, '60ml', 0),
(576, 'Iodex Balm', 'Pharmacy', 'medicines', NULL, 65.00, 'Image/PHARMACY/Medicines/10.jpeg', 100, '40g', 0),
(577, 'Betadine Antiseptic', 'Pharmacy', 'medicines', NULL, 80.00, 'Image/PHARMACY/Medicines/11.jpeg', 100, '100ml', 0),
(578, 'Calpol Suspension', 'Pharmacy', 'medicines', NULL, 55.00, 'Image/PHARMACY/Medicines/12.jpeg', 100, '60ml', 0),
(579, 'Dabur Chyawanprash', 'Pharmacy', 'wellness', NULL, 160.00, 'Image/PHARMACY/Wellness/1.jpeg', 100, '500g', 0),
(580, 'Himalaya Ashwagandha', 'Pharmacy', 'wellness', NULL, 210.00, 'Image/PHARMACY/Wellness/2.jpeg', 100, '60 caps', 0),
(581, 'Vitamin C (Zinc) Tablets', 'Pharmacy', 'wellness', NULL, 95.00, 'Image/PHARMACY/Wellness/3.jpeg', 100, '1 strip', 0),
(582, 'Horlicks Protein+', 'Pharmacy', 'wellness', NULL, 340.00, 'Image/PHARMACY/Wellness/4.jpeg', 100, '400g', 0),
(583, 'Organic Green Tea', 'Pharmacy', 'wellness', NULL, 130.00, 'Image/PHARMACY/Wellness/5.jpeg', 100, '25 bags', 0),
(584, 'Multivitamin Tablets', 'Pharmacy', 'wellness', NULL, 299.00, 'Image/PHARMACY/Wellness/6.jpeg', 100, '60 count', 0),
(585, 'Aloe Vera Juice', 'Pharmacy', 'wellness', NULL, 185.00, 'Image/PHARMACY/Wellness/7.jpeg', 100, '1L', 0),
(586, 'Shilajit Resin', 'Pharmacy', 'wellness', NULL, 450.00, 'Image/PHARMACY/Wellness/8.jpeg', 100, '15g', 0),
(587, 'Omega-3 Fish Oil', 'Pharmacy', 'wellness', NULL, 375.00, 'Image/PHARMACY/Wellness/9.jpeg', 100, '60 Softgels', 0),
(588, 'Patanjali Giloy Juice', 'Pharmacy', 'wellness', NULL, 95.00, 'Image/PHARMACY/Wellness/10.jpeg', 100, '500ml', 0),
(589, 'Organic Moringa Powder', 'Pharmacy', 'wellness', NULL, 160.00, 'Image/PHARMACY/Wellness/11.jpeg', 100, '200g', 0),
(590, 'Apple Cider Vinegar', 'Pharmacy', 'wellness', NULL, 210.00, 'Image/PHARMACY/Wellness/12.jpeg', 100, '500ml', 0),
(591, 'Johnson’s Baby Oil', 'Pharmacy', 'babycare', NULL, 85.00, 'Image/PHARMACY/Baby care/13.jpeg', 100, '100ml', 0),
(592, 'Pampers Diapers', 'Pharmacy', 'babycare', NULL, 350.00, 'Image/PHARMACY/Baby care/2.jpeg', 100, 'Pack of 24', 0),
(593, 'Baby Wipes', 'Pharmacy', 'babycare', NULL, 99.00, 'Image/PHARMACY/Baby care/3.jpeg', 100, '72 pcs', 0),
(594, 'Baby Shampoo', 'Pharmacy', 'babycare', NULL, 120.00, 'Image/PHARMACY/Baby care/4.jpeg', 100, '200ml', 0),
(595, 'Johnson\'s Baby Powder', 'Pharmacy', 'babycare', NULL, 110.00, 'Image/PHARMACY/Baby care/5.jpeg', 100, '200g', 0),
(596, 'Baby Lotion', 'Pharmacy', 'babycare', NULL, 95.00, 'Image/PHARMACY/Baby care/6.jpeg', 100, '100ml', 0),
(597, 'Baby Soap', 'Pharmacy', 'babycare', NULL, 45.00, 'Image/PHARMACY/Baby care/7.jpeg', 100, '75g', 0),
(598, 'Feeding Bottle', 'Pharmacy', 'babycare', NULL, 150.00, 'Image/PHARMACY/Baby care/8.jpeg', 100, '250ml', 0),
(599, 'Himalaya Baby Cream', 'Pharmacy', 'babycare', NULL, 99.00, 'Image/PHARMACY/Baby care/9.jpeg', 100, '100ml', 0),
(600, 'Soft Baby Toothbrush', 'Pharmacy', 'babycare', NULL, 55.00, 'Image/PHARMACY/Baby care/10.jpeg', 100, '1 pc', 0),
(601, 'Diaper Rash Cream', 'Pharmacy', 'babycare', NULL, 85.00, 'Image/PHARMACY/Baby care/11.jpeg', 100, '50g', 0),
(602, 'Baby Nail Clipper Set', 'Pharmacy', 'babycare', NULL, 70.00, 'Image/PHARMACY/Baby care/12.jpeg', 100, '1 set', 0),
(603, 'Digital Thermometer', 'Pharmacy', 'equipment', NULL, 180.00, 'Image/PHARMACY/Equipment/1.jpeg', 100, '1 pc', 0),
(604, 'Omron BP Monitor', 'Pharmacy', 'equipment', NULL, 1799.00, 'Image/PHARMACY/Equipment/2.jpeg', 100, '1 pc', 0),
(605, 'Pulse Oximeter', 'Pharmacy', 'equipment', NULL, 850.00, 'Image/PHARMACY/Equipment/3.jpeg', 100, '1 pc', 0),
(606, 'Steam Inhaler Vaporizer', 'Pharmacy', 'equipment', NULL, 299.00, 'Image/PHARMACY/Equipment/4.jpeg', 100, '1 pc', 0),
(607, 'Accu-Chek Glucometer Kit', 'Pharmacy', 'equipment', NULL, 1190.00, 'Image/PHARMACY/Equipment/5.jpeg', 100, '1 kit', 0),
(608, 'Omron Nebulizer (Compressor)', 'Pharmacy', 'equipment', NULL, 1899.00, 'Image/PHARMACY/Equipment/6.jpeg', 100, '1 pc', 0),
(609, 'Littmann Classic II Stethoscope', 'Pharmacy', 'equipment', NULL, 3100.00, 'Image/PHARMACY/Equipment/7.jpeg', 100, '1 pc', 0),
(610, 'Home First Aid Box (Fully Loaded)', 'Pharmacy', 'equipment', NULL, 350.00, 'Image/PHARMACY/Equipment/8.jpeg', 100, '1 box', 0),
(611, 'Reusable Ice Pack', 'Pharmacy', 'equipment', NULL, 99.00, 'Image/PHARMACY/Equipment/9.jpeg', 100, '1 pc', 0),
(612, 'Portable Oxygen Can', 'Pharmacy', 'equipment', NULL, 499.00, 'Image/PHARMACY/Equipment/10.jpeg', 100, '10L', 0),
(613, 'Rubber Hot Water Bag', 'Pharmacy', 'equipment', NULL, 140.00, 'Image/PHARMACY/Equipment/11.jpeg', 100, '1 pc', 0),
(614, 'Knee Support Belt', 'Pharmacy', 'equipment', NULL, 220.00, 'Image/PHARMACY/Equipment/12.jpeg', 100, '1 pc', 0),
(615, 'Lays Classic Salted', 'Snacks', 'chips', NULL, 20.00, 'Image/SnackZonee/download.jpeg', 100, '1 pack', 0),
(616, 'Kurkure Masala Munch', 'Snacks', 'chips', NULL, 10.00, 'Image/SnackZonee/download (1).jpeg', 100, '1 pack', 0),
(617, 'Uncle Chipps Spicy Treat', 'Snacks', 'chips', NULL, 15.00, 'Image/SnackZonee/download (2).jpeg', 100, '1 pack', 0),
(618, 'Bingo Mad Angles - Tomato Madness', 'Snacks', 'chips', NULL, 25.00, 'Image/SnackZonee/download (3).jpeg', 100, '1 pack', 0),
(619, 'Balaji Wafers - Masala Magic', 'Snacks', 'chips', NULL, 10.00, 'Image/SnackZonee/download (4).jpeg', 100, '1 pack', 0),
(620, 'Haldirams Classic Salted Chips', 'Snacks', 'chips', NULL, 20.00, 'Image/SnackZonee/download (5).jpeg', 100, '1 pack', 0),
(621, 'Lay’s Magic Masala', 'Snacks', 'chips', NULL, 20.00, 'Image/SnackZonee/download (6).jpeg', 100, '1 pack', 0),
(622, 'Peri Peri Spiced Potato Chips', 'Snacks', 'chips', NULL, 30.00, 'Image/SnackZonee/download (7).jpeg', 100, '1 pack', 0),
(623, 'Cheesy Nacho Bites', 'Snacks', 'chips', NULL, 35.00, 'Image/SnackZonee/NN.jpeg', 100, '1 pack', 0),
(624, 'Spicy Tapioca Chips', 'Snacks', 'chips', NULL, 28.00, 'Image/SnackZonee/download (9).jpeg', 100, '1 pack', 0),
(625, 'Crunchy Ragi Millet Chips', 'Snacks', 'chips', NULL, 32.00, 'Image/SnackZonee/download (10).jpeg', 100, '1 pack', 0),
(626, 'Healthy Multigrain Chips', 'Snacks', 'chips', NULL, 38.00, 'Image/SnackZonee/download (11).jpeg', 100, '1 pack', 0),
(627, 'Dairy Milk Silk', 'Snacks', 'chocolates', NULL, 70.00, 'Image/SnackZonee/download (12).jpeg', 100, '1 bar', 0),
(628, 'Perk Chocolate', 'Snacks', 'chocolates', NULL, 10.00, 'Image/SnackZonee/12.png', 100, '1 bar', 0),
(629, 'KitKat Mini', 'Snacks', 'chocolates', NULL, 25.00, 'Image/SnackZonee/download (13).jpeg', 100, '1 pack', 0),
(630, '5 Star Chocolate Bar', 'Snacks', 'chocolates', NULL, 20.00, 'Image/SnackZonee/download (14).jpeg', 100, '1 bar', 0),
(631, 'Nestlé Munch', 'Snacks', 'chocolates', NULL, 10.00, 'Image/SnackZonee/download (15).jpeg', 100, '1 bar', 0),
(632, 'Amul Dark Chocolate', 'Snacks', 'chocolates', NULL, 45.00, 'Image/SnackZonee/download (16).jpeg', 100, '1 bar', 0),
(633, 'Ferrero Rocher (3 pcs)', 'Snacks', 'chocolates', NULL, 140.00, 'Image/SnackZonee/download (17).jpeg', 100, '3 pcs', 0),
(634, 'Cadbury Bournville 50% Cocoa', 'Snacks', 'chocolates', NULL, 99.00, 'Image/SnackZonee/download (18).jpeg', 100, '1 bar', 0),
(635, 'Nestlé Milkybar', 'Snacks', 'chocolates', NULL, 15.00, 'Image/SnackZonee/download (19).jpeg', 100, '1 bar', 0),
(636, 'Toblerone Mini', 'Snacks', 'chocolates', NULL, 150.00, 'Image/SnackZonee/download (20).jpeg', 100, '1 pack', 0),
(637, 'Hershey’s Kisses (33g)', 'Snacks', 'chocolates', NULL, 50.00, 'Image/SnackZonee/download (21).jpeg', 100, '33g', 0),
(638, 'Galaxy Smooth Milk Chocolate', 'Snacks', 'chocolates', NULL, 60.00, 'Image/SnackZonee/download (22).jpeg', 100, '1 bar', 0),
(639, 'Oreo Vanilla', 'Snacks', 'biscuits', NULL, 30.00, 'Image/SnackZonee/download (23).jpeg', 100, '1 pack', 0),
(640, 'Parle-G Classic', 'Snacks', 'biscuits', NULL, 5.00, 'Image/SnackZonee/download (24).jpeg', 100, '1 pack', 0),
(641, 'Bourbon Cream', 'Snacks', 'biscuits', NULL, 20.00, 'Image/SnackZonee/download (25).jpeg', 100, '1 pack', 0),
(642, 'Parle-G Original Glucose', 'Snacks', 'biscuits', NULL, 10.00, 'Image/SnackZonee/download (26).jpeg', 100, '1 pack', 0),
(643, 'Hide & Seek Fab', 'Snacks', 'biscuits', NULL, 25.00, 'Image/SnackZonee/download (27).jpeg', 100, '1 pack', 0),
(644, 'Britannia Good Day Butter', 'Snacks', 'biscuits', NULL, 20.00, 'Image/SnackZonee/download (28).jpeg', 100, '1 pack', 0),
(645, 'Britannia Treat Strawberry', 'Snacks', 'biscuits', NULL, 25.00, 'Image/SnackZonee/download (29).jpeg', 100, '1 pack', 0),
(646, 'Jim Jam Cream Biscuits', 'Snacks', 'biscuits', NULL, 15.00, 'Image/SnackZonee/download (30).jpeg', 100, '1 pack', 0),
(647, 'Marie Gold Classic', 'Snacks', 'biscuits', NULL, 15.00, 'Image/SnackZonee/download (31).jpeg', 100, '1 pack', 0),
(648, 'Krackjack Sweet & Salty', 'Snacks', 'biscuits', NULL, 20.00, 'Image/SnackZonee/download (32).jpeg', 100, '1 pack', 0),
(649, 'Britannia Nice Time', 'Snacks', 'biscuits', NULL, 15.00, 'Image/SnackZonee/download (33).jpeg', 100, '1 pack', 0),
(650, 'Milk Bikis Classic', 'Snacks', 'biscuits', NULL, 10.00, 'Image/SnackZonee/download (34).jpeg', 100, '1 pack', 0),
(651, 'Pepsi 250ml', 'Snacks', 'drinks', NULL, 20.00, 'Image/SnackZonee/download (35).jpeg', 100, '250ml', 0),
(652, 'Frooti Mango', 'Snacks', 'drinks', NULL, 10.00, 'Image/SnackZonee/download (36).jpeg', 100, '1 pack', 0),
(653, 'Red Bull Energy', 'Snacks', 'drinks', NULL, 99.00, 'Image/SnackZonee/download (37).jpeg', 100, '250ml can', 0);
INSERT INTO `products` (`id`, `name`, `category`, `sub_category`, `description`, `price`, `image`, `stock`, `unit`, `stock_quantity`) VALUES
(654, 'Coca-Cola (300ml)', 'Snacks', 'drinks', NULL, 20.00, 'Image/SnackZonee/download (38).jpeg', 100, '300ml', 0),
(655, 'Sprite (300ml)', 'Snacks', 'drinks', NULL, 20.00, 'Image/SnackZonee/download (39).jpeg', 100, '300ml', 0),
(656, 'Fanta Orange (300ml)', 'Snacks', 'drinks', NULL, 20.00, 'Image/SnackZonee/download (40).jpeg', 100, '300ml', 0),
(657, 'Thums Up (250ml)', 'Snacks', 'drinks', NULL, 20.00, 'Image/SnackZonee/download (41).jpeg', 100, '250ml', 0),
(658, 'Maaza Mango Drink (600ml)', 'Snacks', 'drinks', NULL, 35.00, 'Image/SnackZonee/download (42).jpeg', 100, '600ml', 0),
(659, 'Appy Fizz (250ml)', 'Groceries & Staples', 'drinks', '', 25.00, 'Image/SnackZonee/download (43).jpeg', 100, '250ml', 45),
(660, 'Real Mixed Fruit Juice (1L)', 'Snacks', 'drinks', NULL, 110.00, 'Image/SnackZonee/download (44).jpeg', 100, '1L', 0),
(661, 'Red Bull Energy Drink (250ml)', 'Groceries & Staples', 'drinks', '', 120.00, 'Image/SnackZonee/download (45).jpeg', 100, '250ml can', 78),
(662, 'Bisleri Mineral Water (1L)', 'Snacks & Beverages', 'drinks', '', 25.00, 'Image/SnackZonee/download (46).jpeg', 100, '1L', 85),
(664, 'Bisleri Mineral Water (2L)', 'Snacks & Beverages', 'drinks', '', 45.00, 'prod_68b524e2daeb25.32544286.jpeg', 100, '2L', 45),
(665, 'white cake', 'Dairy & Bakery', 'bakery', '', 369.00, 'prod_68b5264bcb60d7.23627360.jpg', 100, '1 pack', 25),
(666, 'Cotton flannel shirt L', 'Fashion', 'men', '', 1299.00, 'prod_68bb369ec2e063.48785231.jpg', 50, '1 pis', 15),
(667, 'Men Black Solid Mid-Top Chelsea Boots', 'Fashion', 'FOOTWARE', '', 2985.00, 'prod_68bb3b763c0f91.55710890.webp', 20, '1 pair', 10),
(668, 'U.S. Polo Assn. Denim Co. Connor Bootcut Fit Black Jeans', 'Groceries & Staples', 'Jeans', '', 2300.00, 'prod_68bc7910ddc6a7.56344933.webp', 50, '1 pis', 10);

-- --------------------------------------------------------

--
-- Table structure for table `stores`
--

CREATE TABLE `stores` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `contact` varchar(100) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `operating_hours` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`operating_hours`)),
  `delivery_pincodes` text DEFAULT NULL COMMENT 'Comma-separated list of serviceable pincodes',
  `delivery_radius_km` decimal(5,2) DEFAULT NULL,
  `default_tax_rate` decimal(5,4) NOT NULL DEFAULT 0.0000,
  `payout_details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Store bank account or UPI info' CHECK (json_valid(`payout_details`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `mobile` varchar(20) NOT NULL,
  `address` text DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `profile_photo` varchar(255) DEFAULT NULL,
  `role` enum('user','admin','delivery') NOT NULL DEFAULT 'user',
  `membership_expiry_date` date DEFAULT NULL,
  `status` enum('active','blocked') NOT NULL DEFAULT 'active',
  `last_login_at` datetime DEFAULT NULL,
  `last_login_ip` varchar(45) DEFAULT NULL,
  `reset_token` varchar(64) DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `mobile`, `address`, `password`, `created_at`, `updated_at`, `profile_photo`, `role`, `membership_expiry_date`, `status`, `last_login_at`, `last_login_ip`, `reset_token`, `reset_expires`) VALUES
(1, 'Andrapiya Yash Jitendrabhai', 'Yashandrapiya1@gmail.com', '9723653140', NULL, '$2y$10$Je//.hup3Xp1f70rJw08Bu4PpepFUZ.DcwSTqtBWnFbJYuMYNM0KK', '2025-07-19 18:08:06', '2025-09-26 18:20:24', 'uploads/profile_photos/1_1756203604.jpg', 'admin', '2026-09-26', 'active', NULL, NULL, '540da39ff31f35c4cab4743134a246e866e171691b309c71ab13ca69c6e68085', '2025-07-21 11:56:22'),
(2, 'Ronak andrapiya', 'randrapiya@gmail.com', '9723653140', NULL, '$2y$10$K5r6kbxUfezHfSMhfNTAletxNYWs5KPeOh4Kn4bkN7qQ0G7.fAQSW', '2025-07-21 04:53:35', '2025-07-21 04:54:14', 'uploads/profile_photos/2_1753073654.jpg', 'user', NULL, 'active', NULL, NULL, NULL, NULL),
(3, 'ANDRAPIYA YASH JITENDRABHAI', 'yandrapiya546@rku.ac.in', '9723653140', NULL, '$2y$10$Xod6siGJQRxBjPtvUhPQRORtCiqpI6.QRuIqjPdSfnJKzr7NwDScu', '2025-07-21 05:39:10', '2025-09-05 18:36:25', 'uploads/profile_photos/3_1756203667.jpg', 'user', NULL, 'active', NULL, NULL, NULL, NULL),
(4, 'Manan Gajjar', 'mnngajjarashokbhai@gmail.com', '6353318455', NULL, '$2y$10$DwPQ4pAIt7c4ksoVo6WTYuYxBTY9SNbjpO/6Ok/0OL/0q6pkeFPt2', '2025-07-21 10:06:31', '2025-09-23 07:50:32', 'uploads/profile_photos/1753092391_manan.jpg', 'admin', '2027-08-08', 'active', NULL, NULL, NULL, NULL),
(5, 'parthiv gajjar', 'pgajjar@gmail.com', '6352092528', NULL, '$2y$10$eRArwPkJKy.fkpfEk1Jex.Cd0UwnrvG8MLyqdkDBmoeGuf6yxgNfC', '2025-07-23 09:34:12', '2025-09-01 19:16:42', NULL, 'user', NULL, 'active', NULL, NULL, NULL, NULL),
(6, 'dhaval', 'dhaval@gmail.com', '8980628004', NULL, '$2y$10$7M2yYfB/H9Pxll6DU.InveJmbwix2x2CCd75QMTw1A8hAosUyEVE6', '2025-08-05 08:17:40', '2025-09-03 07:06:09', NULL, 'user', NULL, 'blocked', NULL, NULL, NULL, NULL),
(7, 'bansi', 'bsolanki679@rku.ac.in', '1234567890', NULL, '$2y$10$xOvaUOvICYWuaONaNdL9UuO1weNH4JesFaHNK.yKFrt8ZFjShqmTi', '2025-08-08 05:55:54', '2025-08-08 06:05:43', 'uploads/profile_photos/7_1754633143.jpg', 'user', NULL, 'active', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `wishlist`
--

CREATE TABLE `wishlist` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `wishlist`
--

INSERT INTO `wishlist` (`id`, `user_id`, `product_id`, `created_at`) VALUES
(17, 1, 667, '2025-09-06 17:49:10'),
(24, 4, 35, '2025-09-08 04:50:23'),
(36, 1, 668, '2025-10-09 09:00:51'),
(40, 1, 666, '2025-10-09 09:17:00');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_product_unique` (`user_id`,`product_id`),
  ADD KEY `cart_ibfk_2` (`product_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `coupons`
--
ALTER TABLE `coupons`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_order_number` (`order_number`),
  ADD KEY `delivery_partner_id` (`delivery_partner_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `stores`
--
ALTER TABLE `stores`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`);

--
-- Indexes for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_product` (`user_id`,`product_id`),
  ADD KEY `wishlist_ibfk_2` (`product_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=215;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `coupons`
--
ALTER TABLE `coupons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=75;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=669;

--
-- AUTO_INCREMENT for table `stores`
--
ALTER TABLE `stores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_product_fk` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`delivery_partner_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD CONSTRAINT `wishlist_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `wishlist_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
