<?php include '../includes/header.php'; ?>
<link rel="stylesheet" href="../css/cart-checkout.css">

    <main class="cart-checkout-container">
        <!-- Breadcrumb Navigation -->
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

        <!-- Checkout Content -->
        <div class="cart-checkout-layout">
            
            <!-- Checkout Form -->
            <section class="checkout-form">
                <!-- Shipping Information -->
                <div class="form-section">
                    <h2>Shipping Information</h2>
                    
                    <div class="form-row two-columns">
                        <div class="form-group">
                            <label>First Name</label>
                            <input type="text" placeholder="Enter first name">
                        </div>
                        <div class="form-group">
                            <label>Last Name</label>
                            <input type="text" placeholder="Enter last name">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" placeholder="your.email@example.com">
                    </div>

                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="tel" placeholder="+1 (555) 123-4567">
                    </div>

                    <div class="form-group">
                        <label>Street Address</label>
                        <input type="text" placeholder="123 Main Street">
                    </div>

                    <div class="form-row three-columns">
                        <div class="form-group">
                            <label>City</label>
                            <input type="text" placeholder="New York">
                        </div>
                        <div class="form-group">
                            <label>State</label>
                            <input type="text" placeholder="NY">
                        </div>
                        <div class="form-group">
                            <label>ZIP Code</label>
                            <input type="text" placeholder="10001">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Country</label>
                        <select>
                            <option>United States</option>
                            <option>Canada</option>
                            <option>United Kingdom</option>
                            <option>Australia</option>
                        </select>
                    </div>
                </div>

                <!-- Shipping Method -->
                <div class="form-section">
                    <h2>Shipping Method</h2>
                    
                    <div class="shipping-option selected">
                        <input type="radio" name="shipping" id="standard" checked>
                        <label for="standard">
                            <div class="shipping-name">Standard Shipping</div>
                            <div class="shipping-time">5-7 business days</div>
                        </label>
                        <span class="shipping-price">FREE</span>
                    </div>

                    <div class="shipping-option">
                        <input type="radio" name="shipping" id="express">
                        <label for="express">
                            <div class="shipping-name">Express Shipping</div>
                            <div class="shipping-time">2-3 business days</div>
                        </label>
                        <span class="shipping-price">$15.00</span>
                    </div>

                    <div class="shipping-option">
                        <input type="radio" name="shipping" id="overnight">
                        <label for="overnight">
                            <div class="shipping-name">Overnight Shipping</div>
                            <div class="shipping-time">Next business day</div>
                        </label>
                        <span class="shipping-price">$35.00</span>
                    </div>
                </div>

                <!-- Payment Information -->
                <div class="form-section">
                    <h2>Payment Information</h2>
                    
                    <div class="form-group">
                        <label>Cardholder Name</label>
                        <input type="text" placeholder="Name on card">
                    </div>

                    <div class="form-group">
                        <label>Card Number</label>
                        <input type="text" placeholder="1234 5678 9012 3456">
                    </div>

                    <div class="form-row two-columns">
                        <div class="form-group">
                            <label>Expiration Date</label>
                            <input type="text" placeholder="MM/YY">
                        </div>
                        <div class="form-group">
                            <label>CVV</label>
                            <input type="text" placeholder="123">
                        </div>
                    </div>
                </div>

                <!-- Terms & Conditions -->
                <div class="terms-section">
                    <input type="checkbox" id="terms" checked>
                    <label for="terms">
                        I agree to the <a href="#">Terms and Conditions</a> and <a href="#">Privacy Policy</a>
                    </label>
                </div>
            </section>

            <!-- Order Summary -->
            <aside class="order-summary">
                <h2>Order Summary</h2>
                
                <!-- Cart Items Preview -->
                <div class="items-preview">
                    <div class="preview-item">
                        <span>Elegant Abaya (1x)</span>
                        <span>$360</span>
                    </div>
                    <div class="preview-item">
                        <span>Classic Abaya (1x)</span>
                        <span>$250</span>
                    </div>
                    <div class="preview-item">
                        <span>Modern Abaya (2x)</span>
                        <span>$580</span>
                    </div>
                </div>

                <!-- Totals -->
                <div class="summary-row">
                    <span class="summary-label">Subtotal</span>
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

                <!-- Total -->
                <div class="order-total">
                    <span class="total-label">Total</span>
                    <span class="total-amount">$1,309.00</span>
                </div>

                <!-- Place Order Button -->
                <button onclick="placeOrder()" class="place-order-btn">Place Order</button>

                <a href="cart.php" class="back-to-cart-btn">Back to Cart</a>
            </aside>
        </div>
    </main>

    <script>
        function placeOrder() {
            alert('Order placed successfully! Thank you for your purchase.');
            // Redirect to confirmation page
            // window.location.href = 'order-confirmation.php';
        }
    </script>

<?php include '../includes/footer.php'; ?>
