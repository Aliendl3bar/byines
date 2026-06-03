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

    /**
     * Add a product variant to the cart.
     * @return bool True on success, false if variant doesn't exist.
     */
    public function add($variantId, $quantity = 1) {
        $variantId = (int)$variantId;
        $quantity = (int)$quantity;

        // Verify that the product variant exists
        $stmt = $this->pdo->prepare("SELECT id FROM product_variants WHERE id = ?");
        $stmt->execute([$variantId]);
        if (!$stmt->fetch()) {
            return false; 
        }

        if (isset($_SESSION['cart'][$variantId])) {
            $_SESSION['cart'][$variantId] += $quantity;
        } else {
            $_SESSION['cart'][$variantId] = $quantity;
        }
        return true;
    }

    /**
     * Update the quantity of a variant in the cart.
     * @return bool
     */
    public function updateQuantity($variantId, $quantity) {
        $variantId = (int)$variantId;
        $quantity = (int)$quantity;

        if ($quantity <= 0) {
            $this->remove($variantId);
            return true;
        }

        if (isset($_SESSION['cart'][$variantId])) {
            $_SESSION['cart'][$variantId] = $quantity;
            return true;
        }
        return false;
    }

    /**
     * Remove a variant from the cart.
     * @return bool
     */
    public function remove($variantId) {
        $variantId = (int)$variantId;
        if (isset($_SESSION['cart'][$variantId])) {
            unset($_SESSION['cart'][$variantId]);
            return true;
        }
        return false;
    }

    /**
     * Clear all items from the cart.
     */
    public function clear() {
        $_SESSION['cart'] = [];
    }

    /**
     * Get detailed info for all items in the cart.
     * @return array
     */
    public function getItems() {
        if (empty($_SESSION['cart'])) {
            return [];
        }

        $items = [];
        foreach ($_SESSION['cart'] as $variantId => $quantity) {
            // Fetch product and variant info with total final price calculation
            $stmt = $this->pdo->prepare("
                SELECT pv.id as variant_id, pv.color, pv.size, pv.stock_quantity, pv.price_modifier,
                       p.id as product_id, p.name, p.sku, p.price, p.description,
                       (p.price + pv.price_modifier) as final_price
                FROM product_variants pv
                JOIN products p ON pv.product_id = p.id
                WHERE pv.id = ?
            ");
            $stmt->execute([$variantId]);
            $details = $stmt->fetch();

            if ($details) {
                $details['quantity'] = $quantity;
                $details['subtotal'] = $details['final_price'] * $quantity;
                $items[] = $details;
            }
        }
        return $items;
    }

    /**
     * Calculate the final cart total amount.
     * @return float
     */
    public function getTotal() {
        $total = 0;
        $items = $this->getItems();
        foreach ($items as $item) {
            $total += $item['subtotal'];
        }
        return $total;
    }

    /**
     * Get the total number of items in the cart.
     * @return int
     */
    public function getCount() {
        return array_sum($_SESSION['cart']);
    }
}
