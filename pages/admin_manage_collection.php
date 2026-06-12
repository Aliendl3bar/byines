<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// admin auth guard
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

require_once '../classes/Collection.php';

$collectionModel = new Collection();

$action = $_POST['action'] ?? $_GET['action'] ?? '';

function uploadCollectionImage($fileInputName) {
    if (!isset($_FILES[$fileInputName]) || $_FILES[$fileInputName]['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $targetDir = __DIR__ . '/../collections_img/';
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    $fileName = $_FILES[$fileInputName]['name'];
    $fileTmpPath = $_FILES[$fileInputName]['tmp_name'];
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];

    if (in_array($fileExtension, $allowedExtensions)) {
        $newFileName = 'col_' . time() . '_' . rand(100, 999) . '.' . $fileExtension;
        $destPath = $targetDir . $newFileName;

        if (move_uploaded_file($fileTmpPath, $destPath)) {
            return '../collections_img/' . $newFileName;
        }
    }
    return null;
}

if ($action === 'add') {
    $title = trim($_POST['title'] ?? '');
    $productsIds = trim($_POST['products_ids'] ?? '');

    if (empty($title)) {
        $_SESSION['admin_error'] = "Collection title is required.";
        header("Location: admin_dashboard.php?tab=collections");
        exit;
    }

    $imagePath = uploadCollectionImage('image');
    if (!$imagePath) {
        $imagePath = '';
    }

    try {
        if ($collectionModel->create($title, $imagePath, $productsIds)) {
            $_SESSION['admin_success'] = "Collection '$title' created successfully.";
        } else {
            $_SESSION['admin_error'] = "Failed to create collection.";
        }
    } catch (Exception $e) {
        $_SESSION['admin_error'] = "Error: " . $e->getMessage();
    }

    header("Location: admin_dashboard.php?tab=collections");
    exit;
} elseif ($action === 'edit') {
    $id = (int)($_POST['id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $productsIds = trim($_POST['products_ids'] ?? '');

    if ($id <= 0 || empty($title)) {
        $_SESSION['admin_error'] = "Collection title is required.";
        header("Location: admin_dashboard.php?tab=collections");
        exit;
    }

    try {
        $existing = $collectionModel->getById($id);
        $imagePath = uploadCollectionImage('image');
        if (!$imagePath) {
            $imagePath = $existing['image_path'] ?? '';
        }

        if ($collectionModel->update($id, $title, $imagePath, $productsIds)) {
            $_SESSION['admin_success'] = "Collection updated successfully.";
        } else {
            $_SESSION['admin_error'] = "Failed to update collection.";
        }
    } catch (Exception $e) {
        $_SESSION['admin_error'] = "Error: " . $e->getMessage();
    }
    
    header("Location: admin_dashboard.php?tab=collections");
    exit;
} elseif ($action === 'delete') {
    $id = (int)($_GET['id'] ?? 0);

    if ($id > 0) {
        try {
            if ($collectionModel->delete($id)) {
                $_SESSION['admin_success'] = "Collection deleted successfully.";
            } else {
                $_SESSION['admin_error'] = "Failed to delete collection.";
            }
        } catch (Exception $e) {
             $_SESSION['admin_error'] = "Error: Cannot delete collection.";
        }
    }
    header("Location: admin_dashboard.php?tab=collections");
    exit;
} else {
    header("Location: admin_dashboard.php?tab=collections");
    exit;
}
