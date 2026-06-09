<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Strict Admin Auth Guard
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

require_once '../classes/Database.php';
require_once '../classes/Product.php';
require_once '../classes/Category.php';

$db = Database::getInstance();
$pdo = $db->getConnection();
$productModel = new Product();
$categoryModel = new Category();

// --- Fetch Overview Stats ---
$stmtOrders = $pdo->query("SELECT COUNT(*) as total FROM orders");
$totalOrders = $stmtOrders->fetch()['total'];

$stmtRevenue = $pdo->query("SELECT SUM(total_amount) as revenue FROM orders WHERE payment_status = 'paid'");
$totalRevenue = $stmtRevenue->fetch()['revenue'] ?? 0.00;

$stmtUsers = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'user'");
$totalUsers = $stmtUsers->fetch()['total'];

// --- Fetch Products ---
$stmtProducts = $pdo->query("
    SELECT p.id, p.category_id, p.name, p.slug, p.sku, p.description, p.price, p.is_active, c.name as category_name
    FROM products p
    JOIN categories c ON p.category_id = c.id
    ORDER BY p.id DESC
");
$products = $stmtProducts->fetchAll();

// Pre-fetch images and variants for each product (pass to JS)
$productImages = [];
$productVariants = [];
foreach ($products as $p) {
    $productImages[$p['id']] = $productModel->getImages($p['id']);
    $productVariants[$p['id']] = $productModel->getVariants($p['id']);
}

// --- Fetch Categories ---
$categories = $categoryModel->getAll();

// --- Fetch Collections ---
$stmtCollections = $pdo->query("SELECT * FROM collections ORDER BY id DESC");
$allCollections = $stmtCollections->fetchAll();

// --- Fetch Orders ---
$stmtRecentOrders = $pdo->query("
    SELECT * FROM orders ORDER BY created_at DESC LIMIT 50
");
$orders = $stmtRecentOrders->fetchAll();

// Pre-fetch order items for each order (pass to JS for modal)
$orderItemsData = [];
foreach ($orders as $o) {
    $itemStmt = $pdo->prepare("
        SELECT oi.quantity, oi.price, v.color, v.size, p.name
        FROM order_items oi
        JOIN product_variants v ON oi.variant_id = v.id
        JOIN products p ON v.product_id = p.id
        WHERE oi.order_id = ?
    ");
    $itemStmt->execute([$o['id']]);
    $orderItemsData[$o['id']] = $itemStmt->fetchAll(PDO::FETCH_ASSOC);
}

// Flash messages
$adminSuccess = $_SESSION['admin_success'] ?? null;
$adminError = $_SESSION['admin_error'] ?? null;
unset($_SESSION['admin_success'], $_SESSION['admin_error']);

include '../includes/header.php'; 
?>
<!-- Include Admin CSS -->
<link rel="stylesheet" href="../css/admin_dashboard.css?v=<?= time() ?>">


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
                    <button class="admin-menu-btn" data-tab="collections">
                        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M4 6H2v14c0 1.1.9 2 2 2h14v-2H4V6zm16-4H8c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm0 14H8V4h12v12z"/></svg>
                        Collections
                    </button>
                </li>
                <li>
                    <button class="admin-menu-btn" data-tab="categories">
                        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M10 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2h-8l-2-2z"/></svg>
                        Categories
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

            <!-- Flash Messages -->
            <?php if ($adminSuccess): ?>
                <div class="admin-alert-banner admin-alert-success" id="flash-success">
                    <?= htmlspecialchars($adminSuccess) ?>
                </div>
            <?php endif; ?>
            <?php if ($adminError): ?>
                <div class="admin-alert-banner admin-alert-error" id="flash-error">
                    <?= htmlspecialchars($adminError) ?>
                </div>
            <?php endif; ?>
            
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
                    <button class="admin-btn" id="btn-open-add-product">+ Add New Product</button>
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
                                <td style="white-space: nowrap;">
                                    <button class="admin-btn admin-btn-sm btn-manage-product"
                                            data-product-id="<?= $p['id'] ?>"
                                            data-product-name="<?= htmlspecialchars($p['name'], ENT_QUOTES) ?>"
                                            data-product-sku="<?= htmlspecialchars($p['sku'], ENT_QUOTES) ?>"
                                            data-product-price="<?= $p['price'] ?>"
                                            data-product-category="<?= $p['category_id'] ?>"
                                            data-product-description="<?= htmlspecialchars($p['description'] ?? '', ENT_QUOTES) ?>"
                                            data-product-active="<?= $p['is_active'] ?>">
                                        Manage
                                    </button>
                                    <button class="admin-btn admin-btn-sm admin-btn-danger"
                                            onclick="if(confirm('Are you sure you want to permanently delete \'<?= htmlspecialchars($p['name'], ENT_QUOTES) ?>\'? This will remove all images, variants, and data. This action cannot be undone.')) { window.location.href='admin_manage_product.php?action=delete_product&product_id=<?= $p['id'] ?>'; }">
                                        Delete
                                    </button>
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

            <!-- COLLECTIONS PANEL -->
            <div class="admin-panel" id="panel-collections">
                <div class="admin-panel-header">
                    <h2 class="admin-panel-title">Manage Collections</h2>
                    <button class="admin-btn" id="btn-open-add-collection">+ Add New Collection</button>
                </div>
                <div class="admin-table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Product IDs</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($allCollections as $col): ?>
                            <tr>
                                <td><?= $col['id'] ?></td>
                                <td><strong><?= htmlspecialchars($col['title']) ?></strong></td>
                                <td><?= htmlspecialchars($col['products_ids'] ?? '') ?></td>
                                <td>
                                    <button class="admin-btn admin-btn-sm btn-edit-collection"
                                            data-id="<?= $col['id'] ?>"
                                            data-title="<?= htmlspecialchars($col['title'], ENT_QUOTES) ?>"
                                            data-products-ids="<?= htmlspecialchars($col['products_ids'] ?? '', ENT_QUOTES) ?>"
                                            data-image-path="<?= htmlspecialchars($col['image_path'] ?? '', ENT_QUOTES) ?>">
                                        Edit
                                    </button>
                                    <button class="admin-btn admin-btn-sm admin-btn-danger"
                                            onclick="if(confirm('Delete this collection?')) { window.location.href='admin_manage_collection.php?action=delete&id=<?= $col['id'] ?>'; }">
                                        Delete
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($allCollections)): ?>
                                <tr><td colspan="4" style="text-align:center;">No collections found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- CATEGORIES PANEL -->
            <div class="admin-panel" id="panel-categories">
                <div class="admin-panel-header">
                    <h2 class="admin-panel-title">Manage Categories</h2>
                    <button class="admin-btn" id="btn-open-add-category">+ Add New Category</button>
                </div>
                <div class="admin-table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Slug</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($categories as $cat): ?>
                            <tr>
                                <td><?= $cat['id'] ?></td>
                                <td><strong><?= htmlspecialchars($cat['name']) ?></strong></td>
                                <td><?= htmlspecialchars($cat['slug']) ?></td>
                                <td>
                                    <button class="admin-btn admin-btn-sm btn-edit-category"
                                            data-id="<?= $cat['id'] ?>"
                                            data-name="<?= htmlspecialchars($cat['name'], ENT_QUOTES) ?>"
                                            data-slug="<?= htmlspecialchars($cat['slug'], ENT_QUOTES) ?>"
                                            data-image-url="<?= htmlspecialchars($cat['image_url'] ?? '', ENT_QUOTES) ?>">
                                        Edit
                                    </button>
                                    <button class="admin-btn admin-btn-sm admin-btn-danger"
                                            onclick="if(confirm('Delete this category?')) { window.location.href='admin_manage_category.php?action=delete&id=<?= $cat['id'] ?>'; }">
                                        Delete
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($categories)): ?>
                                <tr><td colspan="4" style="text-align:center;">No categories found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ORDERS PANEL -->
            <div class="admin-panel" id="panel-orders">
                <div class="admin-panel-header">
                    <h2 class="admin-panel-title">Manage Orders</h2>
                    <select id="orderStatusFilter" onchange="filterOrders()" style="padding: 0.4rem 0.75rem; border: 1px solid #ccc; border-radius: 4px; font-size: 0.875rem;">
                        <option value="all">All Statuses</option>
                        <option value="pending">Pending</option>
                        <option value="processing">Processing</option>
                        <option value="in_transit">In Transit</option>
                        <option value="delivered">Delivered</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div class="admin-table-container">
                    <table class="admin-table" id="ordersTable">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Date</th>
                                <th>Customer</th>
                                <th>Total</th>
                                <th>Payment</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($orders as $o): ?>
                            <tr data-status="<?= $o['status'] ?>">
                                <td><strong><?= htmlspecialchars($o['order_number']) ?></strong></td>
                                <td><?= date('M j, Y', strtotime($o['created_at'])) ?></td>
                                <td><?= htmlspecialchars($o['shipping_name']) ?></td>
                                <td>$<?= number_format($o['total_amount'], 2) ?></td>
                                <td><span style="text-transform: capitalize;"><?= str_replace('_', ' ', $o['payment_method']) ?></span></td>
                                <td>
                                    <?php
                                        $statusClass = 'badge-neutral';
                                        if($o['status'] === 'delivered') $statusClass = 'badge-success';
                                        if($o['status'] === 'processing') $statusClass = 'badge-warning';
                                        if($o['status'] === 'in_transit') $statusClass = 'badge-warning';
                                        if($o['status'] === 'cancelled') $statusClass = 'badge-danger';
                                    ?>
                                    <span class="admin-badge <?= $statusClass ?>"><?= ucfirst(str_replace('_', ' ', $o['status'])) ?></span>
                                </td>
                                <td style="white-space: nowrap;">
                                    <button class="admin-btn admin-btn-sm btn-manage-order"
                                            data-order-id="<?= $o['id'] ?>"
                                            data-order-number="<?= htmlspecialchars($o['order_number'], ENT_QUOTES) ?>"
                                            data-order-date="<?= date('M j, Y \a\t g:i A', strtotime($o['created_at'])) ?>"
                                            data-order-status="<?= $o['status'] ?>"
                                            data-order-subtotal="<?= number_format($o['subtotal'], 2) ?>"
                                            data-order-shipping="<?= number_format($o['shipping_cost'], 2) ?>"
                                            data-order-tax="<?= number_format($o['tax'], 2) ?>"
                                            data-order-total="<?= number_format($o['total_amount'], 2) ?>"
                                            data-order-name="<?= htmlspecialchars($o['shipping_name'], ENT_QUOTES) ?>"
                                            data-order-phone="<?= htmlspecialchars($o['shipping_phone'], ENT_QUOTES) ?>"
                                            data-order-address="<?= htmlspecialchars($o['shipping_address_line1'], ENT_QUOTES) ?>"
                                            data-order-city="<?= htmlspecialchars($o['shipping_city'], ENT_QUOTES) ?>"
                                            data-order-country="<?= htmlspecialchars($o['shipping_country'], ENT_QUOTES) ?>"
                                            data-order-payment="<?= str_replace('_', ' ', $o['payment_method']) ?>"
                                            data-order-payment-status="<?= $o['payment_status'] ?>">
                                        Manage
                                    </button>
                                    <button class="admin-btn admin-btn-sm admin-btn-danger"
                                            onclick="if(confirm('Are you sure you want to permanently delete order \'<?= htmlspecialchars($o['order_number'], ENT_QUOTES) ?>\'? This action cannot be undone.')) { deleteOrder(<?= $o['id'] ?>); }">
                                        Delete
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($orders)): ?>
                                <tr><td colspan="7" style="text-align:center;">No orders found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </section>
    </div>
</main>

<!-- ====================================================================
     MODAL: ADD COLLECTION
     ==================================================================== -->
<div class="admin-modal-backdrop" id="modal-add-collection">
    <div class="admin-modal">
        <div class="admin-modal-header">
            <h3>Add New Collection</h3>
            <button class="admin-modal-close" data-close-modal="modal-add-collection">&times;</button>
        </div>
        <form action="admin_manage_collection.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="add">
            <div class="admin-modal-body">
                <div class="admin-form-group">
                    <label for="add-col-title">Collection Title</label>
                    <input type="text" id="add-col-title" name="title" required placeholder="e.g. Summer Collection">
                </div>
                <div class="admin-form-group">
                    <label for="add-col-products-ids">Product IDs (Comma-separated)</label>
                    <input type="text" id="add-col-products-ids" name="products_ids" placeholder="e.g. 1, 2, 5, 8">
                </div>
                <div class="admin-form-group">
                    <label for="add-col-image">Collection Image</label>
                    <input type="file" id="add-col-image" name="image" accept="image/jpeg,image/png,image/webp">
                </div>
            </div>
            <div class="admin-modal-footer">
                <button type="button" class="admin-btn admin-btn-sm" style="background:transparent;color:var(--brand-dark);border-color:var(--brand-earth);" data-close-modal="modal-add-collection">Cancel</button>
                <button type="submit" class="admin-btn admin-btn-sm">Create Collection</button>
            </div>
        </form>
    </div>
</div>

<!-- ====================================================================
     MODAL: EDIT COLLECTION
     ==================================================================== -->
<div class="admin-modal-backdrop" id="modal-edit-collection">
    <div class="admin-modal">
        <div class="admin-modal-header">
            <h3>Edit Collection</h3>
            <button class="admin-modal-close" data-close-modal="modal-edit-collection">&times;</button>
        </div>
        <form action="admin_manage_collection.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="edit-col-id">
            <div class="admin-modal-body">
                <div class="admin-form-group">
                    <label for="edit-col-title">Collection Title</label>
                    <input type="text" id="edit-col-title" name="title" required>
                </div>
                <div class="admin-form-group">
                    <label for="edit-col-products-ids">Product IDs (Comma-separated)</label>
                    <input type="text" id="edit-col-products-ids" name="products_ids">
                </div>
                <div class="admin-form-group">
                    <label>Current Image</label>
                    <div style="margin-bottom: 0.5rem;">
                        <img id="edit-col-current-image" src="" alt="Current Collection Image" style="max-height: 100px; display: none; border-radius: 4px;">
                    </div>
                    <label for="edit-col-image">New Image (Optional)</label>
                    <input type="file" id="edit-col-image" name="image" accept="image/jpeg,image/png,image/webp">
                </div>
            </div>
            <div class="admin-modal-footer">
                <button type="button" class="admin-btn admin-btn-sm" style="background:transparent;color:var(--brand-dark);border-color:var(--brand-earth);" data-close-modal="modal-edit-collection">Cancel</button>
                <button type="submit" class="admin-btn admin-btn-sm">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- ====================================================================
     MODAL: ADD CATEGORY
     ==================================================================== -->
<div class="admin-modal-backdrop" id="modal-add-category">
    <div class="admin-modal">
        <div class="admin-modal-header">
            <h3>Add New Category</h3>
            <button class="admin-modal-close" data-close-modal="modal-add-category">&times;</button>
        </div>
        <form action="admin_manage_category.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="add">
            <div class="admin-modal-body">
                <div class="admin-form-group">
                    <label for="add-cat-name">Category Name</label>
                    <input type="text" id="add-cat-name" name="name" required placeholder="e.g. Accessories">
                </div>
                <div class="admin-form-group">
                    <label for="add-cat-image">Category Image</label>
                    <div style="margin-bottom: 0.5rem;">
                        <div id="add-cat-preview" class="admin-image-preview-container"></div>
                    </div>
                    <input type="file" id="add-cat-image" name="image" accept="image/jpeg,image/png,image/webp">
                </div>
            </div>
            <div class="admin-modal-footer">
                <button type="button" class="admin-btn admin-btn-sm" style="background:transparent;color:var(--brand-dark);border-color:var(--brand-earth);" data-close-modal="modal-add-category">Cancel</button>
                <button type="submit" class="admin-btn admin-btn-sm">Create Category</button>
            </div>
        </form>
    </div>
</div>

<!-- ====================================================================
     MODAL: EDIT CATEGORY
     ==================================================================== -->
<div class="admin-modal-backdrop" id="modal-edit-category">
    <div class="admin-modal">
        <div class="admin-modal-header">
            <h3>Edit Category</h3>
            <button class="admin-modal-close" data-close-modal="modal-edit-category">&times;</button>
        </div>
        <form action="admin_manage_category.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="edit-cat-id">
            <div class="admin-modal-body">
                <div class="admin-form-group">
                    <label for="edit-cat-name">Category Name</label>
                    <input type="text" id="edit-cat-name" name="name" required>
                </div>
                <div class="admin-form-group">
                    <label for="edit-cat-slug">Slug</label>
                    <input type="text" id="edit-cat-slug" name="slug">
                </div>
                <div class="admin-form-group">
                    <label>Current Image</label>
                    <div style="margin-bottom: 0.5rem;">
                        <img id="edit-cat-current-image" src="" alt="Current Category Image" style="max-height: 100px; display: none; border-radius: 4px;">
                    </div>
                    <label for="edit-cat-image">New Image (Optional)</label>
                    <input type="file" id="edit-cat-image" name="image" accept="image/jpeg,image/png,image/webp">
                </div>
            </div>
            <div class="admin-modal-footer">
                <button type="button" class="admin-btn admin-btn-sm" style="background:transparent;color:var(--brand-dark);border-color:var(--brand-earth);" data-close-modal="modal-edit-category">Cancel</button>
                <button type="submit" class="admin-btn admin-btn-sm">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- ====================================================================
     MODAL: ADD NEW PRODUCT
     ==================================================================== -->
<div class="admin-modal-backdrop" id="modal-add-product">
    <div class="admin-modal">
        <div class="admin-modal-header">
            <h3>Add New Product</h3>
            <button class="admin-modal-close" data-close-modal="modal-add-product">&times;</button>
        </div>
        <form action="admin_manage_product.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="add">
            <div class="admin-modal-body">
                <div class="admin-form-group">
                    <label for="add-name">Product Name</label>
                    <input type="text" id="add-name" name="name" required placeholder="e.g. Silk Evening Dress">
                </div>
                <div class="admin-form-row">
                    <div class="admin-form-group">
                        <label for="add-sku">SKU</label>
                        <input type="text" id="add-sku" name="sku" required placeholder="e.g. BYI-SED-001">
                    </div>
                    <div class="admin-form-group">
                        <label for="add-price">Price ($)</label>
                        <input type="number" id="add-price" name="price" step="0.01" min="0.01" required placeholder="0.00">
                    </div>
                </div>
                <div class="admin-form-row">
                    <div class="admin-form-group">
                        <label for="add-category">Category</label>
                        <select id="add-category" name="category_id" required>
                            <option value="">Select categoryâ€¦</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="admin-form-group">
                        <label for="add-stock">Initial Stock</label>
                        <input type="number" id="add-stock" name="stock" min="0" value="0" required>
                    </div>
                </div>
                <div class="admin-form-group">
                    <label for="add-description">Description</label>
                    <textarea id="add-description" name="description" rows="4" placeholder="Describe the productâ€¦"></textarea>
                </div>
                <div class="admin-form-group">
                    <label for="add-images">Product Images</label>
                    <input type="file" id="add-images" name="images[]" accept="image/jpeg,image/png,image/webp" multiple>
                    <div class="admin-file-preview" id="add-images-preview"></div>
                </div>
                <div class="admin-form-group">
                    <label class="admin-form-checkbox">
                        <input type="checkbox" name="is_active" checked>
                        <span>Make product visible on storefront</span>
                    </label>
                </div>
            </div>
            <div class="admin-modal-footer">
                <button type="button" class="admin-btn admin-btn-sm" style="background:transparent;color:var(--brand-dark);border-color:var(--brand-earth);" data-close-modal="modal-add-product">Cancel</button>
                <button type="submit" class="admin-btn admin-btn-sm">Create Product</button>
            </div>
        </form>
    </div>
</div>

<!-- ====================================================================
     MODAL: MANAGE / EDIT PRODUCT (3 tabs: Details, Images, Stock)
     ==================================================================== -->
<div class="admin-modal-backdrop" id="modal-manage-product">
    <div class="admin-modal" style="max-width:720px;">
        <div class="admin-modal-header">
            <h3 id="manage-modal-title">Manage Product</h3>
            <button class="admin-modal-close" data-close-modal="modal-manage-product">&times;</button>
        </div>

        <!-- TAB BAR -->
        <div class="admin-modal-tabs">
            <button class="admin-modal-tab active" data-manage-tab="details">Edit Details</button>
            <button class="admin-modal-tab" data-manage-tab="images">Images</button>
            <button class="admin-modal-tab" data-manage-tab="stock">Stock</button>
        </div>

        <!-- ======================== TAB: EDIT DETAILS ======================== -->
        <div class="admin-modal-tab-panel active" id="manage-tab-details">
            <form action="admin_manage_product.php" method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit-id">
                <div class="admin-modal-body">
                    <div class="admin-form-group">
                        <label for="edit-name">Product Name</label>
                        <input type="text" id="edit-name" name="name" required>
                    </div>
                    <div class="admin-form-row">
                        <div class="admin-form-group">
                            <label for="edit-sku">SKU</label>
                            <input type="text" id="edit-sku" name="sku" required>
                        </div>
                        <div class="admin-form-group">
                            <label for="edit-price">Price ($)</label>
                            <input type="number" id="edit-price" name="price" step="0.01" min="0.01" required>
                        </div>
                    </div>
                    <div class="admin-form-group">
                        <label for="edit-category">Category</label>
                        <select id="edit-category" name="category_id" required>
                            <option value="">Select categoryâ€¦</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="admin-form-group">
                        <label for="edit-description">Description</label>
                        <textarea id="edit-description" name="description" rows="4"></textarea>
                    </div>
                    <div class="admin-form-group">
                        <label class="admin-form-checkbox">
                            <input type="checkbox" name="is_active" id="edit-is-active">
                            <span>Product visible on storefront</span>
                        </label>
                    </div>
                </div>
                <div class="admin-modal-footer">
                    <button type="button" class="admin-btn admin-btn-sm" style="background:transparent;color:var(--brand-dark);border-color:var(--brand-earth);" data-close-modal="modal-manage-product">Cancel</button>
                    <button type="submit" class="admin-btn admin-btn-sm">Save Changes</button>
                </div>
            </form>
        </div>

        <!-- ======================== TAB: IMAGES ======================== -->
        <div class="admin-modal-tab-panel" id="manage-tab-images">
            <div class="admin-modal-body">
                <p style="font-size:0.8rem;color:var(--gray-500);margin-bottom:1rem;">
                    <strong>â˜… Star</strong> = set as main thumbnail &nbsp;|&nbsp; 
                    <strong>âœ• Delete</strong> = remove image &nbsp;|&nbsp; 
                    Use the <strong>Color</strong> dropdown to assign an image to a specific color variant.
                </p>
                
                <div class="admin-image-edit-grid" id="manage-image-gallery">
                    <!-- JS will populate this -->
                </div>

                <hr style="border:none;border-top:1px solid var(--brand-cream);margin:1.5rem 0;">
                
                <form id="form-upload-images" onsubmit="event.preventDefault(); window.uploadImagesAjax();">
                    <input type="hidden" name="action" value="ajax_upload_images">
                    <input type="hidden" name="product_id" id="upload-product-id">
                    <div class="admin-form-group">
                        <label>Upload New Images</label>
                        <div class="admin-upload-dropzone" id="upload-dropzone">
                            <input type="file" id="upload-new-images" name="new_images[]" accept="image/jpeg,image/png,image/webp" multiple>
                            <div class="dropzone-content">
                                <svg class="dropzone-icon" width="40" height="40" style="width: 40px; height: 40px; display: block;" viewBox="0 0 24 24"><path fill="currentColor" d="M19.35 10.04C18.67 6.59 15.64 4 12 4 9.11 4 6.6 5.64 5.35 8.04 2.34 8.36 0 10.91 0 14c0 3.31 2.69 6 6 6h13c2.76 0 5-2.24 5-5 0-2.64-2.05-4.78-4.65-4.96zM14 13v4h-4v-4H7l5-5 5 5h-3z"/></svg>
                                <span class="dropzone-title">Drag & drop your images here</span>
                                <span class="dropzone-subtitle">or click to browse from files</span>
                            </div>
                        </div>
                        <div class="admin-file-preview" id="upload-images-preview"></div>
                    </div>
                    <div class="admin-modal-footer" style="background:transparent;padding:1rem 0 0;border-top:none;">
                        <button type="submit" class="admin-btn admin-btn-sm" id="btn-upload-images">Upload Images</button>
                    </div>
                </form>
            </div>
            <div class="admin-modal-footer" style="background:#f9f9f9;border-top:1px solid var(--brand-cream);padding:1rem;">
                <button type="button" class="admin-btn admin-btn-sm" style="background:transparent;color:var(--brand-dark);border-color:var(--brand-earth);" onclick="closeModal('modal-manage-product')">Cancel</button>
                <button type="button" class="admin-btn admin-btn-sm" onclick="window.saveProductAssets()">Save Changes</button>
            </div>
        </div>

        <!-- ======================== TAB: STOCK (Variants) ======================== -->
        <div class="admin-modal-tab-panel" id="manage-tab-stock">
            <div class="admin-modal-body">
                <p style="font-size:0.8rem;color:var(--gray-500);margin-bottom:1rem;">
                    Manage color/size combinations and their stock levels. Each row is a purchasable variant.
                </p>

                <!-- Existing Variants Table -->
                <div class="admin-table-container" style="margin-bottom:1.5rem;" id="stock-table-wrap">
                    <table class="admin-table" id="stock-variants-table">
                        <thead>
                            <tr>
                                <th>Color</th>
                                <th>Size</th>
                                <th>Stock</th>
                                <th>Price +/-</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="stock-variants-body">
                            <!-- JS will populate -->
                        </tbody>
                    </table>
                </div>

                <!-- Add New Variant Form -->
                <div class="admin-variant-add-section">
                    <h4 class="admin-variant-add-title">+ Add New Variant</h4>
                    <form id="form-add-variant" onsubmit="event.preventDefault(); window.uiAddVariant();">
                        <div class="admin-form-row" style="grid-template-columns: 1fr 1fr 1fr 1fr;">
                            <div class="admin-form-group">
                                <label for="new-variant-color">Color</label>
                                <input type="text" id="new-variant-color" required placeholder="e.g. Black">
                            </div>
                            <div class="admin-form-group">
                                <label for="new-variant-size">Size</label>
                                <select id="new-variant-size" required>
                                    <option value="XS">XS</option>
                                    <option value="S">S</option>
                                    <option value="M" selected>M</option>
                                    <option value="L">L</option>
                                    <option value="XL">XL</option>
                                    <option value="XXL">XXL</option>
                                </select>
                            </div>
                            <div class="admin-form-group">
                                <label for="new-variant-stock">Stock</label>
                                <input type="number" id="new-variant-stock" min="0" value="0" required>
                            </div>
                            <div class="admin-form-group">
                                <label for="new-variant-price">Price +/-</label>
                                <input type="number" id="new-variant-price" step="0.01" value="0.00">
                            </div>
                        </div>
                        <div style="text-align:right;margin-top:0.5rem;">
                            <button type="submit" class="admin-btn admin-btn-sm">Add Variant</button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="admin-modal-footer" style="background:#f9f9f9;border-top:1px solid var(--brand-cream);padding:1rem;">
                <button type="button" class="admin-btn admin-btn-sm" style="background:transparent;color:var(--brand-dark);border-color:var(--brand-earth);" onclick="closeModal('modal-manage-product')">Cancel</button>
                <button type="button" class="admin-btn admin-btn-sm" onclick="window.saveProductAssets()">Save Changes</button>
            </div>
        </div>

    </div>
</div>

<!-- ====================================================================
     MODAL: EDIT VARIANT (inline edit popup)
     ==================================================================== -->
<div class="admin-modal-backdrop" id="modal-edit-variant">
    <div class="admin-modal" style="max-width:520px;">
        <div class="admin-modal-header">
            <h3>Edit Variant</h3>
            <button class="admin-modal-close" data-close-modal="modal-edit-variant">&times;</button>
        </div>
        <form action="admin_manage_product.php" method="POST">
            <input type="hidden" name="action" value="edit_variant">
            <input type="hidden" name="variant_id" id="ev-variant-id">
            <div class="admin-modal-body">
                <div class="admin-form-row">
                    <div class="admin-form-group">
                        <label for="ev-color">Color</label>
                        <input type="text" id="ev-color" name="color" required>
                    </div>
                    <div class="admin-form-group">
                        <label for="ev-size">Size</label>
                        <select id="ev-size" name="size" required>
                            <option value="XS">XS</option>
                            <option value="S">S</option>
                            <option value="M">M</option>
                            <option value="L">L</option>
                            <option value="XL">XL</option>
                            <option value="XXL">XXL</option>
                        </select>
                    </div>
                </div>
                <div class="admin-form-row">
                    <div class="admin-form-group">
                        <label for="ev-stock">Stock Quantity</label>
                        <input type="number" id="ev-stock" name="stock_quantity" min="0" required>
                    </div>
                    <div class="admin-form-group">
                        <label for="ev-price-mod">Price Modifier ($)</label>
                        <input type="number" id="ev-price-mod" name="price_modifier" step="0.01">
                    </div>
                </div>
            </div>
            <div class="admin-modal-footer">
                <button type="button" class="admin-btn admin-btn-sm" style="background:transparent;color:var(--brand-dark);border-color:var(--brand-earth);" data-close-modal="modal-edit-variant">Cancel</button>
                <button type="submit" class="admin-btn admin-btn-sm">Save Variant</button>
            </div>
        </form>
    </div>
</div>
</div>

<!-- ====================================================================
     MODAL: MANAGE ORDER
     ==================================================================== -->
<div class="admin-modal-backdrop" id="modal-manage-order">
    <div class="admin-modal" style="max-width: 700px;">
        <div class="admin-modal-header">
            <h3 id="mo-title">Order Details</h3>
            <button class="admin-modal-close" data-close-modal="modal-manage-order">&times;</button>
        </div>
        <div class="admin-modal-body" style="max-height: 70vh; overflow-y: auto;">
            <!-- Order Info -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem; font-size: 0.875rem;">
                <div>
                    <p style="color: var(--gray-500); margin: 0;">Order Number</p>
                    <p style="font-weight: 600; margin: 0.25rem 0 0;" id="mo-number"></p>
                </div>
                <div>
                    <p style="color: var(--gray-500); margin: 0;">Date</p>
                    <p style="font-weight: 600; margin: 0.25rem 0 0;" id="mo-date"></p>
                </div>
                <div>
                    <p style="color: var(--gray-500); margin: 0;">Payment Method</p>
                    <p style="font-weight: 600; margin: 0.25rem 0 0; text-transform: capitalize;" id="mo-payment"></p>
                </div>
                <div>
                    <p style="color: var(--gray-500); margin: 0;">Payment Status</p>
                    <p style="font-weight: 600; margin: 0.25rem 0 0; text-transform: capitalize;" id="mo-payment-status"></p>
                </div>
            </div>

            <!-- Items Table -->
            <h4 style="font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--gray-500); margin-bottom: 0.75rem;">Items Ordered</h4>
            <table style="width: 100%; border-collapse: collapse; font-size: 0.875rem; margin-bottom: 1.5rem;">
                <thead>
                    <tr style="border-bottom: 1px solid #eee; text-align: left;">
                        <th style="padding: 0.5rem 0;">Product</th>
                        <th style="padding: 0.5rem 0;">Variant</th>
                        <th style="padding: 0.5rem 0;">Qty</th>
                        <th style="padding: 0.5rem 0;">Price</th>
                    </tr>
                </thead>
                <tbody id="mo-items-body"></tbody>
            </table>

            <!-- Totals -->
            <div style="text-align: right; font-size: 0.875rem; margin-bottom: 1.5rem; border-top: 1px solid #eee; padding-top: 1rem;">
                <p style="margin: 0.25rem 0;">Subtotal: $<span id="mo-subtotal"></span></p>
                <p style="margin: 0.25rem 0;">Shipping: $<span id="mo-shipping"></span></p>
                <p style="margin: 0.25rem 0;">Tax: $<span id="mo-tax"></span></p>
                <p style="margin: 0.5rem 0; font-weight: 700; font-size: 1.1rem;">Total: $<span id="mo-total"></span></p>
            </div>

            <!-- Shipping Details -->
            <h4 style="font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--gray-500); margin-bottom: 0.75rem;">Shipping Details</h4>
            <div style="font-size: 0.875rem; margin-bottom: 1.5rem; line-height: 1.6;">
                <p style="margin: 0;" id="mo-ship-name"></p>
                <p style="margin: 0;" id="mo-ship-phone"></p>
                <p style="margin: 0;" id="mo-ship-address"></p>
                <p style="margin: 0;"><span id="mo-ship-city"></span>, <span id="mo-ship-country"></span></p>
            </div>

            <!-- Update Status -->
            <h4 style="font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--gray-500); margin-bottom: 0.75rem;">Update Status</h4>
            <div style="display: flex; gap: 0.75rem; align-items: center;">
                <select id="mo-status-select" style="flex-grow: 1; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;">
                    <option value="pending">Pending</option>
                    <option value="processing">Processing</option>
                    <option value="in_transit">In Transit</option>
                    <option value="delivered">Delivered</option>
                    <option value="cancelled">Cancelled</option>
                </select>
                <button class="admin-btn admin-btn-sm" onclick="updateOrderStatus()">Save</button>
            </div>
            <input type="hidden" id="mo-order-id">
        </div>
        <div class="admin-modal-footer">
            <button type="button" class="admin-btn admin-btn-sm" style="background:transparent;color:var(--brand-dark);border-color:var(--brand-earth);" data-close-modal="modal-manage-order">Close</button>
        </div>
    </div>
</div>

<!-- ====================================================================
     JAVASCRIPT
     ==================================================================== -->
<script>
    // ============================
    // DATA FROM PHP
    // ============================
    const productImagesData = <?= json_encode($productImages, JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    const productVariantsData = <?= json_encode($productVariants, JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    const orderItemsData = <?= json_encode($orderItemsData, JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

    // ============================
    // ORDER MANAGEMENT FUNCTIONS
    // ============================
    function filterOrders() {
        const filter = document.getElementById('orderStatusFilter').value;
        const rows = document.querySelectorAll('#ordersTable tbody tr[data-status]');
        rows.forEach(row => {
            if (filter === 'all' || row.getAttribute('data-status') === filter) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    function deleteOrder(orderId) {
        const formData = new FormData();
        formData.append('action', 'delete_order');
        formData.append('order_id', orderId);

        fetch('admin_manage_order.php', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Failed to delete order.');
            }
        })
        .catch(err => {
            console.error(err);
            alert('A network error occurred.');
        });
    }

    // Open order modal
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.btn-manage-order').forEach(btn => {
            btn.addEventListener('click', function() {
                const d = this.dataset;
                document.getElementById('mo-order-id').value = d.orderId;
                document.getElementById('mo-title').textContent = 'Order ' + d.orderNumber;
                document.getElementById('mo-number').textContent = d.orderNumber;
                document.getElementById('mo-date').textContent = d.orderDate;
                document.getElementById('mo-payment').textContent = d.orderPayment;
                document.getElementById('mo-payment-status').textContent = d.orderPaymentStatus;
                document.getElementById('mo-subtotal').textContent = d.orderSubtotal;
                document.getElementById('mo-shipping').textContent = d.orderShipping;
                document.getElementById('mo-tax').textContent = d.orderTax;
                document.getElementById('mo-total').textContent = d.orderTotal;
                document.getElementById('mo-ship-name').textContent = d.orderName;
                document.getElementById('mo-ship-phone').textContent = d.orderPhone;
                document.getElementById('mo-ship-address').textContent = d.orderAddress;
                document.getElementById('mo-ship-city').textContent = d.orderCity;
                document.getElementById('mo-ship-country').textContent = d.orderCountry;
                document.getElementById('mo-status-select').value = d.orderStatus;

                // Populate items table
                const tbody = document.getElementById('mo-items-body');
                tbody.innerHTML = '';
                const items = orderItemsData[d.orderId] || [];
                if (items.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="4" style="text-align:center; padding:1rem; color:#999;">No items found.</td></tr>';
                } else {
                    items.forEach(item => {
                        const tr = document.createElement('tr');
                        tr.style.borderBottom = '1px solid #f5f5f5';
                        tr.innerHTML = `
                            <td style="padding:0.5rem 0;">${item.name}</td>
                            <td style="padding:0.5rem 0;">${item.color} / ${item.size}</td>
                            <td style="padding:0.5rem 0;">${item.quantity}</td>
                            <td style="padding:0.5rem 0;">$${parseFloat(item.price).toFixed(2)}</td>
                        `;
                        tbody.appendChild(tr);
                    });
                }

                document.getElementById('modal-manage-order').classList.add('show');
            });
        });
    });

    function updateOrderStatus() {
        const orderId = document.getElementById('mo-order-id').value;
        const newStatus = document.getElementById('mo-status-select').value;

        const formData = new FormData();
        formData.append('action', 'update_status');
        formData.append('order_id', orderId);
        formData.append('status', newStatus);

        fetch('admin_manage_order.php', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Failed to update status.');
            }
        })
        .catch(err => {
            console.error(err);
            alert('A network error occurred.');
        });
    }
</script>
<script src="../scripts/admin_dashboard.js?v=<?= time() ?>"></script>

<?php include '../includes/footer.php'; ?>
