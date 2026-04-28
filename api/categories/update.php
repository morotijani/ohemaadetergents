<?php
require_once __DIR__ . '/../../src/Database.php';
require_once __DIR__ . '/../../src/Helpers.php';
require_once __DIR__ . '/../../src/Auth.php';

use App\Database;
use App\Helpers;
use App\Auth;

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

$admin = Auth::requireAdmin();

$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'] ?? null;
$name = trim($data['name'] ?? '');
$description = trim($data['description'] ?? '');

if (!$id || empty($name)) {
    Helpers::jsonResponse(400, 'ID and Name are required');
}

$slug = Helpers::slugify($name);

try {
    $db = Database::getInstance()->getConnection();
    
    // Check if slug exists for other categories
    $stmt = $db->prepare("SELECT id FROM categories WHERE slug = ? AND id != ?");
    $stmt->execute([$slug, $id]);
    if ($stmt->fetch()) {
        $slug .= '-' . rand(100, 999);
    }

    $stmt = $db->prepare("UPDATE categories SET name = ?, slug = ?, description = ? WHERE id = ?");
    $stmt->execute([$name, $slug, $description, $id]);

    Helpers::jsonResponse(200, 'Category updated successfully');
} catch (\Exception $e) {
    Helpers::jsonResponse(500, 'Server error');
}
