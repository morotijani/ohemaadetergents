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

    /**
     * Generate a unique cart key.
     * For sized products: "productId:sizeId"
     * For plain products: "productId"
     */
    private function key(int $productId, ?int $sizeId = null): string
    {
        return $sizeId ? "{$productId}:{$sizeId}" : (string)$productId;
    }

    public function add(int $productId, int $qty = 1, ?int $sizeId = null, ?string $sizeLabel = null, ?float $sizePrice = null)
    {
        $key = $this->key($productId, $sizeId);
        if (isset($_SESSION['cart'][$key])) {
            $_SESSION['cart'][$key]['qty'] += $qty;
        } else {
            $_SESSION['cart'][$key] = [
                'product_id' => $productId,
                'qty'        => $qty,
                'size_id'    => $sizeId,
                'size_label' => $sizeLabel,
                'size_price' => $sizePrice,
            ];
        }
    }

    public function update(int $productId, int $qty, ?int $sizeId = null)
    {
        $key = $this->key($productId, $sizeId);
        if ($qty <= 0) {
            $this->remove($productId, $sizeId);
        } else {
            if (isset($_SESSION['cart'][$key])) {
                $_SESSION['cart'][$key]['qty'] = $qty;
            }
        }
    }

    public function remove(int $productId, ?int $sizeId = null)
    {
        $key = $this->key($productId, $sizeId);
        unset($_SESSION['cart'][$key]);
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
        $total = 0;
        foreach ($_SESSION['cart'] as $item) {
            $total += is_array($item) ? (int)$item['qty'] : (int)$item;
        }
        return $total;
    }
}
