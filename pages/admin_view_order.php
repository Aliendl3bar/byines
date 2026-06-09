<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Basic Admin Auth Guard
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

require_once '../classes/Order.php';

$orderModel = new Order();

$orderId = $_GET['id'] ?? null;
if (!$orderId) {
    die('Order ID is required.');
}

// Handle Status Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $newStatus = $_POST['status'];
    $orderModel->updateStatus($orderId, $newStatus);
    header("Location: admin_view_order.php?id=" . $orderId . "&msg=updated");
    exit;
}

// Fetch order with items
$order = $orderModel->getById($orderId);

if (!$order) {
    die('Order not found.');
}

$items = $order['items'] ?? [];

$pageTitle = 'Manage Order #' . $order['order_number'];
include '../includes/header.php'; 
?>
<link rel="stylesheet" href="../css/admin.css">

<main class="admin-container">
    <div class="admin-sidebar">
        <!-- Re-using sidebar layout conceptually -->
        <h2 style="font-size: 1.5rem; color: var(--brand-dark); font-family: var(--font-serif); margin-bottom: 2rem;">Admin Panel</h2>
        <ul class="admin-nav" style="list-style: none; padding: 0;">
            <li style="margin-bottom: 0.5rem;"><a href="admin_dashboard.php#panel-overview" style="text-decoration:none; color: var(--gray-500);">Overview</a></li>
            <li style="margin-bottom: 0.5rem;"><a href="admin_dashboard.php#panel-products" style="text-decoration:none; color: var(--gray-500);">Products</a></li>
            <li style="margin-bottom: 0.5rem;"><a href="admin_dashboard.php#panel-orders" style="text-decoration:none; color: var(--brand-dark); font-weight: 600;">Orders</a></li>
        </ul>
    </div>

    <div class="admin-content">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h1 class="admin-page-title">Order <?= htmlspecialchars($order['order_number']) ?></h1>
            <a href="admin_dashboard.php#panel-orders" class="admin-btn admin-btn-sm" style="background: transparent; color: var(--brand-dark); border-color: var(--brand-dark);">Back to Orders</a>
        </div>

        <?php if(isset($_GET['msg']) && $_GET['msg'] === 'updated'): ?>
        <div style="background-color: #e8f5e9; color: #2e7d32; padding: 1rem; border-radius: 4px; margin-bottom: 1rem;">
            Order status updated successfully.
        </div>
        <?php endif; ?>

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
            
            <!-- Left Column: Items & Shipping -->
            <div>
                <div class="admin-panel" style="margin-bottom: 2rem; padding: 1.5rem; background: #fff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                    <h3 style="margin-top: 0; margin-bottom: 1rem; font-size: 1.125rem;">Order Items</h3>
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="border-bottom: 1px solid #eee; text-align: left; color: #666;">
                                <th style="padding-bottom: 0.5rem;">Item</th>
                                <th style="padding-bottom: 0.5rem;">Qty</th>
                                <th style="padding-bottom: 0.5rem;">Price</th>
                                <th style="padding-bottom: 0.5rem;">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                            <tr style="border-bottom: 1px solid #eee;">
                                <td style="padding: 1rem 0;">
                                    <strong><?= htmlspecialchars($item['name']) ?></strong><br>
                                    <span style="color: #666; font-size: 0.875rem;">Color: <?= htmlspecialchars($item['color']) ?> | Size: <?= htmlspecialchars($item['size']) ?></span>
                                </td>
                                <td style="padding: 1rem 0;"><?= $item['quantity'] ?></td>
                                <td style="padding: 1rem 0;">$<?= number_format($item['price'], 2) ?></td>
                                <td style="padding: 1rem 0;">$<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <div style="margin-top: 1.5rem; text-align: right;">
                        <p style="margin: 0.25rem 0;">Subtotal: $<?= number_format($order['subtotal'], 2) ?></p>
                        <p style="margin: 0.25rem 0;">Shipping: $<?= number_format($order['shipping_cost'], 2) ?></p>
                        <p style="margin: 0.25rem 0;">Tax: $<?= number_format($order['tax'], 2) ?></p>
                        <h3 style="margin-top: 0.5rem; font-size: 1.25rem;">Total: $<?= number_format($order['total_amount'], 2) ?></h3>
                    </div>
                </div>

                <div class="admin-panel" style="padding: 1.5rem; background: #fff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                    <h3 style="margin-top: 0; margin-bottom: 1rem; font-size: 1.125rem;">Shipping Details</h3>
                    <p style="margin: 0.25rem 0;"><strong>Name:</strong> <?= htmlspecialchars($order['shipping_name']) ?></p>
                    <p style="margin: 0.25rem 0;"><strong>Phone:</strong> <?= htmlspecialchars($order['shipping_phone']) ?></p>
                    <p style="margin: 0.25rem 0;"><strong>Address:</strong> <?= htmlspecialchars($order['shipping_address_line1']) ?></p>
                    <p style="margin: 0.25rem 0;"><strong>City:</strong> <?= htmlspecialchars($order['shipping_city']) ?></p>
                    <p style="margin: 0.25rem 0;"><strong>Country:</strong> <?= htmlspecialchars($order['shipping_country']) ?></p>
                </div>
            </div>

            <!-- Right Column: Status & Payment -->
            <div>
                <div class="admin-panel" style="margin-bottom: 2rem; padding: 1.5rem; background: #fff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                    <h3 style="margin-top: 0; margin-bottom: 1rem; font-size: 1.125rem;">Order Status</h3>
                    <form method="POST">
                        <input type="hidden" name="action" value="update_status">
                        <select name="status" style="width: 100%; padding: 0.75rem; border: 1px solid #ccc; border-radius: 4px; margin-bottom: 1rem;">
                            <option value="pending" <?= $order['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="processing" <?= $order['status'] === 'processing' ? 'selected' : '' ?>>Processing</option>
                            <option value="in_transit" <?= $order['status'] === 'in_transit' ? 'selected' : '' ?>>In Transit</option>
                            <option value="delivered" <?= $order['status'] === 'delivered' ? 'selected' : '' ?>>Delivered</option>
                            <option value="cancelled" <?= $order['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                        </select>
                        <button type="submit" class="admin-btn" style="width: 100%;">Update Status</button>
                    </form>
                </div>

                <div class="admin-panel" style="padding: 1.5rem; background: #fff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 2rem;">
                    <h3 style="margin-top: 0; margin-bottom: 1rem; font-size: 1.125rem;">Payment Info</h3>
                    <p style="margin: 0.25rem 0;"><strong>Method:</strong> <span style="text-transform: capitalize;"><?= str_replace('_', ' ', htmlspecialchars($order['payment_method'])) ?></span></p>
                    <p style="margin: 0.25rem 0;"><strong>Status:</strong> <span style="text-transform: capitalize;"><?= htmlspecialchars($order['payment_status']) ?></span></p>
                </div>

                <!-- Delete Order -->
                <div class="admin-panel" style="padding: 1.5rem; background: #fff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border: 1px solid #ffcdd2;">
                    <h3 style="margin-top: 0; margin-bottom: 0.5rem; font-size: 1.125rem; color: #d32f2f;">Danger Zone</h3>
                    <p style="font-size: 0.875rem; color: #666; margin-bottom: 1rem;">Permanently delete this order and all its items. This cannot be undone.</p>
                    <button type="button" class="admin-btn" style="width: 100%; background: #d32f2f; border-color: #d32f2f;" onclick="deleteThisOrder()">Delete Order</button>
                </div>
            </div>

        </div>
    </div>
</main>

<script>
function deleteThisOrder() {
    if (!confirm('Are you absolutely sure you want to permanently delete this order? This action cannot be undone.')) {
        return;
    }
    const formData = new FormData();
    formData.append('action', 'delete_order');
    formData.append('order_id', <?= $orderId ?>);

    fetch('admin_manage_order.php', {
        method: 'POST',
        body: formData,
        credentials: 'same-origin'
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            window.location.href = 'admin_dashboard.php#panel-orders';
        } else {
            alert(data.message || 'Failed to delete order.');
        }
    })
    .catch(err => {
        console.error(err);
        alert('A network error occurred.');
    });
}
</script>

<?php include '../includes/footer.php'; ?>
