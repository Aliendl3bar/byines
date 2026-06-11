SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

CREATE TABLE `addresses` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `address_type` enum('shipping','billing') NOT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `address_line1` varchar(150) NOT NULL,
  `address_line2` varchar(150) DEFAULT NULL,
  `city` varchar(100) NOT NULL,
  `state` varchar(100) NOT NULL,
  `zip_code` varchar(20) NOT NULL,
  `country` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `image_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `categories` (`id`, `name`, `slug`, `image_url`) VALUES
(6, 'Abayas', 'Abayas', '../categories_img/cat_1781096624_798.png'),
(7, 'Scarfs', 'Scarfs', '../categories_img/cat_1781096657_832.jpg'),
(8, 'Niqabs', 'Niqabs', '../categories_img/cat_1781096688_192.png'),
(9, 'Accessories', 'Accessories', '../categories_img/cat_1781096703_611.jpg');

CREATE TABLE `collections` (
  `id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `products_ids` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `collections` (`id`, `title`, `image_path`, `products_ids`) VALUES
(3, 'Popular Picks', '', '11,10,9,7'),
(4, 'Coffee collection', '../collections_img/col_1781100385_809.jpg', '9,4,7,11'),
(5, 'Summer Collection', '../collections_img/col_1781100429_878.png', '13,16,20,10');

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `order_number` varchar(30) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `tax` decimal(10,2) NOT NULL,
  `shipping_cost` decimal(10,2) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','processing','in_transit','delivered','cancelled') NOT NULL DEFAULT 'pending',
  `tracking_number` varchar(100) DEFAULT NULL,
  `shipping_name` varchar(100) NOT NULL,
  `shipping_phone` varchar(20) NOT NULL,
  `shipping_address_line1` varchar(150) NOT NULL,
  `shipping_address_line2` varchar(150) DEFAULT NULL,
  `shipping_city` varchar(100) NOT NULL,
  `shipping_state` varchar(100) NOT NULL,
  `shipping_zip` varchar(20) NOT NULL,
  `shipping_country` varchar(100) NOT NULL,
  `shipping_method` varchar(50) NOT NULL,
  `payment_method` enum('cash_on_delivery','paypal','credit_card') NOT NULL,
  `payment_status` enum('unpaid','paid','refunded','failed') NOT NULL DEFAULT 'unpaid',
  `payment_transaction_id` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `orders` (`id`, `user_id`, `order_number`, `subtotal`, `tax`, `shipping_cost`, `total_amount`, `status`, `tracking_number`, `shipping_name`, `shipping_phone`, `shipping_address_line1`, `shipping_address_line2`, `shipping_city`, `shipping_state`, `shipping_zip`, `shipping_country`, `shipping_method`, `payment_method`, `payment_status`, `payment_transaction_id`, `created_at`) VALUES
(4, 1, 'ORD-6A277536011DF937', 28.00, 2.80, 0.00, 30.80, 'cancelled', NULL, 'aymane salmoune', '0719389174', 'birchifa', NULL, 'tanger', '', '', 'Morocco', 'Standard', 'cash_on_delivery', 'unpaid', NULL, '2026-06-09 02:06:46'),
(5, 3, 'BYINES-B2E155A4', 38.00, 3.80, 0.00, 41.80, 'delivered', NULL, 'Alien Dl3bar', '0719389174', 'birchifa', NULL, 'tanger', '', '', 'Morocco', 'Standard', 'cash_on_delivery', 'unpaid', NULL, '2026-06-09 09:55:49'),
(6, 1, 'BYINES-C575D43F', 237.00, 23.70, 0.00, 260.70, 'delivered', NULL, 'aymane salmoune', '0719389174', 'birchifa', NULL, 'tanger', '', '', 'Morocco', 'Standard', 'cash_on_delivery', 'unpaid', NULL, '2026-06-10 10:59:35'),
(7, 1, 'BYINES-293D23AB', 5.00, 0.50, 0.00, 5.50, 'pending', NULL, 'aymane salmoune', '0719389174', 'birchifa', NULL, 'tanger', '', '', 'Morocco', 'Standard', 'cash_on_delivery', 'unpaid', NULL, '2026-06-10 15:04:37'),
(8, 1, 'BYINES-D03694BB', 54.00, 5.40, 0.00, 59.40, 'pending', NULL, 'aymane salmoune', '0719389174', 'birchifa', NULL, 'tanger', '', '', 'Morocco', 'Standard', 'cash_on_delivery', 'unpaid', NULL, '2026-06-11 01:19:34'),
(9, 1, 'BYINES-1B5860C7', 76.00, 7.60, 0.00, 83.60, 'pending', NULL, 'aymane salmoune', '0719389174', 'birchifa', NULL, 'tanger', '', '', 'Morocco', 'Standard', 'cash_on_delivery', 'unpaid', NULL, '2026-06-11 09:37:23');

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `variant_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `order_items` (`id`, `order_id`, `variant_id`, `quantity`, `price`) VALUES
(4, 4, 54, 1, 28.00),
(5, 5, 41, 1, 38.00),
(6, 6, 57, 1, 28.00),
(7, 6, 41, 2, 38.00),
(8, 6, 55, 1, 28.00),
(9, 6, 13, 3, 35.00),
(10, 7, 71, 1, 5.00),
(11, 8, 74, 1, 16.00),
(12, 8, 41, 1, 38.00),
(13, 9, 41, 2, 38.00);

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `slug` varchar(150) NOT NULL,
  `sku` varchar(50) NOT NULL,
  `description` text NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `old_price` decimal(10,2) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `products` (`id`, `category_id`, `name`, `slug`, `sku`, `description`, `price`, `old_price`, `is_active`, `created_at`) VALUES
(7, 6, 'Abaya silk', 'abaya-silk', 'aba-232', 'Experience timeless elegance with our premium Elegant Abaya. Crafted from luxurious chiffon fabric, this piece combines traditional modest wear with contemporary design. Perfect for any occasion, from casual gatherings to special events.', 35.00, NULL, 1, '2026-06-04 08:33:43'),
(8, 6, 'ensemble pani', 'ensemble-pani', 'pan-565', 'Effortless style meets everyday comfort. This premium two-piece matching set is designed for the modern woman who refuses to compromise on elegance or ease. Featuring a beautifully draped, oversized tunic top and a matching fluid maxi skirt, this coordinated ensemble takes the guesswork out of getting dressed.', 23.00, NULL, 1, '2026-06-04 12:08:52'),
(9, 6, 'elegant Abaya', 'elegant-abaya', 'aba-241', 'Crafted from a premium, ultra-soft silk-viscose blend, it offers an exquisite, buttery drape that feels like a second skin. Styled with delicate, tailored cuffs and a minimalist open-front design, it is the perfect statement piece for special occasions, formal gatherings, or elevated everyday wear.', 38.00, NULL, 1, '2026-06-08 09:24:41'),
(10, 7, 'silk scarfs', 'silk-scarfs', 'scr-272', 'scarfs', 9.00, NULL, 1, '2026-06-08 09:26:50'),
(11, 6, 'abaya jogging', 'abaya-jogging', 'aba-718', 'perfect for light sport and activities', 28.00, NULL, 1, '2026-06-08 09:36:20'),
(12, 9, 'flowers bucket', 'flowers-bucket', 'acs-443', 'a beautiful accessory that would be the perfect gift for your loved once', 10.00, NULL, 1, '2026-06-10 13:14:43'),
(13, 9, 'flowers bag', 'flowers-bag', 'acs-231', 'a beautiful accessory that would be the perfect gift for your loved once', 10.00, NULL, 1, '2026-06-10 13:15:47'),
(14, 9, 'flowers bucket', 'flowers-bucket-1781097454', 'acs-341', 'a beautiful accessory that would be the perfect gift for your loved once', 10.00, NULL, 1, '2026-06-10 13:17:34'),
(15, 9, 'beige flower bucket', 'beige-flower-bucket', 'acs-345', 'a beautiful accessory that would be the perfect gift for your loved once', 7.00, NULL, 1, '2026-06-10 13:18:33'),
(16, 9, 'brown flower bucket', 'brown-flower-bucket', 'acs-348', 'a beautiful accessory that would be the perfect gift for your loved once', 7.00, NULL, 1, '2026-06-10 13:19:32'),
(17, 9, 'golden flower', 'golden-flower', 'acs-979', 'a beautiful accessory that would be the perfect gift for your loved once', 5.00, NULL, 1, '2026-06-10 13:20:13'),
(18, 6, 'evening abaya', 'evening-abaya', 'aba-255', 'A perfect abaya to wear for light and fun days', 16.00, NULL, 1, '2026-06-10 13:29:39'),
(19, 6, 'summer abaya', 'summer-abaya', 'aba-116', 'A perfect abaya to wear for light and sunny days', 23.00, NULL, 1, '2026-06-10 13:33:35'),
(20, 6, 'coat abaya', 'coat-abaya', 'aba-898', 'A perfect abaya to wear for cold and snowy days', 30.00, NULL, 1, '2026-06-10 13:36:11'),
(21, 7, 'winter scarf', 'winter-scarf', 'scr-288', 'A perfect scarf to wear for windy and cold days', 15.00, NULL, 1, '2026-06-10 13:49:45'),
(22, 8, 'niqab banda', 'niqab-banda', 'nqb-213', 'A perfect niqab to wear for daily activities', 8.00, NULL, 1, '2026-06-10 13:54:49');

CREATE TABLE `product_images` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `color` varchar(30) DEFAULT NULL,
  `image_name` varchar(100) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 1,
  `is_main` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `product_images` (`id`, `product_id`, `color`, `image_name`, `sort_order`, `is_main`) VALUES
(19, 7, NULL, 'img_1780562023_5529.png', 0, 1),
(20, 7, 'beige', 'img_1780562023_6457.png', 2, 0),
(21, 7, 'beige', 'img_1780562023_4351.png', 1, 0),
(22, 7, 'magenta', 'img_1780562023_5587.png', 3, 0),
(23, 7, 'red', 'img_1780562023_4217.png', 4, 0),
(24, 8, 'magenta', 'img_1780575053_2904.png', 0, 1),
(25, 8, 'purple', 'img_1780575053_2423.png', 1, 0),
(26, 8, 'beige', 'img_1780575053_9389.png', 2, 0),
(27, 8, 'orange', 'img_1780575053_9896.png', 3, 0),
(28, 8, 'lavender', 'img_1780575053_2900.png', 4, 0),
(29, 8, 'turquoise', 'img_1780575053_8598.png', 5, 0),
(34, 9, NULL, 'img_1780910690_9553.png', 1, 0),
(35, 9, NULL, 'img_1780910690_7374.png', 2, 0),
(36, 9, NULL, 'img_1780910690_3301.png', 0, 1),
(37, 9, NULL, 'img_1780910690_7515.png', 3, 0),
(38, 10, NULL, 'img_1780910824_7290.png', 2, 0),
(39, 10, NULL, 'img_1780910824_3348.png', 0, 1),
(40, 10, NULL, 'img_1780910824_7614.png', 1, 0),
(41, 11, NULL, 'img_1780911398_4699.png', 0, 1),
(42, 11, NULL, 'img_1780911398_8799.png', 1, 0),
(43, 11, NULL, 'img_1780911398_2073.png', 2, 0),
(44, 11, NULL, 'img_1780911398_4508.png', 3, 0),
(45, 11, NULL, 'img_1780911398_8415.png', 4, 0),
(46, 12, 'Default', 'img_1781097283_1069.png', 0, 1),
(47, 12, NULL, 'img_1781097283_8559.png', 1, 0),
(50, 13, NULL, 'img_1781097390_6795.png', 0, 1),
(51, 13, NULL, 'img_1781097390_8795.png', 1, 0),
(52, 13, NULL, 'img_1781097390_1432.png', 2, 0),
(53, 14, NULL, 'img_1781097454_7492.png', 1, 0),
(54, 14, 'Default', 'img_1781097454_3170.png', 0, 1),
(55, 15, NULL, 'img_1781097513_1849.png', 1, 1),
(56, 15, NULL, 'img_1781097513_2553.png', 1, 0),
(57, 16, NULL, 'img_1781097572_7253.png', 1, 1),
(58, 16, NULL, 'img_1781097572_4707.png', 1, 0),
(59, 17, NULL, 'img_1781097613_5796.png', 1, 1),
(60, 17, NULL, 'img_1781097613_2136.png', 1, 0),
(61, 18, 'green', 'img_1781098179_9135.png', 0, 1),
(62, 18, 'beige', 'img_1781098179_7710.png', 1, 0),
(63, 18, 'red', 'img_1781098179_7842.png', 2, 0),
(64, 18, 'silver', 'img_1781098179_7010.png', 3, 0),
(65, 19, NULL, 'img_1781098415_8394.png', 1, 1),
(66, 19, NULL, 'img_1781098415_4273.png', 1, 0),
(67, 19, NULL, 'img_1781098415_7998.png', 1, 0),
(68, 20, 'grey', 'img_1781098579_4033.png', 0, 1),
(69, 20, 'black', 'img_1781098579_8939.png', 1, 0),
(70, 20, 'brown', 'img_1781098579_6110.png', 2, 0),
(71, 20, 'beige', 'img_1781098579_4348.png', 3, 0),
(72, 21, NULL, 'img_1781099385_4170.jpg', 1, 1),
(73, 22, 'green', 'img_1781099689_1602.png', 1, 0),
(74, 22, 'brown', 'img_1781099689_3196.png', 2, 0),
(75, 22, 'beige', 'img_1781099689_3437.png', 3, 0),
(76, 22, 'blue', 'img_1781099689_7821.png', 4, 0),
(77, 22, NULL, 'img_1781099689_6065.png', 0, 1);

CREATE TABLE `product_variants` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `color` varchar(30) NOT NULL,
  `size` varchar(10) NOT NULL,
  `stock_quantity` int(11) NOT NULL DEFAULT 0,
  `price_modifier` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `product_variants` (`id`, `product_id`, `color`, `size`, `stock_quantity`, `price_modifier`) VALUES
(13, 7, 'red', 'M', 7, 0.00),
(14, 7, 'magenta', 'M', 10, 0.00),
(15, 7, 'beige', 'M', 10, 0.00),
(17, 8, 'magenta', 'S', 10, 0.00),
(18, 8, 'magenta', 'M', 10, 0.00),
(19, 8, 'magenta', 'L', 10, 0.00),
(20, 8, 'magenta', 'XL', 10, 0.00),
(21, 8, 'purple', 'S', 10, 0.00),
(22, 8, 'purple', 'L', 10, 0.00),
(23, 8, 'purple', 'M', 10, 0.00),
(24, 8, 'purple', 'XL', 10, 0.00),
(25, 8, 'beige', 'S', 10, 0.00),
(26, 8, 'beige', 'M', 10, 0.00),
(27, 8, 'beige', 'L', 10, 0.00),
(28, 8, 'beige', 'XL', 10, 0.00),
(29, 8, 'orange', 'S', 10, 0.00),
(30, 8, 'orange', 'M', 10, 0.00),
(31, 8, 'orange', 'L', 10, 0.00),
(32, 8, 'orange', 'XL', 10, 0.00),
(33, 8, 'turquoise', 'S', 10, 0.00),
(34, 8, 'turquoise', 'M', 10, 0.00),
(35, 8, 'turquoise', 'L', 10, 0.00),
(36, 8, 'turquoise', 'XL', 10, 0.00),
(37, 8, 'lavender', 'S', 10, 0.00),
(38, 8, 'lavender', 'M', 10, 0.00),
(39, 8, 'lavender', 'L', 10, 0.00),
(40, 8, 'lavender', 'XL', 10, 0.00),
(41, 9, 'Default', 'M', 34, 0.00),
(42, 10, 'darkslategray', 'M', 10, 0.00),
(43, 10, 'dimgray', 'M', 10, 0.00),
(44, 10, 'gray', 'M', 10, 0.00),
(45, 10, 'rosybrown', 'M', 10, 0.00),
(46, 10, 'silver', 'M', 10, 0.00),
(47, 10, 'black', 'M', 10, 0.00),
(48, 10, 'midnightblue', 'M', 10, 0.00),
(49, 10, 'lightslategray', 'M', 0, 0.00),
(50, 10, 'darkcyan', 'M', 10, 0.00),
(51, 10, 'crimson', 'M', 10, 0.00),
(52, 10, 'indianred', 'M', 10, 0.00),
(53, 10, 'whitesmoke', 'M', 0, 0.00),
(54, 11, 'black', 'M', 9, 0.00),
(55, 11, 'black', 'L', 9, 0.00),
(56, 11, 'black', 'XL', 10, 0.00),
(57, 11, 'mistyrose', 'M', 9, 0.00),
(58, 11, 'mistyrose', 'L', 10, 0.00),
(59, 11, 'mistyrose', 'XL', 10, 0.00),
(60, 11, 'rosybrown', 'M', 10, 0.00),
(61, 11, 'rosybrown', 'L', 10, 0.00),
(62, 11, 'rosybrown', 'XL', 10, 0.00),
(63, 11, 'gray', 'M', 10, 0.00),
(64, 11, 'gray', 'L', 10, 0.00),
(65, 11, 'gray', 'XL', 10, 0.00),
(66, 12, 'Default', 'M', 3, 0.00),
(67, 13, 'Default', 'M', 5, 0.00),
(68, 14, 'Default', 'M', 13, 0.00),
(69, 15, 'Default', 'M', 7, 0.00),
(70, 16, 'Default', 'M', 7, 0.00),
(71, 17, 'Default', 'M', 20, 0.00),
(72, 18, 'green', 'M', 13, 0.00),
(73, 18, 'beige', 'M', 19, 0.00),
(74, 18, 'red', 'M', 1, 0.00),
(75, 18, 'silver', 'M', 11, 0.00),
(76, 19, 'Default', 'M', 0, 0.00),
(77, 20, 'grey', 'M', 10, 0.00),
(78, 20, 'black', 'M', 0, 0.00),
(79, 20, 'brown', 'M', 20, 0.00),
(80, 20, 'beige', 'M', 6, 0.00),
(81, 21, 'Default', 'M', 22, 0.00),
(82, 22, 'brown', 'M', 10, 0.00),
(83, 22, 'blue', 'M', 10, 0.00),
(84, 22, 'green', 'M', 0, 0.00),
(85, 22, 'beige', 'M', 19, 0.00);

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` tinyint(4) NOT NULL CHECK (`rating` between 1 and 5),
  `review_text` text NOT NULL,
  `is_approved` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('user','admin') NOT NULL DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `phone`, `password_hash`, `role`, `created_at`, `updated_at`) VALUES
(1, 'aymane', 'salmoune', 'aymanesalmoune21@gmail.com', NULL, '$2y$10$WLP.PAdtT1t9JkvAaieHFeHjEdYWvKbmbxiJxG0MgJa.D4NRzyXHy', 'admin', '2026-06-03 09:25:10', '2026-06-03 09:50:52'),
(2, 'aymane28', 'salmoune', 'aymanesalmoune00@gmail.com', NULL, '$2y$10$aHe0BHrwzVIsr/InoH/1wumLBQqqsIQXllTeDMXt2XhFgatA2VtPq', 'admin', '2026-06-03 09:27:42', '2026-06-03 09:50:52'),
(3, 'Alien', 'dl3bar', 'aymanesalmoune28@gmail.com', NULL, '$2y$10$wNHvUU9fEnwDi8lj8E2SW.m/myA8gygH8XDeLivJlvtM6F98N/2nO', 'user', '2026-06-09 09:53:15', '2026-06-09 09:53:15');

CREATE TABLE `wishlist` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `addresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

ALTER TABLE `collections`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `user_id` (`user_id`);

ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `variant_id` (`variant_id`);

ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD UNIQUE KEY `sku` (`sku`),
  ADD KEY `category_id` (`category_id`);

ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

ALTER TABLE `product_variants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `variant_unique` (`product_id`,`color`,`size`);

ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `review_unique` (`product_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `wishlist_unique` (`user_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

ALTER TABLE `addresses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

ALTER TABLE `collections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

ALTER TABLE `product_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=79;

ALTER TABLE `product_variants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=87;

ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

ALTER TABLE `wishlist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `addresses`
  ADD CONSTRAINT `addresses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`);

ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

ALTER TABLE `product_images`
  ADD CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

ALTER TABLE `product_variants`
  ADD CONSTRAINT `product_variants_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `wishlist`
  ADD CONSTRAINT `wishlist_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `wishlist_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
