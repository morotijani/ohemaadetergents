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
if ($_SERVER['REQUEST_METHOD'] !== 'POST') Helpers::jsonResponse(405, 'Method Not Allowed');

$admin = Auth::requireAdmin();
$db = Database::getInstance()->getConnection();

$name = trim($_POST['name'] ?? '');
$slug = trim($_POST['slug'] ?? '');
$description = trim($_POST['description'] ?? '');
$price = (float)($_POST['price'] ?? 0);
$stock = (int)($_POST['stock'] ?? 0);
$isFeatured = isset($_POST['is_featured']) && $_POST['is_featured'] ? 1 : 0;
$categoryId = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;

if (empty($name) || empty($slug) || empty($price)) {
    Helpers::jsonResponse(400, 'Name, slug, and price are required');
}

$imageUrl = null;
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];
    $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($fileInfo, $_FILES['image']['tmp_name']);
    finfo_close($fileInfo);

    if (!in_array($mimeType, $allowedMimeTypes)) {
        Helpers::jsonResponse(400, 'Invalid image format. Only JPG, PNG, and WEBP are allowed.');
    }

    $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
    $filename = uniqid('prod_') . '.' . $ext;
    $uploadDir = __DIR__ . '/../../public/uploads/products/';
    
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $filename)) {
        $imageUrl = 'public/uploads/products/' . $filename;
    } else {
        Helpers::jsonResponse(500, 'Failed to upload image');
    }
}

try {
    $stmt = $db->prepare("INSERT INTO products (product_id, category_id, name, slug, description, price, stock, image_url, is_featured) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $productIdBin = Helpers::generateUuidV7Binary();
    $stmt->execute([
        $productIdBin,
        $categoryId,
        $name,
        $slug,
        $description,
        $price,
        $stock,
        $imageUrl,
        $isFeatured
    ]);
    
    Helpers::logAction($db, 'create_product', "Created product: $name", $admin['admin_id']);
    
    Helpers::jsonResponse(201, 'Product created successfully', [
        'product_id' => Helpers::uuidBinToStr($productIdBin)
    ]);
} catch (\PDOException $e) {
    if ($e->getCode() == 23000) { 
        Helpers::jsonResponse(400, 'A product with this slug already exists.');
    }
    Helpers::jsonResponse(500, 'Database error: ' . $e->getMessage());
}
