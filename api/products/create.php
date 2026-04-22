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
$description = trim($_POST['description'] ?? '');
$price = (float)($_POST['price'] ?? 0);
$stock = (int)($_POST['stock'] ?? 0);
$isFeatured = isset($_POST['is_featured']) && $_POST['is_featured'] ? 1 : 0;
$categoryId = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;

if (empty($name) || empty($price)) {
    Helpers::jsonResponse(400, 'Name and price are required');
}

$slug = Helpers::slugify($name);
$stmt = $db->prepare("SELECT id FROM products WHERE slug = ?");
$stmt->execute([$slug]);
if ($stmt->fetch()) {
    $slug .= '-' . substr(uniqid(), -4);
}

$uploadedImages = [];
if (isset($_FILES['images']) && is_array($_FILES['images']['name'])) {
    $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];
    $uploadDir = __DIR__ . '/../../public/uploads/products/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $count = count($_FILES['images']['name']);
    for ($i = 0; $i < $count; $i++) {
        if ($_FILES['images']['error'][$i] === UPLOAD_ERR_OK) {
            $tmpName = $_FILES['images']['tmp_name'][$i];
            
            $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($fileInfo, $tmpName);
            finfo_close($fileInfo);

            if (in_array($mimeType, $allowedMimeTypes)) {
                $ext = pathinfo($_FILES['images']['name'][$i], PATHINFO_EXTENSION);
                $filename = uniqid('prod_') . '.' . $ext;
                if (move_uploaded_file($tmpName, $uploadDir . $filename)) {
                    $uploadedImages[] = 'public/uploads/products/' . $filename;
                }
            }
        }
    }
}

$imageUrl = $uploadedImages[0] ?? null;

try {
    $db->beginTransaction();
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
    
    $productId = $db->lastInsertId();
    
    for ($i = 1; $i < count($uploadedImages); $i++) {
        $stmtImg = $db->prepare("INSERT INTO product_images (product_id, image_url) VALUES (?, ?)");
        $stmtImg->execute([$productId, $uploadedImages[$i]]);
    }
    
    $db->commit();
    Helpers::logAction($db, 'create_product', "Created product: $name", $admin['admin_id']);
    
    Helpers::jsonResponse(201, 'Product created successfully', [
        'product_id' => Helpers::uuidBinToStr($productIdBin)
    ]);
} catch (\Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    if ($e->getCode() == 23000) { 
        Helpers::jsonResponse(400, 'A product with this slug already exists.');
    }
    Helpers::jsonResponse(500, 'Database error: ' . $e->getMessage());
}
