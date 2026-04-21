<?php
require_once __DIR__ . '/src/Cart.php';
require_once __DIR__ . '/src/Helpers.php';

use App\Cart;
use App\Helpers;

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

switch ($action) {
    case 'add':
        $cart->add($productId, $qty);
        Helpers::jsonResponse(200, 'Added to cart', ['count' => $cart->count()]);
        break;
    case 'update':
        $cart->update($productId, $qty);
        Helpers::jsonResponse(200, 'Cart updated', ['count' => $cart->count()]);
        break;
    case 'remove':
        $cart->remove($productId);
        Helpers::jsonResponse(200, 'Removed from cart', ['count' => $cart->count()]);
        break;
    default:
        Helpers::jsonResponse(400, 'Invalid action');
}
