<?php

require_once __DIR__ . '/src/Database.php';

try {
    // Attempt to get a database connection
    $db = \App\Database::getInstance()->getConnection();
    
    // Test the connection by running a simple query
    $stmt = $db->query("SELECT DATABASE() as db_name");
    $result = $stmt->fetch();
    
    echo "Successfully connected to the database!\n";
    echo "Current database: " . $result['db_name'] . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Note: If you haven't run the database/schema.sql script yet, the 'ohemaa_db' might not exist.\n";
}
