-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 24, 2025 at 01:27 PM
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

--
-- Indexes for dumped tables
--

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
