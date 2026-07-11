<?php
require 'src/Database.php';
$db = \App\Database::getInstance()->getConnection();

try {
    $db->exec("CREATE TABLE IF NOT EXISTS wholesale_products (
        id INT AUTO_INCREMENT PRIMARY KEY,
        product_key VARCHAR(50) NOT NULL UNIQUE,
        name VARCHAR(255) NOT NULL,
        bottle_type VARCHAR(255) NOT NULL,
        carton_size INT NOT NULL,
        tier1_min INT NOT NULL DEFAULT 1,
        tier1_max INT NOT NULL DEFAULT 9,
        tier1_price DECIMAL(10,2) NOT NULL,
        tier2_min INT NOT NULL DEFAULT 10,
        tier2_max INT NOT NULL DEFAULT 49,
        tier2_price DECIMAL(10,2) NOT NULL,
        tier3_min INT NOT NULL DEFAULT 50,
        tier3_max INT NOT NULL DEFAULT 999999,
        tier3_price DECIMAL(10,2) NOT NULL,
        status ENUM('active', 'inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    echo "Table wholesale_products created successfully.\n";

    // Insert initial data based on app.js wholesaleProducts
    $stmt = $db->prepare("INSERT IGNORE INTO wholesale_products 
        (product_key, name, bottle_type, carton_size, tier1_price, tier2_price, tier3_price) VALUES 
        (?, ?, ?, ?, ?, ?, ?)");
        
    $initialData = [
        ['msc', 'Multi-Surface Cleaner', '750ml bottles', 12, 24.00, 21.00, 18.00],
        ['ld',  'Liquid Detergent',       '1L bottles', 12, 30.00, 26.00, 23.00],
        ['dw',  'Dishwashing Liquid',     '500ml bottles', 24, 16.00, 14.00, 12.00],
        ['fs',  'Fabric Softener',        '750ml bottles', 12, 22.00, 19.00, 17.00],
        ['db',  'Disinfectant Bleach',    '1L bottles', 12, 18.00, 16.00, 14.00],
    ];

    foreach($initialData as $data) {
        $stmt->execute($data);
    }
    echo "Initial data inserted successfully.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
