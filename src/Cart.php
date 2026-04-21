<?php
namespace App;

class Cart
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
    }

    public function add(int $productId, int $qty = 1)
    {
        if (isset($_SESSION['cart'][$productId])) {
            $_SESSION['cart'][$productId] += $qty;
        } else {
            $_SESSION['cart'][$productId] = $qty;
        }
    }

    public function update(int $productId, int $qty)
    {
        if ($qty <= 0) {
            $this->remove($productId);
        } else {
            $_SESSION['cart'][$productId] = $qty;
        }
    }

    public function remove(int $productId)
    {
        unset($_SESSION['cart'][$productId]);
    }

    public function clear()
    {
        $_SESSION['cart'] = [];
    }

    public function getItems(): array
    {
        return $_SESSION['cart'];
    }

    public function count(): int
    {
        return array_sum($_SESSION['cart']);
    }
}
