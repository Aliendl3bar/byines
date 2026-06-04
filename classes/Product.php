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
     * Get a specific product by its slug.
     * @return array|null
     */
    public function getBySlug($slug) {
        $stmt = $this->pdo->prepare("
            SELECT p.id, p.category_id, p.name, p.slug, p.sku, p.description, p.price, p.old_price, p.is_active, p.created_at, c.name as category_name
            FROM products p
            JOIN categories c ON p.category_id = c.id
            WHERE p.slug = ?
        ");
        $stmt->execute([$slug]);
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
        $stmt->execute([$productId]);
        return $stmt->fetchAll();
    }

    /**
     * Get all images associated with a product.
     * @return array
     */
    public function getImages($productId) {
        $stmt = $this->pdo->prepare("SELECT id, color, image_name, sort_order, is_main FROM product_images WHERE product_id = ? ORDER BY sort_order ASC, id ASC");
        $stmt->execute([$productId]);
        return $stmt->fetchAll();
    }

    /**
     * Add a product image entry to the database.
     * @return int|false
     */
    public function addImage($productId, $imageName, $color = null, $isMain = 0) {
        $stmt = $this->pdo->prepare("INSERT INTO product_images (product_id, color, image_name, is_main) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$productId, $color, $imageName, $isMain])) {
            return $this->pdo->lastInsertId();
        }
        return false;
    }

    /**
     * Set a specific image as the main product image (is_main = 1) and reset others.
     * @return bool
     */
    public function setMainImage($productId, $imageId) {
        try {
            $this->pdo->beginTransaction();
            // Reset all to 0
            $stmt1 = $this->pdo->prepare("UPDATE product_images SET is_main = 0 WHERE product_id = ?");
            $stmt1->execute([$productId]);
            // Set target to 1
            $stmt2 = $this->pdo->prepare("UPDATE product_images SET is_main = 1 WHERE id = ? AND product_id = ?");
            $stmt2->execute([$imageId, $productId]);
            
            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }

    /**
     * Delete an image from the database and remove the physical file.
     * @return bool
     */
    public function deleteImage($imageId) {
        // Fetch image details first
        $stmt = $this->pdo->prepare("SELECT id, product_id, image_name FROM product_images WHERE id = ?");
        $stmt->execute([$imageId]);
        $img = $stmt->fetch();

        if ($img) {
            // Delete record
            $stmtDel = $this->pdo->prepare("DELETE FROM product_images WHERE id = ?");
            if ($stmtDel->execute([$imageId])) {
                // Delete physical file in pages/../products/{id}/img/filename
                $filePath = __DIR__ . '/../products/' . $img['product_id'] . '/img/' . $img['image_name'];
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
                return true;
            }
        }
        return false;
    }

    /**
     * Update the color tag of an existing product image.
     * @return bool
     */
    public function updateImageColor($imageId, $color) {
        $color = ($color === '' || $color === null) ? null : $color;
        $stmt = $this->pdo->prepare("UPDATE product_images SET color = ? WHERE id = ?");
        return $stmt->execute([$color, $imageId]);
    }

    /**
     * Move an image's sort order relative to others (prev = left/up, next = right/down).
     * @return bool
     */
    public function reorderImage($imageId, $direction) {
        $stmt = $this->pdo->prepare("SELECT product_id FROM product_images WHERE id = ?");
        $stmt->execute([$imageId]);
        $img = $stmt->fetch();
        if (!$img) return false;
        
        $productId = $img['product_id'];
        
        // Get all images sorted by sort_order and id
        $images = $this->getImages($productId);
        $count = count($images);
        
        // Normalize sort orders to distinct sequential values (1, 2, 3, ...)
        for ($i = 0; $i < $count; $i++) {
            $stmtNorm = $this->pdo->prepare("UPDATE product_images SET sort_order = ? WHERE id = ?");
            $stmtNorm->execute([$i + 1, $images[$i]['id']]);
            $images[$i]['sort_order'] = $i + 1;
        }
        
        // Find index of target image
        $targetIndex = -1;
        for ($i = 0; $i < $count; $i++) {
            if ($images[$i]['id'] == $imageId) {
                $targetIndex = $i;
                break;
            }
        }
        
        if ($targetIndex === -1) return false;
        
        $swapIndex = -1;
        if ($direction === 'prev' && $targetIndex > 0) {
            $swapIndex = $targetIndex - 1;
        } elseif ($direction === 'next' && $targetIndex < $count - 1) {
            $swapIndex = $targetIndex + 1;
        }
        
        if ($swapIndex !== -1) {
            $targetImg = $images[$targetIndex];
            $swapImg = $images[$swapIndex];
            
            // Swap their sort orders
            $stmtUpdate = $this->pdo->prepare("UPDATE product_images SET sort_order = ? WHERE id = ?");
            $stmtUpdate->execute([$swapImg['sort_order'], $targetImg['id']]);
            $stmtUpdate->execute([$targetImg['sort_order'], $swapImg['id']]);
            return true;
        }
        
        return false;
    }

    // =====================================================================
    // VARIANT MANAGEMENT
    // =====================================================================

    /**
     * Add a new color/size variant to a product.
     * @return int|false
     */
    public function addVariant($productId, $color, $size, $stockQuantity, $priceModifier = 0.00) {
        $stmt = $this->pdo->prepare("
            INSERT INTO product_variants (product_id, color, size, stock_quantity, price_modifier)
            VALUES (?, ?, ?, ?, ?)
        ");
        if ($stmt->execute([$productId, $color, $size, $stockQuantity, $priceModifier])) {
            return $this->pdo->lastInsertId();
        }
        return false;
    }

    /**
     * Update an existing variant's details.
     * @return bool
     */
    public function updateVariant($variantId, $color, $size, $stockQuantity, $priceModifier = 0.00) {
        $stmt = $this->pdo->prepare("
            UPDATE product_variants
            SET color = ?, size = ?, stock_quantity = ?, price_modifier = ?
            WHERE id = ?
        ");
        return $stmt->execute([$color, $size, $stockQuantity, $priceModifier, $variantId]);
    }

    /**
     * Delete a variant by ID.
     * @return bool
     */
    public function deleteVariant($variantId) {
        $stmt = $this->pdo->prepare("DELETE FROM product_variants WHERE id = ?");
        return $stmt->execute([$variantId]);
    }

    /**
     * Get distinct colors used by a product's variants.
     * Useful for populating image color dropdowns.
     * @return array
     */
    public function getDistinctColors($productId) {
        $stmt = $this->pdo->prepare("SELECT DISTINCT color FROM product_variants WHERE product_id = ? ORDER BY color ASC");
        $stmt->execute([$productId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Delete a product and all associated images (files + DB) and variants.
     * @return bool
     */
    public function deleteProduct($productId) {
        try {
            $this->pdo->beginTransaction();

            // 1. Delete all image files from disk
            $images = $this->getImages($productId);
            foreach ($images as $img) {
                $filePath = __DIR__ . '/../products/' . $productId . '/img/' . $img['image_name'];
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }

            // Remove the product image directory if it exists
            $imgDir = __DIR__ . '/../products/' . $productId . '/img';
            if (is_dir($imgDir)) {
                @rmdir($imgDir);
            }
            $productDir = __DIR__ . '/../products/' . $productId;
            if (is_dir($productDir)) {
                @rmdir($productDir);
            }

            // 2. Delete image DB records
            $stmt = $this->pdo->prepare("DELETE FROM product_images WHERE product_id = ?");
            $stmt->execute([$productId]);

            // 3. Delete variant DB records
            $stmt = $this->pdo->prepare("DELETE FROM product_variants WHERE product_id = ?");
            $stmt->execute([$productId]);

            // 4. Delete the product itself
            $stmt = $this->pdo->prepare("DELETE FROM products WHERE id = ?");
            $stmt->execute([$productId]);

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }
}

