<?php
require_once 'Database.php';

class Cart {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            session_start();
        }
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
    }

    /** Build a unique cart key from product id, color, and size. @return string */
    private function makeKey($productId, $color, $size) {
        return $productId . '_' . md5($color . '_' . $size);
    }

    public function add($productId, $color, $size, $quantity = 1) {
        $db = $this->pdo;
        $stmt = $db->prepare("SELECT v.*, p.price, p.name FROM product_variants v JOIN products p ON v.product_id = p.id WHERE v.product_id = ? AND v.color = ? AND v.size = ? LIMIT 1");
        $stmt->execute([$productId, $color, $size]);
        $variant = $stmt->fetch();

        if (!$variant) return false;

        $cartKey = $this->makeKey($productId, $color, $size);

        if (isset($_SESSION['cart'][$cartKey])) {
            $newQty = $_SESSION['cart'][$cartKey]['quantity'] + $quantity;
            if ($newQty > $variant['stock_quantity']) return false;
            $_SESSION['cart'][$cartKey]['quantity'] = $newQty;
        } else {
            if ($quantity > $variant['stock_quantity']) return false;
            $_SESSION['cart'][$cartKey] = [
                'product_id' => (int)$productId,
                'name' => $variant['name'],
                'color' => $color,
                'size' => $size,
                'quantity' => $quantity,
                'price' => (float)$variant['price'] + (float)$variant['price_modifier'],
                'stock' => (int)$variant['stock_quantity']
            ];
        }
        return true;
    }

    public function quickAdd($productId, $quantity = 1) {
        $stmt = $this->pdo->prepare("SELECT v.*, p.price, p.name FROM product_variants v JOIN products p ON v.product_id = p.id WHERE v.product_id = ? LIMIT 1");
        $stmt->execute([$productId]);
        $variant = $stmt->fetch();

        if (!$variant) return false;

        return $this->add($productId, $variant['color'], $variant['size'], $quantity);
    }

    public function updateQuantity($cartKey, $quantity) {
        if (!isset($_SESSION['cart'][$cartKey])) return false;

        if ($quantity <= 0) {
            $this->remove($cartKey);
            return true;
        }

        $item = $_SESSION['cart'][$cartKey];
        $stmt = $this->pdo->prepare("SELECT stock_quantity FROM product_variants WHERE product_id = ? AND color = ? AND size = ? LIMIT 1");
        $stmt->execute([$item['product_id'], $item['color'], $item['size']]);
        $stock = $stmt->fetchColumn();

        if ($quantity > $stock) return false;

        $_SESSION['cart'][$cartKey]['quantity'] = $quantity;
        return true;
    }

    public function remove($cartKey) {
        if (isset($_SESSION['cart'][$cartKey])) {
            unset($_SESSION['cart'][$cartKey]);
            return true;
        }
        return false;
    }

    public function clear() {
        $_SESSION['cart'] = [];
    }

    public function getItems() {
        return $_SESSION['cart'] ?? [];
    }

    public function getCount() {
        $count = 0;
        foreach ($_SESSION['cart'] as $item) {
            $count += $item['quantity'];
        }
        return $count;
    }

    public function getSubtotal() {
        $subtotal = 0;
        foreach ($_SESSION['cart'] as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }
        return $subtotal;
    }

    public function getTotal() {
        $subtotal = $this->getSubtotal();
        $tax = round($subtotal * 0.10, 2);
        return $subtotal + $tax;
    }

    public function getCartTotals() {
        $subtotal = $this->getSubtotal();
        $count = $this->getCount();
        $tax = round($subtotal * 0.10, 2);
        $total = $subtotal + $tax;

        return [
            'success' => true,
            'cartCount' => $count,
            'subtotal' => number_format($subtotal, 2),
            'tax' => number_format($tax, 2),
            'total' => number_format($total, 2)
        ];
    }

    public function isEmpty() {
        return empty($_SESSION['cart']);
    }
}
