-- Create Settings Table
CREATE TABLE IF NOT EXISTS `settings` (
    `setting_key` VARCHAR(50) PRIMARY KEY,
    `setting_value` TEXT,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Initial Seeds
INSERT IGNORE INTO `settings` (`setting_key`, `setting_value`) VALUES 
('store_name', 'Ohemaa Detergents'),
('contact_email', 'info@ohemaadetergents.com'),
('contact_phone', '+233 24 000 0000'),
('contact_address', 'Accra, Ghana'),
('paystack_public_key', 'YOUR_PAYSTACK_PUBLIC_KEY'),
('paystack_secret_key', 'YOUR_PAYSTACK_SECRET_KEY');
