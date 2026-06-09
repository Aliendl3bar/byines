<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../classes/Order.php';

$orderId = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
$order = null;

if ($orderId > 0) {
    try {
        $orderModel = new Order();
        $order = $orderModel->getById($orderId);
    } catch (Exception $e) {
        // Handle silently
    }
}

if (!$order) {
    header("Location: index.php");
    exit;
}

$pageTitle = 'Order Success';
include '../includes/header.php'; 
?>
<main class="order-success-page">
    <span class="material-symbols-outlined order-success-icon">check_circle</span>
    <h1 class="order-success-title">Thank you for your order!</h1>
    <p class="order-success-message">Your order has been placed successfully and is now being processed.</p>
    
    <div class="order-summary-card">
        <h2>Order Summary</h2>
        
        <div class="order-summary-grid">
            <div>
                <strong class="order-summary-label">Order Number</strong>
                <span><?= htmlspecialchars($order['order_number']) ?></span>
            </div>
            <div>
                <strong class="order-summary-label">Date</strong>
                <span><?= date('F j, Y', strtotime($order['created_at'])) ?></span>
            </div>
            <div>
                <strong class="order-summary-label">Payment Method</strong>
                <span class="text-capitalize"><?= str_replace('_', ' ', htmlspecialchars($order['payment_method'])) ?></span>
            </div>
            <div>
                <strong class="order-summary-label">Total Amount</strong>
                <span class="order-summary-value">$<?= number_format($order['total_amount'], 2) ?></span>
            </div>
        </div>

        <h3 class="order-shipping-title">Shipping Details</h3>
        <p class="order-shipping-details">
            <?= htmlspecialchars($order['shipping_name']) ?><br>
            <?= htmlspecialchars($order['shipping_address_line1']) ?><br>
            <?= htmlspecialchars($order['shipping_city']) ?>, Morocco<br>
            Phone: <?= htmlspecialchars($order['shipping_phone']) ?>
        </p>
    </div>

    <a href="shop.php" class="btn-primary continue-shopping-link">Continue Shopping</a>
</main>
<?php include '../includes/footer.php'; ?>
