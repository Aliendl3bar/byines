<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$pageTitle = 'Your Shopping Cart';
require_once '../classes/Cart.php';
require_once '../classes/Product.php';

$cart = new Cart();
$productModel = new Product();
$cartItems = $cart->getItems();
$subtotal = $cart->getSubtotal();
$itemCount = $cart->getCount();
$tax = round($subtotal * 0.10, 2);
$total = $cart->getTotal();

include '../includes/header.php'; 
?>
    <link rel="stylesheet" href="../css/cart-checkout.css">
    <main class="cart-checkout-container">
        <!-- Breadcrumb Navigation -->
        <nav class="breadcrumb-nav">
            <a href="index.php">Home</a>
            <span>/</span>
            <span class="breadcrumb-current" style="color: var(--brand-dark); font-size: 0.875rem; text-transform: uppercase; letter-spacing: 0.1em; font-weight: 600;">Shopping Cart</span>
        </nav>

        <h1 class="page-title">Your Shopping Cart</h1>

        <!-- Cart Content -->
        <div class="cart-checkout-layout">
            
            <!-- Cart Items List -->
            <section class="cart-items">
                <div id="emptyCart" class="empty-cart-message" style="display: <?= empty($cartItems) ? 'block' : 'none' ?>;">
                    <p>Your cart is empty</p>
                    <a href="shop.php">Continue Shopping</a>
                </div>

                <div id="cartContent" style="display: <?= empty($cartItems) ? 'none' : 'block' ?>;">
                    <?php if (!empty($cartItems)): ?>
                        <?php foreach ($cartItems as $key => $item): 
                            $img = $productModel->getMainImage($item['product_id']);
                            $imgSrc = $img ? "../products/{$item['product_id']}/img/{$img}" : "../assets/placeholder.png";
                        ?>
                        <div class="cart-item" id="item_<?= htmlspecialchars($key) ?>">
                            <div class="cart-item-image">
                                <img src="<?= $imgSrc ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                            </div>
                            <div class="cart-item-info">
                                <div class="cart-item-details">
                                    <h3><?= htmlspecialchars($item['name']) ?></h3>
                                    <?php if ($item['color'] || $item['size']): ?>
                                        <p>
                                            <?php if ($item['color']): ?>Color: <strong><?= htmlspecialchars($item['color']) ?></strong><?php endif; ?>
                                            <?php if ($item['color'] && $item['size']): ?> | <?php endif; ?>
                                            <?php if ($item['size']): ?>Size: <strong><?= htmlspecialchars($item['size']) ?></strong><?php endif; ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                                <div class="cart-item-footer">
                                    <div class="qty-control">
                                        <button onclick="decreaseItemQty('<?= htmlspecialchars($key) ?>')">−</button>
                                        <input type="text" id="qty_<?= htmlspecialchars($key) ?>" value="<?= htmlspecialchars($item['quantity']) ?>" readonly>
                                        <button onclick="increaseItemQty('<?= htmlspecialchars($key) ?>')">+</button>
                                    </div>
                                    <div class="item-price-section">
                                        <p class="item-price">$<?= number_format($item['price'], 2) ?></p>
                                        <button class="remove-btn" onclick="removeItem('<?= htmlspecialchars($key) ?>')">Remove</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <!-- Continue Shopping -->
                    <div class="continue-shopping-section">
                        <a href="shop.php" class="continue-shopping-link">Continue Shopping</a>
                    </div>
                </div>
            </section>

            <!-- Order Summary Sidebar -->
            <aside class="order-summary" style="display: <?= empty($cartItems) ? 'none' : 'block' ?>;">
                <h2>Order Summary</h2>
                
                <div class="summary-row">
                    <span class="summary-label" id="summary-subtotal-label">Subtotal (<?= $itemCount ?> items)</span>
                    <span class="summary-value" id="summary-subtotal">$<?= number_format($subtotal, 2) ?></span>
                </div>

                <div class="summary-row">
                    <span class="summary-label">Shipping</span>
                    <span class="summary-value">FREE</span>
                </div>

                <div class="summary-row">
                    <span class="summary-label">Tax</span>
                    <span class="summary-value" id="summary-tax">$<?= number_format($tax, 2) ?></span>
                </div>

                <!-- Promo Code -->
                <div class="promo-code-section">
                    <label class="promo-code-label">Promo Code</label>
                    <div class="promo-code-input-group">
                        <input type="text" placeholder="Enter code" />
                        <button>Apply</button>
                    </div>
                </div>

                <!-- Total -->
                <div class="order-total">
                    <span class="total-label">Total</span>
                    <span class="total-amount" id="summary-total">$<?= number_format($total, 2) ?></span>
                </div>

                <!-- Checkout Button -->
                <a href="checkout.php" class="checkout-btn">Proceed to Checkout</a>

                <!-- Additional Info -->
                <div class="summary-info">
                    <p><strong>✓</strong> Free shipping on orders over $100</p>
                    <p><strong>✓</strong> 30-day return policy</p>
                    <p><strong>✓</strong> Secure checkout</p>
                </div>
            </aside>
        </div>
    </main>

    <script src="../scripts/cart.js?v=<?= time() ?>"></script>

<?php include '../includes/footer.php'; ?>
