<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');
require_once '../classes/Cart.php';
require_once '../classes/Order.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$cart = new Cart();
if ($cart->isEmpty()) {
    echo json_encode(['success' => false, 'message' => 'Your cart is empty.']);
    exit;
}

try {
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
    $userId = $_SESSION['user_id'] ?? null;

    $cartItems = $cart->getItems();

    // Build items array for Order::create()
    $orderItems = [];
    foreach ($cartItems as $item) {
        $orderItems[] = [
            'product_id' => $item['product_id'],
            'name' => $item['name'],
            'color' => $item['color'],
            'size' => $item['size'],
            'quantity' => $item['quantity'],
            'price' => $item['price']
        ];
    }

    $orderModel = new Order();
    $result = $orderModel->createFromCart($userId, $shippingName, $phone, $address, $city, 'Standard', $paymentMethod, $orderItems);

    if ($result) {
        $orderId = $result['order_id'];
        $orderNumber = $result['order_number'];
        $cart->clear();
        echo json_encode(['success' => true, 'order_id' => $orderId, 'order_number' => $orderNumber]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to create order. Please try again.']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
