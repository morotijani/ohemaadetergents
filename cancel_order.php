<?php
require_once __DIR__ . '/src/Database.php';

use App\Database;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reference'])) {
    $reference = $_POST['reference'];
    
    $db = Database::getInstance()->getConnection();
    
    // Only delete if it's strictly still "pending" (prevent deleting paid orders)
    $stmt = $db->prepare("DELETE FROM orders WHERE tracking_number = ? AND status = 'pending'");
    $stmt->execute([$reference]);
    
    echo json_encode(['success' => true]);
    exit;
}

http_response_code(400);
echo json_encode(['success' => false, 'message' => 'Invalid request']);
