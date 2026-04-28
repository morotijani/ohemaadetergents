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
$name = trim($data['name'] ?? '');
$description = trim($data['description'] ?? '');

if (empty($name)) {
    Helpers::jsonResponse(400, 'Name is required');
}

$slug = Helpers::slugify($name);

try {
    $db = Database::getInstance()->getConnection();
    
    // Check if slug exists
    $stmt = $db->prepare("SELECT id FROM categories WHERE slug = ?");
    $stmt->execute([$slug]);
    if ($stmt->fetch()) {
        $slug .= '-' . rand(100, 999);
    }

    $uuid = Helpers::generateUuidV7Binary();
    $stmt = $db->prepare("INSERT INTO categories (category_id, name, slug, description) VALUES (?, ?, ?, ?)");
    $stmt->execute([$uuid, $name, $slug, $description]);

    Helpers::jsonResponse(201, 'Category created successfully');
} catch (\Exception $e) {
    Helpers::jsonResponse(500, 'Server error: ' . $e->getMessage());
}
