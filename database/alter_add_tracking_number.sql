ALTER TABLE `orders` ADD COLUMN `tracking_number` VARCHAR(50) UNIQUE AFTER `order_id`;
