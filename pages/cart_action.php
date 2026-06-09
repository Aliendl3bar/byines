<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

require_once '../classes/Cart.php';

$action = $_POST['action'] ?? '';
$cart = new Cart();

try {
    if ($action === 'add' || $action === 'quick_add') {
        $productId = (int)$_POST['product_id'];
        $color = trim($_POST['color'] ?? '');
        $size = trim($_POST['size'] ?? '');
        $quantity = max(1, (int)($_POST['quantity'] ?? 1));

        $success = false;
        if ($action === 'quick_add') {
            $success = $cart->quickAdd($productId, $quantity);
        } else {
            $success = $cart->add($productId, $color, $size, $quantity);
        }

        if ($success) {
            echo json_encode(['success' => true, 'cartCount' => $cart->getCount(), 'message' => 'Item added to cart!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Variant not found or not enough stock.']);
        }
        exit;

    } elseif ($action === 'update') {
        $cartKey = $_POST['cart_key'] ?? '';
        $quantity = (int)$_POST['quantity'];

        if ($cart->updateQuantity($cartKey, $quantity)) {
            echo json_encode($cart->getCartTotals());
        } else {
            echo json_encode(['success' => false, 'message' => 'Item not in cart or quantity exceeds stock.']);
        }
        exit;

    } elseif ($action === 'remove') {
        $cartKey = $_POST['cart_key'] ?? '';
        if ($cart->remove($cartKey)) {
            echo json_encode($cart->getCartTotals());
        } else {
            echo json_encode(['success' => false, 'message' => 'Item not found.']);
        }
        exit;

    } elseif ($action === 'get_count') {
        echo json_encode(['success' => true, 'cartCount' => $cart->getCount()]);
        exit;
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid action.']);
