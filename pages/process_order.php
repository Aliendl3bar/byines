<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');
require_once '../classes/Database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$cartItems = $_SESSION['cart'] ?? [];
if (empty($cartItems)) {
    echo json_encode(['success' => false, 'message' => 'Your cart is empty.']);
    exit;
}

try {
    $db = Database::getInstance()->getConnection();

    // Collect shipping details
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $paymentMethod = $_POST['payment_method'] ?? 'cash_on_delivery';

    if (!$firstName || !$lastName || !$email || !$phone || !$address || !$city) {
        echo json_encode(['success' => false, 'message' => 'Please fill out all required fields.']);
        exit;
    }

    $shippingName = $firstName . ' ' . $lastName;
    $shippingCountry = 'Morocco';
    
    // Calculate totals securely on server
    $subtotal = 0;
    foreach ($cartItems as $item) {
        $subtotal += ($item['price'] * $item['quantity']);
    }
    
    $tax = round($subtotal * 0.10, 2);
    
    // Shipping logic
    $cityLower = strtolower($city);
    $shippingCost = 0.00;
    if ($cityLower !== 'tangier' && $cityLower !== 'tanger') {
        $shippingCost = 3.00;
    }

    $totalAmount = $subtotal + $tax + $shippingCost;

    // Generate unique order number
    $orderNumber = 'ORD-' . strtoupper(uniqid()) . rand(100, 999);
    $userId = $_SESSION['user_id'] ?? null; // Null if guest

    $db->beginTransaction();

    // Insert order
    $stmt = $db->prepare("
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
        $shippingName, $phone, $address,
        $city, $shippingCountry,
        $paymentMethod
    ]);

    $orderId = $db->lastInsertId();

    // Insert order items & reduce stock
    $itemStmt = $db->prepare("INSERT INTO order_items (order_id, variant_id, quantity, price) VALUES (?, ?, ?, ?)");
    $stockStmt = $db->prepare("UPDATE product_variants SET stock_quantity = GREATEST(stock_quantity - ?, 0) WHERE product_id = ? AND color = ? AND size = ?");

    foreach ($cartItems as $item) {
        // We need variant_id for order_items, let's fetch it based on product_id, color, size
        $varStmt = $db->prepare("SELECT id FROM product_variants WHERE product_id = ? AND color = ? AND size = ? LIMIT 1");
        $varStmt->execute([$item['product_id'], $item['color'], $item['size']]);
        $variantId = $varStmt->fetchColumn();

        if ($variantId) {
            $itemStmt->execute([$orderId, $variantId, $item['quantity'], $item['price']]);
            // Reduce stock
            $stockStmt->execute([$item['quantity'], $item['product_id'], $item['color'], $item['size']]);
        }
    }

    $db->commit();

    // Clear cart
    unset($_SESSION['cart']);

    echo json_encode(['success' => true, 'order_id' => $orderId, 'order_number' => $orderNumber]);

} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
