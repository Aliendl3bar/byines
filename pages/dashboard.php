<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Secure PHP Auth Guard
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once '../classes/User.php';
require_once '../classes/Order.php';
require_once '../classes/Product.php';

$userModel = new User();
$orderModel = new Order();
$productModel = new Product();

// Fetch user data
$user = $userModel->getProfile($_SESSION['user_id']);

// Fetch orders
$orders = $orderModel->getByUser($_SESSION['user_id']);

$orderCount = count($orders);
$totalSpent = 0;
foreach ($orders as $o) {
    $totalSpent += $o['total_amount'];
}

$recentOrder = $orderCount > 0 ? $orders[0] : null;

include '../includes/header.php'; 
?>
    <link rel="stylesheet" href="../css/dashboard.css">

    <main class="dashboard-container">
        <!-- Breadcrumb Navigation -->
        <nav class="dashboard-breadcrumb">
            <a href="index.php">Home</a>
            <span>/</span>
            <span class="current">My Account</span>
        </nav>

        <!-- Welcome Banner -->
        <section class="welcome-banner">
            <div class="welcome-info">
                <h1>Hello, <span id="bannerFirstName"><?= htmlspecialchars($user['first_name'] ?? 'User') ?></span>!</h1>
                <p>Welcome to your dashboard. Here you can track your recent orders and edit account settings.</p>
            </div>
            <div class="welcome-stats">
                <div class="welcome-stat-item">
                    <p class="welcome-stat-val" id="statOrdersCount"><?= $orderCount ?></p>
                    <p class="welcome-stat-lbl">Orders Placed</p>
                </div>
                <div class="welcome-stat-item">
                    <p class="welcome-stat-val">$<?= number_format($totalSpent, 2) ?></p>
                    <p class="welcome-stat-lbl">Total Spent</p>
                </div>
            </div>
        </section>

        <!-- Core Dashboard Layout -->
        <div class="dashboard-layout">
            
            <!-- Sidebar Navigation -->
            <aside class="dashboard-sidebar">
                <ul class="sidebar-menu">
                    <li>
                        <button class="sidebar-menu-btn active" data-tab="overview" data-action="switch-tab">
                            <svg viewBox="0 0 24 24"><path d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12Zm11.378-3.917c-.742.742-1.02 1.851-.734 2.825l.215.733-.733.215c-.974.286-2.083.008-2.825-.734l-1.06-1.058-2.457 2.456 2.457 2.457c.742.741.742 1.944 0 2.685-.741.742-1.944.742-2.685 0L3.33 12.21l8.363-8.364 2.457 2.457c.742.741.742 1.944 0 2.685-.741.742-1.944.742-2.685 0l-.88-.88Z"/></svg>
                            Overview
                        </button>
                    </li>
                    <li>
                            <button class="sidebar-menu-btn" data-tab="orders" data-action="switch-tab">
                            <svg viewBox="0 0 24 24"><path d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/></svg>
                            Order History
                        </button>
                    </li>
                    <li>
                            <button class="sidebar-menu-btn" data-tab="profile" data-action="switch-tab">
                            <svg viewBox="0 0 24 24"><path d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281a7.186 7.186 0 0 1 1.637.945l1.196-.48c.504-.2.1.066.9.463l1.833 3.175c.277.478.146 1.082-.303 1.401l-1.054.747a7.254 7.254 0 0 1 0 1.876l1.054.747c.45.32.58.923.303 1.402l-1.833 3.175c-.3.52-.77.785-1.3.626l-1.196-.48a7.186 7.186 0 0 1-1.637.945l-.213 1.282c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281a7.167 7.167 0 0 1-1.637-.945l-1.196.48c-.53.212-.1-.052-.9-.463l-1.834-3.175c-.277-.479-.147-1.082.303-1.401l1.054-.747a7.254 7.254 0 0 1 0-1.876l-1.054-.747c-.45-.32-.58-.923-.303-1.402l1.834-3.175c.3-.52.77-.785 1.3-.626l1.196.48a7.167 7.167 0 0 1 1.637-.945l.213-1.282ZM12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"/></svg>
                            Account Details
                        </button>
                    </li>
                </ul>
            </aside>

            <!-- Main Content Area -->
            <section class="dashboard-content">
                
                <!-- Overview Panel -->
                <div class="tab-panel active" id="panel-overview">
                    <h2 class="panel-title">Overview</h2>
                    <div class="overview-intro-grid">
                        
                        <!-- Recent Order Widget -->
                        <div class="overview-card">
                            <h3>Recent Order</h3>
                            <?php if ($recentOrder): ?>
                            <div class="order-widget-row">
                                <div>
                                    <p class="order-widget-id"><?= htmlspecialchars($recentOrder['order_number']) ?></p>
                                    <p class="order-widget-date">Placed on <?= date('M d, Y', strtotime($recentOrder['created_at'])) ?></p>
                                </div>
                                <span class="status-badge status-<?= strtolower($recentOrder['status']) ?>"><?= ucfirst(str_replace('_', ' ', $recentOrder['status'])) ?></span>
                            </div>
                            <div class="order-tracker">
                                <?php
                                    $progress = 0;
                                    switch($recentOrder['status']) {
                                        case 'pending': $progress = 10; break;
                                        case 'processing': $progress = 35; break;
                                        case 'in_transit': $progress = 65; break;
                                        case 'delivered': $progress = 100; break;
                                    }
                                ?>
                                <div class="tracker-bar">
                                    <div class="tracker-progress" style="--progress: <?= $progress ?>%;"></div>
                                </div>
                                <div class="tracker-labels">
                                    <span>Placed</span>
                                    <span>Shipped</span>
                                    <span>Delivered</span>
                                </div>
                            </div>
                            <?php else: ?>
                                <p class="no-recent-text">No recent orders.</p>
                                <a href="shop.php" class="btn-sm-outline">Start Shopping</a>
                            <?php endif; ?>
                        </div>

                        <!-- Quick Links -->
                        <div class="overview-card">
                            <h3>Quick Actions</h3>
                            <ul class="quick-links-list">
                                <li>
                                    <a href="#" data-action="switch-tab" data-tab="orders">
                                        <span>View Order History</span>
                                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M9 5l7 7-7 7"/></svg>
                                    </a>
                                </li>
                                <li>
                                    <a href="#" data-action="switch-tab" data-tab="profile">
                                        <span>Update Account Details</span>
                                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M9 5l7 7-7 7"/></svg>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Order History Panel -->
                <div class="tab-panel" id="panel-orders">
                    <h2 class="panel-title">Order History</h2>
                    <div class="order-history-list">
                        <?php if (empty($orders)): ?>
                            <div class="empty-orders">
                                <p class="empty-orders-text">You haven't placed any orders yet.</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($orders as $order): 
                                $orderDetail = $orderModel->getById($order['id']);
                                $items = $orderDetail['items'] ?? [];
                            ?>
                            <div class="order-history-card">
                                <div class="order-card-header">
                                    <div class="order-header-meta">
                                        <div class="order-meta-item">
                                            <p>Order Placed</p>
                                            <p><?= date('M d, Y', strtotime($order['created_at'])) ?></p>
                                        </div>
                                        <div class="order-meta-item">
                                            <p>Total</p>
                                            <p>$<?= number_format($order['total_amount'], 2) ?></p>
                                        </div>
                                        <div class="order-meta-item">
                                            <p>Ship To</p>
                                            <p><?= htmlspecialchars($order['shipping_name']) ?></p>
                                        </div>
                                    </div>
                                    <div class="order-meta-item text-right">
                                        <p><?= htmlspecialchars($order['order_number']) ?></p>
                                        <span class="status-badge status-<?= strtolower($order['status']) ?> mt-1">
                                            <?= ucfirst(str_replace('_', ' ', $order['status'])) ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="order-card-body">
                                    <div class="order-items-list">
                                        <?php foreach ($items as $item): 
                                            $imgName = $productModel->getImageByColor($item['product_id'], $item['color']);
                                            $imgSrc = $imgName ? "../products/{$item['product_id']}/img/" . htmlspecialchars($imgName) : "../assets/placeholder.png";
                                        ?>
                                        <div class="order-item-row">
                                            <img src="<?= $imgSrc ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="order-item-img">
                                            <div class="order-item-details">
                                                <h4><?= htmlspecialchars($item['name']) ?></h4>
                                                <p>Color: <?= htmlspecialchars($item['color']) ?> | Size: <?= htmlspecialchars($item['size']) ?></p>
                                                <p>Qty: <?= $item['quantity'] ?></p>
                                            </div>
                                            <div class="order-item-price">$<?= number_format($item['price'], 2) ?></div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Account Details Panel -->
                <div class="tab-panel" id="panel-profile">
                    <h2 class="panel-title">Account Details</h2>
                    <div class="profile-form-wrapper">
                        
                        <!-- Details Edit Form -->
                        <form id="profileForm" data-action="save-profile">
                            <div class="form-row-dashboard">
                                <div class="form-group-dashboard">
                                    <label for="firstName">First Name</label>
                                    <input type="text" id="profileFirstName" name="firstName" value="<?= htmlspecialchars($user['first_name'] ?? '') ?>" required>
                                </div>
                                <div class="form-group-dashboard">
                                    <label for="lastName">Last Name</label>
                                    <input type="text" id="profileLastName" name="lastName" value="<?= htmlspecialchars($user['last_name'] ?? '') ?>" required>
                                </div>
                            </div>
                            <div class="form-group-dashboard">
                                <label for="email">Email Address</label>
                                <input type="email" id="profileEmail" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
                            </div>

                            <div class="form-divider"></div>
                            
                            <h3 class="section-subtitle">Change Password</h3>
                            
                            <div class="form-group-dashboard">
                                <label for="currentPassword">Current Password</label>
                                <input type="password" id="currentPassword" placeholder="••••••••">
                            </div>
                            <div class="form-row-dashboard">
                                <div class="form-group-dashboard">
                                    <label for="newPassword">New Password</label>
                                    <input type="password" id="newPassword" placeholder="••••••••">
                                </div>
                                <div class="form-group-dashboard">
                                    <label for="confirmNewPassword">Confirm New Password</label>
                                    <input type="password" id="confirmNewPassword" placeholder="••••••••">
                                </div>
                            </div>

                            <button type="submit" class="profile-submit-btn">Save Changes</button>
                        </form>

                        <!-- Danger Zone -->
                        <div class="form-divider danger-divider"></div>
                        <h3 class="danger-title">Danger Zone</h3>
                        <p class="danger-description">Once you delete your account, there is no going back. All of your order history will be permanently lost.</p>
                        <button type="button" class="btn-danger-outline" data-action="delete-account">Delete Account</button>

                    </div>
                </div>
            </section>
        </div>
    </main>

    <!-- Tab Logic (handled by scripts/dashboard.js via data-action delegation) -->

<?php include '../includes/footer.php'; ?>
