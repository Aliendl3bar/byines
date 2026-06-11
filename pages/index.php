<?php 
require_once '../classes/Product.php';
require_once '../classes/Collection.php';
require_once '../classes/Category.php';
$productModel = new Product();
$collectionModel = new Collection();
$categoryModel = new Category();
$categories = $categoryModel->getAll();
include '../includes/header.php'; 
?>

    <main>
        <section class="hero-section" data-purpose="hero-banner">
            <img alt="woman in a crossroad" class="hero-img" src="../assets/hero-image/Firefly.jpg"/>
            <div class="hero-content">
                <h1 class="hero-title">Timeless Elegance</h1>
                <a class="btn-primary" href="shop.php">start shopping</a>
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
                        <div class="product-card" onclick="window.location.href='product.php?slug=<?= urlencode($product['slug']) ?>'">
                            <div class="product-img-wrapper">
                                <img alt="<?= htmlspecialchars($product['name']) ?>" class="hover-scale" src="<?= $imageSrc ?>"/>
                                <button class="wishlist-btn" onclick="quickAddToCart(<?= $product['id'] ?>, this); event.preventDefault(); event.stopPropagation();">
                                    <svg class="small-icon" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" stroke-linecap="round" stroke-linejoin="round"></path>
                                    </svg>
                                </button>
                            </div>
                            <h3 class="product-name"><?= htmlspecialchars($product['name']) ?></h3>
                            <p class="product-price">$<?= number_format($product['price'], 2) ?></p>
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
                    <a href="shop.php?id=<?= urlencode($collection['id']) ?>" class="btn-outline">Shop Now</a>
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
                <?php if (!empty($categories)): ?>
                    <?php foreach ($categories as $cat): ?>
                        <a href="shop.php?category=<?= urlencode($cat['slug']) ?>" class="category-item">
                            <div class="arch-card">
                                <img alt="<?= htmlspecialchars($cat['name']) ?>" src="<?= htmlspecialchars($cat['image_url'] ?: '../assets/placeholder.png') ?>">
                            </div>
                            <h3><?= htmlspecialchars($cat['name']) ?></h3>
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="empty-state">No categories available.</p>
                <?php endif; ?>
            </div>
        </section>
    </main>

<?php include '../includes/footer.php'; ?>
