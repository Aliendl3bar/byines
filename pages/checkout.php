<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$pageTitle = 'Checkout';
require_once '../classes/Cart.php';

$cart = new Cart();

// Check if cart is empty
if ($cart->isEmpty()) {
    echo "<script>window.location.href='cart.php';</script>";
    exit;
}

$cartItems = $cart->getItems();
$subtotal = $cart->getSubtotal();
$itemCount = $cart->getCount();
$tax = round($subtotal * 0.10, 2); // 10% tax on goods

include '../includes/header.php'; 
?>
<link rel="stylesheet" href="../css/cart-checkout.css">

    <main class="cart-checkout-container">
        <nav class="breadcrumb-nav">
            <a href="index.php">Home</a>
            <span>/</span>
            <a href="cart.php">Cart</a>
            <span>/</span>
            <span style="color: var(--brand-dark); font-size: 0.875rem; text-transform: uppercase; letter-spacing: 0.1em; font-weight: 600;">Checkout</span>
        </nav>

        <h1 class="page-title">Checkout</h1>

        <!-- Progress Steps -->
        <div class="progress-steps">
            <div class="progress-step completed">
                <div class="step-number">✓</div>
                <p class="step-label">Cart</p>
            </div>
            <div class="progress-step active">
                <div class="step-number">2</div>
                <p class="step-label">Shipping</p>
            </div>
            <div class="progress-step">
                <div class="step-number">3</div>
                <p class="step-label">Payment</p>
            </div>
        </div>

        <div class="cart-checkout-layout">
            <section class="checkout-form">
                <!-- Shipping Information -->
                <div class="form-section">
                    <h2>Shipping Information</h2>
                    
                    <div class="form-row two-columns">
                        <div class="form-group">
                            <label>First Name</label>
                            <input type="text" id="first_name" placeholder="Enter first name" required>
                        </div>
                        <div class="form-group">
                            <label>Last Name</label>
                            <input type="text" id="last_name" placeholder="Enter last name" required>
                        </div>
                    </div>

                    <div class="form-row two-columns">
                        <div class="form-group">
                            <label>Email Address</label>
                            <input type="email" id="email" placeholder="your.email@example.com" required>
                        </div>
                        <div class="form-group">
                            <label>Phone Number</label>
                            <input type="tel" id="phone" placeholder="+212 (0) 6..." required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Street Address</label>
                        <input type="text" id="address" placeholder="123 Main Street" required>
                    </div>

                    <div class="form-row two-columns">
                        <div class="form-group">
                            <label>City</label>
                            <input type="text" id="city" placeholder="Tangier" required oninput="calculateShipping()">
                        </div>
                        <div class="form-group">
                            <label>Country</label>
                            <select id="country" disabled style="background-color: #f1f1f1; cursor: not-allowed; color: #333; opacity: 0.8;">
                                <option value="Morocco" selected>Morocco</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Payment Method -->
                <div class="form-section">
                    <h2>Payment Method</h2>
                    
                    <div class="shipping-option selected" id="payment_cod_wrapper" style="cursor: pointer;" onclick="selectPayment('cod')">
                        <input type="radio" name="payment_method" id="payment_cod" value="cash_on_delivery" checked>
                        <label for="payment_cod" style="display:flex; align-items:center; gap: 10px; width: 100%;">
                            <span class="material-symbols-outlined icon-md">local_shipping</span>
                            <div style="flex-grow:1;">
                                <div class="shipping-name">Cash on Delivery</div>
                                <div class="shipping-time">Pay when you receive the item</div>
                            </div>
                        </label>
                    </div>

                    <div class="shipping-option" id="payment_paypal_wrapper" style="cursor: pointer;" onclick="selectPayment('paypal')">
                        <input type="radio" name="payment_method" id="payment_paypal" value="paypal">
                        <label for="payment_paypal" style="display:flex; align-items:center; gap: 10px; width: 100%;">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/b/b5/PayPal.svg" alt="PayPal" style="height: 24px;">
                            <div style="flex-grow:1;">
                                <div class="shipping-name">Pay with PayPal</div>
                                <div class="shipping-time">Secure checkout via PayPal Gateway</div>
                            </div>
                        </label>
                    </div>
                </div>

            </section>

            <!-- Order Summary -->
            <aside class="order-summary">
                <h2>Order Summary</h2>
                
                <div class="items-preview" style="margin-bottom: 1.5rem; padding-bottom: 1.5rem; border-bottom: 1px solid var(--gray-300);">
                    <?php foreach ($cartItems as $item): ?>
                    <div class="preview-item" style="display: flex; justify-content: space-between; margin-bottom: 0.5rem; font-size: 0.875rem;">
                        <span style="color: var(--gray-500);"><?= htmlspecialchars($item['name']) ?> (<?= $item['quantity'] ?>x)</span>
                        <span style="color: var(--brand-dark); font-weight: 500;">$<?= number_format($item['price'] * $item['quantity'], 2) ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="summary-row">
                    <span class="summary-label">Subtotal</span>
                    <span class="summary-value">$<span id="summary-subtotal"><?= number_format($subtotal, 2) ?></span></span>
                </div>

                <div class="summary-row">
                    <span class="summary-label">Shipping Fees</span>
                    <span class="summary-value" id="summary-shipping" style="color: #4CAF50;">Calculating...</span>
                </div>

                <div class="summary-row">
                    <span class="summary-label">Tax</span>
                    <span class="summary-value">$<span id="summary-tax"><?= number_format($tax, 2) ?></span></span>
                </div>

                <div class="order-total">
                    <span class="total-label">Total</span>
                    <span class="total-amount">$<span id="summary-total">---</span></span>
                </div>

                <button onclick="processOrder()" class="place-order-btn" id="placeOrderBtn" style="margin-top: 1.5rem;">Place Order</button>
                <a href="cart.php" class="back-to-cart-btn">Back to Cart</a>
            </aside>
        </div>
    </main>

    <script>
        const PHP_SUBTOTAL = <?= $subtotal ?>;
        const PHP_TAX = <?= $tax ?>;
    </script>
    <script src="../scripts/checkout.js?v=<?= time() ?>"></script>

<?php include '../includes/footer.php'; ?>
