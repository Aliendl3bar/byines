# Byines Development Rules

## Database Access
- All database access MUST go through the classes in `classes/`. Never write raw SQL in page files.
- Use `Database::getInstance()->getConnection()` only inside class constructors — pages should never touch PDO directly.

## Architecture
- Business logic lives in `classes/`. Pages in `pages/` are thin — they call class methods, fetch data, then render HTML.
- AJAX handlers (`cart_action.php`, `process_order.php`, `admin_manage_order.php`) should delegate to classes and return JSON only.
- Admin logic goes in `admin_manage_*.php` files, not inline in `admin_dashboard.php`.

## Classes
- Each class maps to one database table (e.g., `Product` → `products`).
- Constructor always calls `$this->pdo = Database::getInstance()->getConnection();`
- Class autoloading is already set up in `includes/header.php` — just use `new ClassName()`.
- New classes belong in `classes/` with PascalCase filename matching the class name.

## Naming
- Classes: `PascalCase` (`Product`, `Category`, `Order`)
- Files: `snake_case` (`admin_manage_product.php`, `cart_action.php`)
- Database: `snake_case` (`product_variants`, `order_items`)
- CSS: `kebab-case` (`product-img-wrapper`, `header-container`)
- JS: `camelCase` (`addToCart`, `updateCartSummary`)
- PHP variables: `camelCase` (`$productModel`, `$orderItems`)
- URL params: lowercase (`?category=abayas`, `?sort=price_asc`)

## Security
- All queries use PDO prepared statements with parameterized arrays.
- All user output wrapped in `htmlspecialchars()`.
- Passwords hashed with `password_hash(PASSWORD_DEFAULT)`.
- Admin routes check `$_SESSION['user_role'] === 'admin'`.
- No CSRF tokens yet — any new state-changing form MUST include one.

## Cart
- Cart uses composite keys: `$productId . '_' . md5($color . '_' . $size)`.
- Cart items store: `product_id`, `name`, `color`, `size`, `quantity`, `price`, `stock`.
- Always use the `Cart` class to read/write cart data.

## Orders
- Order numbers use the format `BYINES-` + hex string.
- Shipping logic: free for Tangier/Tanger, $3 elsewhere.
- Tax is flat 10%.
- Order creation uses `Order::createFromCart()` for storefront orders.

## Images
- Product images stored at `products/{id}/img/{filename}`.
- Category images at `categories_img/`.
- Collection images at `collections_img/`.
- Use `Product::getMainImage()` or `Product::getImageByColor()` to fetch — never write image queries inline.
