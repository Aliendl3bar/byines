<?php
require_once 'Database.php';

class Order {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }

    /** Create a new order with transaction. @return int|false */
    public function create($userId, $shippingName, $shippingPhone, $addressLine1, $addressLine2, $city, $state, $zip, $country, $shippingMethod, $paymentMethod, $items) {
        try {
            $this->pdo->beginTransaction();

            // 1. Generate unique order number (e.g. BYINES-12345678)
            $orderNumber = 'BYINES-' . strtoupper(bin2hex(random_bytes(4)));

            // 2. Calculate totals
            $subtotal = 0;
            foreach ($items as $item) {
                $subtotal += $item['price'] * $item['quantity'];
            }
            $tax = $subtotal * 0.10; // Flat 10% tax rate
            $shippingCost = $subtotal > 100 ? 0.00 : 15.00; // Free shipping over $100
            $totalAmount = $subtotal + $tax + $shippingCost;

            // 3. Insert order record
            $stmt = $this->pdo->prepare("
                INSERT INTO orders (
                    user_id, order_number, subtotal, tax, shipping_cost, total_amount, status,
                    shipping_name, shipping_phone, shipping_address_line1, shipping_address_line2,
                    shipping_city, shipping_state, shipping_zip, shipping_country,
                    shipping_method, payment_method, payment_status
                ) VALUES (?, ?, ?, ?, ?, ?, 'pending', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'unpaid')
            ");

            $stmt->execute([
                $userId ?: null, $orderNumber, $subtotal, $tax, $shippingCost, $totalAmount,
                $shippingName, $shippingPhone, $addressLine1, $addressLine2,
                $city, $state, $zip, $country,
                $shippingMethod, $paymentMethod
            ]);

            $orderId = $this->pdo->lastInsertId();

            // 4. Insert order items & deduct variant stock
            $stmtItem = $this->pdo->prepare("
                INSERT INTO order_items (order_id, variant_id, quantity, price)
                VALUES (?, ?, ?, ?)
            ");

            $stmtStock = $this->pdo->prepare("
                UPDATE product_variants
                SET stock_quantity = stock_quantity - ?
                WHERE id = ? AND stock_quantity >= ?
            ");

            foreach ($items as $item) {
                $variantId = $item['variant_id'] ?? $this->getVariantId($item['product_id'], $item['color'], $item['size']);
                if (!$variantId) {
                    throw new Exception("Variant not found for product: " . ($item['name'] ?? 'unknown'));
                }
                $stmtItem->execute([$orderId, $variantId, $item['quantity'], $item['price']]);
                $stmtStock->execute([$item['quantity'], $variantId, $item['quantity']]);
                if ($stmtStock->rowCount() === 0) {
                    throw new Exception("Insufficient stock for variant ID " . $variantId);
                }
            }

            $this->pdo->commit();
            return $orderId;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }

    /** Create order from cart items. @return array|false */
    public function createFromCart($userId, $shippingName, $shippingPhone, $addressLine1, $city, $shippingMethod, $paymentMethod, $items) {
        try {
            $this->pdo->beginTransaction();

            $orderNumber = 'BYINES-' . strtoupper(bin2hex(random_bytes(4)));

            $subtotal = 0;
            foreach ($items as $item) {
                $subtotal += $item['price'] * $item['quantity'];
            }

            $tax = round($subtotal * 0.10, 2);
            $cityLower = strtolower($city);
            $shippingCost = ($cityLower === 'tangier' || $cityLower === 'tanger') ? 0.00 : 3.00;
            $totalAmount = $subtotal + $tax + $shippingCost;
            $shippingCountry = 'Morocco';

            $stmt = $this->pdo->prepare("
                INSERT INTO orders (
                    user_id, order_number, subtotal, tax, shipping_cost, total_amount,
                    status, shipping_name, shipping_phone, shipping_address_line1,
                    shipping_city, shipping_state, shipping_zip, shipping_country,
                    shipping_method, payment_method, payment_status
                ) VALUES (
                    ?, ?, ?, ?, ?, ?,
                    'pending', ?, ?, ?,
                    ?, '', '', ?,
                    'Standard', ?, 'unpaid'
                )
            ");

            $stmt->execute([
                $userId, $orderNumber, $subtotal, $tax, $shippingCost, $totalAmount,
                $shippingName, $shippingPhone, $addressLine1,
                $city, $shippingCountry,
                $paymentMethod
            ]);

            $orderId = $this->pdo->lastInsertId();

            $itemStmt = $this->pdo->prepare("INSERT INTO order_items (order_id, variant_id, quantity, price) VALUES (?, ?, ?, ?)");
            $stockStmt = $this->pdo->prepare("UPDATE product_variants SET stock_quantity = GREATEST(stock_quantity - ?, 0) WHERE product_id = ? AND color = ? AND size = ?");

            foreach ($items as $item) {
                $variantId = $this->getVariantId($item['product_id'], $item['color'], $item['size']);
                if ($variantId) {
                    $itemStmt->execute([$orderId, $variantId, $item['quantity'], $item['price']]);
                    $stockStmt->execute([$item['quantity'], $item['product_id'], $item['color'], $item['size']]);
                }
            }

            $this->pdo->commit();
            return ['order_id' => $orderId, 'order_number' => $orderNumber];
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }

    /** Fetch order details with items. @return array|null */
    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt->execute([$id]);
        $order = $stmt->fetch();

        if (!$order) {
            return null;
        }

        // get items associated with this order
        $stmtItems = $this->pdo->prepare("
            SELECT oi.id, oi.variant_id, oi.quantity, oi.price,
                   pv.product_id, pv.color, pv.size, p.name, p.sku
            FROM order_items oi
            JOIN product_variants pv ON oi.variant_id = pv.id
            JOIN products p ON pv.product_id = p.id
            WHERE oi.order_id = ?
        ");
        $stmtItems->execute([$id]);
        $order['items'] = $stmtItems->fetchAll();

        return $order;
    }

    /** Get orders by user. @return array */
    public function getByUser($userId) {
        $stmt = $this->pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    /** Get all recent orders. @return array */
    public function getAll($limit = 50) {
        $stmt = $this->pdo->prepare("SELECT * FROM orders ORDER BY created_at DESC LIMIT ?");
        $stmt->bindValue(1, (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /** Update order status. @return bool */
    public function updateStatus($id, $status) {
        $stmt = $this->pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }

    /** Delete an order. @return bool */
    public function deleteOrder($orderId) {
        try {
            $this->pdo->beginTransaction();
            $stmt = $this->pdo->prepare("DELETE FROM order_items WHERE order_id = ?");
            $stmt->execute([$orderId]);
            $stmt = $this->pdo->prepare("DELETE FROM orders WHERE id = ?");
            $stmt->execute([$orderId]);
            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }

    /** Get variant ID by product, color, and size. @return int|null */
    public function getVariantId($productId, $color, $size) {
        $stmt = $this->pdo->prepare("SELECT id FROM product_variants WHERE product_id = ? AND color = ? AND size = ? LIMIT 1");
        $stmt->execute([$productId, $color, $size]);
        return $stmt->fetchColumn() ?: null;
    }

    /** Get total order count. @return int */
    public function getTotalCount() {
        $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM orders");
        return (int)$stmt->fetch()['total'];
    }

    /** Get total revenue from paid orders. @return float */
    public function getTotalRevenue() {
        $stmt = $this->pdo->query("SELECT SUM(total_amount) as revenue FROM orders WHERE payment_status = 'paid'");
        return (float)($stmt->fetch()['revenue'] ?? 0.00);
    }
}
