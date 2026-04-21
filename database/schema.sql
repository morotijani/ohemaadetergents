CREATE DATABASE IF NOT EXISTS `ohemaa_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `ohemaa_db`;

-- Admins Table
CREATE TABLE IF NOT EXISTS `admins` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `admin_id` BINARY(16) NOT NULL UNIQUE COMMENT 'UUID v7',
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_admins_admin_id` (`admin_id`),
    INDEX `idx_admins_email` (`email`)
) ENGINE=InnoDB;

-- Customers Table
CREATE TABLE IF NOT EXISTS `customers` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `customer_id` BINARY(16) NOT NULL UNIQUE COMMENT 'UUID v7',
    `first_name` VARCHAR(50) NOT NULL,
    `last_name` VARCHAR(50) NOT NULL,
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `phone` VARCHAR(20),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_customers_customer_id` (`customer_id`),
    INDEX `idx_customers_email` (`email`)
) ENGINE=InnoDB;

-- Categories Table
CREATE TABLE IF NOT EXISTS `categories` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `category_id` BINARY(16) NOT NULL UNIQUE COMMENT 'UUID v7',
    `name` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(100) NOT NULL UNIQUE,
    `description` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_categories_category_id` (`category_id`),
    INDEX `idx_categories_slug` (`slug`)
) ENGINE=InnoDB;

-- Products Table
CREATE TABLE IF NOT EXISTS `products` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `product_id` BINARY(16) NOT NULL UNIQUE COMMENT 'UUID v7',
    `category_id` INT,
    `name` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL UNIQUE,
    `description` TEXT,
    `price` DECIMAL(10,2) NOT NULL,
    `stock` INT NOT NULL DEFAULT 0,
    `image_url` VARCHAR(255),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL,
    INDEX `idx_products_product_id` (`product_id`),
    INDEX `idx_products_category_id` (`category_id`),
    INDEX `idx_products_slug` (`slug`)
) ENGINE=InnoDB;

-- Orders Table
CREATE TABLE IF NOT EXISTS `orders` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `order_id` BINARY(16) NOT NULL UNIQUE COMMENT 'UUID v7',
    `customer_id` INT NOT NULL,
    `total_amount` DECIMAL(10,2) NOT NULL,
    `status` ENUM('pending', 'processing', 'completed', 'cancelled') DEFAULT 'pending',
    `shipping_address` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE RESTRICT,
    INDEX `idx_orders_order_id` (`order_id`),
    INDEX `idx_orders_customer_id` (`customer_id`),
    INDEX `idx_orders_status` (`status`)
) ENGINE=InnoDB;

-- Order Items Table
CREATE TABLE IF NOT EXISTS `order_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `order_item_id` BINARY(16) NOT NULL UNIQUE COMMENT 'UUID v7',
    `order_id` INT NOT NULL,
    `product_id` INT NOT NULL,
    `quantity` INT NOT NULL,
    `unit_price` DECIMAL(10,2) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE RESTRICT,
    INDEX `idx_order_items_order_item_id` (`order_item_id`),
    INDEX `idx_order_items_order_id` (`order_id`),
    INDEX `idx_order_items_product_id` (`product_id`)
) ENGINE=InnoDB;

-- Logs Table
CREATE TABLE IF NOT EXISTS `logs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `log_id` BINARY(16) NOT NULL UNIQUE COMMENT 'UUID v7',
    `admin_id` INT,
    `customer_id` INT,
    `action` VARCHAR(100) NOT NULL,
    `description` TEXT,
    `ip_address` VARCHAR(45),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`admin_id`) REFERENCES `admins`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE SET NULL,
    INDEX `idx_logs_log_id` (`log_id`),
    INDEX `idx_logs_admin_id` (`admin_id`),
    INDEX `idx_logs_customer_id` (`customer_id`),
    INDEX `idx_logs_action` (`action`)
) ENGINE=InnoDB;
