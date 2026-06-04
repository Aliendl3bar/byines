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

// --- Fetch Orders ---
$stmtRecentOrders = $pdo->query("
    SELECT id, order_number, total_amount, status, created_at, shipping_name 
    FROM orders 
    ORDER BY created_at DESC 
    LIMIT 50
");
$orders = $stmtRecentOrders->fetchAll();

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


<!-- ====================================================================
     JAVASCRIPT
     ==================================================================== -->
<script>
    // ============================
    // DATA FROM PHP
    // ============================
    const productImagesData = <?= json_encode($productImages, JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    const productVariantsData = <?= json_encode($productVariants, JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
</script>
<script src="../scripts/admin_dashboard.js?v=<?= time() ?>"></script>

<?php include '../includes/footer.php'; ?>

