<?php
require_once __DIR__ . "/../../src/Database.php";
require_once __DIR__ . "/../../src/Helpers.php";

use App\Database;
use App\Helpers;

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") exit;

$productId = (int)($_GET["product_id"] ?? 0);
if (!$productId) Helpers::jsonResponse(400, "product_id required");

try {
    $db   = Database::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT id, label, price, stock, is_default, sort_order FROM product_sizes WHERE product_id = ? ORDER BY sort_order ASC, id ASC");
    $stmt->execute([$productId]);
    $sizes = $stmt->fetchAll();
    Helpers::jsonResponse(200, "Sizes fetched", $sizes);
} catch (\Exception $e) {
    Helpers::jsonResponse(500, "Server error");
}
