<?php include '../includes/header.php'; ?>
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
                <div id="emptyCart" class="empty-cart-message" style="display: none;">
                    <p>Your cart is empty</p>
                    <a href="collections.php">Continue Shopping</a>
                </div>

                <div id="cartContent" style="display: block;">
                    <!-- Cart Item 1 -->
                    <div class="cart-item">
                        <div class="cart-item-image">
                            <img src="../assets/boutique_byines_3314221782547857149.png" alt="Elegant Abaya">
                        </div>
                        <div class="cart-item-info">
                            <div class="cart-item-details">
                                <h3>Elegant Abaya</h3>
                                <p>SKU: ABAYA-001</p>
                                <p>Color: <strong>Black</strong> | Size: <strong>M</strong></p>
                            </div>
                            <div class="cart-item-footer">
                                <div class="qty-control">
                                    <button onclick="decreaseItemQty(1)">−</button>
                                    <input type="text" value="1" readonly>
                                    <button onclick="increaseItemQty(1)">+</button>
                                </div>
                                <div class="item-price-section">
                                    <p class="item-price">$360</p>
                                    <button class="remove-btn" onclick="removeItem(1)">Remove</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Cart Item 2 -->
                    <div class="cart-item">
                        <div class="cart-item-image">
                            <img src="../assets/boutique_byines_3469503560297835658.png" alt="Classic Abaya">
                        </div>
                        <div class="cart-item-info">
                            <div class="cart-item-details">
                                <h3>Classic Abaya</h3>
                                <p>SKU: ABAYA-002</p>
                                <p>Color: <strong>Brown</strong> | Size: <strong>S</strong></p>
                            </div>
                            <div class="cart-item-footer">
                                <div class="qty-control">
                                    <button onclick="decreaseItemQty(2)">−</button>
                                    <input type="text" value="1" readonly>
                                    <button onclick="increaseItemQty(2)">+</button>
                                </div>
                                <div class="item-price-section">
                                    <p class="item-price">$250</p>
                                    <button class="remove-btn" onclick="removeItem(2)">Remove</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Cart Item 3 -->
                    <div class="cart-item">
                        <div class="cart-item-image">
                            <img src="../assets/boutique_byines_3844045366027357924.png" alt="Modern Abaya">
                        </div>
                        <div class="cart-item-info">
                            <div class="cart-item-details">
                                <h3>Modern Abaya</h3>
                                <p>SKU: ABAYA-003</p>
                                <p>Color: <strong>Charcoal</strong> | Size: <strong>L</strong></p>
                            </div>
                            <div class="cart-item-footer">
                                <div class="qty-control">
                                    <button onclick="decreaseItemQty(3)">−</button>
                                    <input type="text" value="2" readonly>
                                    <button onclick="increaseItemQty(3)">+</button>
                                </div>
                                <div class="item-price-section">
                                    <p class="item-price">$580</p>
                                    <button class="remove-btn" onclick="removeItem(3)">Remove</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Continue Shopping -->
                    <div class="continue-shopping-section">
                        <a href="collections.php" class="continue-shopping-link">Continue Shopping</a>
                    </div>
                </div>
            </section>

            <!-- Order Summary Sidebar -->
            <aside class="order-summary">
                <h2>Order Summary</h2>
                
                <div class="summary-row">
                    <span class="summary-label">Subtotal (3 items)</span>
                    <span class="summary-value">$1,190.00</span>
                </div>

                <div class="summary-row">
                    <span class="summary-label">Shipping</span>
                    <span class="summary-value">FREE</span>
                </div>

                <div class="summary-row">
                    <span class="summary-label">Tax</span>
                    <span class="summary-value">$119.00</span>
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
                    <span class="total-amount">$1,309.00</span>
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

    <script>
        function increaseItemQty(itemId) {
            const inputs = document.querySelectorAll('.cart-item input[readonly]');
            const input = inputs[itemId - 1];
            input.value = parseInt(input.value) + 1;
            updateCartSummary();
        }

        function decreaseItemQty(itemId) {
            const inputs = document.querySelectorAll('.cart-item input[readonly]');
            const input = inputs[itemId - 1];
            if (parseInt(input.value) > 1) {
                input.value = parseInt(input.value) - 1;
            }
            updateCartSummary();
        }

        function removeItem(itemId) {
            const items = document.querySelectorAll('.cart-item');
            items[itemId - 1].remove();
            
            // Check if cart is empty
            if (document.querySelectorAll('.cart-item').length === 0) {
                document.getElementById('cartContent').style.display = 'none';
                document.getElementById('emptyCart').style.display = 'block';
            }
            
            updateCartSummary();
        }

        function updateCartSummary() {
            // This would typically update the totals dynamically
            // For now, it's a placeholder for the functionality
            console.log('Cart updated');
        }
    </script>

<?php include '../includes/footer.php'; ?>
