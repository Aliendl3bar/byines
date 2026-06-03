<?php include '../includes/header.php'; ?>
<link rel="stylesheet" href="../css/cstyle.css">
    <main class="main-content">
        <!-- Breadcrumbs & Heading -->
        <div class="header-section">
            <nav class="breadcrumbs">
                <a class="breadcrumb-link" href="#">Home</a>
                <span class="material-symbols-outlined icon-sm">chevron_right</span>
                <a class="breadcrumb-link" href="#">Collections</a>
                <span class="material-symbols-outlined icon-sm">chevron_right</span>
                <span class="breadcrumb-current">All</span>
            </nav>
            <div class="title-row">
                <div>
                    <h1 class="main-title">All Collections</h1>
                    <p class="subtitle">A curated selection of modest wear designed for the modern woman who values timeless elegance and premium craftsmanship.</p>
                </div>
                <div class="sort-container">
                    <span class="sort-label">Sort By:</span>
                    <select class="sort-select">
                        <option>Newest</option>
                        <option>Price: Low to High</option>
                        <option>Price: High to Low</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="content-layout">
            <!-- SideNavBar (Filters) -->
            <aside class="sidebar-filters">
                <div>
                    <h3 class="filter-title">Filters</h3>
                    <p class="filter-subtitle">Refine your selection</p>
                    
                    <!-- Categories -->
                    <div class="filter-group">
                        <div class="filter-group-header">
                            <span class="material-symbols-outlined icon-md text-primary">category</span>
                            <span class="filter-group-title text-primary font-bold">Category</span>
                        </div>
                        <ul class="filter-list">
                            <li><a class="filter-item-link active" href="#">All</a></li>
                            <li><a class="filter-item-link" href="#">Kimonos</a></li>
                            <li><a class="filter-item-link" href="#">Abayas</a></li>
                            <li><a class="filter-item-link" href="#">Scarfs</a></li>
                            <li><a class="filter-item-link" href="#">Niqab</a></li>
                        </ul>
                    </div>
                    
                    <!-- Size -->
                    <div class="filter-group">
                        <div class="filter-group-header">
                            <span class="material-symbols-outlined icon-md">straighten</span>
                            <span class="filter-group-title">Size</span>
                        </div>
                        <div class="size-grid">
                            <button class="size-btn">S</button>
                            <button class="size-btn">M</button>
                            <button class="size-btn">L</button>
                        </div>
                    </div>
                    
                    <!-- Price -->
                    <div class="filter-group">
                        <div class="filter-group-header">
                            <span class="material-symbols-outlined icon-md">payments</span>
                            <span class="filter-group-title">Price Range</span>
                        </div>
                        <div class="price-slider-container">
                            <input class="price-slider" type="range"/>
                            <div class="price-labels">
                                <span>$0</span>
                                <span>$500+</span>
                            </div>
                        </div>
                    </div>
                </div>
            </aside>

            <!-- Product Grid -->
            <section class="product-section">
                <div class="product-grid">
                    <!-- Card 1 -->
                    <div class="product-card">
                        <div class="card-image-wrapper">
                            <img class="product-image" data-alt="..." src="https://lh3.googleusercontent.com/aida-public/AB6AXuDwfxcsBJirpj3N6_4H8yd3ucXFSIj30-HQnJfmqzt0gFyEooSEVzvcwu1guWrzlA32zYyeHFkmIwGAAciyH6YSevn7uHLRXvOsgZvvZLz-wjyBeuK61FYcKEG_4QPKZ7ittE3owekfT87e04sANs4ICKkD-QQ_JVBYPSz9e4Nuzhx6mIisJ0w7zs78dRfLmPXykGK1AoQeuV_9QZ2ZSJNdK2Y80MN8g39FqgJWY4DaYrCUxiX21PV6RPXCcpHL0RNIfm5M2Q04YcQ"/>
                            <button class="wishlist-btn">
                                <span class="material-symbols-outlined icon-md">favorite</span>
                            </button>
                            <div class="badge-wrapper">
                                <span class="badge">NEW</span>
                            </div>
                        </div>
                        <div class="product-info">
                            <div>
                                <h3 class="product-name">Silk Drape Abaya</h3>
                                <p class="product-color">Charcoal Grey</p>
                            </div>
                            <span class="product-price">$360</span>
                        </div>
                    </div>

                    <!-- Card 2 -->
                    <div class="product-card">
                        <div class="card-image-wrapper">
                            <img class="product-image" data-alt="..." src="https://lh3.googleusercontent.com/aida-public/AB6AXuBthszZBUg9L0YPYAZQqwn-8UEyw-iQecWfTrUqAqcf6dQc7A0cSHKHS-nlhVth82XPCRfYiCCuoueom2N81iEtPK3SgSWJvxR74N-ojrL0RF1FqeVshwD0NHNSN6Vw9_ZP3WcoYUucv_1OISWq_PeewtLSt2bBPNlrKRUkHsHhs6H1mdf9oT84GMOtq0lnv4pqQCjAWrC26fujSWgEWdhJxlT_t2hCD2pFNkd-rZFWlSUB0d9JHpEIXw1l-JpUEwD9nFH4rIy1e_M"/>
                            <button class="wishlist-btn">
                                <span class="material-symbols-outlined icon-md">favorite</span>
                            </button>
                        </div>
                        <div class="product-info">
                            <div>
                                <h3 class="product-name">Velvet Evening Kimono</h3>
                                <p class="product-color">Rich Umber</p>
                            </div>
                            <span class="product-price">$290</span>
                        </div>
                    </div>

                    <!-- Card 3 -->
                    <div class="product-card">
                        <div class="card-image-wrapper">
                            <img class="product-image" data-alt="..." src="https://lh3.googleusercontent.com/aida-public/AB6AXuBEYyNtx3qwDNAkPniCh2k0-53JMKR7ZjhjzPOiTko2fV7fGUyqTh5bV0f5xDbLjrBCbxAuTYWqThzWzXYyDZKbU6R0UOhBq2c8oU4hafWEqJ4sP3lsL65zvVD8b9mhAta2GAe-kBDpbs0orbuMNCG6n2TmoIIttsVCRsdH-e-06fPOj_Avc_xGolGju9jBs5t_A3SD28WBka1xJwMpUJ1uPTdnaL1vyPlBxWmIZQyW8bQ6SHsGTJlWWvANp3IR-zTiruLZbVBtV_s"/>
                            <button class="wishlist-btn">
                                <span class="material-symbols-outlined icon-md">favorite</span>
                            </button>
                            <div class="badge-wrapper">
                                <span class="badge">SUSTAINABLE</span>
                            </div>
                        </div>
                        <div class="product-info">
                            <div>
                                <h3 class="product-name">Organic Cotton Scarf</h3>
                                <p class="product-color">Natural Sand</p>
                            </div>
                            <span class="product-price">$85</span>
                        </div>
                    </div>

                    <!-- Card 4 -->
                    <div class="product-card">
                        <div class="card-image-wrapper">
                            <img class="product-image" data-alt="..." src="https://lh3.googleusercontent.com/aida-public/AB6AXuCQpN2IIzvs60ciKUPclZXfOBvG8A0qeQ7MQsbFUbFmNBSU7X6cYT6q8NBsE7Fg-jKu07KxeIdHDZb0oV0jBzfwi-dmtOd7LozrJaGqO-f41LXDGcrfajnBBq9_fuGrzgFeXPHw4ZaAnM5bmkYGkOVajSgaFUD9opoLrX-fHqs5tCkPuvNf0fmO5wmkUjmU7jJ5kZqxN5csNuzSMqobBY9-l1LGxr29Y3YQbEHpcnHKtV6ZQ9KFzee1pxhfd8hJ4hCL3L7_B-nfHXY"/>
                            <button class="wishlist-btn">
                                <span class="material-symbols-outlined icon-md">favorite</span>
                            </button>
                        </div>
                        <div class="product-info">
                            <div>
                                <h3 class="product-name">Classic Chiffon Niqab</h3>
                                <p class="product-color">Midnight Black</p>
                            </div>
                            <span class="product-price">$45</span>
                        </div>
                    </div>

                    <!-- Card 5 -->
                    <div class="product-card">
                        <div class="card-image-wrapper">
                            <img class="product-image" data-alt="..." src="https://lh3.googleusercontent.com/aida-public/AB6AXuDQtpbtCQ7HiZenmkIkfWN5h2iggBpiMLCurhKp5jjz7Va0tGWRf-HzOsdppel11T_4Mp3VAxYfQSLN5CaFSViuBhRnW1-aQdpYbjJCpzz6WEwSDltVkna8d_ZPqvnXhZaqWzde8Sh8O__fg3opK3KXMhvANQvIdb56IjAq16Nb6zWarsGdnR0epBUyB7vJu6RV-dFUChKcCot45ryZiVSKpmGJoGMf8z9kyE8t8Xvkz5EYIhAXuIbzNCvg1cSABcoOAfzHNwscSS4"/>
                            <button class="wishlist-btn">
                                <span class="material-symbols-outlined icon-md">favorite</span>
                            </button>
                        </div>
                        <div class="product-info">
                            <div>
                                <h3 class="product-name">Pearl Accessory Set</h3>
                                <p class="product-color">Ivory/Gold</p>
                            </div>
                            <span class="product-price">$120</span>
                        </div>
                    </div>

                    <!-- Card 6 -->
                    <div class="product-card">
                        <div class="card-image-wrapper">
                            <img class="product-image" data-alt="..." src="https://lh3.googleusercontent.com/aida-public/AB6AXuBSy-Et05jTwmJPbMUE8OaGCnZ1ACKIrY7BfCebD32Fe5tlYvnKYt7WvFxnMjJiX_PMp157Iej-oWt2pKEgIgmN8WxDTGx-GBJeGz7M3lpL2qbuNnbyu1nst7nbwRTy3G01RYhbwpliUC--TILtiMP20tmboTFCAipUesZMCAiPcp0hNMONrIxkXtJ6_svjOyevwP6InkT8Cz-VajdxDADCi-7rIbxo6FqbW9MqQ1g2P6BOtFXgglznVJ65fGWKKqTTalpuq9BLzTw"/>
                            <button class="wishlist-btn">
                                <span class="material-symbols-outlined icon-md">favorite</span>
                            </button>
                        </div>
                        <div class="product-info">
                            <div>
                                <h3 class="product-name">Textured Linen Abaya</h3>
                                <p class="product-color">Oatmeal</p>
                            </div>
                            <span class="product-price">$310</span>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div class="pagination-container">
                    <button class="pagination-btn">
                        <span class="material-symbols-outlined icon-sm">chevron_left</span>
                    </button>
                    <span class="pagination-text">Page 01 / 05</span>
                    <button class="pagination-btn">
                        <span class="material-symbols-outlined icon-sm">chevron_right</span>
                    </button>
                </div>
            </section>
        </div>
    </main>

<?php include '../includes/footer.php'; ?>