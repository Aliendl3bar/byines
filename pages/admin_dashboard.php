<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Strict Admin Auth Guard
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    // If not logged in or not an admin, kick them to the homepage
    header("Location: index.php");
    exit;
}

require_once '../classes/Database.php';
$db = Database::getInstance();
$pdo = $db->getConnection();

// --- Fetch Overview Stats ---
// Total Orders
$stmtOrders = $pdo->query("SELECT COUNT(*) as total FROM orders");
$totalOrders = $stmtOrders->fetch()['total'];

// Total Revenue
$stmtRevenue = $pdo->query("SELECT SUM(total_amount) as revenue FROM orders WHERE payment_status = 'paid'");
$totalRevenue = $stmtRevenue->fetch()['revenue'] ?? 0.00;

// Total Users
$stmtUsers = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'user'");
$totalUsers = $stmtUsers->fetch()['total'];

// --- Fetch Products ---
$stmtProducts = $pdo->query("
    SELECT p.id, p.name, p.sku, p.price, p.is_active, c.name as category_name
    FROM products p
    JOIN categories c ON p.category_id = c.id
    ORDER BY p.id DESC
");
$products = $stmtProducts->fetchAll();

// --- Fetch Orders ---
$stmtRecentOrders = $pdo->query("
    SELECT id, order_number, total_amount, status, created_at, shipping_name 
    FROM orders 
    ORDER BY created_at DESC 
    LIMIT 50
");
$orders = $stmtRecentOrders->fetchAll();

include '../includes/header.php'; 
?>
<!-- Include Admin CSS -->
<link rel="stylesheet" href="../css/admin_dashboard.css">

<main class="admin-container">
    <div class="admin-layout">
        
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="admin-sidebar-header">
                <h1 class="admin-sidebar-title">Admin Panel</h1>
            </div>
            <ul class="admin-menu">
                <li>
                    <button class="admin-menu-btn active" data-tab="overview">
                        <svg viewBox="0 0 24 24"><path d="M3 3h18v18H3V3zm16 16V5H5v14h14zm-6-8h4v6h-4v-6zm-6 2h4v4H7v-4z"/></svg>
                        Overview
                    </button>
                </li>
                <li>
                    <button class="admin-menu-btn" data-tab="products">
                        <svg viewBox="0 0 24 24"><path d="M20 6h-4V4c0-1.11-.89-2-2-2h-4c-1.11 0-2 .89-2 2v2H4c-1.11 0-1.99.89-1.99 2L2 19c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V8c0-1.11-.89-2-2-2zm-6 0h-4V4h4v2z"/></svg>
                        Products
                    </button>
                </li>
                <li>
                    <button class="admin-menu-btn" data-tab="orders">
                        <svg viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V5h14v14zM7 10h2v7H7v-7zm4-3h2v10h-2V7zm4 6h2v4h-2v-4z"/></svg>
                        Orders
                    </button>
                </li>
            </ul>
        </aside>

        <!-- Main Content -->
        <section class="admin-content">
            
            <!-- OVERVIEW PANEL -->
            <div class="admin-panel active" id="panel-overview">
                <div class="admin-panel-header">
                    <h2 class="admin-panel-title">Dashboard Overview</h2>
                </div>
                
                <div class="admin-stats-grid">
                    <div class="admin-stat-card">
                        <h3>Total Orders</h3>
                        <p class="stat-value"><?= number_format($totalOrders) ?></p>
                    </div>
                    <div class="admin-stat-card">
                        <h3>Total Revenue</h3>
                        <p class="stat-value">$<?= number_format($totalRevenue, 2) ?></p>
                    </div>
                    <div class="admin-stat-card">
                        <h3>Registered Users</h3>
                        <p class="stat-value"><?= number_format($totalUsers) ?></p>
                    </div>
                </div>
            </div>

            <!-- PRODUCTS PANEL -->
            <div class="admin-panel" id="panel-products">
                <div class="admin-panel-header">
                    <h2 class="admin-panel-title">Manage Products</h2>
                    <button class="admin-btn" onclick="alert('Add Product Form coming soon!')">+ Add New Product</button>
                </div>
                
                <div class="admin-table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>SKU</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($products as $p): ?>
                            <tr>
                                <td><?= $p['id'] ?></td>
                                <td><?= htmlspecialchars($p['sku']) ?></td>
                                <td><strong><?= htmlspecialchars($p['name']) ?></strong></td>
                                <td><?= htmlspecialchars($p['category_name']) ?></td>
                                <td>$<?= number_format($p['price'], 2) ?></td>
                                <td>
                                    <?php if($p['is_active']): ?>
                                        <span class="admin-badge badge-success">Visible</span>
                                    <?php else: ?>
                                        <span class="admin-badge badge-neutral">Hidden</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button class="admin-btn admin-btn-sm" onclick="alert('Edit product coming soon!')">Edit</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($products)): ?>
                                <tr><td colspan="7" style="text-align:center;">No products found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ORDERS PANEL -->
            <div class="admin-panel" id="panel-orders">
                <div class="admin-panel-header">
                    <h2 class="admin-panel-title">Recent Orders</h2>
                </div>
                
                <div class="admin-table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Date</th>
                                <th>Customer</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($orders as $o): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($o['order_number']) ?></strong></td>
                                <td><?= date('M j, Y', strtotime($o['created_at'])) ?></td>
                                <td><?= htmlspecialchars($o['shipping_name']) ?></td>
                                <td>$<?= number_format($o['total_amount'], 2) ?></td>
                                <td>
                                    <?php
                                        $statusClass = 'badge-neutral';
                                        if($o['status'] === 'delivered') $statusClass = 'badge-success';
                                        if($o['status'] === 'in_transit') $statusClass = 'badge-warning';
                                        if($o['status'] === 'cancelled') $statusClass = 'badge-danger';
                                    ?>
                                    <span class="admin-badge <?= $statusClass ?>"><?= ucfirst($o['status']) ?></span>
                                </td>
                                <td>
                                    <button class="admin-btn admin-btn-sm" onclick="alert('View Order details coming soon!')">View</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($orders)): ?>
                                <tr><td colspan="6" style="text-align:center;">No recent orders.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </section>
    </div>
</main>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Simple tab switcher for Admin Dashboard
        document.querySelectorAll('.admin-menu-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                // Remove active classes
                document.querySelectorAll('.admin-menu-btn').forEach(b => b.classList.remove('active'));
                document.querySelectorAll('.admin-panel').forEach(p => p.classList.remove('active'));
                
                // Add active class to clicked button and target panel
                this.classList.add('active');
                const targetPanel = document.getElementById('panel-' + this.getAttribute('data-tab'));
                if (targetPanel) {
                    targetPanel.classList.add('active');
                }
            });
        });
    });
</script>

<?php include '../includes/footer.php'; ?>
