-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 14, 2025 at 06:55 AM
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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`announcement_id`, `title`, `content`, `author_id`, `created_at`, `updated_at`) VALUES
(3, 'System Announcement', 'test to all users', 1, '2025-09-14 02:33:09', '2025-09-14 02:33:09'),
(4, 'System Announcement', 'test to STAFF', 1, '2025-09-14 02:37:28', '2025-09-14 02:37:28'),
(5, 'System Announcement', 'test to STAFF', 1, '2025-09-14 02:37:32', '2025-09-14 02:37:32');

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
(11, NULL, 'Testting', 'Testing', 'test@test.com', '+61400123123', 'Sydney', '2025-09-15 04:00:00', 'Pending', 'Test');

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
(1, 'John', 'Doe', 'john@example.com', '+61 412 345 678', 'franchise', 'Test', 'Yes', '2025-09-04 05:54:26');

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
(1, 'Test Franchise', '123 Test St, Sydney', '12 345 678 901', '2025-08-01', 'Active', 'John Doe', '+61 420 123 456', 'franchisee@test.com', '$2y$10$WNB449pW2xhGq/4BTiafge2xm86rksqzmxFsa7AlyI7jSIXAZMOJW'),
(2, 'Cleaning', 'aasaasasa', '123456', '2025-09-14', 'Active', 'Mary', '0400123123', 'mary@test.com', '$2y$10$eMF6ejHcmhKhpFX8KgT/2OZFxRSyPuHas/Yb6rZtx6zt3Lsoi8KIK');

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
  `sender_id` int(11) DEFAULT NULL,
  `receiver_id` int(11) DEFAULT NULL,
  `subject` varchar(200) DEFAULT NULL,
  `content` text DEFAULT NULL,
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('Read','Unread') DEFAULT 'Unread',
  `is_announcement` tinyint(1) DEFAULT 0 COMMENT '1 for announcements, 0 for direct messages',
  `parent_message_id` int(11) DEFAULT NULL COMMENT 'ID of the parent message for replies'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`message_id`, `sender_id`, `receiver_id`, `subject`, `content`, `sent_at`, `status`, `is_announcement`, `parent_message_id`) VALUES
(11, 1, NULL, NULL, 'test', '2025-09-12 07:54:55', 'Read', 0, NULL),
(12, 1, NULL, NULL, 'test2', '2025-09-12 07:54:59', 'Read', 0, NULL),
(13, 1, 2, NULL, 'Hi! Test', '2025-09-14 02:21:48', 'Unread', 0, NULL),
(14, 1, 2, NULL, 'Testing for report!', '2025-09-14 02:22:49', 'Unread', 0, NULL),
(15, NULL, 1, 'Re:', 'test reply', '2025-09-14 02:24:53', 'Read', 0, NULL),
(16, 2, NULL, 'test 3', 'test', '2025-09-14 03:43:51', 'Read', 1, NULL),
(17, 1, 2, NULL, 'test', '2025-09-14 04:39:32', 'Unread', 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `message_recipients`
--

CREATE TABLE `message_recipients` (
  `id` int(11) NOT NULL,
  `message_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(5, 'Microfiber Cloths', 'Set of 12 microfiber cloths', NULL, 12.50, 25, 'Cleaning Supplies');

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
(1, 'Admin', 'Admin Test', 'admin@test.com', '$2y$10$OssFHKgWMhM5LYSfjlc5NuYTM0dO8GFpxMcoeQPAE5FUHVVuRduiW', '+61 400 123 456', 'Active', '2025-08-30 03:58:39', '2025-08-30 05:54:45'),
(2, 'Staff', 'Staff Test', 'staff@test.com', '$2y$10$WM7nk9sXTg/6pMWmP8WD0u6T386kB1x7t4arE5rGnGjdGT3ytOXc2', '+61 410 123 456', 'Active', '2025-08-30 03:58:39', '2025-09-14 04:20:57');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`announcement_id`),
  ADD KEY `announcements_ibfk_1` (`author_id`);

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
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`),
  ADD KEY `parent_message_id` (`parent_message_id`);

--
-- Indexes for table `message_recipients`
--
ALTER TABLE `message_recipients`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_message_receiver` (`message_id`,`receiver_id`),
  ADD KEY `receiver_id` (`receiver_id`);

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
  MODIFY `announcement_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `booking_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `contact_inquiries`
--
ALTER TABLE `contact_inquiries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `franchisees`
--
ALTER TABLE `franchisees`
  MODIFY `franchisee_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

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
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `message_recipients`
--
ALTER TABLE `message_recipients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

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
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

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
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `announcements`
--
ALTER TABLE `announcements`
  ADD CONSTRAINT `announcements_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

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
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `messages_ibfk_3` FOREIGN KEY (`parent_message_id`) REFERENCES `messages` (`message_id`) ON DELETE SET NULL;

--
-- Constraints for table `message_recipients`
--
ALTER TABLE `message_recipients`
  ADD CONSTRAINT `message_recipients_ibfk_1` FOREIGN KEY (`message_id`) REFERENCES `messages` (`message_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `message_recipients_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

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
