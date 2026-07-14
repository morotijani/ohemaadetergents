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
$db    = Database::getInstance()->getConnection();

$name           = trim($_POST['name'] ?? '');
$description    = trim($_POST['description'] ?? '');
$price          = (float)($_POST['price'] ?? 0);
$stock          = (int)($_POST['stock'] ?? 0);
$stockThreshold = isset($_POST['stock_threshold']) && $_POST['stock_threshold'] !== '' ? (int)$_POST['stock_threshold'] : 5;
$isFeatured     = isset($_POST['is_featured']) && $_POST['is_featured'] ? 1 : 0;
$categoryId     = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;

// Parse sizes JSON if provided
$sizesRaw  = $_POST['sizes'] ?? '[]';
$sizes     = json_decode($sizesRaw, true) ?? [];
$hasSizes  = !empty($sizes);

if (empty($name)) {
    Helpers::jsonResponse(400, 'Name is required');
}

// If sizes provided, derive base price from default size
if ($hasSizes) {
    $defaultSize = null;
    foreach ($sizes as $s) {
        if (!empty($s['is_default'])) { $defaultSize = $s; break; }
    }
    if (!$defaultSize) $defaultSize = $sizes[0];
    $price = (float)($defaultSize['price'] ?? 0);
    $stock = 0; // total stock will be sum of size stocks
} else {
    if (empty($price)) Helpers::jsonResponse(400, 'Price is required');
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
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    $count = count($_FILES['images']['name']);
    for ($i = 0; $i < $count; $i++) {
        if ($_FILES['images']['error'][$i] === UPLOAD_ERR_OK) {
            $tmpName  = $_FILES['images']['tmp_name'][$i];
            $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($fileInfo, $tmpName);
            finfo_close($fileInfo);

            if (in_array($mimeType, $allowedMimeTypes)) {
                $ext      = pathinfo($_FILES['images']['name'][$i], PATHINFO_EXTENSION);
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

    $stmt         = $db->prepare("INSERT INTO products (product_id, category_id, name, slug, description, price, stock, stock_threshold, image_url, is_featured) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $productIdBin = Helpers::generateUuidV7Binary();
    $stmt->execute([$productIdBin, $categoryId, $name, $slug, $description, $price, $stock, $stockThreshold, $imageUrl, $isFeatured]);
    $productId = $db->lastInsertId();

    // Extra images
    for ($i = 1; $i < count($uploadedImages); $i++) {
        $stmtImg = $db->prepare("INSERT INTO product_images (product_id, image_url) VALUES (?, ?)");
        $stmtImg->execute([$productId, $uploadedImages[$i]]);
    }

    // Insert sizes
    if ($hasSizes) {
        $sortOrder = 0;
        foreach ($sizes as $s) {
            $sLabel     = trim($s['label'] ?? '');
            $sPrice     = (float)($s['price'] ?? 0);
            $sStock     = (int)($s['stock'] ?? 0);
            $sIsDefault = !empty($s['is_default']) ? 1 : 0;
            if (empty($sLabel) || $sPrice <= 0) continue;
            $stmtSz = $db->prepare("INSERT INTO product_sizes (product_id, label, price, stock, is_default, sort_order) VALUES (?, ?, ?, ?, ?, ?)");
            $stmtSz->execute([$productId, $sLabel, $sPrice, $sStock, $sIsDefault, $sortOrder++]);
        }
        // Keep products.stock = sum of size stocks
        $db->prepare("UPDATE products SET stock = (SELECT COALESCE(SUM(stock),0) FROM product_sizes WHERE product_id=?) WHERE id=?")->execute([$productId, $productId]);
    }

    $db->commit();
    Helpers::logAction($db, 'create_product', "Created product: $name", $admin['admin_id']);

    Helpers::jsonResponse(201, 'Product created successfully', [
        'product_id' => Helpers::uuidBinToStr($productIdBin)
    ]);
} catch (\Exception $e) {
    if ($db->inTransaction()) $db->rollBack();
    if ($e->getCode() == 23000) Helpers::jsonResponse(400, 'A product with this slug already exists.');
    Helpers::jsonResponse(500, 'Database error: ' . $e->getMessage());
}
