<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// admin auth guard
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

// handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $newStatus = $_POST['status'];
    $orderModel->updateStatus($orderId, $newStatus);
    header("Location: admin_view_order.php?id=" . $orderId . "&msg=updated");
    exit;
}

// fetch order with items
$order = $orderModel->getById($orderId);

if (!$order) {
    die('Order not found.');
}

$items = $order['items'] ?? [];

$pageTitle = 'Manage Order #' . $order['order_number'];
include '../includes/header.php'; 
?>
<link rel="stylesheet" href="../css/admin_dashboard.css">

<main class="admin-container" data-order-id="<?= $orderId ?>">
    <div class="admin-sidebar">
        <h2 class="avo-sidebar-title">Admin Panel</h2>
        <ul class="avo-nav">
            <li><a href="admin_dashboard.php#panel-overview" class="avo-nav-link">Overview</a></li>
            <li><a href="admin_dashboard.php#panel-products" class="avo-nav-link">Products</a></li>
            <li><a href="admin_dashboard.php#panel-orders" class="avo-nav-link active">Orders</a></li>
        </ul>
    </div>

    <div class="admin-content">
        <div class="avo-page-header">
            <h1 class="admin-page-title">Order <?= htmlspecialchars($order['order_number']) ?></h1>
            <a href="admin_dashboard.php#panel-orders" class="admin-btn admin-btn-sm avo-btn-back">Back to Orders</a>
        </div>

        <?php if(isset($_GET['msg']) && $_GET['msg'] === 'updated'): ?>
        <div class="avo-success-msg">
            Order status updated successfully.
        </div>
        <?php endif; ?>

        <div class="avo-layout">
            
            <!-- left column: items & shipping -->
            <div>
                <div class="avo-card">
                    <h3 class="avo-card-title">Order Items</h3>
                    <table class="avo-items-table">
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
                                    <span class="avo-variant-info">Color: <?= htmlspecialchars($item['color']) ?> | Size: <?= htmlspecialchars($item['size']) ?></span>
                                </td>
                                <td><?= $item['quantity'] ?></td>
                                <td>$<?= number_format($item['price'], 2) ?></td>
                                <td>$<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <div class="avo-totals">
                        <p>Subtotal: $<?= number_format($order['subtotal'], 2) ?></p>
                        <p>Shipping: $<?= number_format($order['shipping_cost'], 2) ?></p>
                        <p>Tax: $<?= number_format($order['tax'], 2) ?></p>
                        <h3 class="avo-total-amount">Total: $<?= number_format($order['total_amount'], 2) ?></h3>
                    </div>
                </div>

                <div class="avo-card">
                    <h3 class="avo-card-title">Shipping Details</h3>
                    <div class="avo-shipping-details">
                        <p><strong>Name:</strong> <?= htmlspecialchars($order['shipping_name']) ?></p>
                        <p><strong>Phone:</strong> <?= htmlspecialchars($order['shipping_phone']) ?></p>
                        <p><strong>Address:</strong> <?= htmlspecialchars($order['shipping_address_line1']) ?></p>
                        <p><strong>City:</strong> <?= htmlspecialchars($order['shipping_city']) ?></p>
                        <p><strong>Country:</strong> <?= htmlspecialchars($order['shipping_country']) ?></p>
                    </div>
                </div>
            </div>

            <!-- right column: status & payment -->
            <div>
                <div class="avo-card">
                    <h3 class="avo-card-title">Order Status</h3>
                    <form method="POST">
                        <input type="hidden" name="action" value="update_status">
                        <select name="status" class="avo-status-select">
                            <option value="pending" <?= $order['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="processing" <?= $order['status'] === 'processing' ? 'selected' : '' ?>>Processing</option>
                            <option value="in_transit" <?= $order['status'] === 'in_transit' ? 'selected' : '' ?>>In Transit</option>
                            <option value="delivered" <?= $order['status'] === 'delivered' ? 'selected' : '' ?>>Delivered</option>
                            <option value="cancelled" <?= $order['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                        </select>
                        <button type="submit" class="admin-btn avo-btn-full">Update Status</button>
                    </form>
                </div>

                <div class="avo-payment-card">
                    <h3 class="avo-card-title">Payment Info</h3>
                    <p><strong>Method:</strong> <span class="text-capitalize"><?= str_replace('_', ' ', htmlspecialchars($order['payment_method'])) ?></span></p>
                    <p><strong>Status:</strong> <span class="text-capitalize"><?= htmlspecialchars($order['payment_status']) ?></span></p>
                </div>

                <!-- delete order -->
                <div class="avo-danger-zone">
                    <h3>Danger Zone</h3>
                    <p>Permanently delete this order and all its items. This cannot be undone.</p>
                    <button type="button" class="admin-btn avo-btn-danger" onclick="deleteThisOrder()">Delete Order</button>
                </div>
            </div>

        </div>
    </div>
</main>

<script src="../scripts/admin_view_order.js?v=<?= time() ?>"></script>

<?php include '../includes/footer.php'; ?>
