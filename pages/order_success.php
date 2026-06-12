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
        // handle silently
    }
}

if (!$order) {
    header("Location: index.php");
    exit;
}

$pageTitle = 'Order Success';
include '../includes/header.php'; 
?>
<main class="order-success-container">
    <span class="material-symbols-outlined order-success-icon">check_circle</span>
    <h1 class="order-success-title">Thank you for your order!</h1>
    <p class="order-success-subtitle">Your order has been placed successfully and is now being processed.</p>
    
    <div class="order-success-card">
        <h2 class="order-success-card-title">Order Summary</h2>
        
        <div class="order-success-grid">
            <div>
                <strong class="order-success-label">Order Number</strong>
                <span><?= htmlspecialchars($order['order_number']) ?></span>
            </div>
            <div>
                <strong class="order-success-label">Date</strong>
                <span><?= date('F j, Y', strtotime($order['created_at'])) ?></span>
            </div>
            <div>
                <strong class="order-success-label">Payment Method</strong>
                <span class="order-success-text-capitalize"><?= str_replace('_', ' ', htmlspecialchars($order['payment_method'])) ?></span>
            </div>
            <div>
                <strong class="order-success-label">Total Amount</strong>
                <span class="order-success-value">$<?= number_format($order['total_amount'], 2) ?></span>
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

    <div class="order-success-actions">
        <a href="shop.php" class="btn-primary order-success-btn">Continue Shopping</a>
        <a href="https://wa.me/212654873611?text=Hello%20Byines%2C%20I%20just%20placed%20an%20order%20(%23<?= urlencode($order['order_number']) ?>)%20and%20would%20like%20to%20verify%20it." target="_blank" rel="noopener" class="btn-whatsapp order-success-btn">
            Verify Order via WhatsApp
        </a>
    </div>
</main>
<?php include '../includes/footer.php'; ?>
