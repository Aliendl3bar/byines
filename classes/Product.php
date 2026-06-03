<?php
require_once 'Database.php';

class Product {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }

    /**
     * Retrieve all products with their category name.
     * @param bool $includeHidden If true, returns both active and inactive products (for admin).
     * @return array
     */
    public function getAll($includeHidden = false) {
        $sql = "
            SELECT p.id, p.category_id, p.name, p.slug, p.sku, p.description, p.price, p.old_price, p.is_active, p.created_at, c.name as category_name
            FROM products p
            JOIN categories c ON p.category_id = c.id
        ";
        if (!$includeHidden) {
            $sql .= " WHERE p.is_active = 1";
        }
        $sql .= " ORDER BY p.id DESC";

        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Get a specific product by its ID.
     * @return array|null
     */
    public function getById($id) {
        $stmt = $this->pdo->prepare("
            SELECT p.id, p.category_id, p.name, p.slug, p.sku, p.description, p.price, p.old_price, p.is_active, p.created_at, c.name as category_name
            FROM products p
            JOIN categories c ON p.category_id = c.id
            WHERE p.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Create a new product.
     * @return int|false The new product ID on success, or false on failure.
     */
    public function create($categoryId, $name, $slug, $sku, $description, $price, $oldPrice = null, $isActive = 1) {
        $stmt = $this->pdo->prepare("
            INSERT INTO products (category_id, name, slug, sku, description, price, old_price, is_active)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        if ($stmt->execute([$categoryId, $name, $slug, $sku, $description, $price, $oldPrice, $isActive])) {
            return $this->pdo->lastInsertId();
        }
        return false;
    }

    /**
     * Update an existing product.
     * @return bool
     */
    public function update($id, $categoryId, $name, $slug, $sku, $description, $price, $oldPrice = null, $isActive = 1) {
        $stmt = $this->pdo->prepare("
            UPDATE products
            SET category_id = ?, name = ?, slug = ?, sku = ?, description = ?, price = ?, old_price = ?, is_active = ?
            WHERE id = ?
        ");
        return $stmt->execute([$categoryId, $name, $slug, $sku, $description, $price, $oldPrice, $isActive, $id]);
    }

    /**
     * Quickly toggle a product's visibility (is_active status).
     * @return bool
     */
    public function toggleStatus($id, $isActive) {
        $stmt = $this->pdo->prepare("UPDATE products SET is_active = ? WHERE id = ?");
        return $stmt->execute([$isActive, $id]);
    }

    /**
     * Get size & color variants of a product.
     * @return array
     */
    public function getVariants($productId) {
        $stmt = $this->pdo->prepare("SELECT id, color, size, stock_quantity, price_modifier FROM product_variants WHERE product_id = ?");
        $stmt->execute([productId]);
        return $stmt->fetchAll();
    }
}
