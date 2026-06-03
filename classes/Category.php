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
}
