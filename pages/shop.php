<?php 
require_once '../classes/Product.php';
require_once '../classes/Category.php';
require_once '../classes/Collection.php';

$productModel = new Product();
$categoryModel = new Category();
$collectionModel = new Collection();

$categorySlug = $_GET['category'] ?? null;
$collectionId = $_GET['id'] ?? null;
$sort = $_GET['sort'] ?? 'newest';
$priceFilter = $_GET['price_filter'] ?? 500;

// Fetch categories
$categories = $categoryModel->getAll();

// Base query for products
$where = ["p.is_active = 1"];
$params = [];

$pageTitle = "All Collections";
$breadcrumbCat = "All";

if ($categorySlug) {
    $cat = $categoryModel->getBySlug($categorySlug);
    if ($cat) {
        $where[] = "p.category_id = ?";
        $params[] = $cat['id'];
        $pageTitle = htmlspecialchars($cat['name']);
        $breadcrumbCat = htmlspecialchars($cat['name']);
    }
} elseif ($collectionId) {
    $col = $collectionModel->getById($collectionId);
    if ($col && !empty($col['products_ids'])) {
        $ids = array_filter(array_map('intval', explode(',', $col['products_ids'])));
        if (!empty($ids)) {
            $inQuery = implode(',', array_fill(0, count($ids), '?'));
            $where[] = "p.id IN ($inQuery)";
            $params = array_merge($params, $ids);
            $pageTitle = htmlspecialchars($col['title']);
            $breadcrumbCat = htmlspecialchars($col['title']);
        } else {
            $where[] = "1=0";
        }
    }
}

// Price filtering
if ($priceFilter > 0 && $priceFilter < 500) {
    $where[] = "p.price <= ?";
    $params[] = $priceFilter;
}

// Sorting
$orderBy = "p.created_at DESC";
if ($sort === 'price_asc') $orderBy = "p.price ASC";
if ($sort === 'price_desc') $orderBy = "p.price DESC";

$whereClause = implode(" AND ", $where);

// Pagination
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 12;
$offset = ($page - 1) * $limit;

// Total count
$totalProducts = $productModel->countProducts($whereClause, $params);
$totalPages = max(1, ceil($totalProducts / $limit));

// Fetch products
$products = $productModel->getProductsWithImages($whereClause, $params, $orderBy, $limit, $offset);

include '../includes/header.php'; 
?>
<link rel="stylesheet" href="../css/cstyle.css">
    <main class="main-content">
        <!-- Breadcrumbs & Heading -->
        <div class="header-section">
            <nav class="breadcrumbs">
                <a class="breadcrumb-link" href="index.php">Home</a>
                <span class="material-symbols-outlined icon-sm">chevron_right</span>
                <a class="breadcrumb-link" href="shop.php">Shop</a>
                <span class="material-symbols-outlined icon-sm">chevron_right</span>
                <span class="breadcrumb-current"><?= $breadcrumbCat ?></span>
            </nav>
            <div class="title-row">
                <div>
                    <h1 class="main-title"><?= $pageTitle ?></h1>
                    <p class="subtitle">A curated selection of modest wear designed for the modern woman who values timeless elegance and premium craftsmanship.</p>
                </div>
                <div class="sort-container">
                    <form method="GET" action="shop.php" id="sort-form">
                        <?php if ($categorySlug): ?><input type="hidden" name="category" value="<?= htmlspecialchars($categorySlug) ?>"><?php endif; ?>
                        <?php if ($collectionId): ?><input type="hidden" name="id" value="<?= htmlspecialchars($collectionId) ?>"><?php endif; ?>
                        <input type="hidden" name="price_filter" value="<?= htmlspecialchars($priceFilter) ?>">
                        <span class="sort-label">Sort By:</span>
                        <select class="sort-select" name="sort" onchange="document.getElementById('sort-form').submit();">
                            <option value="newest" <?= $sort == 'newest' ? 'selected' : '' ?>>Newest</option>
                            <option value="price_asc" <?= $sort == 'price_asc' ? 'selected' : '' ?>>Price: Low to High</option>
                            <option value="price_desc" <?= $sort == 'price_desc' ? 'selected' : '' ?>>Price: High to Low</option>
                        </select>
                    </form>
                </div>
            </div>
        </div>

        <div class="content-layout">
            <!-- SideNavBar (Filters) -->
            <aside class="sidebar-filters">
                <form method="GET" action="shop.php" id="filter-form">
                    <?php if ($collectionId): ?><input type="hidden" name="id" value="<?= htmlspecialchars($collectionId) ?>"><?php endif; ?>
                    <input type="hidden" name="sort" value="<?= htmlspecialchars($sort) ?>">
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
                                <li>
                                    <a class="filter-item-link <?= !$categorySlug ? 'active' : '' ?>" href="shop.php">All</a>
                                </li>
                                <?php foreach ($categories as $cat): ?>
                                <li>
                                    <a class="filter-item-link <?= ($categorySlug === $cat['slug']) ? 'active' : '' ?>" 
                                       href="shop.php?category=<?= urlencode($cat['slug']) ?>">
                                        <?= htmlspecialchars($cat['name']) ?>
                                    </a>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        
                        <!-- Size (For later) -->
                        <div class="filter-group filter-group-disabled">
                            <div class="filter-group-header">
                                <span class="material-symbols-outlined icon-md">straighten</span>
                                <span class="filter-group-title">Size</span>
                            </div>
                            <div class="size-grid">
                                <button type="button" class="size-btn" disabled>S</button>
                                <button type="button" class="size-btn" disabled>M</button>
                                <button type="button" class="size-btn" disabled>L</button>
                            </div>
                            <small>Coming soon</small>
                        </div>
                        
                        <!-- Price -->
                        <div class="filter-group">
                            <div class="filter-group-header">
                                <span class="material-symbols-outlined icon-md">payments</span>
                                <span class="filter-group-title">Max Price: $<span id="price-val"><?= $priceFilter >= 500 ? '500+' : $priceFilter ?></span></span>
                            </div>
                            <div class="price-slider-container">
                                <input class="price-slider" type="range" name="price_filter" min="0" max="500" step="10" value="<?= $priceFilter ?>" 
                                       oninput="document.getElementById('price-val').innerText = this.value >= 500 ? '500+' : this.value"
                                       onchange="document.getElementById('filter-form').submit();"/>
                                <div class="price-labels">
                                    <span>$0</span>
                                    <span>$500+</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </aside>

            <!-- Product Grid -->
            <section class="product-section">
                <div class="product-grid">
                    <?php if (empty($products)): ?>
                        <div class="empty-products">
                            <h3>No products found in this category.</h3>
                        </div>
                    <?php else: ?>
                        <?php foreach ($products as $prod): 
                            $imageSrc = !empty($prod['image_name']) 
                                ? "../products/{$prod['id']}/img/" . htmlspecialchars($prod['image_name']) 
                                : "../assets/placeholder.png";
                        ?>
                        <div class="product-card">
                            <div class="card-image-wrapper">
                                <a href="product.php?id=<?= $prod['id'] ?>" class="product-link">
                                    <img class="product-image hover-scale" alt="<?= htmlspecialchars($prod['name']) ?>" src="<?= $imageSrc ?>"/>
                                </a>
                                <button class="wishlist-btn" onclick="quickAddToCart(<?= $prod['id'] ?>, this); event.preventDefault();">
                                    <span class="material-symbols-outlined icon-md">shopping_bag</span>
                                </button>
                            </div>
                            <div class="product-info">
                                <div>
                                    <h3 class="product-name"><a href="product.php?id=<?= $prod['id'] ?>"><?= htmlspecialchars($prod['name']) ?></a></h3>
                                    <p class="product-color"><?= htmlspecialchars($prod['color'] ?? 'Various Colors') ?></p>
                                </div>
                                <span class="product-price">$<?= number_format((float)$prod['price'], 2) ?></span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                <div class="pagination-container">
                    <?php 
                        $prevPage = max(1, $page - 1);
                        $nextPage = min($totalPages, $page + 1);
                        
                        $baseParams = $_GET;
                    ?>
                    <a href="shop.php?<?= http_build_query(array_merge($baseParams, ['page' => $prevPage])) ?>" class="pagination-btn <?= $page <= 1 ? 'pagination-disabled' : '' ?>">
                        <span class="material-symbols-outlined icon-sm">chevron_left</span>
                    </a>
                    <span class="pagination-text">Page <?= sprintf('%02d', $page) ?> / <?= sprintf('%02d', $totalPages) ?></span>
                    <a href="shop.php?<?= http_build_query(array_merge($baseParams, ['page' => $nextPage])) ?>" class="pagination-btn <?= $page >= $totalPages ? 'pagination-disabled' : '' ?>">
                        <span class="material-symbols-outlined icon-sm">chevron_right</span>
                    </a>
                </div>
                <?php endif; ?>
            </section>
        </div>
    </main>

<?php include '../includes/footer.php'; ?>