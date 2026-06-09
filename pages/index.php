<?php 
require_once '../classes/Product.php';
require_once '../classes/Collection.php';
$productModel = new Product();
$collectionModel = new Collection();
include '../includes/header.php'; 
?>

    <main>
        <section class="hero-section" data-purpose="hero-banner">
            <img alt="woman in a crossroad" class="hero-img" src="../assets/Firefly.jpg"/>
            <div class="hero-content">
                <h1 class="hero-title">Timeless Elegance</h1>
                <a class="btn-primary" href="#">start shopping</a>
            </div>
            <div class="hero-gradient"></div>
        </section>

        <section class="popular-picks" data-purpose="popular-picks">
            <h2 class="section-title">Popular Picks</h2>
            <div class="product-grid">
                <?php
                $popularPicksIds = $collectionModel->getByTitle('Popular Picks');

                if ($popularPicksIds) {
                    $idsArray = array_filter(array_map('intval', explode(',', $popularPicksIds)));
                    if (!empty($idsArray)) {
                        $popularProducts = $productModel->getProductsByIds($idsArray, 4);

                        foreach ($popularProducts as $product):
                            $imageSrc = !empty($product['image_name']) 
                                ? "../products/{$product['id']}/img/" . htmlspecialchars($product['image_name']) 
                                : "../assets/placeholder.png";
                ?>
                        <div class="product-card" style="cursor: pointer;" onclick="window.location.href='product.php?slug=<?= urlencode($product['slug']) ?>'">
                            <div class="product-img-wrapper" style="position: relative; aspect-ratio: 3/4; margin-bottom: 1rem; overflow: hidden; background-color: #EAE4DE; border-radius: 0.75rem;">
                                <img alt="<?= htmlspecialchars($product['name']) ?>" class="hover-scale" src="<?= $imageSrc ?>" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease-in-out;"/>
                                <button class="wishlist-btn" style="position: absolute; top: 1rem; right: 1rem; padding: 0.5rem; background: rgba(255, 255, 255, 0.5); backdrop-filter: blur(4px); border-radius: 50%; border: none; cursor: pointer;" onclick="quickAddToCart(<?= $product['id'] ?>, this); event.preventDefault(); event.stopPropagation();">
                                    <svg class="small-icon" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="width: 1.25rem; height: 1.25rem;">
                                        <path d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" stroke-linecap="round" stroke-linejoin="round"></path>
                                    </svg>
                                </button>
                            </div>
                            <h3 class="product-name" style="font-size: 1rem; font-weight: 500; color: var(--brand-dark); margin-bottom: 0.5rem;"><?= htmlspecialchars($product['name']) ?></h3>
                            <p class="product-price" style="color: var(--gray-500); font-size: 0.875rem;">$<?= number_format($product['price'], 2) ?></p>
                        </div>
                <?php 
                        endforeach;
                    }
                }
                ?>
            </div>
        </section>

        <section class="featured-banners" data-purpose="featured-banners">
            <?php
            $allCollections = $collectionModel->getAll();
            $collections = array_filter($allCollections, function($c) {
                return $c['title'] !== 'Popular Picks';
            });

            if ($collections):
                foreach ($collections as $collection):
            ?>
            <div class="feature-card">
                <div class="feature-text">
                    <h2 class="feature-title"><?= htmlspecialchars($collection['title']) ?></h2>
                    <a href="shop.php?id=<?= urlencode($collection['id']) ?>" class="btn-outline" style="text-decoration:none; display:inline-block;">Shop Now</a>
                </div>
                <div class="feature-img-box">
                    <img class="feature-img" alt="<?= htmlspecialchars($collection['title']) ?>" src="<?= htmlspecialchars($collection['image_path']) ?>"/>
                </div>
            </div>
            <?php 
                endforeach;
            endif; 
            ?>
        </section>

        <section class="category-browse" data-purpose="category-browse">
            <h2 class="section-title">Browse by Category</h2>
            <div class="category-grid">
                <div class="category-item">
                    <div class="arch-card">
                        <img alt="Niqab" src="../assets/boutique_byines_3581710268287392853.png">
                    </div>
                    <h3>Niqab</h3>
                </div>
                <div class="category-item">
                    <div class="arch-card">
                        <img alt="Kimono" src="../assets/boutique_byines_3755720010749460967's2026-5-14-15.0.37 story.jpg"/>
                    </div>
                    <h3>Kimonos</h3>
                </div>
                <div class="category-item">
                    <div class="arch-card">
                        <img alt="Scarfs" src="../assets/boutique_byines_3481002996511481087's2026-5-14-15.21.22 story.jpg"/>
                    </div>
                    <h3>Scarfs</h3>
                </div>
                <div class="category-item">
                    <div class="arch-card">
                        <img alt="Accessories" src="../assets/samaa.creat_3847060077198625518's2026-5-14-12.51.304 story.jpg"/>
                    </div>
                    <h3>Accessories</h3>
                </div>
            </div>
        </section>
    </main>

<?php include '../includes/footer.php'; ?>
