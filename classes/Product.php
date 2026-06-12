<?php
require_once 'Database.php';

class Product {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }

    /** Get all products with category name. @param bool $includeHidden Whether to include hidden products. @return array */
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

    /** Get product by ID. @return array|null */
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

    /** Get product by slug. @return array|null */
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

    /** Create a new product. @return int|false */
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

    /** Update an existing product. @return bool */
    public function update($id, $categoryId, $name, $slug, $sku, $description, $price, $oldPrice = null, $isActive = 1) {
        $stmt = $this->pdo->prepare("
            UPDATE products
            SET category_id = ?, name = ?, slug = ?, sku = ?, description = ?, price = ?, old_price = ?, is_active = ?
            WHERE id = ?
        ");
        return $stmt->execute([$categoryId, $name, $slug, $sku, $description, $price, $oldPrice, $isActive, $id]);
    }

    /** Toggle product visibility. @return bool */
    public function toggleStatus($id, $isActive) {
        $stmt = $this->pdo->prepare("UPDATE products SET is_active = ? WHERE id = ?");
        return $stmt->execute([$isActive, $id]);
    }

    /** Get variants of a product. @return array */
    public function getVariants($productId) {
        $stmt = $this->pdo->prepare("SELECT id, color, size, stock_quantity, price_modifier FROM product_variants WHERE product_id = ?");
        $stmt->execute([$productId]);
        return $stmt->fetchAll();
    }

    /** Get all images for a product. @return array */
    public function getImages($productId) {
        $stmt = $this->pdo->prepare("SELECT id, color, image_name, sort_order, is_main FROM product_images WHERE product_id = ? ORDER BY sort_order ASC, id ASC");
        $stmt->execute([$productId]);
        return $stmt->fetchAll();
    }

    /** Add a product image. @return int|false */
    public function addImage($productId, $imageName, $color = null, $isMain = 0) {
        $stmt = $this->pdo->prepare("INSERT INTO product_images (product_id, color, image_name, is_main) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$productId, $color, $imageName, $isMain])) {
            return $this->pdo->lastInsertId();
        }
        return false;
    }

    /** Set image as main and reset others. @return bool */
    public function setMainImage($productId, $imageId) {
        try {
            $this->pdo->beginTransaction();
            // reset all to 0
            $stmt1 = $this->pdo->prepare("UPDATE product_images SET is_main = 0 WHERE product_id = ?");
            $stmt1->execute([$productId]);
            // set target to 1
            $stmt2 = $this->pdo->prepare("UPDATE product_images SET is_main = 1 WHERE id = ? AND product_id = ?");
            $stmt2->execute([$imageId, $productId]);
            
            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }

    /** Delete an image and its file. @return bool */
    public function deleteImage($imageId) {
        // fetch image details first
        $stmt = $this->pdo->prepare("SELECT id, product_id, image_name FROM product_images WHERE id = ?");
        $stmt->execute([$imageId]);
        $img = $stmt->fetch();

        if ($img) {
            // delete record
            $stmtDel = $this->pdo->prepare("DELETE FROM product_images WHERE id = ?");
            if ($stmtDel->execute([$imageId])) {
                // delete physical file
                $filePath = __DIR__ . '/../products/' . $img['product_id'] . '/img/' . $img['image_name'];
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
                return true;
            }
        }
        return false;
    }

    /** Update image color tag. @return bool */
    public function updateImageColor($imageId, $color) {
        $color = ($color === '' || $color === null) ? null : $color;
        $stmt = $this->pdo->prepare("UPDATE product_images SET color = ? WHERE id = ?");
        return $stmt->execute([$color, $imageId]);
    }

    /** Move image sort order by one position. @return bool */
    public function reorderImage($imageId, $direction) {
        $stmt = $this->pdo->prepare("SELECT product_id FROM product_images WHERE id = ?");
        $stmt->execute([$imageId]);
        $img = $stmt->fetch();
        if (!$img) return false;
        
        $productId = $img['product_id'];
        
        // get all images sorted by sort_order and id
        $images = $this->getImages($productId);
        $count = count($images);
        
        // normalize sort orders to sequential values
        for ($i = 0; $i < $count; $i++) {
            $stmtNorm = $this->pdo->prepare("UPDATE product_images SET sort_order = ? WHERE id = ?");
            $stmtNorm->execute([$i + 1, $images[$i]['id']]);
            $images[$i]['sort_order'] = $i + 1;
        }
        
        // find index of target image
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
            
            // swap their sort orders
            $stmtUpdate = $this->pdo->prepare("UPDATE product_images SET sort_order = ? WHERE id = ?");
            $stmtUpdate->execute([$swapImg['sort_order'], $targetImg['id']]);
            $stmtUpdate->execute([$targetImg['sort_order'], $swapImg['id']]);
            return true;
        }
        
        return false;
    }

    // --- Variant Management ---

    /** Add a new variant. @return int|false */
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

    /** Update an existing variant. @return bool */
    public function updateVariant($variantId, $color, $size, $stockQuantity, $priceModifier = 0.00) {
        $stmt = $this->pdo->prepare("
            UPDATE product_variants
            SET color = ?, size = ?, stock_quantity = ?, price_modifier = ?
            WHERE id = ?
        ");
        return $stmt->execute([$color, $size, $stockQuantity, $priceModifier, $variantId]);
    }

    /** Delete a variant. @return bool */
    public function deleteVariant($variantId) {
        $stmt = $this->pdo->prepare("DELETE FROM product_variants WHERE id = ?");
        return $stmt->execute([$variantId]);
    }

    /** Get distinct colors from product variants. @return array */
    public function getDistinctColors($productId) {
        $stmt = $this->pdo->prepare("SELECT DISTINCT color FROM product_variants WHERE product_id = ? ORDER BY color ASC");
        $stmt->execute([$productId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /** Get products with main image for listing pages. @return array */
    public function getProductsWithImages($whereClause, $params, $orderBy, $limit, $offset) {
        $query = "
            SELECT p.id, p.name, p.price, p.old_price, pi.image_name,
                   (SELECT color FROM product_variants pv WHERE pv.product_id = p.id LIMIT 1) as color
            FROM products p
            LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_main = 1
            WHERE $whereClause
            ORDER BY $orderBy
            LIMIT $limit OFFSET $offset
        ";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** Count products matching conditions. @return int */
    public function countProducts($whereClause, $params) {
        $query = "SELECT COUNT(p.id) FROM products p WHERE $whereClause";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    /** Get related products from same category. @return array */
    public function getRelatedProducts($categoryId, $excludeId, $limit = 4) {
        $stmt = $this->pdo->prepare("
            SELECT p.id, p.name, p.slug, p.price, p.old_price
            FROM products p
            WHERE p.category_id = ? AND p.id != ? AND p.is_active = 1
            ORDER BY RAND() LIMIT ?
        ");
        $stmt->execute([$categoryId, $excludeId, $limit]);
        $products = $stmt->fetchAll();

        if (count($products) < $limit) {
            $needed = $limit - count($products);
            $excludeIds = array_merge([$excludeId], array_column($products, 'id'));
            $placeholders = implode(',', array_fill(0, count($excludeIds), '?'));
            $stmtFill = $this->pdo->prepare("
                SELECT p.id, p.name, p.slug, p.price, p.old_price
                FROM products p
                WHERE p.id NOT IN ($placeholders) AND p.is_active = 1
                ORDER BY RAND() LIMIT $needed
            ");
            $stmtFill->execute($excludeIds);
            $products = array_merge($products, $stmtFill->fetchAll());
        }

        return $products;
    }

    /** Get products by IDs with main images. @return array */
    public function getProductsByIds(array $ids, $limit = 4) {
        if (empty($ids)) return [];
        $inQuery = implode(',', array_fill(0, count($ids), '?'));
        $placeholders = implode(',', $ids);
        $query = "
            SELECT p.id, p.name, p.price, p.slug, pi.image_name
            FROM products p
            LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_main = 1
            WHERE p.id IN ($inQuery) AND p.is_active = 1
            ORDER BY FIELD(p.id, $placeholders)
            LIMIT ?
        ";
        $stmt = $this->pdo->prepare($query);
        $params = array_merge($ids, [$limit]);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** Check if SKU exists. @return bool */
    public function skuExists($sku, $excludeId = null) {
        if ($excludeId) {
            $stmt = $this->pdo->prepare("SELECT id FROM products WHERE sku = ? AND id != ?");
            $stmt->execute([$sku, $excludeId]);
        } else {
            $stmt = $this->pdo->prepare("SELECT id FROM products WHERE sku = ?");
            $stmt->execute([$sku]);
        }
        return (bool)$stmt->fetch();
    }

    /** Check if slug exists. @return bool */
    public function slugExists($slug, $excludeId = null) {
        if ($excludeId) {
            $stmt = $this->pdo->prepare("SELECT id FROM products WHERE slug = ? AND id != ?");
            $stmt->execute([$slug, $excludeId]);
        } else {
            $stmt = $this->pdo->prepare("SELECT id FROM products WHERE slug = ?");
            $stmt->execute([$slug]);
        }
        return (bool)$stmt->fetch();
    }

    /** Get main image filename. @return string|null */
    public function getMainImage($productId) {
        $stmt = $this->pdo->prepare("SELECT image_name FROM product_images WHERE product_id = ? ORDER BY is_main DESC, sort_order ASC, id ASC LIMIT 1");
        $stmt->execute([$productId]);
        return $stmt->fetchColumn() ?: null;
    }

    /** Get image matching a specific color. @return string|null */
    public function getImageByColor($productId, $color) {
        $stmt = $this->pdo->prepare("SELECT image_name FROM product_images WHERE product_id = ? ORDER BY (color = ?) DESC, is_main DESC LIMIT 1");
        $stmt->execute([$productId, $color]);
        return $stmt->fetchColumn() ?: null;
    }

    /** Delete a product, images, and variants. @return bool */
    public function deleteProduct($productId) {
        try {
            $this->pdo->beginTransaction();

            // 1. delete all image files from disk
            $images = $this->getImages($productId);
            foreach ($images as $img) {
                $filePath = __DIR__ . '/../products/' . $productId . '/img/' . $img['image_name'];
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }

            // remove the product image directory if it exists
            $imgDir = __DIR__ . '/../products/' . $productId . '/img';
            if (is_dir($imgDir)) {
                @rmdir($imgDir);
            }
            $productDir = __DIR__ . '/../products/' . $productId;
            if (is_dir($productDir)) {
                @rmdir($productDir);
            }

            // 2. delete image db records
            $stmt = $this->pdo->prepare("DELETE FROM product_images WHERE product_id = ?");
            $stmt->execute([$productId]);

            // 3. delete variant db records
            $stmt = $this->pdo->prepare("DELETE FROM product_variants WHERE product_id = ?");
            $stmt->execute([$productId]);

            // 4. delete the product itself
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

