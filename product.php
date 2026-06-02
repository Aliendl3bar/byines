<?php include 'header.php'; ?>

    <main style="max-width: 1280px; margin: 0 auto; padding: 2rem 1.5rem;">
        <!-- Breadcrumb Navigation -->
        <nav class="breadcrumb-nav" style="margin-bottom: 2rem;">
            <a href="index.php" style="color: var(--gray-500); text-decoration: none; font-size: 0.875rem; text-transform: uppercase; letter-spacing: 0.1em;">Home</a>
            <span style="color: var(--gray-500); margin: 0 0.75rem; font-size: 0.75rem;">/</span>
            <a href="collections.php" style="color: var(--gray-500); text-decoration: none; font-size: 0.875rem; text-transform: uppercase; letter-spacing: 0.1em;">Collections</a>
            <span style="color: var(--gray-500); margin: 0 0.75rem; font-size: 0.75rem;">/</span>
            <span style="color: var(--brand-dark); font-size: 0.875rem; text-transform: uppercase; letter-spacing: 0.1em; font-weight: 600;">Elegant Abaya</span>
        </nav>

        <!-- Product Container -->
        <div class="product-detail-container" style="display: grid; grid-template-columns: 1fr 1fr; gap: 4rem; margin-bottom: 6rem;">
            
            <!-- Product Image Gallery -->
            <section class="product-gallery">
                <!-- Main Image -->
                <div class="main-image-wrapper" style="position: relative; aspect-ratio: 3/4; margin-bottom: 1.5rem; overflow: hidden; background-color: #EAE4DE; border-radius: 1rem;">
                    <img id="mainImage" alt="Elegant Abaya - Main View" src="assets/boutique_byines_3314221782547857149.png" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;" />
                    <button class="wishlist-btn" style="position: absolute; top: 1.5rem; right: 1.5rem; padding: 0.75rem; background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(4px); border-radius: 50%; border: none; cursor: pointer; transition: all 0.3s ease;">
                        <svg style="width: 1.5rem; height: 1.5rem;" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>
                    </button>
                </div>

                <!-- Thumbnail Gallery -->
                <div class="thumbnail-gallery" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 0.75rem;">
                    <img class="thumbnail" src="assets/boutique_byines_3314221782547857149.png" alt="View 1" style="width: 100%; aspect-ratio: 3/4; object-fit: cover; border-radius: 0.5rem; cursor: pointer; border: 2px solid var(--brand-dark); opacity: 1; transition: opacity 0.3s ease;" onclick="updateMainImage(this.src)" />
                    <img class="thumbnail" src="assets/boutique_byines_3469503560297835658.png" alt="View 2" style="width: 100%; aspect-ratio: 3/4; object-fit: cover; border-radius: 0.5rem; cursor: pointer; border: 2px solid transparent; opacity: 0.6; transition: opacity 0.3s ease;" onclick="updateMainImage(this.src)" />
                    <img class="thumbnail" src="assets/boutique_byines_3844045366027357924.png" alt="View 3" style="width: 100%; aspect-ratio: 3/4; object-fit: cover; border-radius: 0.5rem; cursor: pointer; border: 2px solid transparent; opacity: 0.6; transition: opacity 0.3s ease;" onclick="updateMainImage(this.src)" />
                    <img class="thumbnail" src="assets/boutique_byines_3628664731358744968.png" alt="View 4" style="width: 100%; aspect-ratio: 3/4; object-fit: cover; border-radius: 0.5rem; cursor: pointer; border: 2px solid transparent; opacity: 0.6; transition: opacity 0.3s ease;" onclick="updateMainImage(this.src)" />
                </div>
            </section>

            <!-- Product Details -->
            <section class="product-details" style="display: flex; flex-direction: column; justify-content: center;">
                <!-- Product Title & Price -->
                <div style="margin-bottom: 2rem;">
                    <h1 style="font-size: 2.5rem; font-weight: 300; color: var(--brand-dark); margin-bottom: 0.5rem;">Elegant Abaya</h1>
                    <p style="color: var(--gray-500); font-size: 1rem; margin-bottom: 1rem;">SKU: ABAYA-001</p>
                    <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem;">
                        <span style="font-size: 1.875rem; font-weight: 600; color: var(--brand-dark);">$360</span>
                        <span style="font-size: 1rem; color: var(--gray-500); text-decoration: line-through;">$450</span>
                        <span style="background-color: var(--brand-earth); color: var(--white); padding: 0.25rem 0.75rem; border-radius: 0.25rem; font-size: 0.875rem; font-weight: 600;">20% OFF</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <span style="color: var(--brand-dark); font-weight: 600;">★★★★★</span>
                        <span style="color: var(--gray-500); font-size: 0.875rem;">(145 reviews)</span>
                    </div>
                </div>

                <!-- Product Description -->
                <div style="margin-bottom: 2rem; padding: 1.5rem; background-color: rgba(244, 241, 238, 0.5); border-radius: 0.75rem;">
                    <p style="color: var(--brand-dark); line-height: 1.8; font-size: 1rem;">
                        Experience timeless elegance with our premium Elegant Abaya. Crafted from luxurious chiffon fabric, this piece combines traditional modest wear with contemporary design. Perfect for any occasion, from casual gatherings to special events.
                    </p>
                    <ul style="margin-top: 1rem; list-style: none; padding-left: 0;">
                        <li style="color: var(--gray-500); margin-bottom: 0.5rem;">✓ Premium chiffon fabric</li>
                        <li style="color: var(--gray-500); margin-bottom: 0.5rem;">✓ Comfortable fit with flowing silhouette</li>
                        <li style="color: var(--gray-500); margin-bottom: 0.5rem;">✓ Embroidered detailing on sleeves</li>
                        <li style="color: var(--gray-500); margin-bottom: 0.5rem;">✓ Available in multiple colors</li>
                    </ul>
                </div>

                <!-- Color Options -->
                <div style="margin-bottom: 2rem;">
                    <label style="display: block; font-weight: 600; color: var(--brand-dark); margin-bottom: 1rem; text-transform: uppercase; font-size: 0.875rem; letter-spacing: 0.1em;">Select Color</label>
                    <div class="color-options" style="display: grid; grid-template-columns: repeat(5, 1fr); gap: 0.75rem;">
                        <button class="color-btn active" style="width: 100%; padding: 1rem; background-color: #1A1A1A; border: 2px solid var(--brand-dark); border-radius: 0.5rem; cursor: pointer; transition: all 0.3s ease; color: var(--white); font-size: 0.875rem; font-weight: 500;" data-color="Black" onclick="selectColor(this)">
                            Black
                        </button>
                        <button class="color-btn" style="width: 100%; padding: 1rem; background-color: #8B7355; border: 2px solid transparent; border-radius: 0.5rem; cursor: pointer; transition: all 0.3s ease; color: var(--white); font-size: 0.875rem; font-weight: 500;" data-color="Brown" onclick="selectColor(this)">
                            Brown
                        </button>
                        <button class="color-btn" style="width: 100%; padding: 1rem; background-color: #4A4A4A; border: 2px solid transparent; border-radius: 0.5rem; cursor: pointer; transition: all 0.3s ease; color: var(--white); font-size: 0.875rem; font-weight: 500;" data-color="Charcoal" onclick="selectColor(this)">
                            Charcoal
                        </button>
                        <button class="color-btn" style="width: 100%; padding: 1rem; background-color: #D4A574; border: 2px solid transparent; border-radius: 0.5rem; cursor: pointer; transition: all 0.3s ease; color: var(--brand-dark); font-size: 0.875rem; font-weight: 500;" data-color="Camel" onclick="selectColor(this)">
                            Camel
                        </button>
                        <button class="color-btn" style="width: 100%; padding: 1rem; background-color: #C9B8A8; border: 2px solid transparent; border-radius: 0.5rem; cursor: pointer; transition: all 0.3s ease; color: var(--brand-dark); font-size: 0.875rem; font-weight: 500;" data-color="Cream" onclick="selectColor(this)">
                            Cream
                        </button>
                    </div>
                    <p id="selectedColor" style="margin-top: 0.75rem; color: var(--gray-500); font-size: 0.875rem;">Selected: <strong>Black</strong></p>
                </div>

                <!-- Size Options -->
                <div style="margin-bottom: 2rem;">
                    <label style="display: block; font-weight: 600; color: var(--brand-dark); margin-bottom: 1rem; text-transform: uppercase; font-size: 0.875rem; letter-spacing: 0.1em;">Select Size</label>
                    <div class="size-options" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 0.75rem;">
                        <button class="size-btn" style="padding: 1rem; border: 1px solid var(--gray-300); background-color: transparent; border-radius: 0.5rem; cursor: pointer; transition: all 0.3s ease; font-weight: 600;" onclick="selectSize(this)">
                            XS
                        </button>
                        <button class="size-btn" style="padding: 1rem; border: 1px solid var(--gray-300); background-color: transparent; border-radius: 0.5rem; cursor: pointer; transition: all 0.3s ease; font-weight: 600;" onclick="selectSize(this)">
                            S
                        </button>
                        <button class="size-btn active" style="padding: 1rem; border: 2px solid var(--brand-dark); background-color: rgba(26, 26, 26, 0.05); border-radius: 0.5rem; cursor: pointer; transition: all 0.3s ease; font-weight: 600;" onclick="selectSize(this)">
                            M
                        </button>
                        <button class="size-btn" style="padding: 1rem; border: 1px solid var(--gray-300); background-color: transparent; border-radius: 0.5rem; cursor: pointer; transition: all 0.3s ease; font-weight: 600;" onclick="selectSize(this)">
                            L
                        </button>
                    </div>
                </div>

                <!-- Quantity Selector -->
                <div style="margin-bottom: 2rem;">
                    <label style="display: block; font-weight: 600; color: var(--brand-dark); margin-bottom: 1rem; text-transform: uppercase; font-size: 0.875rem; letter-spacing: 0.1em;">Quantity</label>
                    <div style="display: flex; align-items: center; border: 1px solid var(--gray-300); border-radius: 0.5rem; width: fit-content;">
                        <button onclick="decreaseQuantity()" style="width: 3rem; height: 3rem; border: none; background: transparent; font-size: 1.5rem; cursor: pointer; transition: opacity 0.3s ease;">−</button>
                        <input id="quantity" type="text" value="1" readonly style="width: 3rem; height: 3rem; border: none; border-left: 1px solid var(--gray-300); border-right: 1px solid var(--gray-300); text-align: center; font-size: 1rem; font-weight: 600;">
                        <button onclick="increaseQuantity()" style="width: 3rem; height: 3rem; border: none; background: transparent; font-size: 1.5rem; cursor: pointer; transition: opacity 0.3s ease;">+</button>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 2rem;">
                    <button class="add-to-cart-btn" style="padding: 1.25rem 2rem; background-color: var(--white); border: 2px solid var(--brand-dark); border-radius: 0.5rem; font-size: 0.875rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.1em; cursor: pointer; transition: all 0.3s ease; color: var(--brand-dark);" onclick="addToCart()">
                        Add to Cart
                    </button>
                    <button class="buy-now-btn" style="padding: 1.25rem 2rem; background-color: var(--brand-dark); border: 2px solid var(--brand-dark); border-radius: 0.5rem; font-size: 0.875rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.1em; cursor: pointer; transition: all 0.3s ease; color: var(--white);" onclick="buyNow()">
                        Buy Now
                    </button>
                </div>

                <!-- Additional Info -->
                <div style="padding-top: 2rem; border-top: 1px solid var(--gray-200);">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-top: 1.5rem;">
                        <div>
                            <p style="font-weight: 600; color: var(--brand-dark); margin-bottom: 0.5rem; font-size: 0.875rem;">Free Shipping</p>
                            <p style="color: var(--gray-500); font-size: 0.875rem;">On orders over $100</p>
                        </div>
                        <div>
                            <p style="font-weight: 600; color: var(--brand-dark); margin-bottom: 0.5rem; font-size: 0.875rem;">Easy Returns</p>
                            <p style="color: var(--gray-500); font-size: 0.875rem;">30-day return policy</p>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <!-- Product Specifications & Reviews Section -->
        <section style="margin-bottom: 6rem;">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 3rem;">
                <!-- Specifications -->
                <div>
                    <h2 style="font-size: 1.5rem; font-weight: 300; color: var(--brand-dark); margin-bottom: 1.5rem;">Specifications</h2>
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr style="border-bottom: 1px solid var(--gray-200);">
                            <td style="padding: 1rem 0; color: var(--gray-500); font-weight: 600; text-transform: uppercase; font-size: 0.875rem;">Material</td>
                            <td style="padding: 1rem 0; color: var(--brand-dark);">100% Premium Chiffon</td>
                        </tr>
                        <tr style="border-bottom: 1px solid var(--gray-200);">
                            <td style="padding: 1rem 0; color: var(--gray-500); font-weight: 600; text-transform: uppercase; font-size: 0.875rem;">Length</td>
                            <td style="padding: 1rem 0; color: var(--brand-dark);">Full length (55-58 inches)</td>
                        </tr>
                        <tr style="border-bottom: 1px solid var(--gray-200);">
                            <td style="padding: 1rem 0; color: var(--gray-500); font-weight: 600; text-transform: uppercase; font-size: 0.875rem;">Care</td>
                            <td style="padding: 1rem 0; color: var(--brand-dark);">Dry clean or hand wash</td>
                        </tr>
                        <tr>
                            <td style="padding: 1rem 0; color: var(--gray-500); font-weight: 600; text-transform: uppercase; font-size: 0.875rem;">Made In</td>
                            <td style="padding: 1rem 0; color: var(--brand-dark);">UAE</td>
                        </tr>
                    </table>
                </div>

                <!-- Reviews -->
                <div>
                    <h2 style="font-size: 1.5rem; font-weight: 300; color: var(--brand-dark); margin-bottom: 1.5rem;">Customer Reviews</h2>
                    <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                        <div style="padding-bottom: 1.5rem; border-bottom: 1px solid var(--gray-200);">
                            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.5rem;">
                                <span style="font-weight: 600; color: var(--brand-dark);">Sarah M.</span>
                                <span style="color: var(--brand-dark);">★★★★★</span>
                            </div>
                            <p style="color: var(--gray-500); font-size: 0.875rem; margin-bottom: 0.5rem;">Verified Purchase</p>
                            <p style="color: var(--brand-dark); line-height: 1.6;">Beautiful quality and perfect fit. The fabric is soft and flows beautifully. Highly recommended!</p>
                        </div>
                        <div>
                            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.5rem;">
                                <span style="font-weight: 600; color: var(--brand-dark);">Fatima A.</span>
                                <span style="color: var(--brand-dark);">★★★★☆</span>
                            </div>
                            <p style="color: var(--gray-500); font-size: 0.875rem; margin-bottom: 0.5rem;">Verified Purchase</p>
                            <p style="color: var(--brand-dark); line-height: 1.6;">Great quality. Shipping took a bit longer than expected but the product is worth the wait.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Related Products -->
        <section style="margin-bottom: 6rem;">
            <h2 class="section-title" style="font-size: 2.25rem; text-align: center; margin-bottom: 4rem; font-weight: 300;">You May Also Like</h2>
            <div class="product-grid" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.5rem 1rem;">
                <div class="product-card" style="cursor: pointer;">
                    <div class="product-img-wrapper" style="position: relative; aspect-ratio: 3/4; margin-bottom: 1rem; overflow: hidden; background-color: #EAE4DE; border-radius: 0.75rem;">
                        <img alt="Classic Abaya" class="hover-scale" src="assets/boutique_byines_3469503560297835658.png" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease-in-out;"/>
                        <button class="wishlist-btn" style="position: absolute; top: 1rem; right: 1rem; padding: 0.5rem; background: rgba(255, 255, 255, 0.5); backdrop-filter: blur(4px); border-radius: 50%; border: none; cursor: pointer;">
                            <svg class="small-icon" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="width: 1.25rem; height: 1.25rem;">
                                <path d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" stroke-linecap="round" stroke-linejoin="round"></path>
                            </svg>
                        </button>
                    </div>
                    <h3 class="product-name" style="font-size: 1rem; font-weight: 500; color: var(--brand-dark); margin-bottom: 0.5rem;">Classic Abaya</h3>
                    <p class="product-price" style="color: var(--gray-500); font-size: 0.875rem;">$250</p>
                </div>
                <div class="product-card" style="cursor: pointer;">
                    <div class="product-img-wrapper" style="position: relative; aspect-ratio: 3/4; margin-bottom: 1rem; overflow: hidden; background-color: #EAE4DE; border-radius: 0.75rem;">
                        <img alt="Modern Abaya" class="hover-scale" src="assets/boutique_byines_3844045366027357924.png" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease-in-out;"/>
                        <button class="wishlist-btn" style="position: absolute; top: 1rem; right: 1rem; padding: 0.5rem; background: rgba(255, 255, 255, 0.5); backdrop-filter: blur(4px); border-radius: 50%; border: none; cursor: pointer;">
                            <svg class="small-icon" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="width: 1.25rem; height: 1.25rem;">
                                <path d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" stroke-linecap="round" stroke-linejoin="round"></path>
                            </svg>
                        </button>
                    </div>
                    <h3 class="product-name" style="font-size: 1rem; font-weight: 500; color: var(--brand-dark); margin-bottom: 0.5rem;">Modern Abaya</h3>
                    <p class="product-price" style="color: var(--gray-500); font-size: 0.875rem;">$290</p>
                </div>
                <div class="product-card" style="cursor: pointer;">
                    <div class="product-img-wrapper" style="position: relative; aspect-ratio: 3/4; margin-bottom: 1rem; overflow: hidden; background-color: #EAE4DE; border-radius: 0.75rem;">
                        <img alt="Evening Abaya" class="hover-scale" src="assets/boutique_byines_3628664731358744968.png" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease-in-out;"/>
                        <button class="wishlist-btn" style="position: absolute; top: 1rem; right: 1rem; padding: 0.5rem; background: rgba(255, 255, 255, 0.5); backdrop-filter: blur(4px); border-radius: 50%; border: none; cursor: pointer;">
                            <svg class="small-icon" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="width: 1.25rem; height: 1.25rem;">
                                <path d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" stroke-linecap="round" stroke-linejoin="round"></path>
                            </svg>
                        </button>
                    </div>
                    <h3 class="product-name" style="font-size: 1rem; font-weight: 500; color: var(--brand-dark); margin-bottom: 0.5rem;">Evening Abaya</h3>
                    <p class="product-price" style="color: var(--gray-500); font-size: 0.875rem;">$290</p>
                </div>
                <div class="product-card" style="cursor: pointer;">
                    <div class="product-img-wrapper" style="position: relative; aspect-ratio: 3/4; margin-bottom: 1rem; overflow: hidden; background-color: #EAE4DE; border-radius: 0.75rem;">
                        <img alt="Textured Abaya" class="hover-scale" src="assets/boutique_byines_3628664731358744968.png" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease-in-out;"/>
                        <button class="wishlist-btn" style="position: absolute; top: 1rem; right: 1rem; padding: 0.5rem; background: rgba(255, 255, 255, 0.5); backdrop-filter: blur(4px); border-radius: 50%; border: none; cursor: pointer;">
                            <svg class="small-icon" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="width: 1.25rem; height: 1.25rem;">
                                <path d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" stroke-linecap="round" stroke-linejoin="round"></path>
                            </svg>
                        </button>
                    </div>
                    <h3 class="product-name" style="font-size: 1rem; font-weight: 500; color: var(--brand-dark); margin-bottom: 0.5rem;">Textured Abaya</h3>
                    <p class="product-price" style="color: var(--gray-500); font-size: 0.875rem;">$310</p>
                </div>
            </div>
        </section>
    </main>

    <script>
        function updateMainImage(src) {
            const mainImage = document.getElementById('mainImage');
            mainImage.src = src;
            
            // Update thumbnail states
            document.querySelectorAll('.thumbnail').forEach(thumb => {
                if (thumb.src === src) {
                    thumb.style.border = '2px solid var(--brand-dark)';
                    thumb.style.opacity = '1';
                } else {
                    thumb.style.border = '2px solid transparent';
                    thumb.style.opacity = '0.6';
                }
            });
        }

        function selectColor(button) {
            document.querySelectorAll('.color-btn').forEach(btn => {
                btn.style.border = '2px solid transparent';
            });
            button.style.border = '2px solid var(--brand-dark)';
            document.getElementById('selectedColor').innerHTML = 'Selected: <strong>' + button.getAttribute('data-color') + '</strong>';
        }

        function selectSize(button) {
            document.querySelectorAll('.size-btn').forEach(btn => {
                btn.style.border = '1px solid var(--gray-300)';
                btn.style.backgroundColor = 'transparent';
            });
            button.style.border = '2px solid var(--brand-dark)';
            button.style.backgroundColor = 'rgba(26, 26, 26, 0.05)';
        }

        function increaseQuantity() {
            const qty = document.getElementById('quantity');
            qty.value = parseInt(qty.value) + 1;
        }

        function decreaseQuantity() {
            const qty = document.getElementById('quantity');
            if (parseInt(qty.value) > 1) {
                qty.value = parseInt(qty.value) - 1;
            }
        }

        function addToCart() {
            const quantity = document.getElementById('quantity').value;
            alert('Added ' + quantity + ' item(s) to your cart!');
            // Here you would integrate with your cart system
        }

        function buyNow() {
            alert('Proceeding to checkout...');
            // Here you would redirect to checkout page
        }
    </script>

<?php include 'footer.php'; ?>
