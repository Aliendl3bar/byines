<?php
require_once 'Database.php';

class Order {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }

    /**
     * Create a new customer order.
     * Uses a database transaction to ensure atomicity.
     * @return int|false The new order ID, or false on failure.
     */
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
                // Insert item
                $stmtItem->execute([$orderId, $item['variant_id'], $item['quantity'], $item['price']]);

                // Deduct inventory
                $stmtStock->execute([$item['quantity'], $item['variant_id'], $item['quantity']]);
                if ($stmtStock->rowCount() === 0) {
                    throw new Exception("Insufficient stock for variant ID " . $item['variant_id']);
                }
            }

            $this->pdo->commit();
            return $orderId;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }

    /**
     * Fetch order details including ordered items.
     * @return array|null
     */
    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt->execute([$id]);
        $order = $stmt->fetch();

        if (!$order) {
            return null;
        }

        // Get items associated with this order
        $stmtItems = $this->pdo->prepare("
            SELECT oi.id, oi.variant_id, oi.quantity, oi.price,
                   pv.color, pv.size, p.name as product_name, p.sku
            FROM order_items oi
            JOIN product_variants pv ON oi.variant_id = pv.id
            JOIN products p ON pv.product_id = p.id
            WHERE oi.order_id = ?
        ");
        $stmtItems->execute([$id]);
        $order['items'] = $stmtItems->fetchAll();

        return $order;
    }

    /**
     * Get order history of a specific user.
     * @return array
     */
    public function getByUser($userId) {
        $stmt = $this->pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    /**
     * Get all recent orders (usually for Admin Panel).
     * @return array
     */
    public function getAll($limit = 50) {
        $stmt = $this->pdo->prepare("SELECT * FROM orders ORDER BY created_at DESC LIMIT ?");
        $stmt->bindValue(1, (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Update order shipment/processing status.
     * @return bool
     */
    public function updateStatus($id, $status) {
        $stmt = $this->pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }
}
