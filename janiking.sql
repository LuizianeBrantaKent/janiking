-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 25, 2025 at 07:20 AM
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
-- Database: `janiking`
--

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `announcement_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `author_id` int(11) NOT NULL,
  `recipient_type` enum('All','Staff','Franchisee') NOT NULL DEFAULT 'All',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`announcement_id`, `title`, `content`, `author_id`, `recipient_type`, `created_at`, `updated_at`) VALUES
(3, 'System Announcement', 'test to all users', 1, 'All', '2025-09-14 02:33:09', '2025-09-14 02:33:09'),
(4, 'System Announcement', 'test to STAFF', 1, 'All', '2025-09-14 02:37:28', '2025-09-14 02:37:28'),
(5, 'System Announcement', 'test to STAFF', 1, 'All', '2025-09-14 02:37:32', '2025-09-14 02:37:32'),
(6, 'Hello, test', 'This is a final test', 1, 'All', '2025-09-24 05:11:05', '2025-09-24 05:11:05'),
(7, 'Test admin', 'All admin', 1, '', '2025-09-24 05:11:15', '2025-09-24 05:11:15'),
(8, 'Test staff', 'All staff', 1, 'Staff', '2025-09-24 05:11:27', '2025-09-24 05:11:27'),
(9, 'Test franchisee', 'All franchisee', 1, 'Franchisee', '2025-09-24 05:11:44', '2025-09-24 05:11:44');

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `booking_id` int(11) NOT NULL,
  `franchisee_id` int(11) DEFAULT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(50) NOT NULL,
  `preferred_location` varchar(150) NOT NULL,
  `scheduled_date` datetime DEFAULT NULL,
  `status` enum('Pending','Confirmed','Completed','Cancelled') DEFAULT 'Pending',
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`booking_id`, `franchisee_id`, `first_name`, `last_name`, `email`, `phone`, `preferred_location`, `scheduled_date`, `status`, `notes`) VALUES
(6, NULL, 'Test', 'Vieira', 'thais@example.com', '123456789', 'Sydney', '2025-09-03 04:00:00', 'Pending', ''),
(7, NULL, 'Jen', 'Ganga', 'jen@example.com', '+61 123456789', 'Sydney', '2025-09-19 09:00:00', 'Pending', ''),
(9, NULL, 'teste', 'teste', 'thais@example.com', '+61 123456789', 'Sydney', '2025-09-02 09:00:00', 'Pending', ''),
(10, NULL, 'Jessica', 'de Poli', 'jessica_poli@yahoo.com.br', '0451531207', 'Lane Cove North', '2025-09-22 10:30:00', 'Pending', 'Hi there,\r\nI&#039;m very interested in become one one yours franchisees. I&#039;m looking forward to meeting you on Monday. \r\nThanks.'),
(11, NULL, 'Testting', 'Testing', 'test@test.com', '+61400123123', 'Sydney', '2025-09-15 04:00:00', 'Pending', 'Test'),
(12, 1, 'Test', 'Test', 'franchisee@test.com', '0400123123', 'Sydney', '2025-09-24 19:26:00', 'Pending', 'testing again'),
(13, 4, 'Test', 'Test', 'test@test.com', '0400123123', 'Sydney', '2025-09-26 16:55:00', 'Pending', ''),
(14, 4, 'Test', 'Test', 'test@test.com', '0400123123', 'Sydney', '2025-09-26 16:03:00', 'Pending', ''),
(15, 4, 'staff', '', 'staff@test.com', '', 'perth', '2025-09-25 03:09:00', 'Pending', ''),
(16, NULL, 'Testing', 'Report', 'test@report.com', '+61400123123', 'Sydney', '2025-09-25 09:00:00', 'Pending', '');

-- --------------------------------------------------------

--
-- Table structure for table `contact_inquiries`
--

CREATE TABLE `contact_inquiries` (
  `id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `interest` varchar(50) NOT NULL,
  `message` text NOT NULL,
  `consent` enum('Yes','No') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact_inquiries`
--

INSERT INTO `contact_inquiries` (`id`, `first_name`, `last_name`, `email`, `phone`, `interest`, `message`, `consent`, `created_at`) VALUES
(1, 'John', 'Doe', 'john@example.com', '+61 412 345 678', 'franchise', 'Test', 'Yes', '2025-09-04 05:54:26'),
(2, 'Testing', 'Test', 'testnow@test.com', '+61 400 123 123', 'services', 'Test', 'Yes', '2025-09-20 00:01:43'),
(3, 'Testing', 'Test', 'testnow@test.com', '+61 400 123 123', 'services', 'Test', 'Yes', '2025-09-20 00:06:57');

-- --------------------------------------------------------

--
-- Table structure for table `documents`
--

CREATE TABLE `documents` (
  `documents_id` int(11) NOT NULL,
  `franchisee_id` int(11) DEFAULT NULL,
  `title` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `file_path` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `documents`
--

INSERT INTO `documents` (`documents_id`, `franchisee_id`, `title`, `description`, `file_path`, `created_at`) VALUES
(1, 1, 'Contract test', 'Test', 'janiking.pdf', '2025-09-24 05:27:27');

-- --------------------------------------------------------

--
-- Table structure for table `franchisees`
--

CREATE TABLE `franchisees` (
  `franchisee_id` int(11) NOT NULL,
  `business_name` varchar(100) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `abn` varchar(20) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `point_of_contact` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `franchisees`
--

INSERT INTO `franchisees` (`franchisee_id`, `business_name`, `address`, `abn`, `start_date`, `status`, `point_of_contact`, `phone`, `email`, `password_hash`) VALUES
(1, 'Test Franchise', '123 Test St, Sydney', '12 345 678 901', '2025-08-01', 'Active', 'John Doe', '+61 420 123 456', 'franchisee@gmail.com', '$2y$10$6QJpV77034zofml7pfV1qu9lz7gAc31HX6gTv6s477Hm7K63kXFne'),
(2, 'Cleaning', 'aasaasasa', '123456', '2025-09-14', 'Active', 'Mary', '0400123123', 'mary@test.com', '$2y$10$eMF6ejHcmhKhpFX8KgT/2OZFxRSyPuHas/Yb6rZtx6zt3Lsoi8KIK'),
(3, 'test2', 'tstedsd', '123456', '2025-09-23', 'Active', 'test2', '123456', 'test22@test.com', '$2y$10$BB4AD0brqJvUIpjCRMI1ku/mx4T2H/eDXPrMwfeXtvhrr/O7TI4T.'),
(4, 'Testing inc', '123 testing', '12345678912', '2025-08-25', 'Active', 'Jane Doe', '+61400123123', 'test3@test.com', '$2y$10$qDK8r8704G0LuLXeZOoEreSV0jEPv2MOvaT2./O5O4r7bPUh3ixZe');

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE `invoices` (
  `invoice_id` int(11) NOT NULL,
  `franchisee_id` int(11) DEFAULT NULL,
  `invoice_date` date DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `status` enum('Unpaid','Paid','Overdue') DEFAULT 'Unpaid',
  `due_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invoice_items`
--

CREATE TABLE `invoice_items` (
  `item_id` int(11) NOT NULL,
  `invoice_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `unit_price` decimal(10,2) DEFAULT NULL,
  `subtotal` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `message_id` int(11) NOT NULL,
  `sender_type` enum('User','Franchisee','Contact') NOT NULL,
  `sender_ref_id` int(11) NOT NULL,
  `subject` varchar(200) DEFAULT NULL,
  `content` text DEFAULT NULL,
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `parent_message_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`message_id`, `sender_type`, `sender_ref_id`, `subject`, `content`, `sent_at`, `parent_message_id`) VALUES
(1, 'User', 2, 'testing 24-09', 'test', '2025-09-24 05:43:38', NULL),
(2, 'User', 1, 'testing 25-09', 'Test', '2025-09-25 00:21:38', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `message_recipients`
--

CREATE TABLE `message_recipients` (
  `id` int(11) NOT NULL,
  `message_id` int(11) NOT NULL,
  `receiver_type` enum('User','Franchisee','Contact','All','AllFranchisee','AllStaff','AllAdmin','Others') NOT NULL,
  `receiver_ref_id` int(11) DEFAULT NULL,
  `email_override` varchar(255) DEFAULT NULL,
  `status` enum('Read','Unread') DEFAULT 'Unread'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `message_recipients`
--

INSERT INTO `message_recipients` (`id`, `message_id`, `receiver_type`, `receiver_ref_id`, `email_override`, `status`) VALUES
(1, 1, 'Franchisee', 1, 'franchisee@test.com', 'Read'),
(2, 2, 'Franchisee', 1, 'franchisee@test.com', 'Read');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`id`, `email`, `token`, `expires_at`, `created_at`) VALUES
(1, 'franchisee@test.com', '18f98a90f8e0732630ab5142bb43892f2ef7e2b768782f2d08717d87ea067d96', '2025-08-30 08:02:02', '2025-08-30 05:02:02'),
(2, 'franchisee@test.com', 'd05e45e88d5174334a23ea424755006d9e12ce623c965af2e8fda462a73b683f', '2025-08-30 08:17:01', '2025-08-30 05:17:01'),
(3, 'franchisee@test.com', 'b82c7b0ec18549833edaf6295a5598a7762055486dce4361e33aad80d7ebae78', '2025-08-30 08:21:44', '2025-08-30 05:21:44'),
(4, 'franchisee@test.com', '7ec4a6185bc15ca2c9f483d67ae6f15c1533b95b856ff878ac286ab2fc0402b9', '2025-08-30 08:21:52', '2025-08-30 05:21:52'),
(5, 'franchisee@test.com', '57f59ba443d928558c13857036da4245b9e36bb7bf99d6d622e086375e4012a8', '2025-08-30 08:22:21', '2025-08-30 05:22:21'),
(6, 'franchisee@test.com', 'a9004448413342363a7dfe5e81d956935b285069c888c1aa596445019cb1efb3', '2025-08-30 16:32:31', '2025-08-30 05:32:31'),
(7, 'franchisee@test.com', '0b1c551c58fc349b22e5eef0de4ce6fe068312fcd85a21e4c1774709cd339748', '2025-08-30 16:34:06', '2025-08-30 05:34:06');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `invoice_id` int(11) DEFAULT NULL,
  `payment_date` date DEFAULT NULL,
  `amount_paid` decimal(10,2) DEFAULT NULL,
  `payment_method` enum('Card','Bank Transfer','Paypal') NOT NULL,
  `transaction_reference` varchar(100) DEFAULT NULL,
  `status` enum('Successful','Failed','Pending') DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `stock_quantity` int(11) DEFAULT NULL,
  `category` enum('equipment','safety gear','uniforms','consumables','Cleaning Supplies','Cleaning Tools','Cleaning Equipment') DEFAULT 'equipment'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `name`, `description`, `image_path`, `price`, `stock_quantity`, `category`) VALUES
(1, 'Cleaning Spray', 'Disinfectant spray, 500ml', NULL, 9.99, 50, 'Cleaning Supplies'),
(2, 'Mop Set', 'Heavy-duty mop with handle', NULL, 24.99, 10, 'Cleaning Tools'),
(3, 'Sponge Pack', 'Pack of 10 sponges', NULL, 4.99, 0, 'Cleaning Supplies'),
(4, 'Vacuum Cleaner', 'Portable vacuum, 1200W', NULL, 89.99, 5, 'Cleaning Equipment'),
(5, 'Microfiber Cloths', 'Set of 12 microfiber cloths', NULL, 12.50, 25, 'Cleaning Supplies'),
(12, 'JK Glass & Mirror Cleaner 5L', 'Streak-free ammonia-free formula for glass, mirrors, and stainless.', 'glass-cleaner-5l.jpg', 18.90, 60, 'Cleaning Supplies'),
(13, 'JK Hospital-Grade Disinfectant 5L', 'Quaternary ammonium compound; broad-spectrum disinfection.', 'disinfectant-5l.jpg', 32.50, 0, 'Cleaning Supplies'),
(14, 'JK Heavy-Duty Degreaser 5L', 'Alkaline citrus degreaser for kitchens and workshop floors.', 'degreaser-5l.jpg', 29.80, 40, 'Cleaning Supplies'),
(15, 'Microfibre Flat Mop Kit', 'Aluminium handle + 40cm frame + 2x washable pads.', 'mf-flat-mop-kit.jpg', 42.00, 50, 'Cleaning Tools'),
(16, 'Window Squeegee 35cm', 'Stainless channel, pro rubber blade for streak-free pull.', 'squeegee-35.jpg', 19.90, 0, 'Cleaning Tools'),
(17, 'Grout & Detail Brush Set (2pc)', 'Nylon bristles, ergonomic grips for edges and grout lines.', 'grout-brush-set.jpg', 12.50, 100, 'Cleaning Tools'),
(18, 'Backpack Vacuum 15L (HEPA)', 'Lightweight commercial backpack vac with HEPA filter set.', 'backpack-vac-15l.jpg', 499.00, 12, 'Cleaning Equipment'),
(19, 'Auto Floor Scrubber 20\"', 'Battery walk-behind scrubber; charger included.', 'floor-scrubber-20.jpg', 4890.00, 0, 'Cleaning Equipment'),
(20, 'Wet/Dry Vacuum 30L', 'Poly tank, tilt-n-drain, 1200W motor with tool kit.', 'wet-dry-30l.jpg', 239.00, 18, 'Cleaning Equipment'),
(21, 'Nitrile Gloves (Box 100) – L', 'Powder-free, chemical-resistant disposable gloves.', 'nitrile-gloves-l.jpg', 14.90, 120, 'safety gear'),
(22, 'Safety Goggles – Anti-Fog', 'Wrap-around, splash and dust protection.', 'safety-goggles.jpg', 11.50, 70, 'safety gear'),
(23, 'Hi-Vis Vest – Day/Night', 'AS/NZS compliant reflective vest with zipper.', 'hi-vis-vest.jpg', 19.00, 0, 'safety gear'),
(24, 'JK Polo Shirt – Navy', 'Breathable quick-dry fabric, embroidered JK logo.', 'jk-polo-navy.jpg', 29.00, 55, 'uniforms'),
(25, 'Work Cargo Pants – Black', 'Durable stretch fabric with utility pockets.', 'cargo-pants-black.jpg', 49.00, 0, 'uniforms'),
(26, 'JK Cap – Navy', 'Curved peak, adjustable strap, embroidered logo.', 'jk-cap.jpg', 16.00, 80, 'uniforms'),
(27, 'Bin Liners 82L – HD (250pk)', 'Tear-resistant liners for general waste bins.', 'bin-liners-82l.jpg', 23.90, 65, 'consumables'),
(28, 'Paper Towel Rolls (Carton 12)', '2-ply, high absorbency for kitchens and washrooms.', 'paper-towel-12.jpg', 36.50, 30, 'consumables'),
(30, 'Janitorial Cart w/ Press Wringer', 'Mop bucket press, tool holders, 2 trays, waste bag.', 'jani-cart-press.jpg', 249.00, 10, 'equipment'),
(31, 'Wet Floor Sign', 'High-visibility folding caution sign.', 'wet-floor-sign.jpg', 15.90, 90, 'equipment'),
(32, 'Heavy-Duty Extension Lead 20m', 'Industrial grade cable reel for equipment.', 'extension-lead-20m.jpg', 44.00, 0, 'equipment');

-- --------------------------------------------------------

--
-- Table structure for table `training`
--

CREATE TABLE `training` (
  `training_id` int(11) NOT NULL,
  `franchisee_id` int(11) DEFAULT NULL,
  `title` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `file_path` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `training`
--

INSERT INTO `training` (`training_id`, `franchisee_id`, `title`, `description`, `file_path`, `created_at`) VALUES
(1, 1, 'test', 'test', 'janiking.pdf', '2025-09-12 08:44:06');

-- --------------------------------------------------------

--
-- Table structure for table `training_acknowledgements`
--

CREATE TABLE `training_acknowledgements` (
  `acknowledgement_id` int(11) NOT NULL,
  `training_id` int(11) DEFAULT NULL,
  `franchisee_id` int(11) DEFAULT NULL,
  `acknowledge_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('Acknowledged','Pending') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `role` enum('Admin','Staff') DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password_hash` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `role`, `name`, `email`, `password_hash`, `phone`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Admin', 'Admin Test', 'admin@janiking.com', '$2y$10$9F4K1hiLK3h3kHWE4SpsSeL.bCm2jbkhwaAnglA/WFPAXuhoq/T82', '+61 400 123 456', 'Active', '2025-08-30 03:58:39', '2025-09-25 04:49:13'),
(2, 'Staff', 'Staff Test', 'staff@janiking.com', '$2y$10$kULGa/1.DBkiJGZOnn6KyOOh74u1kIkiDNxuILgjuIr74qi80K..O', '+61 410 123 456', 'Active', '2025-08-30 03:58:39', '2025-09-25 04:49:41'),
(3, 'Staff', 'test2', 'test2@test.com', '$2y$10$DgVFfI4Wo6mz6yuAJrLYEeyrOkX6KNZb67FXg5mF.HfGRDiTvEd8S', '', 'Active', '2025-09-22 05:29:31', '2025-09-22 05:29:31');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`announcement_id`),
  ADD KEY `fk_announcements_author` (`author_id`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`booking_id`),
  ADD KEY `franchisee_id` (`franchisee_id`);

--
-- Indexes for table `contact_inquiries`
--
ALTER TABLE `contact_inquiries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `email` (`email`);

--
-- Indexes for table `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`documents_id`),
  ADD KEY `franchisee_id` (`franchisee_id`);

--
-- Indexes for table `franchisees`
--
ALTER TABLE `franchisees`
  ADD PRIMARY KEY (`franchisee_id`),
  ADD UNIQUE KEY `unique_email` (`email`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`invoice_id`),
  ADD KEY `franchisee_id` (`franchisee_id`);

--
-- Indexes for table `invoice_items`
--
ALTER TABLE `invoice_items`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `invoice_id` (`invoice_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`message_id`),
  ADD KEY `parent_message_id` (`parent_message_id`);

--
-- Indexes for table `message_recipients`
--
ALTER TABLE `message_recipients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `message_id` (`message_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `email` (`email`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `invoice_id` (`invoice_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`);

--
-- Indexes for table `training`
--
ALTER TABLE `training`
  ADD PRIMARY KEY (`training_id`),
  ADD KEY `franchisee_id` (`franchisee_id`);

--
-- Indexes for table `training_acknowledgements`
--
ALTER TABLE `training_acknowledgements`
  ADD PRIMARY KEY (`acknowledgement_id`),
  ADD UNIQUE KEY `uq_training_franchisee` (`training_id`,`franchisee_id`),
  ADD KEY `franchisee_id` (`franchisee_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `announcement_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `booking_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `contact_inquiries`
--
ALTER TABLE `contact_inquiries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `documents`
--
ALTER TABLE `documents`
  MODIFY `documents_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `franchisees`
--
ALTER TABLE `franchisees`
  MODIFY `franchisee_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `invoice_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `invoice_items`
--
ALTER TABLE `invoice_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `message_recipients`
--
ALTER TABLE `message_recipients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `training`
--
ALTER TABLE `training`
  MODIFY `training_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `training_acknowledgements`
--
ALTER TABLE `training_acknowledgements`
  MODIFY `acknowledgement_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `announcements`
--
ALTER TABLE `announcements`
  ADD CONSTRAINT `fk_announcements_author` FOREIGN KEY (`author_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`franchisee_id`) REFERENCES `franchisees` (`franchisee_id`);

--
-- Constraints for table `invoices`
--
ALTER TABLE `invoices`
  ADD CONSTRAINT `invoices_ibfk_1` FOREIGN KEY (`franchisee_id`) REFERENCES `franchisees` (`franchisee_id`);

--
-- Constraints for table `invoice_items`
--
ALTER TABLE `invoice_items`
  ADD CONSTRAINT `invoice_items_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`invoice_id`),
  ADD CONSTRAINT `invoice_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`parent_message_id`) REFERENCES `messages` (`message_id`) ON DELETE SET NULL;

--
-- Constraints for table `message_recipients`
--
ALTER TABLE `message_recipients`
  ADD CONSTRAINT `message_recipients_ibfk_1` FOREIGN KEY (`message_id`) REFERENCES `messages` (`message_id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`invoice_id`);

--
-- Constraints for table `training`
--
ALTER TABLE `training`
  ADD CONSTRAINT `training_ibfk_1` FOREIGN KEY (`franchisee_id`) REFERENCES `franchisees` (`franchisee_id`);

--
-- Constraints for table `training_acknowledgements`
--
ALTER TABLE `training_acknowledgements`
  ADD CONSTRAINT `training_acknowledgements_ibfk_1` FOREIGN KEY (`training_id`) REFERENCES `training` (`training_id`),
  ADD CONSTRAINT `training_acknowledgements_ibfk_2` FOREIGN KEY (`franchisee_id`) REFERENCES `franchisees` (`franchisee_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
