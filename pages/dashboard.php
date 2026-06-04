<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Secure PHP Auth Guard
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once '../classes/Database.php';
$db = Database::getInstance();
$pdo = $db->getConnection();

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

include '../includes/header.php'; 
?>
    <!-- Page Specific Stylesheet linked inside the page itself, not the header -->
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
                <p>Welcome to your dashboard. Here you can track your recent orders, manage shipping addresses, and edit account settings.</p>
            </div>
            <div class="welcome-stats">
                <div class="welcome-stat-item">
                    <p class="welcome-stat-val" id="statOrdersCount">2</p>
                    <p class="welcome-stat-lbl">Orders Placed</p>
                </div>
                <div class="welcome-stat-item">
                    <p class="welcome-stat-val" id="statWishlistCount">3</p>
                    <p class="welcome-stat-lbl">Wishlist Items</p>
                </div>
            </div>
        </section>

        <!-- Core Dashboard Layout -->
        <div class="dashboard-layout">
            
            <!-- Sidebar Navigation -->
            <aside class="dashboard-sidebar">
                <ul class="sidebar-menu">
                    <li>
                        <button class="sidebar-menu-btn active" data-tab="overview">
                            <svg viewBox="0 0 24 24"><path d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12Zm11.378-3.917c-.742.742-1.02 1.851-.734 2.825l.215.733-.733.215c-.974.286-2.083.008-2.825-.734l-1.06-1.058-2.457 2.456 2.457 2.457c.742.741.742 1.944 0 2.685-.741.742-1.944.742-2.685 0L3.33 12.21l8.363-8.364 2.457 2.457c.742.741.742 1.944 0 2.685-.741.742-1.944.742-2.685 0l-.88-.88Z"/></svg>
                            Overview
                        </button>
                    </li>
                    <li>
                        <button class="sidebar-menu-btn" data-tab="orders">
                            <svg viewBox="0 0 24 24"><path d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/></svg>
                            Order History
                        </button>
                    </li>
                    <li>
                        <button class="sidebar-menu-btn" data-tab="wishlist">
                            <svg viewBox="0 0 24 24"><path d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z"/></svg>
                            Wishlist
                        </button>
                    </li>
                    <li>
                        <button class="sidebar-menu-btn" data-tab="addresses">
                            <svg viewBox="0 0 24 24"><path d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75 0 4.13 2.557 7.667 6.136 9.106a.75.75 0 0 0 .972-.942l-1.077-3.77a.75.75 0 0 1 .374-.836 5.25 5.25 0 0 0 2.345-4.437l-.001-3.62a.75.75 0 0 1 .75-.75h1.5a.75.75 0 0 1 .75.75v3.62a5.25 5.25 0 0 0 2.345 4.437.75.75 0 0 1 .374.836l-1.077 3.77a.75.75 0 0 0 .972.942c3.579-1.439 6.136-4.976 6.136-9.106 0-5.385-4.365-9.75-9.75-9.75Z"/></svg>
                            Address Book
                        </button>
                    </li>
                    <li>
                        <button class="sidebar-menu-btn" data-tab="profile">
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
                            <div class="order-widget-row">
                                <div>
                                    <p class="order-widget-id">Order #BY-1082</p>
                                    <p class="order-widget-date">Placed on May 28, 2026</p>
                                </div>
                                <span class="status-badge status-transit">In Transit</span>
                            </div>
                            <div class="order-tracker">
                                <div class="tracker-bar">
                                    <div class="tracker-progress" style="width: 65%;"></div>
                                </div>
                                <div class="tracker-labels">
                                    <span>Placed</span>
                                    <span>Shipped</span>
                                    <span>Delivered</span>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Links -->
                        <div class="overview-card">
                            <h3>Quick Actions</h3>
                            <ul class="quick-links-list">
                                <li>
                                    <a href="#" onclick="switchTab('orders')">
                                        <span>View Order History</span>
                                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M9 5l7 7-7 7"/></svg>
                                    </a>
                                </li>
                                <li>
                                    <a href="#" onclick="switchTab('wishlist')">
                                        <span>Manage Wishlist</span>
                                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M9 5l7 7-7 7"/></svg>
                                    </a>
                                </li>
                                <li>
                                    <a href="#" onclick="switchTab('profile')">
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
                        
                        <!-- Order 1 -->
                        <div class="order-history-card">
                            <div class="order-card-header">
                                <div class="order-header-meta">
                                    <div class="order-meta-item">
                                        <p>Order Placed</p>
                                        <p>May 28, 2026</p>
                                    </div>
                                    <div class="order-meta-item">
                                        <p>Total</p>
                                        <p>$360.00</p>
                                    </div>
                                    <div class="order-meta-item">
                                        <p>Ship To</p>
                                        <p id="shipToName1">Guest User</p>
                                    </div>
                                </div>
                                <div class="order-meta-item" style="text-align: right;">
                                    <p>Order #BY-1082</p>
                                    <span class="status-badge status-transit" style="margin-top: 0.25rem;">In Transit</span>
                                </div>
                            </div>
                            <div class="order-card-body">
                                <div class="order-items-list">
                                    <div class="order-item-row">
                                        <img src="../assets/boutique_byines_3314221782547857149.png" alt="Elegant Abaya" class="order-item-img">
                                        <div class="order-item-details">
                                            <h4>Elegant Abaya</h4>
                                            <p>Color: Black | Size: M</p>
                                            <p>Qty: 1</p>
                                        </div>
                                        <div class="order-item-price">$360.00</div>
                                    </div>
                                </div>
                            </div>
                            <div class="order-card-footer">
                                <button class="btn-sm-outline" onclick="alert('Tracking ID: USPS-94001112020269384. Package is currently at regional distribution facility.')">Track Package</button>
                                <button class="btn-sm-outline" onclick="alert('Review feature coming soon!')">Write Review</button>
                            </div>
                        </div>

                        <!-- Order 2 -->
                        <div class="order-history-card">
                            <div class="order-card-header">
                                <div class="order-header-meta">
                                    <div class="order-meta-item">
                                        <p>Order Placed</p>
                                        <p>April 12, 2026</p>
                                    </div>
                                    <div class="order-meta-item">
                                        <p>Total</p>
                                        <p>$335.00</p>
                                    </div>
                                    <div class="order-meta-item">
                                        <p>Ship To</p>
                                        <p id="shipToName2">Guest User</p>
                                    </div>
                                </div>
                                <div class="order-meta-item" style="text-align: right;">
                                    <p>Order #BY-0974</p>
                                    <span class="status-badge status-delivered" style="margin-top: 0.25rem;">Delivered</span>
                                </div>
                            </div>
                            <div class="order-card-body">
                                <div class="order-items-list">
                                    <div class="order-item-row">
                                        <img src="../assets/boutique_byines_3469503560297835658.png" alt="Classic Abaya" class="order-item-img">
                                        <div class="order-item-details">
                                            <h4>Classic Abaya</h4>
                                            <p>Color: Brown | Size: S</p>
                                            <p>Qty: 1</p>
                                        </div>
                                        <div class="order-item-price">$250.00</div>
                                    </div>
                                    <div class="order-item-row">
                                        <img src="../assets/boutique_byines_3844045366027357924.png" alt="Organic Cotton Scarf" class="order-item-img">
                                        <div class="order-item-details">
                                            <h4>Organic Cotton Scarf</h4>
                                            <p>Color: Natural Sand | Size: One Size</p>
                                            <p>Qty: 1</p>
                                        </div>
                                        <div class="order-item-price">$85.00</div>
                                    </div>
                                </div>
                            </div>
                            <div class="order-card-footer">
                                <button class="btn-sm-outline" href="#">Invoice</button>
                                <button class="btn-sm-outline" onclick="alert('Added item(s) to shopping cart!')">Buy Again</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Wishlist Panel -->
                <div class="tab-panel" id="panel-wishlist">
                    <h2 class="panel-title">Saved Items (Wishlist)</h2>
                    <div class="wishlist-grid" id="wishlistGrid">
                        
                        <!-- Wishlist Item 1 -->
                        <div class="wishlist-card" id="wishlist-item-1">
                            <div class="wishlist-card-image">
                                <img src="../assets/boutique_byines_3628664731358744968.png" alt="Evening Abaya">
                                <button class="wishlist-remove-btn" onclick="removeWishlistItem(1)">×</button>
                            </div>
                            <div class="wishlist-card-content">
                                <div class="wishlist-item-meta">
                                    <h3>Evening Abaya</h3>
                                    <p>$290.00</p>
                                </div>
                                <button class="wishlist-add-cart-btn" onclick="alert('Added Evening Abaya to your cart!')">Add to Cart</button>
                            </div>
                        </div>

                        <!-- Wishlist Item 2 -->
                        <div class="wishlist-card" id="wishlist-item-2">
                            <div class="wishlist-card-image">
                                <img src="../assets/boutique_byines_3581710268287392853.png" alt="Classic Chiffon Niqab">
                                <button class="wishlist-remove-btn" onclick="removeWishlistItem(2)">×</button>
                            </div>
                            <div class="wishlist-card-content">
                                <div class="wishlist-item-meta">
                                    <h3>Classic Chiffon Niqab</h3>
                                    <p>$45.00</p>
                                </div>
                                <button class="wishlist-add-cart-btn" onclick="alert('Added Classic Chiffon Niqab to your cart!')">Add to Cart</button>
                            </div>
                        </div>

                        <!-- Wishlist Item 3 -->
                        <div class="wishlist-card" id="wishlist-item-3">
                            <div class="wishlist-card-image">
                                <img src="../assets/samaa.creat_3847060077198625518's2026-5-14-12.51.304 story.jpg" alt="Pearl Accessory Set">
                                <button class="wishlist-remove-btn" onclick="removeWishlistItem(3)">×</button>
                            </div>
                            <div class="wishlist-card-content">
                                <div class="wishlist-item-meta">
                                    <h3>Pearl Accessory Set</h3>
                                    <p>$120.00</p>
                                </div>
                                <button class="wishlist-add-cart-btn" onclick="alert('Added Pearl Accessory Set to your cart!')">Add to Cart</button>
                            </div>
                        </div>
                    </div>
                    <div id="wishlistEmptyMessage" style="display: none; text-align: center; padding: 3rem 0;">
                        <p style="color: var(--gray-500); margin-bottom: 1.5rem;">Your wishlist is currently empty.</p>
                        <a href="collections.php" class="btn-sm-outline" style="text-decoration: none;">Browse Collections</a>
                    </div>
                </div>

                <!-- Address Book Panel -->
                <div class="tab-panel" id="panel-addresses">
                    <h2 class="panel-title">Address Book</h2>
                    <div class="addresses-grid">
                        
                        <!-- Address 1 (Shipping) -->
                        <div class="address-card">
                            <div class="address-card-header">
                                <h3>Shipping Address</h3>
                                <span class="badge-outline">Primary</span>
                            </div>
                            <div class="address-card-body">
                                <p id="addrName">Sarah Martinez</p>
                                <p>123 Main Street, Apt 4B</p>
                                <p>New York, NY 10001</p>
                                <p>United States</p>
                                <p style="margin-top: 0.5rem;">Phone: +1 (555) 123-4567</p>
                            </div>
                            <div class="address-card-actions">
                                <button onclick="alert('Edit address details feature coming soon!')">Edit</button>
                                <button onclick="alert('Primary addresses cannot be deleted directly.')" style="color: var(--gray-400);">Delete</button>
                            </div>
                        </div>

                        <!-- Address 2 (Billing) -->
                        <div class="address-card">
                            <div class="address-card-header">
                                <h3>Billing Address</h3>
                                <span class="badge-outline">Primary</span>
                            </div>
                            <div class="address-card-body">
                                <p id="billName">Sarah Martinez</p>
                                <p>123 Main Street, Apt 4B</p>
                                <p>New York, NY 10001</p>
                                <p>United States</p>
                                <p style="margin-top: 0.5rem;">Phone: +1 (555) 123-4567</p>
                            </div>
                            <div class="address-card-actions">
                                <button onclick="alert('Edit address details feature coming soon!')">Edit</button>
                                <button onclick="alert('Primary addresses cannot be deleted directly.')" style="color: var(--gray-400);">Delete</button>
                            </div>
                        </div>

                        <!-- Add New Address Card -->
                        <div class="address-card add-new" onclick="alert('Add Address feature coming soon!')">
                            <div class="add-new-content">
                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.5v15m7.5-7.5h-15"/>
                                </svg>
                                <p>Add New Address</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Account Details Panel -->
                <div class="tab-panel" id="panel-profile">
                    <h2 class="panel-title">Account Details</h2>
                    <div class="profile-form-wrapper">
                        
                        <!-- Details Edit Form -->
                        <form id="profileForm" onsubmit="saveProfileDetails(event)">
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
                            
                            <h3 style="font-size: 1.1rem; font-weight: 600; margin-bottom: 1.5rem; color: var(--brand-dark);">Change Password</h3>
                            
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
                    </div>
                </div>
            </section>
        </div>
    </main>

    <!-- Tab Logic, User details loading & profile saving scripts -->
    <script src="../scripts/dashboard.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            loadUserProfile();
            
            // Set up sidebar tab selection click handlers
            document.querySelectorAll('.sidebar-menu-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const tabName = this.getAttribute('data-tab');
                    switchTab(tabName);
                });
            });
        });

        // Load profile data into dashboard elements
        window.loadUserProfile = function() {
            const firstName = <?= json_encode($user['first_name'] ?? 'User') ?>;
            const lastName = <?= json_encode($user['last_name'] ?? '') ?>;
            const fullName = `${firstName} ${lastName}`.trim();
            
            // Set names in UI
            document.getElementById('shipToName1').textContent = fullName;
            document.getElementById('shipToName2').textContent = fullName;
            document.getElementById('addrName').textContent = fullName;
            document.getElementById('billName').textContent = fullName;
        }
    </script>

<?php include '../includes/footer.php'; ?>
