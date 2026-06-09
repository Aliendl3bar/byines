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
<main style="max-width: 800px; margin: 4rem auto; padding: 2rem; text-align: center; font-family: var(--font-sans);">
    <span class="material-symbols-outlined" style="font-size: 5rem; color: #4CAF50; margin-bottom: 1rem;">check_circle</span>
    <h1 style="font-family: var(--font-serif); color: var(--brand-dark); font-size: 2.5rem; margin-bottom: 0.5rem;">Thank you for your order!</h1>
    <p style="color: var(--gray-500); font-size: 1.125rem; margin-bottom: 2rem;">Your order has been placed successfully and is now being processed.</p>
    
    <div style="background: white; padding: 2rem; border-radius: 0.5rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); margin-bottom: 2rem; text-align: left;">
        <h2 style="font-family: var(--font-serif); margin-top: 0; margin-bottom: 1.5rem; border-bottom: 1px solid var(--gray-300); padding-bottom: 0.5rem;">Order Summary</h2>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
            <div>
                <strong style="display: block; color: var(--gray-500); font-size: 0.875rem;">Order Number</strong>
                <span><?= htmlspecialchars($order['order_number']) ?></span>
            </div>
            <div>
                <strong style="display: block; color: var(--gray-500); font-size: 0.875rem;">Date</strong>
                <span><?= date('F j, Y', strtotime($order['created_at'])) ?></span>
            </div>
            <div>
                <strong style="display: block; color: var(--gray-500); font-size: 0.875rem;">Payment Method</strong>
                <span style="text-transform: capitalize;"><?= str_replace('_', ' ', htmlspecialchars($order['payment_method'])) ?></span>
            </div>
            <div>
                <strong style="display: block; color: var(--gray-500); font-size: 0.875rem;">Total Amount</strong>
                <span style="font-weight: bold; color: var(--brand-dark);">$<?= number_format($order['total_amount'], 2) ?></span>
            </div>
        </div>

        <h3 style="font-size: 1.125rem; margin-bottom: 1rem; color: var(--brand-dark);">Shipping Details</h3>
        <p style="margin: 0; color: var(--gray-600); line-height: 1.5;">
            <?= htmlspecialchars($order['shipping_name']) ?><br>
            <?= htmlspecialchars($order['shipping_address_line1']) ?><br>
            <?= htmlspecialchars($order['shipping_city']) ?>, Morocco<br>
            Phone: <?= htmlspecialchars($order['shipping_phone']) ?>
        </p>
    </div>

    <a href="shop.php" class="btn-primary" style="text-decoration: none; display: inline-block;">Continue Shopping</a>
</main>
<?php include '../includes/footer.php'; ?>
