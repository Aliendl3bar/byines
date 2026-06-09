<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Autoload core database and domain classes
spl_autoload_register(function ($className) {
    $file = __DIR__ . '/../classes/' . $className . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

$productModel = new Product();
$product = null;

// Get product identifier from URL query string
if (isset($_GET['id'])) {
    $product = $productModel->getById(intval($_GET['id']));
} elseif (isset($_GET['slug'])) {
    $product = $productModel->getBySlug(trim($_GET['slug']));
}

// Fallback: If no identifier is passed, but there's an active product in the DB, load the first one.
if (!$product && !isset($_GET['id']) && !isset($_GET['slug'])) {
    $allProducts = $productModel->getAll(false);
    if (!empty($allProducts)) {
        $product = $productModel->getById($allProducts[0]['id']);
    }
}

// If product is still not found or inactive (and user is not an admin), show Product Not Found
if (!$product || (!$product['is_active'] && ($_SESSION['user_role'] ?? '') !== 'admin')) {
    $pageTitle = 'Product Not Found';
    include '../includes/header.php';
    ?>
    <main class="not-found-page">
        <h1 class="not-found-title">Product Not Found</h1>
        <p class="not-found-text">The product you are looking for does not exist or is currently unavailable.</p>
        <a href="shop.php" class="not-found-link">Browse Collections</a>
    </main>
    <?php
    include '../includes/footer.php';
    exit;
}

$productId = $product['id'];
$pageTitle = $product['name'];

// Fetch images and variants
$images = $productModel->getImages($productId);
$variants = $productModel->getVariants($productId);

// Resolve Main Image (is_main = 1 or the first uploaded image)
$mainImageSrc = '../assets/placeholder.png'; // default fallback
if (!empty($images)) {
    $mainImageSrc = '../products/' . $productId . '/img/' . $images[0]['image_name'];
    foreach ($images as $img) {
        if ($img['is_main']) {
            $mainImageSrc = '../products/' . $productId . '/img/' . $img['image_name'];
            break;
        }
    }
}

// Extract distinct colors and sizes from variants
$distinctColors = [];
$distinctSizes = [];
foreach ($variants as $v) {
    if (!in_array($v['color'], $distinctColors)) {
        $distinctColors[] = $v['color'];
    }
    if (!in_array($v['size'], $distinctSizes)) {
        $distinctSizes[] = $v['size'];
    }
}

// Sort sizes logically
$sizeOrder = ['XS' => 1, 'S' => 2, 'M' => 3, 'L' => 4, 'XL' => 5, 'XXL' => 6];
usort($distinctSizes, function($a, $b) use ($sizeOrder) {
    return ($sizeOrder[$a] ?? 99) <=> ($sizeOrder[$b] ?? 99);
});

// Fetch reviews
$db = Database::getInstance();
$pdo = $db->getConnection();
$stmtReviews = $pdo->prepare("
    SELECT r.rating, r.review_text, r.created_at, u.first_name, u.last_name
    FROM reviews r
    JOIN users u ON r.user_id = u.id
    WHERE r.product_id = ? AND r.is_approved = 1
    ORDER BY r.created_at DESC
");
$stmtReviews->execute([$productId]);
$reviews = $stmtReviews->fetchAll();

// Calculate average rating
$avgRating = 0.0;
$reviewCount = count($reviews);
if ($reviewCount > 0) {
    $totalRating = 0;
    foreach ($reviews as $rev) {
        $totalRating += $rev['rating'];
    }
    $avgRating = round($totalRating / $reviewCount, 1);
}

// Fetch related products (same category, active, limit 4)
$relatedProducts = $productModel->getRelatedProducts($product['category_id'], $productId, 4);

include '../includes/header.php';
?>

    <main class="product-page" data-variants="<?= htmlspecialchars(json_encode($variants), ENT_QUOTES, 'UTF-8') ?>" data-images="<?= htmlspecialchars(json_encode($images), ENT_QUOTES, 'UTF-8') ?>" data-base-price="<?= floatval($product['price']) ?>" data-product-id="<?= intval($productId) ?>">
        <!-- Breadcrumb Navigation -->
        <nav class="breadcrumb-nav">
            <a href="index.php" class="breadcrumb-link">Home</a>
            <span class="breadcrumb-separator">/</span>
            <a href="shop.php" class="breadcrumb-link">Collections</a>
            <span class="breadcrumb-separator">/</span>
            <a href="shop.php?category=<?= urlencode($product['category_name']) ?>" class="breadcrumb-link"><?= htmlspecialchars($product['category_name']) ?></a>
            <span class="breadcrumb-separator">/</span>
            <span class="breadcrumb-current"><?= htmlspecialchars($product['name']) ?></span>
        </nav>

        <!-- Product Container -->
        <div class="product-detail-container">
            
            <!-- Product Image Gallery -->
            <section class="product-gallery">
                <!-- Main Image -->
                <div class="main-image-wrapper">
                    <img id="mainImage" class="main-image" alt="<?= htmlspecialchars($product['name']) ?> - Main View" src="<?= $mainImageSrc ?>" />
                    <button class="wishlist-btn" data-action="wishlist-add-to-cart">
                        <svg class="wishlist-icon" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>
                    </button>
                </div>

                <link rel="stylesheet" href="../css/product.css">
                <!-- Thumbnail Gallery -->
                <div class="thumbnail-gallery">
                    <?php foreach ($images as $index => $img): 
                        $imgUrl = '../products/' . $productId . '/img/' . $img['image_name'];
                        $isActive = ($mainImageSrc === $imgUrl);
                        ?>
                        <img 
                            class="thumbnail<?= $isActive ? ' active' : '' ?>" 
                            src="<?= $imgUrl ?>" 
                            alt="View <?= $index + 1 ?>" 
                            data-color="<?= htmlspecialchars($img['color'] ?? '') ?>"
                            data-is-main="<?= $img['is_main'] ?>"
                            data-action="update-main-image"
                        />
                    <?php endforeach; ?>
                </div>
            </section>

            <!-- Product Details -->
            <section class="product-details">
                <!-- Product Title & Price -->
                <div class="product-header">
                    <h1 class="product-title"><?= htmlspecialchars($product['name']) ?></h1>
                    <p class="product-sku">SKU: <?= htmlspecialchars($product['sku']) ?></p>
                    
                    <div class="product-price-row">
                        <span id="productPrice" class="product-price">$<?= number_format($product['price'], 2) ?></span>
                        
                        <?php if ($product['old_price'] && $product['old_price'] > $product['price']): ?>
                            <span class="product-old-price">$<?= number_format($product['old_price'], 2) ?></span>
                            <?php 
                            $discount = round((($product['old_price'] - $product['price']) / $product['old_price']) * 100);
                            ?>
                            <span class="product-discount-badge"><?= $discount ?>% OFF</span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="product-rating">
                        <span class="product-rating-stars">
                            <?php
                            $stars = round($avgRating);
                            for ($i = 1; $i <= 5; $i++) {
                                echo $i <= $stars ? '★' : '☆';
                            }
                            ?>
                        </span>
                        <span class="product-rating-count">(<?= $reviewCount ?> customer reviews)</span>
                    </div>
                </div>

                <!-- Product Description -->
                <div class="product-description">
                    <p class="product-description-text">
                        <?= nl2br(htmlspecialchars($product['description'])) ?>
                    </p>
                </div>

                <!-- Color Options -->
                <?php if (!empty($distinctColors)): ?>
                    <div class="product-section">
                        <label class="product-section-label">Select Color</label>
                        <div class="color-options">
                            <?php foreach ($distinctColors as $color): 
                                $colorKey = strtolower(trim($color));
                                $colorMap = [
                                    'beige' => ['bg' => '#E8DCC4', 'text' => '#1A1A1A'],
                                    'magenta' => ['bg' => '#8C5B6B', 'text' => '#FFFFFF'],
                                    'red' => ['bg' => '#A64B4B', 'text' => '#FFFFFF'],
                                    'black' => ['bg' => '#1A1A1A', 'text' => '#FFFFFF'],
                                    'white' => ['bg' => '#FAF9F6', 'text' => '#1A1A1A']
                                ];
                                $bg = $colorMap[$colorKey]['bg'] ?? $colorKey;
                                $text = $colorMap[$colorKey]['text'] ?? '#1A1A1A';
                            ?>
                                <button class="color-btn" style="background-color: <?= $bg ?>; color: <?= $text ?>;" data-color="<?= htmlspecialchars($color) ?>" data-action="select-color">
                                    <?= htmlspecialchars($color) ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                        <p id="selectedColor" class="selected-color-label">Selected: <strong></strong></p>
                    </div>
                <?php endif; ?>

                <!-- Size Options -->
                <?php if (!empty($distinctSizes)): ?>
                    <div class="product-section">
                        <label class="product-section-label">Select Size</label>
                        <div class="size-options">
                            <?php foreach ($distinctSizes as $size): ?>
                                <button class="size-btn" data-size="<?= htmlspecialchars($size) ?>" data-action="select-size">
                                    <?= htmlspecialchars($size) ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Quantity Selector & Stock Info -->
                <div class="quantity-section">
                    <div>
                        <label class="quantity-label">Quantity</label>
                        <div class="quantity-controls">
                            <button class="qty-btn" data-action="decrease-qty">−</button>
                            <input id="quantity" type="text" value="1" readonly class="qty-input">
                            <button class="qty-btn" data-action="increase-qty">+</button>
                        </div>
                    </div>
                    
                    <div class="stock-wrapper">
                        <span id="stockStatus"></span>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="action-buttons">
                    <button class="add-to-cart-btn" data-action="add-to-cart">
                        Add to Cart
                    </button>
                    <button class="buy-now-btn" data-action="buy-now">
                        Buy Now
                    </button>
                </div>

                <!-- Additional Info -->
                <div class="additional-info">
                    <div class="info-grid">
                        <div>
                            <p class="info-label">Free Shipping</p>
                            <p class="info-text">On orders over $100</p>
                        </div>
                        <div>
                            <p class="info-label">Easy Returns</p>
                            <p class="info-text">30-day return policy</p>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <!-- Product Specifications & Reviews Section -->
        <section class="specs-section">
            <div class="specs-grid">
                <!-- Specifications -->
                <div>
                    <h2 class="specs-title">Specifications</h2>
                    <table class="specs-table">
                        <tr class="specs-row">
                            <td class="specs-label">Category</td>
                            <td class="specs-value"><?= htmlspecialchars($product['category_name']) ?></td>
                        </tr>
                        <tr class="specs-row">
                            <td class="specs-label">SKU Reference</td>
                            <td class="specs-value"><?= htmlspecialchars($product['sku']) ?></td>
                        </tr>
                        <tr class="specs-row">
                            <td class="specs-label">Care Instruction</td>
                            <td class="specs-value">Dry clean or gentle hand wash</td>
                        </tr>
                        <tr>
                            <td class="specs-label">Availability</td>
                            <td class="specs-value">Modest storefront exclusive</td>
                        </tr>
                    </table>
                </div>

                <!-- Reviews -->
                <div>
                    <h2 class="reviews-title">Customer Reviews</h2>
                    <div class="reviews-list">
                        <?php if (empty($reviews)): ?>
                            <p class="no-reviews">No reviews yet for this product. Be the first to share your thoughts!</p>
                        <?php else: ?>
                            <?php foreach ($reviews as $rev): ?>
                                <div class="review-item">
                                    <div class="review-header">
                                        <span class="review-author"><?= htmlspecialchars($rev['first_name']) ?> <?= htmlspecialchars(substr($rev['last_name'], 0, 1)) ?>.</span>
                                        <span class="review-rating">
                                            <?php
                                            for ($i = 1; $i <= 5; $i++) {
                                                echo $i <= $rev['rating'] ? '★' : '☆';
                                            }
                                            ?>
                                        </span>
                                    </div>
                                    <p class="review-date">
                                        Verified Purchase &bull; <?= date('F j, Y', strtotime($rev['created_at'])) ?>
                                    </p>
                                    <p class="review-text"><?= nl2br(htmlspecialchars($rev['review_text'])) ?></p>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>

        <!-- Related Products -->
        <section class="related-section">
            <h2 class="section-title">You May Also Like</h2>
            <div class="product-grid">
                <?php foreach ($relatedProducts as $rel): 
                    $relImg = $productModel->getMainImage($rel['id']);
                    $relImgUrl = $relImg ? '../products/' . $rel['id'] . '/img/' . $relImg : '../assets/placeholder.png';
                    ?>
                    <div class="product-card" data-action="navigate" data-slug="product.php?slug=<?= urlencode($rel['slug']) ?>">
                        <div class="product-img-wrapper">
                            <img alt="<?= htmlspecialchars($rel['name']) ?>" class="hover-scale main-image" src="<?= $relImgUrl ?>" />
                            <button class="wishlist-btn" data-action="quick-add-cart" data-product-id="<?= $rel['id'] ?>">
                                <svg class="small-icon" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" stroke-linecap="round" stroke-linejoin="round"></path>
                                </svg>
                            </button>
                        </div>
                        <h3 class="product-name"><?= htmlspecialchars($rel['name']) ?></h3>
                        <p class="product-price">$<?= number_format($rel['price'], 2) ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </main>

    <!-- Client-side controllers and data initialization -->
    <script src="../scripts/product.js?v=<?= time() ?>"></script>

<?php include '../includes/footer.php'; ?>
