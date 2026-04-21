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

$id = (int)($_POST['id'] ?? 0);
if (!$id) Helpers::jsonResponse(400, 'Product ID is required');

$name = trim($_POST['name'] ?? '');
$slug = trim($_POST['slug'] ?? '');
$description = trim($_POST['description'] ?? '');
$price = (float)($_POST['price'] ?? 0);
$stock = (int)($_POST['stock'] ?? 0);

$stmt = $db->prepare("SELECT image_url FROM products WHERE id = ?");
$stmt->execute([$id]);
$currentProduct = $stmt->fetch();

if (!$currentProduct) Helpers::jsonResponse(404, 'Product not found');

$imageUrl = $currentProduct['image_url'];

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
    
    if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $filename)) {
        if ($imageUrl && file_exists(__DIR__ . '/../../' . $imageUrl)) {
            unlink(__DIR__ . '/../../' . $imageUrl);
        }
        $imageUrl = 'public/uploads/products/' . $filename;
    }
}

try {
    $stmt = $db->prepare("UPDATE products SET name=?, slug=?, description=?, price=?, stock=?, image_url=? WHERE id=?");
    $stmt->execute([$name, $slug, $description, $price, $stock, $imageUrl, $id]);
    
    Helpers::logAction($db, 'update_product', "Updated product: $name (ID: $id)", $admin['admin_id']);
    
    Helpers::jsonResponse(200, 'Product updated successfully');
} catch (\PDOException $e) {
    if ($e->getCode() == 23000) Helpers::jsonResponse(400, 'A product with this slug already exists.');
    Helpers::jsonResponse(500, 'Database error');
}
