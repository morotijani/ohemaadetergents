<?php

require_once __DIR__ . '/src/Database.php';
require_once __DIR__ . '/src/Helpers.php';

use App\Database;
use App\Helpers;

try {
    $db = Database::getInstance()->getConnection();

    $name = 'Admin';
    $email = 'admin@ohemaadetergents.com';
    $password = 'password123';
    $hash = password_hash($password, PASSWORD_DEFAULT);

    $adminIdBin = Helpers::generateUuidV7Binary();

    $stmt = $db->prepare("INSERT INTO admins (admin_id, name, email, password_hash) VALUES (?, ?, ?, ?)");
    $stmt->execute([$adminIdBin, $name, $email, $hash]);

    echo "Admin created successfully!\n";
    echo "Email: $email\n";
    echo "Password: $password\n";

} catch (\PDOException $e) {
    if ($e->getCode() == 23000) {
        echo "Admin with email $email already exists.\n";
    } else {
        echo "Error: " . $e->getMessage() . "\n";
    }
} catch (\Exception $e) {
    echo "General Error: " . $e->getMessage() . "\n";
}
