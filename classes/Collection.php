<?php
require_once 'Database.php';

class Collection {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }

    public function getAll() {
        $stmt = $this->pdo->query("SELECT id, title, image_path, products_ids FROM collections ORDER BY id ASC");
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT id, title, image_path, products_ids FROM collections WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function getByTitle($title) {
        $stmt = $this->pdo->prepare("SELECT products_ids FROM collections WHERE title = ? LIMIT 1");
        $stmt->execute([$title]);
        return $stmt->fetchColumn() ?: null;
    }

    public function create($title, $imagePath, $productsIds) {
        $stmt = $this->pdo->prepare("INSERT INTO collections (title, image_path, products_ids) VALUES (?, ?, ?)");
        return $stmt->execute([$title, $imagePath, $productsIds]);
    }

    public function update($id, $title, $imagePath, $productsIds) {
        $stmt = $this->pdo->prepare("UPDATE collections SET title = ?, image_path = ?, products_ids = ? WHERE id = ?");
        return $stmt->execute([$title, $imagePath, $productsIds, $id]);
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM collections WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
