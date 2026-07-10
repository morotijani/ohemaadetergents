<?php
require 'config/config.php';
require 'src/Database.php';
$db = App\Database::getInstance()->getConnection();

$sql = "CREATE TABLE IF NOT EXISTS stockist_applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    application_id BINARY(16) NOT NULL UNIQUE,
    owner_name VARCHAR(255) NOT NULL,
    phone VARCHAR(50) NOT NULL,
    shop_name VARCHAR(255) NOT NULL,
    business_type VARCHAR(100) NOT NULL,
    region VARCHAR(100) NOT NULL,
    town_area VARCHAR(255) NOT NULL,
    monthly_volume VARCHAR(100) NOT NULL,
    document_path VARCHAR(255) NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";

$db->exec($sql);
echo "Table created.";
