<?php
try {
    $db = new PDO('mysql:host=127.0.0.1;dbname=ohemaa_db', 'root', '');
    $sql = "CREATE TABLE IF NOT EXISTS coupons (
        id INT AUTO_INCREMENT PRIMARY KEY,
        coupon_id BINARY(16) NOT NULL UNIQUE,
        code VARCHAR(50) NOT NULL UNIQUE,
        type ENUM('percentage', 'fixed') NOT NULL,
        value DECIMAL(10,2) NOT NULL,
        min_order_amount DECIMAL(10,2) DEFAULT 0.00,
        max_uses INT DEFAULT NULL,
        used_count INT DEFAULT 0,
        expiry_date DATE DEFAULT NULL,
        status ENUM('active', 'inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    
    $db->exec($sql);
    echo "Coupons table created successfully!\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
