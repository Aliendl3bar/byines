-- 1. Create and select Database
drop database if exists `byines`;
CREATE DATABASE IF NOT EXISTS `byines` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `byines`;
-- 2. Create Users Table
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `first_name` VARCHAR(50) NOT NULL,
    `last_name` VARCHAR(50) NOT NULL,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `phone` VARCHAR(20) NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `role` ENUM('user', 'admin') NOT NULL DEFAULT 'user',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ;
-- 3. Create Categories Table
CREATE TABLE IF NOT EXISTS `categories` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(100) NOT NULL UNIQUE,
    `image_url` VARCHAR(255) NULL
) ;
-- 4. Create Products Table
CREATE TABLE IF NOT EXISTS `products` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `category_id` INT NOT NULL,
    `name` VARCHAR(150) NOT NULL,
    `slug` VARCHAR(150) NOT NULL UNIQUE,
    `sku` VARCHAR(50) NOT NULL UNIQUE,
    `description` TEXT NOT NULL,
    `price` DECIMAL(10,2) NOT NULL,
    `old_price` DECIMAL(10,2) NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '0 = hidden, 1 = visible',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE
) ;
-- 5. Create Product Images Table
CREATE TABLE IF NOT EXISTS `product_images` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `product_id` INT NOT NULL,
    `color` VARCHAR(30) NULL COMMENT 'NULL = applies to all variants, otherwise color-specific',
    `image_name` VARCHAR(100) NOT NULL COMMENT 'File name only, e.g. front.jpg',
    `sort_order` INT NOT NULL DEFAULT 1,
    `is_main` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 = hero/thumbnail image',
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ;
-- 6. Create Product Variants Table (Size & Color combinations)
CREATE TABLE IF NOT EXISTS `product_variants` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `product_id` INT NOT NULL,
    `color` VARCHAR(30) NOT NULL,
    `size` VARCHAR(10) NOT NULL,
    `stock_quantity` INT NOT NULL DEFAULT 0,
    `price_modifier` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    UNIQUE KEY `variant_unique` (`product_id`, `color`, `size`),
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ;
-- 7. Create Addresses Table
CREATE TABLE IF NOT EXISTS `addresses` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `address_type` ENUM('shipping', 'billing') NOT NULL,
    `is_primary` TINYINT(1) NOT NULL DEFAULT 0,
    `first_name` VARCHAR(50) NOT NULL,
    `last_name` VARCHAR(50) NOT NULL,
    `phone` VARCHAR(20) NOT NULL,
    `address_line1` VARCHAR(150) NOT NULL,
    `address_line2` VARCHAR(150) NULL,
    `city` VARCHAR(100) NOT NULL,
    `state` VARCHAR(100) NOT NULL,
    `zip_code` VARCHAR(20) NOT NULL,
    `country` VARCHAR(100) NOT NULL,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ;
-- 8. Create Orders Table
CREATE TABLE IF NOT EXISTS `orders` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NULL,
    `order_number` VARCHAR(30) NOT NULL UNIQUE,
    `subtotal` DECIMAL(10,2) NOT NULL,
    `tax` DECIMAL(10,2) NOT NULL,
    `shipping_cost` DECIMAL(10,2) NOT NULL,
    `total_amount` DECIMAL(10,2) NOT NULL,
    `status` ENUM('pending', 'processing', 'in_transit', 'delivered', 'cancelled') NOT NULL DEFAULT 'pending',
    `tracking_number` VARCHAR(100) NULL COMMENT 'Shipment tracking reference',
    `shipping_name` VARCHAR(100) NOT NULL,
    `shipping_phone` VARCHAR(20) NOT NULL,
    `shipping_address_line1` VARCHAR(150) NOT NULL,
    `shipping_address_line2` VARCHAR(150) NULL,
    `shipping_city` VARCHAR(100) NOT NULL,
    `shipping_state` VARCHAR(100) NOT NULL,
    `shipping_zip` VARCHAR(20) NOT NULL,
    `shipping_country` VARCHAR(100) NOT NULL,
    `shipping_method` VARCHAR(50) NOT NULL,
    `payment_method` ENUM('cash_on_delivery', 'paypal', 'credit_card') NOT NULL,
    `payment_status` ENUM('unpaid', 'paid', 'refunded', 'failed') NOT NULL DEFAULT 'unpaid',
    `payment_transaction_id` VARCHAR(100) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ;
-- 9. Create Order Items Table
CREATE TABLE IF NOT EXISTS `order_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `order_id` INT NOT NULL,
    `variant_id` INT NOT NULL,
    `quantity` INT NOT NULL,
    `price` DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`variant_id`) REFERENCES `product_variants`(`id`) ON DELETE RESTRICT
) ;
-- 10. Create Wishlist Table
CREATE TABLE IF NOT EXISTS `wishlist` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `product_id` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `wishlist_unique` (`user_id`, `product_id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ;
-- 11. Create Reviews Table
CREATE TABLE IF NOT EXISTS `reviews` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `product_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `rating` TINYINT NOT NULL CHECK (`rating` BETWEEN 1 AND 5),
    `review_text` TEXT NOT NULL,
    `is_approved` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '0 = pending moderation, 1 = visible',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `review_unique` (`product_id`, `user_id`),
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ;
-- Seed Data
-- Insert Initial Categories
INSERT INTO `categories` (`name`, `slug`, `image_url`) VALUES
('Abayas', 'abayas', '../assets/boutique_byines_3314221782547857149.png'),
('Kimonos', 'kimonos', '../assets/boutique_byines_3755720010749460967''s2026-5-14-15.0.37 story.jpg'),
('Scarfs', 'scarfs', '../assets/boutique_byines_3481002996511481087''s2026-5-14-15.21.22 story.jpg'),
('Niqab', 'niqab', '../assets/boutique_byines_3581710268287392853.png')
ON DUPLICATE KEY UPDATE name=VALUES(name);
-- Insert Initial Products
INSERT INTO `products` (`category_id`, `name`, `slug`, `sku`, `description`, `price`, `old_price`, `is_active`) VALUES
(1, 'Elegant Abaya', 'elegant-abaya', 'ABAYA-001', 'Experience timeless elegance with our premium Elegant Abaya. Crafted from luxurious chiffon fabric, this piece combines traditional modest wear with contemporary design.', 360.00, 450.00, 1),
(1, 'Classic Abaya', 'classic-abaya', 'ABAYA-002', 'Flowing silhouette, designed for comfort and everyday luxury.', 250.00, NULL, 1),
(2, 'Velvet Evening Kimono', 'velvet-evening-kimono', 'KIMONO-001', 'Rich velvet texture with premium embroidery, perfect for formal outings.', 290.00, NULL, 1),
(3, 'Organic Cotton Scarf', 'organic-cotton-scarf', 'SCARF-001', 'Breathable organic cotton construction in soft neutral tones.', 85.00, 100.00, 1),
(4, 'Classic Chiffon Niqab', 'classic-chiffon-niqab', 'NIQAB-001', 'Double-layer premium lightweight chiffon niqab offering optimal coverage and comfort.', 45.00, NULL, 1)
ON DUPLICATE KEY UPDATE name=VALUES(name);
-- Insert Product Images
INSERT INTO `product_images` (`product_id`, `color`, `image_name`, `sort_order`, `is_main`) VALUES
(1, NULL,        'hero.jpg',            1, 1),
(1, 'Black',     'black_front.jpg',     1, 0),
(1, 'Black',     'black_back.jpg',      2, 0),
(1, 'Black',     'black_detail.jpg',    3, 0),
(1, 'Brown',     'brown_front.jpg',     1, 0),
(1, 'Brown',     'brown_back.jpg',      2, 0),
(1, 'Charcoal',  'charcoal_front.jpg',  1, 0),
(1, 'Charcoal',  'charcoal_back.jpg',   2, 0),
(2, NULL,        'hero.jpg',            1, 1),
(2, 'Brown',     'brown_front.jpg',     1, 0),
(2, 'Brown',     'brown_back.jpg',      2, 0),
(3, NULL,        'hero.jpg',            1, 1),
(3, 'Umber',     'umber_front.jpg',     1, 0),
(3, 'Umber',     'umber_back.jpg',      2, 0),
(4, NULL,        'hero.jpg',            1, 1),
(5, NULL,        'hero.jpg',            1, 1);
-- Insert Product Variants
INSERT INTO `product_variants` (`product_id`, `color`, `size`, `stock_quantity`, `price_modifier`) VALUES
(1, 'Black', 'S', 15, 0.00),
(1, 'Black', 'M', 20, 0.00),
(1, 'Black', 'L', 10, 0.00),
(1, 'Brown', 'M', 8, 0.00),
(1, 'Charcoal', 'L', 12, 0.00),
(2, 'Brown', 'S', 25, 0.00),
(2, 'Brown', 'M', 30, 0.00),
(3, 'Umber', 'M', 5, 0.00),
(3, 'Umber', 'L', 10, 0.00)
ON DUPLICATE KEY UPDATE stock_quantity=VALUES(stock_quantity);
