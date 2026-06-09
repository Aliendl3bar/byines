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
<link rel="stylesheet" href="../css/admin_dashboard.css">

<main class="admin-container">
    <div class="admin-sidebar">
        <h2 class="admin-sidebar-title">Admin Panel</h2>
        <ul class="admin-nav">
            <li><a href="admin_dashboard.php#panel-overview">Overview</a></li>
            <li><a href="admin_dashboard.php#panel-products">Products</a></li>
            <li><a href="admin_dashboard.php#panel-orders" class="active-link">Orders</a></li>
        </ul>
    </div>

    <div class="admin-content">
        <div class="admin-header-bar">
            <h1 class="admin-page-title">Order <?= htmlspecialchars($order['order_number']) ?></h1>
            <a href="admin_dashboard.php#panel-orders" class="admin-btn admin-btn-sm admin-btn-outline">Back to Orders</a>
        </div>

        <?php if(isset($_GET['msg']) && $_GET['msg'] === 'updated'): ?>
        <div class="admin-alert-banner admin-alert-success">
            Order status updated successfully.
        </div>
        <?php endif; ?>

        <div class="admin-order-grid">
            
            <!-- Left Column: Items & Shipping -->
            <div>
                <div class="admin-card admin-card-mb">
                    <h3 class="admin-card-title">Order Items</h3>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Qty</th>
                                <th>Price</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($item['name']) ?></strong><br>
                                    <span class="admin-item-subtitle">Color: <?= htmlspecialchars($item['color']) ?> | Size: <?= htmlspecialchars($item['size']) ?></span>
                                </td>
                                <td><?= $item['quantity'] ?></td>
                                <td>$<?= number_format($item['price'], 2) ?></td>
                                <td>$<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <div class="admin-order-totals">
                        <p class="admin-detail-line">Subtotal: $<?= number_format($order['subtotal'], 2) ?></p>
                        <p class="admin-detail-line">Shipping: $<?= number_format($order['shipping_cost'], 2) ?></p>
                        <p class="admin-detail-line">Tax: $<?= number_format($order['tax'], 2) ?></p>
                        <h3 class="admin-total-final">Total: $<?= number_format($order['total_amount'], 2) ?></h3>
                    </div>
                </div>

                <div class="admin-card">
                    <h3 class="admin-card-title">Shipping Details</h3>
                    <p class="admin-detail-line"><strong>Name:</strong> <?= htmlspecialchars($order['shipping_name']) ?></p>
                    <p class="admin-detail-line"><strong>Phone:</strong> <?= htmlspecialchars($order['shipping_phone']) ?></p>
                    <p class="admin-detail-line"><strong>Address:</strong> <?= htmlspecialchars($order['shipping_address_line1']) ?></p>
                    <p class="admin-detail-line"><strong>City:</strong> <?= htmlspecialchars($order['shipping_city']) ?></p>
                    <p class="admin-detail-line"><strong>Country:</strong> <?= htmlspecialchars($order['shipping_country']) ?></p>
                </div>
            </div>

            <!-- Right Column: Status & Payment -->
            <div>
                <div class="admin-card admin-card-mb">
                    <h3 class="admin-card-title">Order Status</h3>
                    <form method="POST">
                        <input type="hidden" name="action" value="update_status">
                        <select name="status" class="admin-select-full">
                            <option value="pending" <?= $order['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="processing" <?= $order['status'] === 'processing' ? 'selected' : '' ?>>Processing</option>
                            <option value="in_transit" <?= $order['status'] === 'in_transit' ? 'selected' : '' ?>>In Transit</option>
                            <option value="delivered" <?= $order['status'] === 'delivered' ? 'selected' : '' ?>>Delivered</option>
                            <option value="cancelled" <?= $order['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                        </select>
                        <button type="submit" class="admin-btn admin-btn-block">Update Status</button>
                    </form>
                </div>

                <div class="admin-card admin-card-mb">
                    <h3 class="admin-card-title">Payment Info</h3>
                    <p class="admin-detail-line"><strong>Method:</strong> <span class="admin-capitalize"><?= str_replace('_', ' ', htmlspecialchars($order['payment_method'])) ?></span></p>
                    <p class="admin-detail-line"><strong>Status:</strong> <span class="admin-capitalize"><?= htmlspecialchars($order['payment_status']) ?></span></p>
                </div>

                <!-- Delete Order -->
                <div class="admin-card admin-danger-zone">
                    <h3 class="admin-card-title admin-danger-title">Danger Zone</h3>
                    <p class="admin-danger-text">Permanently delete this order and all its items. This cannot be undone.</p>
                    <button type="button" class="admin-btn admin-btn-block admin-btn-danger" data-action="delete-order" data-order-id="<?= $orderId ?>">Delete Order</button>
                </div>
            </div>

        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>
