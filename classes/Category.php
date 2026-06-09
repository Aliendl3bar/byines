<?php
require_once 'Database.php';

class Category {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }

    /**
     * Retrieve all categories from the database.
     * @return array
     */
    public function getAll() {
        $stmt = $this->pdo->query("SELECT id, name, slug, image_url FROM categories ORDER BY name ASC");
        return $stmt->fetchAll();
    }

    /**
     * Get details of a single category by its ID.
     * @return array|null
     */
    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT id, name, slug, image_url FROM categories WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Create a new category.
     * @return bool
     */
    public function create($name, $slug, $imageUrl) {
        $stmt = $this->pdo->prepare("INSERT INTO categories (name, slug, image_url) VALUES (?, ?, ?)");
        return $stmt->execute([$name, $slug, $imageUrl]);
    }

    /**
     * Update an existing category.
     * @return bool
     */
    public function update($id, $name, $slug, $imageUrl) {
        $stmt = $this->pdo->prepare("UPDATE categories SET name = ?, slug = ?, image_url = ? WHERE id = ?");
        return $stmt->execute([$name, $slug, $imageUrl, $id]);
    }

    /**
     * Delete a category by its ID.
     * @return bool
     */
    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM categories WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
