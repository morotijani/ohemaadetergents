-- Product Sizes Table
CREATE TABLE IF NOT EXISTS `product_sizes` (
    `id`         INT AUTO_INCREMENT PRIMARY KEY,
    `product_id` INT NOT NULL,
    `label`      VARCHAR(100) NOT NULL COMMENT 'e.g. 350ml, 750ml, 1.5L',
    `price`      DECIMAL(10,2) NOT NULL,
    `stock`      INT NOT NULL DEFAULT 0,
    `is_default` TINYINT(1) NOT NULL DEFAULT 0,
    `sort_order` INT NOT NULL DEFAULT 0,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
    INDEX `idx_product_sizes_product_id` (`product_id`)
) ENGINE=InnoDB;

ALTER TABLE `order_items`
    ADD COLUMN IF NOT EXISTS `size_id`    INT NULL AFTER `product_id`,
    ADD COLUMN IF NOT EXISTS `size_label` VARCHAR(100) NULL AFTER `size_id`;
