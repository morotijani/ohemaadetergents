<?php
require_once __DIR__ . '/../../src/Cart.php';
require_once __DIR__ . '/../../src/Helpers.php';
require_once __DIR__ . '/../../src/Database.php';

use App\Cart;
use App\Helpers;
use App\Database;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Helpers::jsonResponse(405, 'Method not allowed');
}

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$action = $input['action'] ?? '';
$productId = (int)($input['product_id'] ?? 0);
$qty = (int)($input['qty'] ?? 1);

if (!$action || !$productId) {
    Helpers::jsonResponse(400, 'Invalid request');
}

$cart = new Cart();

try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT stock FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();

    if (!$product) {
        Helpers::jsonResponse(404, 'Product not found');
    }

    $availableStock = (int)$product['stock'];
    $currentCartQty = $cart->getItems()[$productId] ?? 0;

    switch ($action) {
        case 'add':
            if ($currentCartQty + $qty > $availableStock) {
                Helpers::jsonResponse(400, "Cannot add. Only $availableStock available in stock, and you already have $currentCartQty in your bag.");
            }
            $cart->add($productId, $qty);
            Helpers::jsonResponse(200, 'Added to cart', ['count' => $cart->count()]);
            break;
        case 'update':
            if ($qty > $availableStock) {
                Helpers::jsonResponse(400, "Cannot update. Only $availableStock available in stock.");
            }
            $cart->update($productId, $qty);
            Helpers::jsonResponse(200, 'Cart updated', ['count' => $cart->count()]);
            break;
        case 'update_relative':
            $change = (int)($input['change'] ?? 0);
            $newQty = $currentCartQty + $change;
            if ($newQty < 1) {
                $cart->remove($productId);
                Helpers::jsonResponse(200, 'Item removed', ['count' => $cart->count()]);
                break;
            }
            if ($newQty > $availableStock) {
                Helpers::jsonResponse(400, "Cannot add more. Only $availableStock available in stock.");
            }
            $cart->update($productId, $newQty);
            Helpers::jsonResponse(200, 'Cart updated', ['count' => $cart->count()]);
            break;
        case 'remove':
            $cart->remove($productId);
            Helpers::jsonResponse(200, 'Removed from cart', ['count' => $cart->count()]);
            break;
        default:
            Helpers::jsonResponse(400, 'Invalid action');
    }
} catch (Exception $e) {
    Helpers::jsonResponse(500, 'Server error');
}
