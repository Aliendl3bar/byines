<?php 
include '../includes/header.php'; 
$db = Database::getInstance()->getConnection();
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
                // Fetch Popular Picks collection
                $stmtPop = $db->prepare("SELECT products_ids FROM collections WHERE title = 'Popular Picks' LIMIT 1");
                $stmtPop->execute();
                $popularPicksIds = $stmtPop->fetchColumn();

                if ($popularPicksIds) {
                    $idsArray = array_filter(array_map('intval', explode(',', $popularPicksIds)));
                    if (!empty($idsArray)) {
                        $inQuery = implode(',', array_fill(0, count($idsArray), '?'));
                        $prodQuery = "
                            SELECT p.id, p.name, p.price, pi.image_name 
                            FROM products p 
                            LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_main = 1 
                            WHERE p.id IN ($inQuery) AND p.is_active = 1
                            ORDER BY FIELD(p.id, $popularPicksIds)
                            LIMIT 4
                        ";
                        $prodStmt = $db->prepare($prodQuery);
                        $prodStmt->execute($idsArray);
                        $popularProducts = $prodStmt->fetchAll(PDO::FETCH_ASSOC);

                        foreach ($popularProducts as $product):
                            $imageSrc = !empty($product['image_name']) 
                                ? "../products/{$product['id']}/img/" . htmlspecialchars($product['image_name']) 
                                : "../assets/placeholder.png";
                ?>
                <div class="product-card">
                    <div class="product-img-wrapper">
                        <a href="product.php?id=<?= $product['id'] ?>" style="display: block;">
                            <img alt="<?= htmlspecialchars($product['name']) ?>" class="hover-scale" src="<?= $imageSrc ?>"/>
                        </a>
                        <button class="wishlist-btn" onclick="quickAddToCart(<?= $product['id'] ?>, this); event.preventDefault();">
                            <svg class="small-icon" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" stroke-linecap="round" stroke-linejoin="round"></path>
                            </svg>
                        </button>
                    </div>
                    <h3 class="product-name"><a href="product.php?id=<?= $product['id'] ?>" style="text-decoration:none; color:inherit;"><?= htmlspecialchars($product['name']) ?></a></h3>
                    <p class="product-price">$<?= htmlspecialchars($product['price']) ?></p>
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
            // Fetch collections from the database
            $stmt = $db->query("SELECT * FROM collections WHERE title != 'Popular Picks' ORDER BY id ASC");
            $collections = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
